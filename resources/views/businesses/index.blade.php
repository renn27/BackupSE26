@extends('layouts.app')

@section('title', 'Monitoring SBR')

@section('content')
<div class="space-y-5 sm:space-y-8">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-950/5 sm:rounded-3xl sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-4 mb-4">
            <div class="flex items-center gap-3 min-w-0">
                <h1 class="text-lg font-semibold tracking-tight text-se-ink sm:text-2xl">Monitoring SBR</h1>
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.monitoring-sbr.export') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm shadow-emerald-600/15 hover:bg-emerald-700 transition focus:outline-none focus:ring-4 focus:ring-emerald-500/10">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export Progress Monitoring
                    </a>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-4 sm:gap-6 text-sm">
                <!-- Petugas / Superadmin Info -->
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-medium uppercase tracking-wide text-slate-400 flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a8.25 8.25 0 1115 0"></path>
                        </svg>
                        {{ auth()->user()->isSuperAdmin() ? 'Superadmin' : 'Petugas' }}:
                    </span>
                    <span class="inline-flex max-w-full items-center truncate rounded-full bg-orange-50 px-3 py-1 text-[11px] font-medium uppercase tracking-wide text-orange-700 ring-1 ring-orange-100">{{ auth()->user()->name }}</span>
                </div>

                <!-- Wilayah Tugas Info -->
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-medium uppercase tracking-wide text-slate-400 flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                        Wilayah:
                    </span>
                    <div class="flex flex-wrap gap-1.5">
                        @if(auth()->user()->isSuperAdmin())
                            <span class="inline-flex max-w-full items-center truncate rounded-full bg-orange-50 px-3 py-1 text-[11px] font-medium uppercase tracking-wide text-orange-700 ring-1 ring-orange-100">Semua Wilayah (Superadmin)</span>
                        @else
                            @forelse($assignedVillages as $village)
                                <span class="inline-flex max-w-full items-center truncate rounded-full bg-orange-50 px-3 py-1 text-[11px] font-medium uppercase tracking-wide text-orange-700 ring-1 ring-orange-100">{{ $village->nmkec }} - {{ $village->nmdesa }}</span>
                            @empty
                                <span class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">Kamu belum ditugaskan ke desa manapun. Hubungi admin.</span>
                            @endforelse
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->isSuperAdmin())
            <!-- Superadmin Layout (Search full width, Grid 3 Columns below for Desa, Petugas, and Status) -->
            <form id="filter-form" method="GET" class="w-full">
                <!-- Search Input -->
                <div class="relative w-full">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input id="search-input" type="search" name="search" value="{{ $search }}" placeholder="Cari ID SBR atau nama usaha..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm font-medium text-se-ink outline-none transition placeholder:text-slate-400 focus:border-se-primary/40 focus:bg-white focus:ring-4 focus:ring-orange-100/70">
                </div>

                <div class="grid gap-3 grid-cols-3 mt-3">
                    <!-- Desa Filter -->
                    <div class="relative">
                        <select id="village-filter" name="village_id" class="w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 pr-10 text-sm font-medium text-slate-700 outline-none transition focus:border-se-primary/40 focus:bg-white focus:ring-4 focus:ring-orange-100/70">
                            <option value="">Semua Desa</option>
                            @foreach($assignedVillages as $village)
                                <option value="{{ $village->id }}" {{ $selectedVillageId == $village->id ? 'selected' : '' }}>
                                    {{ $village->nmkec }} - {{ $village->nmdesa }}
                                </option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400">&#9662;</span>
                    </div>

                    <!-- Petugas Filter -->
                    <div class="relative">
                        <select id="user-filter" name="user_id" class="w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 pr-10 text-sm font-medium text-slate-700 outline-none transition focus:border-se-primary/40 focus:bg-white focus:ring-4 focus:ring-orange-100/70">
                            <option value="">Semua Petugas</option>
                            @foreach($officers as $officer)
                                <option value="{{ $officer->id }}" {{ $selectedUserId == $officer->id ? 'selected' : '' }}>
                                    {{ $officer->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400">&#9662;</span>
                    </div>

                    <!-- Status Filter -->
                    <div class="relative">
                        <select id="status-filter" name="status" class="w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 pr-10 text-sm font-medium text-slate-700 outline-none transition focus:border-se-primary/40 focus:bg-white focus:ring-4 focus:ring-orange-100/70">
                            <option value="">Semua Status</option>
                            <option value="belum_dicatat" {{ $selectedStatus == 'belum_dicatat' ? 'selected' : '' }}>Belum Dicatat</option>
                            <option value="ditemukan" {{ $selectedStatus == 'ditemukan' ? 'selected' : '' }}>Ditemukan</option>
                            <option value="tidak_ditemukan" {{ $selectedStatus == 'tidak_ditemukan' ? 'selected' : '' }}>Tidak Ditemukan</option>
                            <option value="pindah" {{ $selectedStatus == 'pindah' ? 'selected' : '' }}>Pindah</option>
                            <option value="baru" {{ $selectedStatus == 'baru' ? 'selected' : '' }}>Baru</option>
                            <option value="tutup" {{ $selectedStatus == 'tutup' ? 'selected' : '' }}>Tutup</option>
                            <option value="ganda" {{ $selectedStatus == 'ganda' ? 'selected' : '' }}>Ganda</option>
                        </select>
                        <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400">&#9662;</span>
                    </div>
                </div>
            </form>
        @else
            <!-- Petugas Layout (Search full width, Grid 2 Columns below for Desa and Status) -->
            <form id="filter-form" method="GET" class="w-full">
                <!-- Search Input -->
                <div class="relative w-full">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input id="search-input" type="search" name="search" value="{{ $search }}" placeholder="Cari ID SBR atau nama usaha..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm font-medium text-se-ink outline-none transition placeholder:text-slate-400 focus:border-se-primary/40 focus:bg-white focus:ring-4 focus:ring-orange-100/70">
                </div>

                <div class="grid gap-3 grid-cols-2 mt-3">
                    <!-- Desa Filter -->
                    <div class="relative">
                        <select id="village-filter" name="village_id" class="w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 pr-10 text-sm font-medium text-slate-700 outline-none transition focus:border-se-primary/40 focus:bg-white focus:ring-4 focus:ring-orange-100/70">
                            <option value="">Semua Desa</option>
                            @foreach($assignedVillages as $village)
                                <option value="{{ $village->id }}" {{ $selectedVillageId == $village->id ? 'selected' : '' }}>
                                    {{ $village->nmkec }} - {{ $village->nmdesa }}
                                </option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400">&#9662;</span>
                    </div>

                    <!-- Status Filter -->
                    <div class="relative">
                        <select id="status-filter" name="status" class="w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 pr-10 text-sm font-medium text-slate-700 outline-none transition focus:border-se-primary/40 focus:bg-white focus:ring-4 focus:ring-orange-100/70">
                            <option value="">Semua Status</option>
                            <option value="belum_dicatat" {{ $selectedStatus == 'belum_dicatat' ? 'selected' : '' }}>Belum Dicatat</option>
                            <option value="ditemukan" {{ $selectedStatus == 'ditemukan' ? 'selected' : '' }}>Ditemukan</option>
                            <option value="tidak_ditemukan" {{ $selectedStatus == 'tidak_ditemukan' ? 'selected' : '' }}>Tidak Ditemukan</option>
                            <option value="pindah" {{ $selectedStatus == 'pindah' ? 'selected' : '' }}>Pindah</option>
                            <option value="baru" {{ $selectedStatus == 'baru' ? 'selected' : '' }}>Baru</option>
                            <option value="tutup" {{ $selectedStatus == 'tutup' ? 'selected' : '' }}>Tutup</option>
                            <option value="ganda" {{ $selectedStatus == 'ganda' ? 'selected' : '' }}>Ganda</option>
                        </select>
                        <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400">&#9662;</span>
                    </div>
                </div>
            </form>
        @endif
    </div>

    <section id="business-table-container" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-950/5 sm:rounded-3xl">
        @include('businesses.partials.table')
    </section>
</div>

<div id="status-modal" class="fixed inset-0 z-50 hidden overflow-hidden bg-slate-950/55 px-4 py-4 justify-center items-center transition-all duration-300 ease-out opacity-0 sm:py-6">
    <div class="modal-content w-full max-w-xl max-h-[calc(100dvh-2rem)] transform overflow-hidden rounded-2xl bg-white shadow-xl shadow-slate-950/20 transition-all duration-300 ease-out scale-95 opacity-0 flex flex-col border border-slate-200 sm:max-h-[calc(100dvh-3rem)]">
        <!-- Header -->
        <div class="relative shrink-0 border-b border-slate-100 p-4 pr-12 sm:p-5 sm:pr-12">
            <div class="flex min-w-0 items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-orange-100 bg-orange-50 text-se-primary">
                    <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex h-10 items-center">
                        <h2 id="modal-name" class="line-clamp-2 text-base font-medium leading-5 text-slate-800"></h2>
                    </div>
                    <div class="mt-2 flex min-w-0 flex-wrap items-center gap-2">
                        <span id="modal-idsbr" class="inline-flex max-w-full items-center truncate rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium uppercase tracking-wide text-slate-500 ring-1 ring-slate-200"></span>
                        <span id="modal-last-status" class="inline-flex w-fit items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1"></span>
                    </div>
                </div>
            </div>
            
            <button id="modal-close" type="button" class="absolute right-4 top-4 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 sm:right-5 sm:top-5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-4 sm:p-5">
        <!-- Status Terakhir & Info Update -->
        <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3.5">
            <div class="mb-3 flex items-start gap-2 border-b border-slate-200/70 pb-3 text-xs leading-relaxed text-slate-600">
                <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <div class="min-w-0">
                    <span class="block text-[10px] font-medium uppercase tracking-wide text-slate-400">Alamat</span>
                    <span id="modal-address" class="block break-words text-slate-700"></span>
                </div>
            </div>
            <div class="flex items-start gap-2 text-xs leading-snug text-slate-500">
                    <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="min-w-0">
                        <span class="block text-[10px] font-medium uppercase tracking-wide text-slate-400">Diperbarui</span>
                        <span id="modal-last-update" class="block text-slate-700"></span>
                    </div>
            </div>
        </div>

        <!-- Status Options Grid -->
        <div class="mt-4 space-y-2">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <span class="text-[10px] font-medium uppercase tracking-wide text-slate-500">Pilih status baru</span>
                <span id="modal-readonly-note" class="hidden rounded-full bg-slate-100 px-2.5 py-1 text-[11px] text-slate-600">Hanya bisa dilihat</span>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <button type="button" data-status-option="tidak_ditemukan" class="status-option group relative flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-slate-300 hover:bg-slate-50">
                    <div class="status-icon flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 8.75a3 3 0 116 0c0 2.25-3 2.35-3 4.75M12 17.25h.01"></path>
                        </svg>
                    </div>
                    <div class="space-y-0.5 min-w-0">
                        <p class="font-medium text-slate-700 text-sm leading-tight transition-colors status-title">Tidak Ditemukan</p>
                        <p class="text-[11px] text-slate-500 leading-tight">Keberadaan usaha tidak teridentifikasi</p>
                    </div>
                    <span class="absolute top-2 right-2 h-1.5 w-1.5 rounded-full bg-slate-500 scale-0 transition-transform duration-200 status-indicator"></span>
                </button>

                <button type="button" data-status-option="ditemukan" class="status-option group relative flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-green-200 hover:bg-green-50/40">
                    <div class="status-icon flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-green-100 bg-white text-green-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="space-y-0.5 min-w-0">
                        <p class="font-medium text-slate-700 text-sm leading-tight transition-colors status-title">Ditemukan</p>
                        <p class="text-[11px] text-slate-500 leading-tight">Usaha ditemukan di lokasi</p>
                    </div>
                    <span class="absolute top-2 right-2 h-1.5 w-1.5 rounded-full bg-green-500 scale-0 transition-transform duration-200 status-indicator"></span>
                </button>

                <button type="button" data-status-option="baru" class="status-option group relative flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-blue-200 hover:bg-blue-50/40">
                    <div class="status-icon flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-blue-100 bg-white text-blue-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14m7-7H5"></path>
                        </svg>
                    </div>
                    <div class="space-y-0.5 min-w-0">
                        <p class="font-medium text-slate-700 text-sm leading-tight transition-colors status-title">Baru</p>
                        <p class="text-[11px] text-slate-500 leading-tight">Usaha baru ditemukan di lapangan</p>
                    </div>
                    <span class="absolute top-2 right-2 h-1.5 w-1.5 rounded-full bg-blue-500 scale-0 transition-transform duration-200 status-indicator"></span>
                </button>

                <button type="button" data-status-option="tutup" class="status-option group relative flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-rose-200 hover:bg-rose-50/40">
                    <div class="status-icon flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-rose-100 bg-white text-rose-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div class="space-y-0.5 min-w-0">
                        <p class="font-medium text-slate-700 text-sm leading-tight transition-colors status-title">Tutup</p>
                        <p class="text-[11px] text-slate-500 leading-tight">Usaha tutup atau tidak beroperasi</p>
                    </div>
                    <span class="absolute top-2 right-2 h-1.5 w-1.5 rounded-full bg-rose-500 scale-0 transition-transform duration-200 status-indicator"></span>
                </button>

                <button type="button" data-status-option="ganda" class="status-option group relative flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-amber-200 hover:bg-amber-50/40">
                    <div class="status-icon flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-amber-100 bg-white text-amber-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h8M8 12h8M8 17h5"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 5h14v14H5z"></path>
                        </svg>
                    </div>
                    <div class="space-y-0.5 min-w-0">
                        <p class="font-medium text-slate-700 text-sm leading-tight transition-colors status-title">Ganda</p>
                        <p class="text-[11px] text-slate-500 leading-tight">Usaha terindikasi duplikasi</p>
                    </div>
                    <span class="absolute top-2 right-2 h-1.5 w-1.5 rounded-full bg-amber-500 scale-0 transition-transform duration-200 status-indicator"></span>
                </button>
            </div>
        </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 bg-white p-4 sm:flex-row sm:justify-end sm:gap-2.5 sm:p-5">
            <button id="modal-cancel" type="button" class="w-full sm:w-auto rounded-xl border border-slate-200 px-5 py-2.5 text-sm text-slate-600 hover:bg-slate-50 transition">
                Batal
            </button>
            <button id="modal-save" type="button" class="w-full sm:w-auto rounded-xl bg-se-primary px-6 py-2.5 text-sm font-medium text-white shadow-sm shadow-se-primary/20 hover:bg-se-rust active:scale-[0.99] transition flex items-center justify-center gap-1.5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                </svg>
                <span>Simpan Status</span>
            </button>
        </div>
    </div>
</div>

<div id="toast" class="fixed right-4 top-4 z-[60] hidden rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700 shadow-lg">Status berhasil disimpan.</div>
<div id="error-toast" class="fixed right-4 top-4 z-[60] hidden max-w-sm rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 shadow-lg">Gagal menyimpan status.</div>
@endsection

@push('scripts')
<script>
const statusLabels = {
    tidak_ditemukan: 'Tidak Ditemukan',
    ditemukan: 'Ditemukan',
    baru: 'Baru',
    tutup: 'Tutup',
    ganda: 'Ganda'
};
const statusClasses = {
    tidak_ditemukan: 'bg-slate-100 text-slate-600 ring-slate-200',
    ditemukan: 'bg-green-50 text-green-700 ring-green-200',
    baru: 'bg-blue-50 text-blue-700 ring-blue-200',
    tutup: 'bg-rose-50 text-rose-700 ring-rose-200',
    ganda: 'bg-amber-50 text-amber-700 ring-amber-200'
};
const isSuperAdmin = {{ auth()->user()->isSuperAdmin() ? 'true' : 'false' }};
let selectedBusinessId = null;
let selectedStatus = null;
let modalReadOnly = false;
let lockedScrollY = 0;
let searchTimer = null;
let searchController = null;
const tableContainer = document.getElementById('business-table-container');
const searchInput = document.getElementById('search-input');
const filterForm = document.getElementById('filter-form');
const saveButton = document.getElementById('modal-save');
const saveButtonLabel = saveButton.querySelector('span');

function lockBodyScroll() {
    lockedScrollY = window.scrollY || document.documentElement.scrollTop || 0;
    document.body.style.position = 'fixed';
    document.body.style.top = `-${lockedScrollY}px`;
    document.body.style.left = '0';
    document.body.style.right = '0';
    document.body.style.width = '100%';
    document.body.style.overflow = 'hidden';
}

function unlockBodyScroll() {
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.left = '';
    document.body.style.right = '';
    document.body.style.width = '';
    document.body.style.overflow = '';
    window.scrollTo(0, lockedScrollY);
}

filterForm.addEventListener('submit', (event) => {
    event.preventDefault();
    runLiveSearch(1);
});

searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => runLiveSearch(1), 350);
});

