<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureActiveAndRolePetugas
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->role !== 'petugas' && $request->user()->role !== 'superadmin') {
            abort(403);
        }
        if ($request->user()->status !== 'active') {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun tidak aktif.');
        }
        return $next($request);
    }
}
