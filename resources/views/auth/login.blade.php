<!DOCTYPE html>
<html lang="id" class="h-full bg-se-soft">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name', 'Asisten SE2026') }}</title>
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Outfit', 'Inter', sans-serif; }</style>
</head>
<body class="flex min-h-full items-center justify-center bg-se-soft p-4 text-se-ink">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <div class="mx-auto mb-5 flex w-max items-center gap-3 rounded-xl border border-se-line bg-white p-3 shadow-sm">
                <img src="{{ asset('images/logo-bps.svg') }}" alt="Logo BPS" class="h-12 w-12 object-contain">
                <div class="h-10 w-px bg-se-line"></div>
                <img src="{{ asset('images/logo-se2026-small.png') }}" alt="Sensus Ekonomi 2026" class="h-14 w-10 object-contain">
            </div>
            <h1 class="text-2xl font-semibold text-se-ink">{{ config('app.name', 'Asisten SE2026') }}</h1>
            <p class="mt-2 text-sm font-medium text-se-primary">Sistem Manajemen File Sensus Ekonomi 2026</p>
        </div>

        <div class="rounded-2xl border border-se-line bg-white p-8 shadow-xl shadow-amber-900/5">
            @if(session('error'))
                <div class="mb-6 flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4">
                    <svg class="h-5 w-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm font-medium text-rose-700">{{ session('error') }}</p>
                </div>
            @endif

            <div class="mb-6 text-center">
                <h2 class="text-lg font-semibold text-se-ink">Masuk ke Akun Anda</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Gunakan Gmail khusus yang telah didaftarkan
                </p>
            </div>

            <a href="{{ route('auth.google') }}"
               class="group flex w-full items-center justify-center gap-3 rounded-xl border-2 border-se-line px-4 py-3 font-medium text-se-ink transition-all duration-200 hover:border-amber-300 hover:bg-amber-50 hover:text-se-primary focus:outline-none focus:ring-4 focus:ring-amber-500/20">
                <svg class="h-5 w-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <span>Masuk dengan Google</span>
            </a>

            <div class="mt-8 border-t border-se-line pt-6">
                <div class="flex items-start gap-3 rounded-lg border border-amber-100 bg-amber-50 p-3 text-xs text-se-muted">
                    <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-se-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>File tersimpan aman di Google Drive pribadi. Sistem hanya menyimpan metadata file.</span>
                </div>
            </div>
        </div>

        <p class="mt-8 text-center text-xs font-medium text-slate-400">
            &copy; {{ date('Y') }} {{ config('app.name', 'Asisten SE2026') }}. Hak Cipta Dilindungi.
        </p>
    </div>
</body>
</html>