const villageFilter = document.getElementById('village-filter');
if (villageFilter) {
    villageFilter.addEventListener('change', () => runLiveSearch(1));
}

const userFilter = document.getElementById('user-filter');
if (userFilter) {
    userFilter.addEventListener('change', () => runLiveSearch(1));
}

const statusFilter = document.getElementById('status-filter');
if (statusFilter) {
    statusFilter.addEventListener('change', () => runLiveSearch(1));
}

tableContainer.addEventListener('click', (event) => {
    const paginationLink = event.target.closest('a[href]');
    if (!paginationLink) return;

    const url = new URL(paginationLink.href);
    if (!url.searchParams.has('page')) return;

    event.preventDefault();
    runLiveSearch(url.searchParams.get('page') || 1);
});

async function runLiveSearch(page = 1) {
    if (searchController) searchController.abort();

    searchController = new AbortController();
    const url = new URL(window.location.href);
    url.searchParams.set('search', searchInput.value);
    
    const villageFilter = document.getElementById('village-filter');
    if (villageFilter && villageFilter.value) {
        url.searchParams.set('village_id', villageFilter.value);
    } else {
        url.searchParams.delete('village_id');
    }

    const userFilter = document.getElementById('user-filter');
    if (userFilter && userFilter.value) {
        url.searchParams.set('user_id', userFilter.value);
    } else {
        url.searchParams.delete('user_id');
    }

    const statusFilter = document.getElementById('status-filter');
    if (statusFilter && statusFilter.value) {
        url.searchParams.set('status', statusFilter.value);
    } else {
        url.searchParams.delete('status');
    }

    url.searchParams.set('page', page);
    window.history.replaceState({}, '', url);
    tableContainer.classList.add('opacity-60');

    try {
        const response = await fetch(url, {
            headers: {'Accept': 'application/json'},
            signal: searchController.signal
        });
        const data = await response.json();
        tableContainer.innerHTML = data.html;
    } catch (error) {
        if (error.name !== 'AbortError') {
            console.error(error);
        }
    } finally {
        tableContainer.classList.remove('opacity-60');
    }
}

