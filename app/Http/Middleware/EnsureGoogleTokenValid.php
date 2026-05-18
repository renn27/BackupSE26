<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureGoogleTokenValid
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if ($user && !$user->google_refresh_token) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Sesi Google Anda telah berakhir. Silakan login ulang.');
        }
        return $next($request);
    }
}
