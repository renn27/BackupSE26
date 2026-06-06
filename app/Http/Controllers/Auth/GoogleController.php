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
    public function redirect(Request $request)
    {
        $prompt = $request->boolean('consent')
            ? 'consent'
            : config('google.prompt');

        $oauthOptions = [
            'access_type'            => config('google.access_type', 'offline'),
            'include_granted_scopes' => 'true',
        ];

        if ($prompt) {
            $oauthOptions['prompt'] = $prompt;
        }

        if ($request->filled('email')) {
            $oauthOptions['login_hint'] = $request->input('email');
        }

        $driver = Socialite::driver('google')
            ->scopes(config('google.scopes'))
            ->with($oauthOptions);
            
        // Bypass SSL di lokal (Windows) untuk mencegah cURL error 60
        if (app()->environment('local')) {
            $driver->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
        }

        return $driver->redirect();
    }

    public function callback(Request $request, GoogleDriveService $driveService)
    {
        // Antisipasi jika user mengklik "Batal" atau terjadi error dari Google OAuth
        if ($request->has('error')) {
            $errorMsg = $request->get('error_description') ?: ($request->get('error') === 'access_denied' ? 'Akses dibatalkan oleh pengguna.' : $request->get('error'));
            return redirect()->route('login')
                ->with('error', 'Login Google dibatalkan. Alasan: ' . $errorMsg);
        }

        if (!$request->has('code')) {
            return redirect()->route('login')
                ->with('error', 'Login Google gagal: Parameter code tidak ditemukan.');
        }

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

        $refreshToken = $googleUser->refreshToken;

        if (!$user) {
            if (!$refreshToken) {
                if (! $request->session()->pull('google_consent_retry', false)) {
                    $request->session()->put('google_consent_retry', true);

                    return redirect()->route('auth.google', [
                        'consent' => 1,
                        'email' => $googleUser->getEmail()
                    ])->with('error', 'Google belum mengirim izin Drive. Silakan setujui akses Google Drive.');
                }

                return redirect()->route('login', ['consent' => 1])
                    ->with('error', 'Google belum memberikan refresh token Drive. Klik Masuk dengan Google dan setujui akses Drive.');
            }

            // Auto-register petugas baru
            $user = User::create([
                'name'                  => $googleUser->getName(),
                'email'                 => $googleUser->getEmail(),
                'google_id'             => $googleUser->getId(),
                'avatar'                => $googleUser->getAvatar(),
                'google_refresh_token'  => $refreshToken,
                'role'                  => $this->isSuperAdminEmail($googleUser->getEmail())
                                            ? 'superadmin' : 'petugas',
                'status'                => 'active',
            ]);

            // Buat folder di Drive user secara async
            dispatch(new \App\Jobs\CreateUserDriveFolder($user));
        } else {
            if (!$user->google_refresh_token && !$refreshToken) {
                if (! $request->session()->pull('google_consent_retry', false)) {
                    $request->session()->put('google_consent_retry', true);

                    return redirect()->route('auth.google', [
                        'consent' => 1,
                        'email' => $googleUser->getEmail()
                    ])->with('error', 'Google belum mengirim izin Drive. Silakan setujui akses Google Drive.');
                }

                return redirect()->route('login', ['consent' => 1])
                    ->with('error', 'Google belum memberikan refresh token Drive. Klik Masuk dengan Google dan setujui akses Drive.');
            }

            // Update token jika ada yang baru (refresh token bisa berubah)
            $updateData = [
                'name'   => $googleUser->getName(),
                'avatar' => $googleUser->getAvatar(),
            ];
            if ($refreshToken) {
                $updateData['google_refresh_token'] = $refreshToken;
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
