@extends('layouts.app')

@section('title', 'File Saya')

@section('content')
<div class="space-y-4 sm:space-y-6" x-data="{ viewMode: '{{ request('type') === 'photo' ? 'grid' : 'table' }}' }">
    <div class="file-toolbar">
        <form action="{{ route('petugas.files.index') }}" method="GET" class="grid w-full grid-cols-[minmax(0,1fr)_auto] gap-2 sm:gap-3 lg:grid-cols-[minmax(0,1fr)_auto_auto]">
            @if(request('type'))
                <input type="hidden" name="type" value="{{ request('type') }}">
            @endif
            <div class="relative col-span-2 lg:col-span-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama file..." class="file-search-control">
                <svg class="pointer-events-none absolute left-3.5 top-3.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <div class="relative">
                <select name="status" class="file-select-control" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="uploaded" {{ request('status') === 'uploaded' ? 'selected' : '' }}>Berhasil</option>
                    <option value="uploading" {{ request('status') === 'uploading' ? 'selected' : '' }}>Proses</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Gagal</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>

            <button type="submit" class="file-primary-button w-28 px-3 sm:w-32 lg:w-auto lg:px-5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filter
            </button>
        </form>
    </div>

    <div class="file-results-shell">
        <div class="file-tabs-shell">
            <div class="file-tab-list">
                <a href="{{ request()->fullUrlWithQuery(['type' => null]) }}" class="file-tab {{ empty(request('type')) ? 'file-tab-active-neutral' : '' }}" title="Semua File">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="{{ empty(request('type')) ? '' : 'max-sm:sr-only' }}">Semua File</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['type' => 'photo']) }}" class="file-tab {{ request('type') === 'photo' ? 'file-tab-active-photo' : '' }}" title="Foto Laporan">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="{{ request('type') === 'photo' ? '' : 'max-sm:sr-only' }}">Foto Laporan</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['type' => 'backup']) }}" class="file-tab {{ request('type') === 'backup' ? 'file-tab-active-backup' : '' }}" title="Data Backup">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    <span class="{{ request('type') === 'backup' ? '' : 'max-sm:sr-only' }}">Data Backup</span>
                </a>
            </div>

            <div class="file-view-toggle">
                <button @click="viewMode = 'table'" :class="viewMode === 'table' ? 'file-view-toggle-button-active' : ''" class="file-view-toggle-button" title="Mode Tabel">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                </button>
                <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'file-view-toggle-button-active' : ''" class="file-view-toggle-button" title="Mode Grid">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                </button>
            </div>
        </div>

        <div x-show="viewMode === 'table'" x-transition.opacity.duration.150ms x-cloak class="space-y-2 bg-slate-50/60 p-2.5 md:hidden" style="display: none;">
            @forelse($files as $file)
                <article x-data="{ actionOpen: false }" class="relative rounded-xl border border-slate-200/80 bg-white p-3 shadow-sm">
                    <div class="flex items-start gap-3">
                        @if($file->type === 'photo')
                            <div class="file-icon file-icon-photo h-11 w-11">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        @else
                            <div class="file-icon file-icon-backup h-11 w-11">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                            </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <p class="line-clamp-2 min-w-0 text-sm font-semibold leading-5 text-slate-900" title="{{ $file->original_name }}">{{ $file->original_name }}</p>

                                @if($file->status === 'uploaded' || ($file->status !== 'uploading' && $file->status !== 'deleted'))
                                    <button type="button" @click="actionOpen = !actionOpen" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50" title="Buka aksi">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z"></path></svg>
                                        <span class="sr-only">Aksi</span>
                                    </button>
                                @endif
                            </div>

                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                @if($file->status === 'uploaded')
                                    <span class="file-status-badge file-status-success min-w-0 px-2 py-0.5">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Berhasil
                                    </span>
                                @elseif($file->status === 'uploading')
                                    <span class="file-status-badge file-status-process min-w-0 px-2 py-0.5">
                                        <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Proses
                                    </span>
                                @elseif($file->status === 'failed')
                                    <span class="file-status-badge file-status-failed min-w-0 px-2 py-0.5" title="{{ $file->upload_error }}">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Gagal
                                    </span>
                                @else
                                    <span class="file-status-badge file-status-muted min-w-0 px-2 py-0.5">Dihapus</span>
                                @endif
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">{{ $file->human_size }}</span>
                                <span class="text-[11px] font-medium text-slate-500">{{ $file->created_at->format('d M Y H:i') }}</span>
                            </div>

                            @if($file->status === 'uploaded' || ($file->status !== 'uploading' && $file->status !== 'deleted'))
                                <div x-show="actionOpen" @click.away="actionOpen = false" x-transition.origin.top.right x-cloak class="absolute right-3 top-12 z-20 w-40 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-950/10">
                                    @if($file->status === 'uploaded')
                                        @if($file->drive_web_view_link)
                                        <a href="{{ $file->drive_web_view_link }}" target="_blank" class="flex items-center gap-2 px-3 py-2.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-50" title="Buka di Drive">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                            Drive
                                        </a>
                                        @endif
                                        <a href="{{ route('file.download', $file) }}" class="flex items-center gap-2 px-3 py-2.5 text-xs font-semibold text-green-700 transition hover:bg-green-50" title="Download">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            Unduh
                                        </a>
                                    @endif

                                    @if($file->status !== 'uploading' && $file->status !== 'deleted')
                                        <button type="button" @click="deleteFile({{ $file->id }})" class="flex w-full items-center gap-2 px-3 py-2.5 text-left text-xs font-semibold text-rose-700 transition hover:bg-rose-50" title="Hapus">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Hapus
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="p-4">
                    <div class="file-empty-state">
                        <svg class="mx-auto mb-3 h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <p class="text-sm font-semibold text-slate-500">Tidak ada file yang ditemukan.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div x-show="viewMode === 'grid'" x-transition.opacity.duration.150ms x-cloak class="grid grid-cols-2 gap-2 bg-slate-50/60 p-2.5 md:hidden" style="display: none;">
            @forelse($files as $file)
                <article class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="relative flex aspect-square items-center justify-center bg-slate-50">
                        @if($file->type === 'photo' && $file->status === 'uploaded')
                            <img src="{{ route('file.view', $file) }}" alt="{{ $file->original_name }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async">
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/70 to-transparent p-2">
                                <span class="text-[10px] font-semibold text-white">{{ $file->human_size }}</span>
                            </div>
                        @else
                            <div class="file-icon {{ $file->type === 'photo' ? 'file-icon-danger' : 'file-icon-backup' }} h-12 w-12">
                                @if($file->type === 'photo')
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                @else
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                @endif
                            </div>
                        @endif

                        <div class="absolute left-2 top-2">
                            @if($file->status === 'uploaded')
                                <span class="rounded-full border border-green-200 bg-green-50 px-2 py-0.5 text-[10px] font-semibold text-green-700">OK</span>
                            @elseif($file->status === 'uploading')
                                <span class="rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700">Proses</span>
                            @elseif($file->status === 'failed')
                                <span class="rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700">Gagal</span>
                            @else
                                <span class="rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">Dihapus</span>
                            @endif
                        </div>
                    </div>

                    <div class="p-2.5">
                        <p class="line-clamp-2 min-h-9 text-xs font-semibold leading-4 text-slate-900" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                        <p class="mt-1 text-[10px] font-medium text-slate-500">{{ $file->created_at->format('d M') }} - {{ $file->human_size }}</p>

                        <div class="mt-2 flex items-center gap-1">
                            @if($file->status === 'uploaded' && $file->drive_web_view_link)
                                <a href="{{ $file->drive_web_view_link }}" target="_blank" class="file-action file-action-compact file-action-blue" title="Buka di Drive">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            @endif
                            @if($file->status === 'uploaded')
                                <a href="{{ route('file.download', $file) }}" class="file-action file-action-compact file-action-green" title="Download">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                </a>
                            @endif
                            @if($file->status !== 'uploading' && $file->status !== 'deleted')
                                <button type="button" @click="deleteFile({{ $file->id }})" class="file-action file-action-compact file-action-rose" title="Hapus">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full p-2">
                    <div class="file-empty-state">
                        <svg class="mx-auto mb-3 h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <p class="text-sm font-semibold text-slate-500">Tidak ada file yang ditemukan.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div x-show="viewMode === 'table'" x-transition.opacity.duration.150ms x-cloak class="file-panel max-md:hidden" style="display: none;">
            <div class="overflow-x-auto">
                <table class="file-table">
                    <thead class="file-table-head">
                        <tr>
                            <th class="file-table-th">File</th>
                            <th class="file-table-th text-center">Status</th>
                            <th class="file-table-th w-36">Ukuran</th>
                            <th class="file-table-th">Waktu Upload</th>
                            <th class="file-table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($files as $file)
                        <tr class="file-table-row">
                            <td class="file-table-cell">
                                <div class="flex items-center gap-3">
                                    @if($file->type === 'photo')
                                        <div class="file-icon file-icon-photo">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @else
                                        <div class="file-icon file-icon-backup">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="file-name" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="file-table-cell text-center">
                                @if($file->status === 'uploaded')
                                    <span class="file-status-badge file-status-success">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Berhasil
                                    </span>
                                @elseif($file->status === 'uploading')
                                    <span class="file-status-badge file-status-process">
                                        <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Proses
                                    </span>
                                @elseif($file->status === 'failed')
                                    <span class="file-status-badge file-status-failed" title="{{ $file->upload_error }}">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Gagal
                                    </span>
                                @else
                                    <span class="file-status-badge file-status-muted">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> Dihapus
                                    </span>
                                @endif
                            </td>
                            <td class="file-table-cell">
                                <div class="file-size-stack">
                                    <span class="file-size-value">{{ $file->human_size }}</span>
                                </div>
                            </td>
                            <td class="file-table-cell text-xs font-medium text-slate-500">
                                {{ $file->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="file-table-cell text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if($file->status === 'uploaded')
                                        @if($file->drive_web_view_link)
                                        <a href="{{ $file->drive_web_view_link }}" target="_blank" class="file-action file-action-blue" title="Buka di Drive">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        </a>
                                        @endif
                                        <a href="{{ route('file.download', $file) }}" class="file-action file-action-green" title="Download">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        </a>
                                    @endif

                                    @if($file->status !== 'uploading' && $file->status !== 'deleted')
                                        <button type="button" @click="deleteFile({{ $file->id }})" class="file-action file-action-rose" title="Hapus">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-14">
                                <div class="file-empty-state">
                                    <svg class="mx-auto mb-3 h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="text-sm font-semibold text-slate-500">Tidak ada file.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="viewMode === 'grid'" x-transition.opacity.duration.150ms x-cloak class="max-md:hidden p-4" style="display: none;">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                @forelse($files as $file)
                    @if($file->type === 'photo' && $file->status === 'uploaded')
                        <div class="file-grid-card group flex flex-col">
                            <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
                                <img src="{{ route('file.view', $file) }}" alt="{{ $file->original_name }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" decoding="async">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/10 to-transparent opacity-0 transition duration-300 group-hover:opacity-100"></div>
                                <p class="absolute inset-x-3 bottom-3 truncate text-sm font-semibold text-white opacity-0 transition duration-300 group-hover:opacity-100" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                            </div>
                            <div class="flex items-center justify-between gap-3 border-t border-slate-100 bg-white px-3 py-2">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                                    <p class="mt-0.5 text-xs font-medium text-slate-500">{{ $file->created_at->format('d M') }} - {{ $file->human_size }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-0.5">
                                    @if($file->drive_web_view_link)
                                    <a href="{{ $file->drive_web_view_link }}" target="_blank" class="file-action file-action-compact file-action-blue" title="Buka di Drive">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </a>
                                    @endif
                                    <button type="button" @click="deleteFile({{ $file->id }})" class="file-action file-action-compact file-action-rose" title="Hapus">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="file-grid-card flex flex-col">
                            <div class="relative flex aspect-[4/3] flex-col items-center justify-center overflow-hidden bg-slate-50/80 p-5 text-center">
                                @if($file->type === 'photo')
                                    <div class="file-icon file-icon-danger mb-4">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                @else
                                    <div class="file-icon file-icon-backup mb-4">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                    </div>
                                @endif
                                <span class="file-status-badge {{ $file->status === 'uploaded' ? 'file-status-success' : ($file->status === 'uploading' ? 'file-status-process' : ($file->status === 'failed' ? 'file-status-failed' : 'file-status-muted')) }}">
                                    @if($file->status === 'uploaded')
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Berhasil
                                    @elseif($file->status === 'uploading')
                                        <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Proses
                                    @elseif($file->status === 'failed')
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Gagal
                                    @else
                                        Dihapus
                                    @endif
                                </span>
                            </div>
                            <div class="flex items-center justify-between gap-3 border-t border-slate-100 bg-white px-3 py-2">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                                    <p class="mt-0.5 text-xs font-medium text-slate-500">{{ $file->created_at->format('d M') }} - {{ $file->human_size }}</p>
                                </div>
                                <div class="flex items-center gap-0.5">
                                    @if($file->status === 'uploaded' && $file->drive_web_view_link)
                                    <a href="{{ $file->drive_web_view_link }}" target="_blank" class="file-action file-action-compact file-action-blue" title="Buka di Drive">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </a>
                                    @endif
                                    @if($file->status !== 'uploading' && $file->status !== 'deleted')
                                    <button type="button" @click="deleteFile({{ $file->id }})" class="file-action file-action-compact file-action-rose" title="Hapus">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="col-span-full file-empty-state">
                        <svg class="mx-auto mb-3 h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <p class="text-sm font-semibold text-slate-500">Tidak ada file yang ditemukan.</p>
                    </div>
                @endforelse
            </div>
        </div>

        @if($files->hasPages())
        <div class="border-t border-slate-100 bg-slate-50/70 px-3 py-4 sm:px-6">
            {{ $files->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function deleteFile(id) {
        if (!confirm('Yakin ingin menghapus file ini? File akan dihapus permanen dari Google Drive.')) return;

        fetch(`/petugas/files/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Gagal menghapus file.');
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan koneksi.');
        });
    }
</script>
@endpush
