<?php
namespace App\Jobs;

use App\Models\File;
use App\Models\User;
use App\Services\GoogleDriveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteFileFromDrive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;
    public int $backoff = 30;

    public function __construct(
        private File $file,
        private User $user
    ) {}

    public function handle(GoogleDriveService $driveService): void
    {
        if ($this->file->drive_file_id) {
            $deleted = $driveService->deleteFile($this->user, $this->file->drive_file_id);
            if ($deleted) {
                Log::info("File {$this->file->id} berhasil dihapus dari Drive user {$this->user->id}");
            } else {
                throw new \Exception("Gagal menghapus file {$this->file->id} dari Drive");
            }
        }
    }
}