function openModal(button) {
    selectedBusinessId = button.dataset.id;
    selectedStatus = button.dataset.status || null;
    modalReadOnly = Boolean(selectedStatus && !isSuperAdmin);
    document.getElementById('modal-name').textContent = button.dataset.name;
    document.getElementById('modal-idsbr').textContent = `SBR ${button.dataset.idsbr || '-'}`;
    document.getElementById('modal-address').textContent = button.dataset.address;
    
    // Style the last status badge dynamically
    const lastStatusBadge = document.getElementById('modal-last-status');
    lastStatusBadge.textContent = selectedStatus ? statusLabels[selectedStatus] : 'Belum Dicatat';
    lastStatusBadge.className = 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ' + 
        (selectedStatus ? statusClasses[selectedStatus] : 'bg-slate-100 text-slate-600 ring-slate-200');

    const updatedBy = button.dataset.updatedBy || '-';
    const updatedAt = button.dataset.updatedAt || '-';
    document.getElementById('modal-last-update').textContent =
        updatedBy === '-' && updatedAt === '-' ? 'Belum ada pembaruan' : `oleh ${updatedBy} pada ${updatedAt}`;
    
    renderStatusOptions();
    renderModalPermissions();
    
    const modal = document.getElementById('status-modal');
    lockBodyScroll();
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.querySelector('.modal-content > .min-h-0').scrollTop = 0;
    
    // Trigger transition
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.querySelector('.modal-content').classList.remove('scale-95', 'opacity-0');
        modal.querySelector('.modal-content').classList.add('scale-100', 'opacity-100');
    }, 20);
}

