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
                <div class="flex items-center gap-3 bg-gradient-to-br from-slate-50 to-orange-50/50 px-4 py-4">
                    <img class="h-11 w-11 rounded-full border-2 border-white shadow-sm ring-1 ring-slate-200" src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=FFF3DE&color=9A3D12' }}" alt="Avatar">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold leading-5 text-se-ink">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs font-normal leading-5 text-slate-500">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <div class="space-y-1.5 border-y border-slate-100 bg-white px-4 py-3">
                    <div class="flex items-center justify-between gap-3 px-1 py-1.5 text-xs">
                        <span class="font-medium text-slate-500">Role</span>
                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 font-semibold text-se-ink">{{ auth()->user()->isSuperAdmin() ? 'Admin' : 'Petugas' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-1 py-1.5 text-xs">
                        <span class="font-medium text-slate-500">Google Drive</span>
                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 font-semibold {{ auth()->user()->google_refresh_token ? 'border-green-200 bg-green-50 text-green-700' : 'border-rose-200 bg-rose-50 text-rose-700' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ auth()->user()->google_refresh_token ? 'bg-green-500' : 'bg-rose-500' }}"></span>
                            {{ auth()->user()->google_refresh_token ? 'Terhubung' : 'Terputus' }}
                        </span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="bg-white px-3 py-2.5">
                    @csrf
                    <button type="submit" class="group flex w-full items-center justify-between rounded-xl px-2.5 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-50 focus:outline-none focus:ring-4 focus:ring-rose-500/10">
                        <span class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg border border-rose-100 bg-white text-rose-500 transition group-hover:border-rose-200 group-hover:bg-rose-50 group-hover:text-rose-600">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </span>
                            Keluar
                        </span>
                        <svg class="h-3.5 w-3.5 text-rose-400 transition group-hover:translate-x-0.5 group-hover:text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
