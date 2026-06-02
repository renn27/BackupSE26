<header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-3 backdrop-blur-md sm:h-[68px] sm:px-6 lg:px-8">
    <div class="flex min-w-0 items-center gap-3 sm:gap-4">
        <button @click="sidebarOpen = true" class="rounded-xl border border-slate-200 bg-white p-2.5 text-se-rust shadow-sm shadow-slate-950/5 transition hover:border-amber-200 hover:bg-se-subtle lg:hidden focus:outline-none focus:ring-4 focus:ring-amber-500/10" aria-label="Buka menu">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
            </svg>
        </button>
        <div class="min-w-0">
            <h1 class="truncate text-base font-semibold tracking-tight text-se-ink sm:text-xl">@yield('title', 'Dashboard')</h1>
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-2 sm:gap-3">
        <div x-data="{ dropdownOpen: false }" class="relative">
            <button @click="dropdownOpen = !dropdownOpen" type="button" class="relative rounded-full border border-slate-200 bg-white p-0.5 shadow-sm shadow-slate-950/5 transition hover:border-amber-300 hover:ring-4 hover:ring-amber-500/10 focus:outline-none focus:ring-4 focus:ring-amber-500/10" aria-label="Buka menu akun dan status" title="Akun dan status">
                <img class="h-9 w-9 rounded-full border border-white sm:h-10 sm:w-10" src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=FFF3DE&color=9A3D12' }}" alt="Avatar">
                <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white {{ auth()->user()->google_refresh_token ? 'bg-green-500' : 'bg-rose-500' }}"></span>
            </button>

            <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition.origin.top.right x-cloak
                 class="absolute right-0 mt-3 w-[calc(100vw-1.5rem)] max-w-72 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-950/10 ring-1 ring-black/5">
                <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/70 px-4 py-4">
                    <img class="h-11 w-11 rounded-full border border-white shadow-sm" src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=FFF3DE&color=9A3D12' }}" alt="Avatar">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-se-ink">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs font-medium text-slate-500">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <div class="space-y-2 border-b border-slate-100 px-4 py-3">
                    <div class="flex items-center justify-between gap-3 text-xs">
                        <span class="font-medium text-slate-500">Role</span>
                        <span class="font-semibold text-se-ink">{{ auth()->user()->isSuperAdmin() ? 'Admin' : 'Petugas' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-xs">
                        <span class="font-medium text-slate-500">Google Drive</span>
                        <span class="inline-flex items-center gap-1.5 font-semibold {{ auth()->user()->google_refresh_token ? 'text-green-700' : 'text-rose-700' }}">
                            <span class="h-2 w-2 rounded-full {{ auth()->user()->google_refresh_token ? 'bg-green-500' : 'bg-rose-500' }}"></span>
                            {{ auth()->user()->google_refresh_token ? 'Terhubung' : 'Terputus' }}
                        </span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