function renderModalPermissions() {
    const saveButton = document.getElementById('modal-save');
    const readOnlyNote = document.getElementById('modal-readonly-note');

    saveButton.classList.toggle('hidden', modalReadOnly);
    readOnlyNote.classList.toggle('hidden', !modalReadOnly);
    readOnlyNote.textContent = modalReadOnly ? 'Sudah diperbarui & terkunci' : '';

    document.querySelectorAll('.status-option').forEach(button => {
        button.disabled = modalReadOnly;
        button.classList.toggle('cursor-not-allowed', modalReadOnly);
        button.classList.toggle('opacity-75', modalReadOnly);
    });
}

function closeModal() {
    const modal = document.getElementById('status-modal');
    modal.classList.add('opacity-0');
    
    const content = modal.querySelector('.modal-content');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        unlockBodyScroll();
    }, 300);
}

function showErrorToast(message) {
    const toast = document.getElementById('error-toast');
    toast.textContent = message || 'Gagal menyimpan status.';
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3000);
}

function setSaveLoading(loading) {
    saveButton.disabled = loading;
    saveButton.classList.toggle('cursor-not-allowed', loading);
    saveButton.classList.toggle('opacity-75', loading);
    saveButtonLabel.textContent = loading ? 'Menyimpan...' : 'Simpan Status';
}

