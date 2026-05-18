<?php
namespace App\Jobs;

use App\Models\File;
use App\Models\User;
use App\Services\GoogleDriveService;
use App\Services\ImageCompressionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UploadFileToDrive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;
    public int $backoff = 30; // detik antar retry

    public function __construct(
        private File $file,
        private User $user,
        private string $tempPath,  // path sementara di server
    ) {}

    public function handle(GoogleDriveService $driveService, ImageCompressionService $compressor): void
    {
        @set_time_limit(0);

        try {
            $pathToUpload = $this->tempPath;
            $compressedSize = null;

            // Kompresi jika foto
            if ($this->file->type === 'photo') {
                $ext = pathinfo($this->tempPath, PATHINFO_EXTENSION);
                $compressedPath = str_replace('.' . $ext, '_compressed.' . $ext, $this->tempPath);
                $compressionResult = $compressor->compress($this->tempPath, $compressedPath);
                $pathToUpload = $compressedPath;
                $compressedSize = $compressionResult['compressed_size'];
            }

            // Upload ke Drive
            $result = $driveService->uploadFile(
                $this->user,
                $pathToUpload,
                $this->file->stored_name,
                $this->file->mime_type,
                $this->file->type,
            );

            // Update metadata file di database
            $this->file->update([
                'drive_file_id'          => $result['drive_file_id'],
                'drive_folder_id'        => $result['drive_folder_id'],
                'drive_web_view_link'    => $result['web_view_link'],
                'compressed_size_bytes'  => $compressedSize,
                'status'                 => 'uploaded',
            ]);

            Log::info("File {$this->file->id} berhasil diupload ke Drive user {$this->user->id}");

        } catch (\Throwable $e) {
            $this->file->update([
                'status'       => 'failed',
                'upload_error' => $e->getMessage(),
            ]);
            Log::error("Gagal upload file {$this->file->id}: " . $e->getMessage(), [
                'exception' => get_class($e),
            ]);
            throw $e; // Trigger retry
        } finally {
            // Hapus file sementara dari server
            @unlink($this->tempPath);
            if (isset($compressedPath)) @unlink($compressedPath);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->file->update([
            'status'       => 'failed',
            'upload_error' => 'Gagal setelah ' . $this->tries . ' percobaan: ' . $exception->getMessage(),
        ]);
    }
}
