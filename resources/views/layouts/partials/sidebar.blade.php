<div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" x-cloak class="fixed inset-y-0 left-0 z-50 flex w-[min(20rem,calc(100vw-2rem))] transform flex-col border-r border-slate-200 bg-white shadow-2xl shadow-slate-950/5 transition-transform duration-300 ease-out lg:w-72 lg:translate-x-0 lg:shadow-none">
    <div class="flex h-16 items-center justify-between gap-3 border-b border-slate-200 px-4 sm:h-[68px] sm:px-5">
        <div class="flex min-w-0 items-center gap-2.5 sm:gap-3">
            <div class="flex h-10 shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-2.5 shadow-sm shadow-slate-950/5 sm:h-12 sm:gap-2.5 sm:px-3">
                <img src="{{ asset('images/logo-bps.svg') }}" alt="Logo BPS" class="h-7 w-7 object-contain sm:h-9 sm:w-9">
                <span class="h-6 w-px bg-slate-200 sm:h-7"></span>
                <img src="{{ asset('images/logo-se2026-small.png') }}" alt="Logo Sensus Ekonomi 2026" class="h-8 w-6 object-contain sm:h-10 sm:w-8">
            </div>
            <div class="min-w-0">
                <p class="truncate text-sm font-medium text-se-ink sm:text-base sm:font-semibold sm:tracking-tight">{{ config('app.name', 'Asisten SE2026') }}</p>
                <p class="mt-0.5 hidden truncate text-xs font-medium text-se-rust min-[360px]:block">Sensus Ekonomi 2026</p>
            </div>
        </div>
        <button @click="sidebarOpen = false" class="rounded-lg p-2 text-slate-400 transition hover:bg-se-subtle hover:text-se-rust lg:hidden">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-4 py-5">
        @if(auth()->user()->isSuperAdmin())
            <div class="mb-4 px-2">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Menu</p>
            </div>
            <div class="space-y-1.5">
                <a href="{{ route('admin.dashboard') }}" class="group relative flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold transition duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-se-subtle text-se-rust ring-1 ring-amber-200/70' : 'text-slate-600 hover:bg-slate-50 hover:text-se-rust' }}">
                    <span class="{{ request()->routeIs('admin.dashboard') ? 'opacity-100' : 'opacity-0' }} absolute left-0 top-3 bottom-3 w-1 rounded-r-full bg-se-primary transition"></span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-se-primary text-white shadow-sm shadow-se-primary/25' : 'border border-slate-200 bg-white text-se-rust group-hover:border-amber-200 group-hover:bg-se-subtle' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1 truncate">Dashboard</span>
                </a>

                <a href="{{ route('admin.users.index') }}" class="group relative flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold transition duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-se-subtle text-se-rust ring-1 ring-amber-200/70' : 'text-slate-600 hover:bg-slate-50 hover:text-se-rust' }}">
                    <span class="{{ request()->routeIs('admin.users.*') ? 'opacity-100' : 'opacity-0' }} absolute left-0 top-3 bottom-3 w-1 rounded-r-full bg-se-primary transition"></span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-se-primary text-white shadow-sm shadow-se-primary/25' : 'border border-slate-200 bg-white text-se-rust group-hover:border-amber-200 group-hover:bg-se-subtle' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1 truncate">Manajemen User</span>
                </a>

                <a href="{{ route('admin.files.index') }}" class="group relative flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold transition duration-200 {{ request()->routeIs('admin.files.*') ? 'bg-se-subtle text-se-rust ring-1 ring-amber-200/70' : 'text-slate-600 hover:bg-slate-50 hover:text-se-rust' }}">
                    <span class="{{ request()->routeIs('admin.files.*') ? 'opacity-100' : 'opacity-0' }} absolute left-0 top-3 bottom-3 w-1 rounded-r-full bg-se-primary transition"></span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl transition {{ request()->routeIs('admin.files.*') ? 'bg-se-primary text-white shadow-sm shadow-se-primary/25' : 'border border-slate-200 bg-white text-se-rust group-hover:border-amber-200 group-hover:bg-se-subtle' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1 truncate">Semua File</span>
                </a>

                <a href="{{ route('admin.monitoring-sbr.index') }}" class="group relative flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold transition duration-200 {{ request()->routeIs('admin.monitoring-sbr.*') ? 'bg-se-subtle text-se-rust ring-1 ring-amber-200/70' : 'text-slate-600 hover:bg-slate-50 hover:text-se-rust' }}">
                    <span class="{{ request()->routeIs('admin.monitoring-sbr.*') ? 'opacity-100' : 'opacity-0' }} absolute left-0 top-3 bottom-3 w-1 rounded-r-full bg-se-primary transition"></span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl transition {{ request()->routeIs('admin.monitoring-sbr.*') ? 'bg-se-primary text-white shadow-sm shadow-se-primary/25' : 'border border-slate-200 bg-white text-se-rust group-hover:border-amber-200 group-hover:bg-se-subtle' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-4M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1 truncate">Monitoring SBR</span>
                </a>
            </div>
        @else
            <div class="mb-4 px-2">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Menu</p>
            </div>
            <div class="space-y-1.5">
                <a href="{{ route('petugas.dashboard') }}" class="group relative flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold transition duration-200 {{ request()->routeIs('petugas.dashboard') ? 'bg-se-subtle text-se-rust ring-1 ring-amber-200/70' : 'text-slate-600 hover:bg-slate-50 hover:text-se-rust' }}">
                    <span class="{{ request()->routeIs('petugas.dashboard') ? 'opacity-100' : 'opacity-0' }} absolute left-0 top-3 bottom-3 w-1 rounded-r-full bg-se-primary transition"></span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl transition {{ request()->routeIs('petugas.dashboard') ? 'bg-se-primary text-white shadow-sm shadow-se-primary/25' : 'border border-slate-200 bg-white text-se-rust group-hover:border-amber-200 group-hover:bg-se-subtle' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1 truncate">Backup</span>
                </a>

                <a href="{{ route('petugas.monitoring-sbr.index') }}" class="group relative flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold transition duration-200 {{ request()->routeIs('petugas.monitoring-sbr.*') ? 'bg-se-subtle text-se-rust ring-1 ring-amber-200/70' : 'text-slate-600 hover:bg-slate-50 hover:text-se-rust' }}">
                    <span class="{{ request()->routeIs('petugas.monitoring-sbr.*') ? 'opacity-100' : 'opacity-0' }} absolute left-0 top-3 bottom-3 w-1 rounded-r-full bg-se-primary transition"></span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl transition {{ request()->routeIs('petugas.monitoring-sbr.*') ? 'bg-se-primary text-white shadow-sm shadow-se-primary/25' : 'border border-slate-200 bg-white text-se-rust group-hover:border-amber-200 group-hover:bg-se-subtle' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-4M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1 truncate">Monitoring SBR</span>
                </a>

                <a href="{{ route('petugas.tanya-kondef') }}" class="group relative flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold transition duration-200 {{ request()->routeIs('petugas.tanya-kondef') ? 'bg-se-subtle text-se-rust ring-1 ring-amber-200/70' : 'text-slate-600 hover:bg-slate-50 hover:text-se-rust' }}">
                    <span class="{{ request()->routeIs('petugas.tanya-kondef') ? 'opacity-100' : 'opacity-0' }} absolute left-0 top-3 bottom-3 w-1 rounded-r-full bg-se-primary transition"></span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl transition {{ request()->routeIs('petugas.tanya-kondef') ? 'bg-se-primary text-white shadow-sm shadow-se-primary/25' : 'border border-slate-200 bg-white text-se-rust group-hover:border-amber-200 group-hover:bg-se-subtle' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.84L3 20l1.33-3.1A7.45 7.45 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1 truncate">Tanya KonDef</span>
                </a>
            </div>
        @endif
    </nav>
</div>