function renderStatusOptions() {
    document.querySelectorAll('.status-option').forEach(button => {
        const option = button.dataset.statusOption;
        const active = option === selectedStatus;

        button.classList.remove(
            'border-green-300', 'bg-green-50',
            'border-blue-300', 'bg-blue-50',
            'border-rose-300', 'bg-rose-50',
            'border-amber-300', 'bg-amber-50',
            'border-slate-400', 'bg-slate-50',
            'border-slate-200', 'bg-white'
        );

        const title = button.querySelector('.status-title');
        title.classList.remove('text-green-700', 'text-blue-700', 'text-rose-700', 'text-amber-700', 'text-slate-800', 'text-slate-700');

        const indicator = button.querySelector('.status-indicator');
        indicator.classList.remove('scale-100');
        indicator.classList.add('scale-0');

        if (active) {
            indicator.classList.remove('scale-0');
            indicator.classList.add('scale-100');

            if (option === 'ditemukan') {
                button.classList.add('border-green-300', 'bg-green-50');
                title.classList.add('text-green-700');
            } else if (option === 'baru') {
                button.classList.add('border-blue-300', 'bg-blue-50');
                title.classList.add('text-blue-700');
            } else if (option === 'tutup') {
                button.classList.add('border-rose-300', 'bg-rose-50');
                title.classList.add('text-rose-700');
            } else if (option === 'ganda') {
                button.classList.add('border-amber-300', 'bg-amber-50');
                title.classList.add('text-amber-700');
            } else if (option === 'tidak_ditemukan') {
                button.classList.add('border-slate-400', 'bg-slate-50');
                title.classList.add('text-slate-800');
            }
        } else {
            button.classList.add('border-slate-200', 'bg-white');
            title.classList.add('text-slate-700');
        }
    });
}

