<?php
namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadPhotoRequest;
use App\Http\Requests\UploadBackupRequest;
use App\Jobs\UploadFileToDrive;
use App\Models\File;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('petugas.dashboard', $request->query());
    }

    public function uploadPhoto(UploadPhotoRequest $request)
    {
        $uploadedFile = $request->file('photo');
        $user = Auth::user();

        // Ambil info file sebelum dipindah
        $mimeType = $uploadedFile->getMimeType();
        $sizeBytes = $uploadedFile->getSize();
        $sourceName = $uploadedFile->getClientOriginalName();
        $displayName = $this->resolveDisplayName($sourceName, $request->rename);
        $storedName = $request->filled('rename')
            ? $displayName
            : $this->generateStoredName($sourceName);

        // Simpan sementara di server
        $tempName = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
        $tempPath = storage_path('app/temp/' . $tempName);
        $this->ensureTempDirectoryExists();
        $uploadedFile->move(storage_path('app/temp'), $tempName);

        // Buat record file di DB dengan status 'uploading'
        $file = File::create([
            'user_id'       => $user->id,
            'drive_file_id' => '',
            'drive_folder_id' => '',
            'original_name' => $displayName,
            'stored_name'   => $storedName,
            'type'          => 'photo',
            'mime_type'     => $mimeType,
            'size_bytes'    => $sizeBytes,
            'status'        => 'uploading',
        ]);

        Log::info("Mulai upload foto ke Drive", ['file_id' => $file->id, 'temp_path' => $tempPath]);
        // Jalankan langsung agar upload ke Drive tidak bergantung pada worker/config queue server.
        UploadFileToDrive::dispatchSync($file, $user, $tempPath);
        Log::info("Selesai dispatch upload foto ke Drive", ['file_id' => $file->id]);

        // Log aktivitas
        ActivityLog::create([
            'user_id'      => $user->id,
            'action'       => 'upload_photo',
            'subject_type' => File::class,
            'subject_id'   => $file->id,
            'properties'   => ['filename' => $file->original_name],
            'ip_address'   => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Upload sedang diproses. File akan tersedia dalam beberapa saat.',
            'file_id' => $file->id,
        ]);
    }

    public function uploadBackup(UploadBackupRequest $request)
    {
        // Sama dengan uploadPhoto tapi type = 'backup'
        $uploadedFile = $request->file('backup');
        $user = Auth::user();

        $mimeType = $uploadedFile->getMimeType();
        $sizeBytes = $uploadedFile->getSize();
        $sourceName = $uploadedFile->getClientOriginalName();
        $displayName = $this->resolveDisplayName($sourceName, $request->rename);
        $storedName = $request->filled('rename')
            ? $displayName
            : $this->generateStoredName($sourceName);

        $tempName = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
        $tempPath = storage_path('app/temp/' . $tempName);
        $this->ensureTempDirectoryExists();
        $uploadedFile->move(storage_path('app/temp'), $tempName);

        $file = File::create([
            'user_id'       => $user->id,
            'drive_file_id' => '',
            'drive_folder_id' => '',
            'original_name' => $displayName,
            'stored_name'   => $storedName,
            'type'          => 'backup',
            'mime_type'     => $mimeType,
            'size_bytes'    => $sizeBytes,
            'status'        => 'uploading',
        ]);

        Log::info("Mulai upload backup ke Drive", ['file_id' => $file->id, 'temp_path' => $tempPath]);
        UploadFileToDrive::dispatchSync($file, $user, $tempPath);
        Log::info("Selesai dispatch upload backup ke Drive", ['file_id' => $file->id]);

        ActivityLog::create([
            'user_id'      => $user->id,
            'action'       => 'upload_backup',
            'subject_type' => File::class,
            'subject_id'   => $file->id,
            'properties'   => ['filename' => $file->original_name],
            'ip_address'   => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File backup sedang diproses.',
            'file_id' => $file->id,
        ]);
    }

    public function checkStatus(File $file)
    {
        // Pastikan user hanya bisa cek status miliknya
        abort_if($file->user_id !== Auth::id(), 403);

        if ($file->status === 'uploading' && $file->created_at->lt(now()->subMinutes(15))) {
            $file->update([
                'status' => 'failed',
                'upload_error' => 'Upload berhenti terlalu lama di server. Coba unggah ulang atau cek konfigurasi queue/timeout hosting.',
            ]);
            $file->refresh();
        }

        return response()->json([
            'status'       => $file->status,
            'drive_file_id' => $file->status === 'uploaded' ? $file->drive_file_id : null,
            'upload_error' => $file->status === 'failed' ? $file->upload_error : null,
        ]);
    }

    public function destroy(File $file)
    {
        abort_if($file->user_id !== Auth::id(), 403);
        abort_if($file->status === 'uploading', 422, 'File sedang diproses, tunggu selesai.');

        \App\Jobs\DeleteFileFromDrive::dispatch($file, Auth::user());

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'action'       => 'delete_file',
            'subject_type' => File::class,
            'subject_id'   => $file->id,
            'properties'   => ['filename' => $file->original_name],
            'ip_address'   => request()->ip(),
        ]);

        $file->update(['status' => 'deleted']);
        $file->delete();

        return response()->json(['success' => true, 'message' => 'File berhasil dihapus.']);
    }

    private function generateStoredName(string $originalName): string
    {
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
        $slug = Str::slug($nameWithoutExt);
        return $slug . '_' . now()->format('Ymd_His') . '_' . Str::random(6) . '.' . $ext;
    }

    private function resolveDisplayName(string $originalName, ?string $rename): string
    {
        if (! filled($rename)) {
            return $originalName;
        }

        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $nameWithoutExtension = trim($rename);

        if ($extension && preg_match('/\.' . preg_quote($extension, '/') . '$/i', $nameWithoutExtension)) {
            $nameWithoutExtension = substr($nameWithoutExtension, 0, -strlen($extension) - 1);
        }

        $sanitizedName = trim(preg_replace('/[\\\\\/:*?"<>|\x00-\x1F]+/', '-', $nameWithoutExtension), " .-_");

        if ($sanitizedName === '') {
            $sanitizedName = pathinfo($originalName, PATHINFO_FILENAME);
        }

        return $extension
            ? "{$sanitizedName}.{$extension}"
            : $sanitizedName;
    }

    private function ensureTempDirectoryExists(): void
    {
        $tempDirectory = storage_path('app/temp');

        if (! is_dir($tempDirectory)) {
            mkdir($tempDirectory, 0755, true);
        }
    }
}
