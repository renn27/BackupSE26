<?php
namespace App\Http\Controllers;

use App\Models\File;
use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Auth;

class FileProxyController extends Controller
{
    /**
     * Proxy download file — superadmin bisa download file siapapun
     * Petugas hanya bisa download miliknya sendiri
     */
    public function download(File $file, GoogleDriveService $driveService)
    {
        $user = Auth::user();

        // Otorisasi
        if (!$user->isSuperAdmin() && $file->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke file ini.');
        }

        abort_if($file->status !== 'uploaded', 404, 'File belum tersedia.');

        // Ambil file stream menggunakan token pemilik file
        $fileOwner = $file->user;
        $stream = $driveService->getFileStream($fileOwner, $file->drive_file_id);

        return response()->streamDownload(function () use ($stream) {
            echo $stream->getContents();
        }, $file->original_name, [
            'Content-Type'        => $file->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $file->original_name . '"',
        ]);
    }

    /**
     * Proxy view file — menampilkan file langsung di browser (inline)
     */
    public function view(File $file, GoogleDriveService $driveService)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && $file->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke file ini.');
        }

        abort_if($file->status !== 'uploaded', 404, 'File belum tersedia.');

        $fileOwner = $file->user;
        $stream = $driveService->getFileStream($fileOwner, $file->drive_file_id);

        return response()->stream(function () use ($stream) {
            echo $stream->getContents();
        }, 200, [
            'Content-Type'        => $file->mime_type,
            'Content-Disposition' => 'inline; filename="' . $file->original_name . '"',
        ]);
    }
}
