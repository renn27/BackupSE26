<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        $driver = Socialite::driver('google')
            ->scopes(config('google.scopes'))
            ->with([
                'access_type'             => config('google.access_type', 'offline'),
                'prompt'                  => config('google.prompt', 'consent'),
                'include_granted_scopes'  => 'true',
            ]);
            
        // Bypass SSL di lokal (Windows) untuk mencegah cURL error 60
        if (app()->environment('local')) {
            $driver->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
        }

        return $driver->redirect();
    }

    public function callback(Request $request, GoogleDriveService $driveService)
    {
        try {
            $driver = Socialite::driver('google');
            if (app()->environment('local')) {
                $driver->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
            }
            $googleUser = $driver->user();
        } catch (\Exception $e) {
            \Log::error('Google Login Error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Login Google gagal. Alasan: ' . $e->getMessage());
        }

        // Cek apakah email sudah terdaftar
        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            // Auto-register petugas baru
            $user = User::create([
                'name'                  => $googleUser->getName(),
                'email'                 => $googleUser->getEmail(),
                'google_id'             => $googleUser->getId(),
                'avatar'                => $googleUser->getAvatar(),
                'google_refresh_token'  => $googleUser->refreshToken,
                'role'                  => $this->isSuperAdminEmail($googleUser->getEmail())
                                            ? 'superadmin' : 'petugas',
                'status'                => 'active',
            ]);

            // Buat folder di Drive user secara async
            dispatch(new \App\Jobs\CreateUserDriveFolder($user));
        } else {
            // Update token jika ada yang baru (refresh token bisa berubah)
            $updateData = [
                'name'   => $googleUser->getName(),
                'avatar' => $googleUser->getAvatar(),
            ];
            if ($googleUser->refreshToken) {
                $updateData['google_refresh_token'] = $googleUser->refreshToken;
            }
            $user->update($updateData);
        }

        // Cek status akun
        if (!$user->isActive()) {
            return redirect()->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Hubungi superadmin.');
        }

        // Update info login terakhir
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Log aktivitas
        ActivityLog::create([
            'user_id'    => $user->id,
            'action'     => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        Auth::login($user);

        return redirect()->intended(
            $user->isSuperAdmin()
                ? route('admin.dashboard')
                : route('petugas.dashboard')
        );
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action'  => 'logout',
                'ip_address' => $request->ip(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function isSuperAdminEmail(string $email): bool
    {
        return $email === config('app.superadmin_email', env('SUPERADMIN_EMAIL'));
    }
}
