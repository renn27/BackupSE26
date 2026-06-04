<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserDeletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'petugas');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['files' => function ($query) {
            $query->latest()->take(10);
        }, 'activityLogs' => function ($query) {
            $query->latest()->take(10);
        }]);

        return view('admin.users.show', compact('user'));
    }

    public function toggleStatus(User $user)
    {
        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        return back()->with('success', "Status user {$user->name} berhasil diubah menjadi {$newStatus}.");
    }

    public function destroy(User $user, UserDeletionService $userDeletionService)
    {
        abort_if($user->isSuperAdmin(), 403, 'Akun superadmin tidak boleh dihapus dari halaman ini.');

        try {
            $userDeletionService->deletePermanently($user);
        } catch (\Throwable $e) {
            Log::error("Gagal menghapus user {$user->id} secara permanen: " . $e->getMessage(), [
                'exception' => get_class($e),
            ]);

            return redirect()
                ->route('admin.users.index')
                ->with('error', 'User belum dihapus karena proses hapus file/data terkait gagal. Coba lagi atau cek koneksi Google Drive user.');
        }

        return redirect()->route('admin.users.index')->with('success', 'User dan seluruh data terkait berhasil dihapus permanen.');
    }
}
