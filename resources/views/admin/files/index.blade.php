@extends('layouts.app')

@section('title', 'Semua File')

@section('content')
<div class="space-y-6" x-data="{ viewMode: '{{ request('type') === 'photo' ? 'grid' : 'table' }}' }">
    <div class="file-toolbar">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <form action="{{ route('admin.files.index') }}" method="GET" class="grid w-full grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_auto_auto] xl:max-w-4xl">
                @if(request('user_id'))
                    <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                @endif
                @if(request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama file..." class="file-search-control">
                    <svg class="pointer-events-none absolute left-3.5 top-3.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <div class="relative">
                    <select name="status" class="file-select-control" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="uploaded" {{ request('status') === 'uploaded' ? 'selected' : '' }}>Berhasil</option>
                        <option value="uploading" {{ request('status') === 'uploading' ? 'selected' : '' }}>Proses</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Gagal</option>
                        <option value="deleted" {{ request('status') === 'deleted' ? 'selected' : '' }}>Dihapus</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <button type="submit" class="file-primary-button w-full lg:w-auto">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filter
                </button>
            </form>

            <a href="{{ route('admin.files.export') }}" class="file-secondary-button w-full xl:w-auto">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export CSV
            </a>
        </div>
    </div>

    @if($selectedUser)
        <div class="rounded-lg border border-blue-100 bg-blue-50/70 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <img class="h-11 w-11 rounded-full border border-white shadow-sm" src="{{ $selectedUser->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($selectedUser->name).'&background=EBF4FF&color=2563EB' }}" alt="">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-blue-950">File milik {{ $selectedUser->name }}</p>
                        <p class="truncate text-xs font-medium text-blue-700">{{ $selectedUser->email }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.files.index') }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-blue-200 bg-white px-3 text-xs font-semibold text-blue-700 transition hover:bg-blue-50">
                    Lihat Semua File
                </a>
            </div>
        </div>
    @elseif(request('user_id'))
        <div class="rounded-lg border border-amber-100 bg-amber-50 p-4 text-sm font-medium text-amber-800">
            User tidak ditemukan atau bukan petugas. Menampilkan hasil sesuai filter yang tersedia.
        </div>
    @endif

    <div class="file-results-shell">
        <div class="file-tabs-shell">
            <div class="file-tab-list">
                <a href="{{ request()->fullUrlWithQuery(['type' => null]) }}" class="file-tab {{ empty(request('type')) ? 'file-tab-active-neutral' : '' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Semua File
                </a>
                <a href="{{ request()->fullUrlWithQuery(['type' => 'photo']) }}" class="file-tab {{ request('type') === 'photo' ? 'file-tab-active-photo' : '' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Foto Laporan
                </a>
                <a href="{{ request()->fullUrlWithQuery(['type' => 'backup']) }}" class="file-tab {{ request('type') === 'backup' ? 'file-tab-active-backup' : '' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    Data Backup
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

        <div x-show="viewMode === 'table'" x-transition.opacity.duration.150ms x-cloak class="file-panel" style="display: none;">
            <div class="overflow-x-auto">
                <table class="file-table min-w-[1040px]">
                    <thead class="file-table-head">
                        <tr>
                            <th class="file-table-th">File</th>
                            <th class="file-table-th">Uploader</th>
                            <th class="file-table-th">Kategori</th>
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
                                        <p class="file-name max-w-[220px]" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                                        @if($file->description)
                                            <p class="file-description max-w-[220px]" title="{{ $file->description }}">{{ $file->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="file-table-cell">
                                <a href="{{ route('admin.files.index', ['user_id' => $file->user_id]) }}" class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 transition hover:border-blue-200 hover:bg-blue-100">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A8 8 0 1118.879 17.8M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $file->user->name ?? 'N/A' }}
                                </a>
                            </td>
                            <td class="file-table-cell">
                                @if($file->category)
                                    <span class="file-category-badge">{{ $file->category }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
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
                                @if($file->status === 'uploaded')
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('file.view', $file) }}" target="_blank" class="file-action file-action-muted" title="Lihat">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>
                                        <a href="{{ route('file.download', $file) }}" class="file-action file-action-blue" title="Download">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14">
                                <div class="file-empty-state">
                                    <svg class="mx-auto mb-3 h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="text-sm font-semibold text-slate-500">Tidak ada file yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="viewMode === 'grid'" x-transition.opacity.duration.150ms x-cloak class="p-4" style="display: none;">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                @forelse($files as $file)
                    <div class="file-grid-card flex flex-col">
                        @if($file->type === 'photo' && $file->status === 'uploaded')
                            <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
                                <img src="{{ route('file.view', $file) }}" alt="{{ $file->original_name }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 hover:scale-105" loading="lazy">
                                <div class="absolute left-3 top-3">
                                    <span class="file-status-badge file-status-success bg-white/95">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Berhasil
                                    </span>
                                </div>
                            </div>
                        @else
                            <div class="relative flex aspect-[4/3] flex-col items-center justify-center gap-3 overflow-hidden bg-slate-50/80 p-5 text-center">
                                @if($file->type === 'photo')
                                    <div class="file-icon file-icon-photo">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @else
                                    <div class="file-icon file-icon-backup">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                    </div>
                                @endif

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
                            </div>
                        @endif

                        <div class="flex flex-1 flex-col gap-3 p-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-900" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                                @if($file->description)
                                    <p class="mt-1 truncate text-xs text-slate-500" title="{{ $file->description }}">{{ $file->description }}</p>
                                @endif
                            </div>

                            <div class="space-y-1.5 text-xs font-medium text-slate-500">
                                <div class="flex min-w-0 items-center gap-1.5">
                                    <a href="{{ route('admin.files.index', ['user_id' => $file->user_id]) }}" class="truncate font-semibold text-se-rust hover:underline">
                                        {{ $file->user->name ?? 'N/A' }}
                                    </a>
                                    <span class="text-slate-300">•</span>
                                    <span class="shrink-0 font-semibold text-slate-700">{{ $file->human_size }}</span>
                                </div>
                                <div class="flex min-w-0 items-center gap-1.5">
                                    <span class="truncate">{{ $file->category ?: 'Tanpa kategori' }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span class="shrink-0">{{ $file->created_at->format('d M H:i') }}</span>
                                </div>
                            </div>

                            <div class="mt-auto flex items-center justify-end border-t border-slate-100 pt-1.5">
                                @if($file->status === 'uploaded')
                                    <div class="flex items-center gap-0.5">
                                        <a href="{{ route('file.view', $file) }}" target="_blank" class="file-action file-action-compact file-action-muted" title="Lihat">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>
                                        <a href="{{ route('file.download', $file) }}" class="file-action file-action-compact file-action-blue" title="Download">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full file-empty-state">
                        <svg class="mx-auto mb-3 h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <p class="text-sm font-semibold text-slate-500">Tidak ada file yang ditemukan.</p>
                    </div>
                @endforelse
            </div>
        </div>

        @if($files->hasPages())
        <div class="border-t border-slate-100 bg-slate-50/70 px-6 py-4">
            {{ $files->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
