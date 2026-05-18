<!DOCTYPE html>
<html lang="id" class="h-full bg-se-soft">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BackupSE26') - {{ config('app.name', 'BackupSE26') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    <!-- AlpineJS for interactive components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 px-3 pb-[calc(env(safe-area-inset-bottom)+0.5rem)] pt-2 shadow-[0_-8px_24px_rgba(15,23,42,0.08)] backdrop-blur lg:hidden" aria-label="Navigasi petugas">
                    <div class="mx-auto grid max-w-md grid-cols-2 gap-2">
                        <a href="{{ route('petugas.dashboard') }}" class="flex min-h-[3.25rem] flex-col items-center justify-center gap-1 rounded-2xl px-3 text-xs font-bold transition {{ request()->routeIs('petugas.dashboard') ? 'bg-se-subtle text-se-rust ring-1 ring-amber-200/70' : 'text-slate-500 hover:bg-slate-50 hover:text-se-rust' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Dashboard
                        </a>
                        <a href="{{ route('petugas.files.index') }}" class="flex min-h-[3.25rem] flex-col items-center justify-center gap-1 rounded-2xl px-3 text-xs font-bold transition {{ request()->routeIs('petugas.files.*') ? 'bg-se-subtle text-se-rust ring-1 ring-amber-200/70' : 'text-slate-500 hover:bg-slate-50 hover:text-se-rust' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                            </svg>
                            File Saya
                        </a>
                    </div>
                </nav>
            @endif
        </div>
    </div>
    @stack('scripts')
</body>
</html>
