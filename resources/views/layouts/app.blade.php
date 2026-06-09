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
        @media (max-width: 639.98px) {
            .file-tab-inactive span {
                display: none !important;
            }
        }
    </style>
    <!-- AlpineJS for interactive components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.webPushRoutes = {
            config: @js(route('web-push.config')),
            subscribe: @js(route('web-push.subscriptions.store')),
            unsubscribe: @js(route('web-push.subscriptions.destroy')),
            test: @js(route('web-push.test')),
        };

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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 4.5c5 0 8.5 3.05 8.5 7.15S17 19 12 19a10.4 10.4 0 01-3.6-.63L4 19.5l1.25-3.25a6.85 6.85 0 01-1.75-4.6C3.5 7.55 7 4.5 12 4.5z"></path>
                                <circle cx="8.7" cy="11.75" r="0.8" fill="currentColor" stroke="none"></circle>
                                <circle cx="12" cy="11.75" r="0.8" fill="currentColor" stroke="none"></circle>
                                <circle cx="15.3" cy="11.75" r="0.8" fill="currentColor" stroke="none"></circle>
                            </svg>
                            <span>Tanya</span>
                        </a>
                    </div>
                </nav>
            @endif
        </div>
    </div>

    @if(auth()->check() && !auth()->user()->isSuperAdmin() && ($welcomeType = session()->pull('show_welcome_modal')))
        <div
            x-data="{ open: true }"
            x-show="open"
            x-transition.opacity.duration.300ms
            class="fixed inset-0 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-[4px]"
            style="z-index: 9999;"
            @keydown.escape.window="open = false"
        >
            <!-- Card -->
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="relative w-full max-w-md overflow-hidden rounded-3xl border border-amber-100 bg-white p-6 text-center shadow-[0_24px_80px_rgba(249,115,22,0.15)] sm:p-8"
                @click.outside="open = false"
            >
                <!-- Background Glow -->
                <div class="absolute -top-12 -left-12 h-40 w-40 rounded-full bg-orange-100/50 blur-3xl"></div>
                <div class="absolute -bottom-12 -right-12 h-40 w-40 rounded-full bg-amber-100/50 blur-3xl"></div>

                <div class="relative z-10 flex flex-col items-center">
                    <!-- Logo -->
                    <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-2xl bg-orange-50/50 p-3 ring-1 ring-orange-100/50">
                        <img src="{{ asset('images/logo-se2026-ribbon-only-v3.png') }}" class="h-full w-full object-contain" alt="Logo SE2026">
                    </div>

                    <!-- Badge/Greeting Tag -->
                    <span class="mb-3 inline-flex items-center rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700 ring-1 ring-inset ring-orange-600/10">
                        Mitra Kerja SE2026
                    </span>

                    <!-- Message -->
                    <h3 class="mb-4 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        @if($welcomeType === 'first_time')
                            Selamat Bergabung
                        @else
                            Selamat Datang Kembali!
                        @endif
                    </h3>
                    
                    <p class="mb-8 text-sm leading-relaxed text-slate-600 sm:text-base">
                        @if($welcomeType === 'first_time')
                            Halo <span class="font-bold text-slate-900">{{ auth()->user()->name }}</span>, Sebagai Mitra SE2026. Mari kita Sukseskan SE2026. Semangat !!
                        @else
                            Halo <span class="font-bold text-slate-900">{{ auth()->user()->name }}</span>, Mitra SE2026. Mari lanjutkan kerja kita, Semangat !!
                        @endif
                    </p>

                    <!-- Action Button -->
                    <button
                        type="button"
                        @click="open = false"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-se-primary px-8 py-3 mt-4 text-sm font-bold text-white shadow-md shadow-se-primary/20 transition-all hover:bg-se-rust active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-se-primary focus:ring-offset-2"
                        style="background-color: #f68b24;"
                    >
                        Siap!
                    </button>
                </div>
            </div>
        </div>
    @endif

    @stack('scripts')
</body>
</html>
