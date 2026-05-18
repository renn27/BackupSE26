<?php
namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $storageLimit = 15 * 1024 * 1024 * 1024; // 15GB
        $storageUsed = $user->files()->where('status', 'uploaded')->sum('size_bytes');
        $storagePercent = $storageLimit > 0 ? min(100, ($storageUsed / $storageLimit) * 100) : 0;

        $stats = [
            'total_photos'   => $user->files()->where('type', 'photo')->where('status', 'uploaded')->count(),
            'total_backups'  => $user->files()->where('type', 'backup')->where('status', 'uploaded')->count(),
            'storage_used'   => $storageUsed,
            'storage_limit'  => $storageLimit,
            'storage_percent' => $storagePercent,
            'uploading'      => $user->files()->where('status', 'uploading')->count(),
        ];

        $recentPhotos = $user->files()
            ->where('type', 'photo')
            ->where('status', 'uploaded')
            ->latest()
            ->take(6)
            ->get();

        $recentBackups = $user->files()
            ->where('type', 'backup')
            ->where('status', 'uploaded')
            ->latest()
            ->take(5)
            ->get();

        return view('petugas.dashboard', compact('stats', 'recentPhotos', 'recentBackups'));
    }
}
