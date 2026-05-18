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
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $query = File::where('user_id', Auth::id())
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->search, fn($q, $s) => $q->where('original_name', 'like', "%{$s}%"))
            ->latest();

        $files = $query->paginate(20)->withQueryString();

        return view('petugas.files.index', compact('files'));
    }

    public function uploadPhoto(UploadPhotoRequest $request)
    {
        $uploadedFile = $request->file('photo');
        $user = Auth::user();

        // Ambil info file sebelum dipindah
        $mimeType = $uploadedFile->getMimeType();
        $sizeBytes = $uploadedFile->getSize();
        $originalName = $uploadedFile->getClientOriginalName();
        $storedName = $this->generateStoredName($originalName);

        // Simpan sementara di server
        $tempName = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
        $tempPath = storage_path('app/temp/' . $tempName);
        $uploadedFile->move(storage_path('app/temp'), $tempName);

        // Buat record file di DB dengan status 'uploading'
        $file = File::create([
            'user_id'       => $user->id,
            'drive_file_id' => '',
            'drive_folder_id' => '',
            'original_name' => $originalName,
            'stored_name'   => $storedName,
            'type'          => 'photo',
            'mime_type'     => $mimeType,
            'size_bytes'    => $sizeBytes,
            'status'        => 'uploading',
            'description'   => $request->description,
            'category'      => $request->category,
        ]);

        // Dispatch job upload ke Drive (background)
        UploadFileToDrive::dispatch($file, $user, $tempPath);

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
        $originalName = $uploadedFile->getClientOriginalName();
        $storedName = $this->generateStoredName($originalName);

        $tempName = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
        $tempPath = storage_path('app/temp/' . $tempName);
        $uploadedFile->move(storage_path('app/temp'), $tempName);

        $file = File::create([
            'user_id'       => $user->id,
            'drive_file_id' => '',
            'drive_folder_id' => '',
            'original_name' => $originalName,
            'stored_name'   => $storedName,
            'type'          => 'backup',
            'mime_type'     => $mimeType,
            'size_bytes'    => $sizeBytes,
            'status'        => 'uploading',
            'description'   => $request->description,
            'category'      => $request->category,
        ]);

        UploadFileToDrive::dispatch($file, $user, $tempPath);

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
}
