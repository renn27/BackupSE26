<!DOCTYPE html>
<html lang="id" class="h-full bg-se-soft">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Asisten SE2026') - {{ config('app.name', 'Asisten SE2026') }}</title>
    <meta name="theme-color" content="#f68b24">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="ASISTEN SE2026">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="ASISTEN SE2026">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="msapplication-TileColor" content="#f68b24">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/asisten-se2026-icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/asisten-se2026-icon-512.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/asisten-se2026-icon-192.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', 'Plus Jakarta Sans', 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    <!-- AlpineJS for interactive components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>
</head>
<body class="h-full font-sans antialiased text-se-ink">
    <div x-data="{ sidebarOpen: false }" class="min-h-full">
        {{-- Sidebar Mobile Overlay --}}
        <div x-show="sidebarOpen" x-transition.opacity x-cloak class="fixed inset-0 z-40 bg-se-ink/80 lg:hidden" @click="sidebarOpen = false"></div>

        {{-- Sidebar --}}
        @include('layouts.partials.sidebar')

        {{-- Main Content --}}
        <div class="flex min-h-screen flex-col lg:pl-72">
            @include('layouts.partials.topbar')

            <main class="flex-1 bg-se-soft pb-24 pt-4 sm:pt-6 lg:pb-8 lg:pt-8">
                <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
                    {{-- Flash Messages --}}
                    @if(session('success'))
                        <div x-data="{ show: true }" x-show="show" class="mb-6 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 p-4 shadow-sm transition-all">
                            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-green-800">Berhasil</h3>
                                <div class="mt-1 text-sm text-green-700">{{ session('success') }}</div>
                            </div>
                            <button @click="show = false" class="text-green-500 hover:text-green-600 focus:outline-none">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div x-data="{ show: true }" x-show="show" class="mb-6 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 shadow-sm transition-all">
                            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-rose-800">Error</h3>
                                <div class="mt-1 text-sm text-rose-700">{{ session('error') }}</div>
                            </div>
                            <button @click="show = false" class="text-rose-500 hover:text-rose-600 focus:outline-none">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>

            @if(!auth()->user()->isSuperAdmin())
                <nav class="fixed inset-x-0 bottom-0 z-40 rounded-t-3xl border-t border-slate-200 bg-white pb-[calc(env(safe-area-inset-bottom)+0.35rem)] pt-2 shadow-[0_-8px_28px_rgba(15,23,42,0.10)] lg:hidden" aria-label="Navigasi petugas">
                    <div class="mx-auto grid h-14 max-w-md grid-cols-3">
                        <a href="{{ route('petugas.dashboard') }}" class="group flex flex-col items-center justify-center gap-1 text-[11px] font-medium transition {{ request()->routeIs('petugas.dashboard') ? 'text-se-primary' : 'text-slate-500 hover:text-se-primary' }}">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                            </svg>
                            <span>Backup</span>
                        </a>

                        <a href="{{ route('petugas.monitoring-sbr.index') }}" class="group flex flex-col items-center justify-center gap-1 text-[11px] font-medium transition {{ request()->routeIs('petugas.monitoring-sbr.*') ? 'text-se-primary' : 'text-slate-500 hover:text-se-primary' }}">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M9 17v-6m4 6V7m4 10v-4M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <span>SBR</span>
                        </a>

                        <a href="{{ route('petugas.tanya-kondef') }}" class="group flex flex-col items-center justify-center gap-1 text-[11px] font-medium transition {{ request()->routeIs('petugas.tanya-kondef') ? 'text-se-primary' : 'text-slate-500 hover:text-se-primary' }}">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.84L3 20l1.33-3.1A7.45 7.45 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span>Tanya</span>
                        </a>
                    </div>
                </nav>
            @endif
        </div>
    </div>
    @stack('scripts')
</body>
</html>
