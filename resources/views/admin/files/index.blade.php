@extends('layouts.app')

@section('title', 'Semua File')

@section('content')
@php
    $statusLabelsList = [
        'uploaded' => 'Berhasil',
        'uploading' => 'Proses',
        'failed' => 'Gagal',
        'deleted' => 'Dihapus',
    ];
    $selectedStatusLabel = 'Semua Status';
    if (request('status') && isset($statusLabelsList[request('status')])) {
        $selectedStatusLabel = $statusLabelsList[request('status')];
    }
@endphp

<div class="space-y-6" x-data="{ viewMode: '{{ request('type') === 'photo' ? 'grid' : 'table' }}' }">
    <div class="file-toolbar">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center justify-between">
            <form id="filter-form" action="{{ route('admin.files.index') }}" method="GET" class="w-full lg:w-[min(100%,36rem)]">
                <div class="grid gap-3 grid-cols-1 sm:grid-cols-2 w-full">
                    @if(request('user_id'))
                        <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                    @endif
                    @if(request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif
                    <div class="relative w-full">
                        <input id="search-input" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama file..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm font-medium text-se-ink outline-none transition placeholder:text-slate-400 focus:border-se-primary/40 focus:bg-white focus:ring-4 focus:ring-orange-100/70">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>

                    <div x-data="{ open: false, selectedLabel: '{{ $selectedStatusLabel }}', selectedValue: '{{ request('status') }}' }" 
                         @click.outside="open = false" 
                         class="relative w-full">
                        <button type="button" @click="open = !open" 
                                class="flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm font-medium text-slate-700 outline-none transition hover:border-se-primary/30 hover:bg-white focus:border-se-primary/40 focus:bg-white focus:ring-4 focus:ring-orange-100/70">
                            <span class="truncate" x-text="selectedLabel"></span>
                            <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             x-cloak 
                             class="absolute top-full left-0 z-30 mt-1.5 w-full rounded-2xl border border-slate-200 bg-white py-1 shadow-xl shadow-slate-900/5 focus:outline-none flex flex-col overflow-hidden">
                            <button type="button" @click="selectedValue = ''; selectedLabel = 'Semua Status'; open = false; $nextTick(() => { $refs.statusSelect.dispatchEvent(new Event('change')) })"
                                    class="flex w-full items-center px-4 py-2 text-left text-sm transition hover:bg-orange-50 hover:text-orange-700 focus:bg-orange-50 focus:text-orange-700 focus:outline-none"
                                    :class="selectedValue === '' ? 'bg-orange-50 text-orange-700 font-bold' : 'text-slate-700 font-medium'">
                                Semua Status
                            </button>
                            @foreach($statusLabelsList as $val => $label)
                                <button type="button" @click="selectedValue = '{{ $val }}'; selectedLabel = '{{ $label }}'; open = false; $nextTick(() => { $refs.statusSelect.dispatchEvent(new Event('change')) })"
                                        class="flex w-full items-center px-4 py-2 text-left text-sm transition hover:bg-orange-50 hover:text-orange-700 focus:bg-orange-50 focus:text-orange-700 focus:outline-none"
                                        :class="selectedValue === '{{ $val }}' ? 'bg-orange-50 text-orange-700 font-bold' : 'text-slate-700 font-medium'">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        <select x-ref="statusSelect" id="status-filter" name="status" x-model="selectedValue" class="hidden">
                            <option value="">Semua Status</option>
                            @foreach($statusLabelsList as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>

            <a href="{{ route('admin.files.export') }}" class="file-secondary-button w-full sm:w-auto h-[46px] rounded-2xl px-4 py-3 flex items-center justify-center gap-1.5 text-xs sm:text-sm font-semibold whitespace-nowrap">
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

    <div id="files-table-wrapper" class="file-results-shell">
        <div class="file-tabs-shell">
            <div class="file-tab-list">
                <a href="{{ request()->fullUrlWithQuery(['type' => null]) }}" class="file-tab {{ empty(request('type')) ? 'file-tab-active-neutral' : 'file-tab-inactive' }}" title="Semua File">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>Semua File</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['type' => 'photo']) }}" class="file-tab {{ request('type') === 'photo' ? 'file-tab-active-photo' : 'file-tab-inactive' }}" title="Foto Laporan">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>Foto Laporan</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['type' => 'backup']) }}" class="file-tab {{ request('type') === 'backup' ? 'file-tab-active-backup' : 'file-tab-inactive' }}" title="Data Backup">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    <span>Data Backup</span>
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
                            <th class="file-table-th text-center">Status</th>
                            <th class="file-table-th w-36">Ukuran</th>
                            <th class="file-table-th">Waktu Upload</th>
                            <th class="file-table-th text-center">Aksi</th>
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
                                    </div>
                                </div>
                            </td>
                            <td class="file-table-cell">
                                <a href="{{ route('admin.files.index', ['user_id' => $file->user_id]) }}" class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 transition hover:border-blue-200 hover:bg-blue-100">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A8 8 0 1118.879 17.8M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $file->user->name ?? 'N/A' }}
                                </a>
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
                            <td class="file-table-cell text-center">
                                @if($file->status === 'uploaded')
                                    <div class="flex items-center justify-center gap-1">
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
                            <td colspan="6" class="px-6 py-14">
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
                                <img src="{{ route('file.view', $file) }}" alt="{{ $file->original_name }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 hover:scale-105" loading="lazy" decoding="async">
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
                                <p class="truncate text-sm font-semibold text-slate-900" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                            </div>

                            <div class="space-y-1.5 text-xs font-medium text-slate-500">
                                <div class="flex min-w-0 items-center gap-1.5">
                                    <a href="{{ route('admin.files.index', ['user_id' => $file->user_id]) }}" class="truncate font-semibold text-se-rust hover:underline">
                                        {{ $file->user->name ?? 'N/A' }}
                                    </a>
                                    <span class="text-slate-300">•</span>
                                    <span class="shrink-0 font-semibold text-slate-700">{{ $file->human_size }}</span>
                                </div>
                                <div>{{ $file->created_at->format('d M H:i') }}</div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const filterForm = document.getElementById('filter-form');
    const searchInput = document.getElementById('search-input');
    const statusSelect = document.getElementById('status-filter');
    const filesTableWrapper = document.getElementById('files-table-wrapper');
    let searchTimer;
    let abortController = null;

    function runLiveSearch(page = 1) {
        if (!filterForm || !filesTableWrapper) return;

        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        for (const [key, val] of formData.entries()) {
            if (val) params.set(key, val);
        }
        params.set('page', page);

        filesTableWrapper.classList.add('opacity-60');

        const requestUrl = `${filterForm.action}?${params.toString()}`;

        window.history.pushState(null, '', requestUrl);

        fetch(requestUrl, { signal: abortController.signal })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTable = doc.getElementById('files-table-wrapper');
                if (newTable) {
                    filesTableWrapper.innerHTML = newTable.innerHTML;
                }
            })
            .catch(err => {
                if (err.name !== 'AbortError') console.error(err);
            })
            .finally(() => {
                filesTableWrapper.classList.remove('opacity-60');
            });
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => runLiveSearch(1), 350);
        });
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', () => runLiveSearch(1));
    }

    if (filesTableWrapper) {
        filesTableWrapper.addEventListener('click', (e) => {
            const link = e.target.closest('a[href]');
            if (!link) return;

            const url = new URL(link.href);
            if (url.origin !== window.location.origin || url.pathname !== window.location.pathname) return;

            e.preventDefault();
            
            const typeParam = url.searchParams.get('type');
            const typeInput = filterForm.querySelector('input[name="type"]');
            if (typeInput) {
                typeInput.value = typeParam || '';
            }

            if (url.searchParams.has('type')) {
                runLiveSearch(1);
            } else {
                runLiveSearch(url.searchParams.get('page') || 1);
            }
        });
    }
});
</script>
@endpush
