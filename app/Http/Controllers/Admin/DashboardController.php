<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'        => User::where('role', 'petugas')->count(),
            'active_users'       => User::where('role', 'petugas')->where('status', 'active')->count(),
            'total_files'        => File::count(),
            'total_photos'       => File::where('type', 'photo')->where('status', 'uploaded')->count(),
            'total_backups'      => File::where('type', 'backup')->where('status', 'uploaded')->count(),
            'uploading_files'    => File::where('status', 'uploading')->count(),
            'failed_files'       => File::where('status', 'failed')->count(),
            'total_size_bytes'   => File::where('status', 'uploaded')->sum('size_bytes'),
        ];

        $recentFiles = File::with('user')
            ->where('status', 'uploaded')
            ->latest()
            ->take(10)
            ->get();

        $recentActivity = ActivityLog::with('user')
            ->latest()
            ->take(5)
            ->get();

        // Upload activity 7 hari terakhir (untuk chart)
        // Group by DATE format
        $uploadChart = File::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentFiles', 'recentActivity', 'uploadChart'));
    }

    public function activities()
    {
        $activities = ActivityLog::with('user')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.activities.index', compact('activities'));
    }
}