tableContainer.addEventListener('click', (event) => {
    const button = event.target.closest('.status-open');
    if (!button) return;

    openModal(button);
});

document.querySelectorAll('.status-option').forEach(button => {
    button.addEventListener('click', () => {
        if (modalReadOnly) return;

        selectedStatus = button.dataset.statusOption;
        renderStatusOptions();
    });
});

document.getElementById('modal-close').addEventListener('click', closeModal);
document.getElementById('modal-cancel').addEventListener('click', closeModal);

document.getElementById('modal-save').addEventListener('click', async () => {
    if (modalReadOnly || !selectedBusinessId || !selectedStatus) return;

    setSaveLoading(true);

    try {
        const response = await fetch(`{{ url('/petugas/monitoring-sbr') }}/${selectedBusinessId}/status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({status: selectedStatus, catatan: null})
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok || !data.success) {
            showErrorToast(data.message || 'Gagal menyimpan status. Coba muat ulang halaman.');
            return;
        }

        const badge = document.querySelector(`[data-status-badge="${selectedBusinessId}"]`);
        badge.className = `inline-flex rounded-full px-3 py-1 text-xs font-medium ring-1 ${statusClasses[data.status]}`;
        badge.textContent = data.status_label;

        const button = document.querySelector(`.status-open[data-id="${selectedBusinessId}"]`);
        button.dataset.status = data.status;
        button.dataset.note = data.catatan || '';
        button.dataset.updatedBy = data.updated_by;
        button.dataset.updatedAt = data.updated_at;
        button.dataset.statusOwnerId = '{{ auth()->id() }}';

        closeModal();
        const toast = document.getElementById('toast');
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 2200);
    } catch (error) {
        showErrorToast('Terjadi kesalahan koneksi saat menyimpan status.');
    } finally {
        setSaveLoading(false);
    }
});
</script>
@endpush
