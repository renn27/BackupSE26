@extends('layouts.app')

@section('title', 'Backup')

@section('content')
<div class="space-y-5 sm:space-y-8" x-data="uploadManager()">
    
    <!-- Welcome Card -->
    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-sm shadow-slate-950/5 sm:p-6">
        <div class="absolute inset-y-0 right-0 hidden w-72 bg-gradient-to-l from-amber-50 via-orange-50/60 to-transparent lg:block"></div>
        <div class="relative flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div class="mb-4 hidden h-1 w-16 rounded-full bg-gradient-to-r from-se-primary to-se-gold sm:block"></div>
                <h2 class="text-lg font-semibold tracking-tight text-se-ink sm:text-2xl">Selamat datang, {{ explode(' ', auth()->user()->name)[0] }}!</h2>
                <p class="mt-1.5 hidden max-w-3xl text-sm leading-6 text-slate-600 sm:block">Unggah foto laporan atau data backup Sensus Ekonomi 2026 ke Google Drive pribadi.</p>
            </div>

            @php
                $storageUsed = $stats['storage_used'];
                $storageLimit = $stats['storage_limit'];
                $storagePercent = $stats['storage_percent'];
                $storageBarWidth = $storageUsed > 0 ? max(2, $storagePercent) : 0;
                $formatBytes = function ($bytes) {
                    if ($bytes >= 1073741824) {
                        return number_format($bytes / 1073741824, 2) . ' GB';
                    }
                    if ($bytes >= 1048576) {
                        return number_format($bytes / 1048576, 1) . ' MB';
                    }
                    return number_format(max($bytes, 0) / 1024, 0) . ' KB';
                };
            @endphp
            <div class="grid w-full grid-cols-[5.25rem_minmax(0,1fr)] overflow-hidden rounded-2xl border border-amber-100 bg-white/85 shadow-sm shadow-amber-900/5 backdrop-blur sm:w-[28rem] sm:grid-cols-[0.75fr_1.25fr]">
                <div class="px-3.5 py-2.5 text-center sm:px-4 sm:py-3">
                    <p class="whitespace-nowrap text-[10px] font-semibold uppercase tracking-wide text-slate-500 sm:text-xs">Total File</p>
                    <p class="mt-1.5 text-lg font-semibold leading-none tracking-tight text-se-rust sm:mt-2 sm:text-2xl">{{ $stats['total_photos'] + $stats['total_backups'] }}</p>
                </div>
                <div class="border-l border-amber-100 bg-amber-50/30 px-3.5 py-2.5 sm:bg-transparent sm:px-4 sm:py-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 sm:text-xs">Storage</p>
                            <p class="mt-0.5 truncate text-sm font-semibold tracking-tight text-se-rust sm:text-lg">{{ $formatBytes($storageUsed) }} <span class="text-[10px] font-semibold text-slate-500 sm:text-xs">/ 15 GB</span></p>
                        </div>
                        <span class="shrink-0 rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-se-rust shadow-sm ring-1 ring-amber-100 sm:text-[11px]">{{ number_format($storagePercent, 1) }}%</span>
                    </div>
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-amber-100 sm:h-2">
                        <div class="h-full rounded-full bg-gradient-to-r from-se-primary to-se-gold" style="width: {{ $storageBarWidth }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Area Grid -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6">
        <!-- Photo Upload -->
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-shadow hover:shadow-md sm:rounded-3xl sm:p-6"
             @dragover.prevent="dragPhoto = true"
             @dragleave.prevent="dragPhoto = false"
             @drop.prevent="dropPhoto($event)">
            
            <div class="mb-3 flex items-center justify-between sm:mb-4">
                <h3 class="flex items-center gap-2 text-base font-semibold text-slate-800 sm:text-lg">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-50 text-se-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </span>
                    Upload Foto Laporan
                </h3>
            </div>

            <form id="formPhoto" action="{{ route('petugas.files.upload.photo') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div :class="dragPhoto ? 'bg-blue-50 border-blue-400 border-solid' : 'bg-slate-50 border-slate-200 border-dashed'" 
                     class="relative min-h-36 cursor-pointer rounded-2xl border-2 p-4 text-center transition-all sm:min-h-44 sm:p-8"
                     @click="$refs.photoInput.click()">
                    
                    <input type="file" name="photo" x-ref="photoInput" class="hidden" accept="image/jpeg,image/png,image/webp,image/heic" @change="handlePhotoSelect">
                    
                    <div x-show="!photoPreview" class="space-y-2 sm:space-y-3">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full border border-slate-100 bg-white text-blue-500 shadow-sm sm:h-16 sm:w-16">
                            <svg class="h-6 w-6 sm:h-8 sm:w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        </div>
                        <p class="text-sm font-medium text-slate-700">Pilih atau letakkan foto di sini</p>
                        <p class="text-xs text-slate-400">JPG, PNG, WebP (Max 10MB)</p>
                    </div>

                    <div x-show="photoPreview" class="relative group" x-cloak>
                        <img :src="photoPreview" class="max-h-48 mx-auto rounded-xl shadow-sm object-contain" alt="Preview">
                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-xl flex items-center justify-center">
                            <span class="text-white text-sm font-medium bg-slate-900/60 px-3 py-1 rounded-lg backdrop-blur-sm">Ganti Foto</span>
                        </div>
                    </div>
                </div>

                <div x-show="photoFile" x-collapse class="mt-4 space-y-4" x-cloak>
                    <div>
                        <input type="text" name="rename" placeholder="Nama file baru (Opsional)" class="w-full text-sm border-slate-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all bg-slate-50">
                    </div>
                    <button type="button" @click="submitPhoto" :disabled="isUploadingPhoto" class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-transparent bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-blue-500 disabled:opacity-90">
                        <svg x-show="isUploadingPhoto" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isUploadingPhoto ? 'Mengunggah Foto' : 'Unggah Foto'"></span>
                    </button>
                    <div x-show="isUploadingPhoto" x-transition.opacity.duration.150ms class="rounded-xl border border-blue-100 bg-blue-50/70 px-3 py-2" x-cloak>
                        <div class="flex items-center justify-between gap-3">
                            <span class="truncate text-xs font-semibold text-blue-700" x-text="photoDetail"></span>
                            <span class="shrink-0 text-xs font-bold tabular-nums text-blue-700" x-text="photoProgress + '%'"></span>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-blue-100">
                            <div class="h-full rounded-full bg-blue-600 transition-all duration-300" :style="`width: ${photoProgress}%`"></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Backup Upload -->
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-shadow hover:shadow-md sm:rounded-3xl sm:p-6"
             @dragover.prevent="dragBackup = true"
             @dragleave.prevent="dragBackup = false"
             @drop.prevent="dropBackup($event)">
            
            <div class="mb-3 flex items-center justify-between sm:mb-4">
                <h3 class="flex items-center gap-2 text-base font-semibold text-slate-800 sm:text-lg">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-50 text-se-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    </span>
                    Upload Data Backup
                </h3>
            </div>

            <form id="formBackup" action="{{ route('petugas.files.upload.backup') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div :class="dragBackup ? 'bg-amber-50 border-amber-400 border-solid' : 'bg-slate-50 border-slate-200 border-dashed'" 
                     class="flex min-h-36 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 p-4 text-center transition-all sm:h-48 sm:p-8"
                     @click="$refs.backupInput.click()">
                    
                    <input type="file" name="backup" x-ref="backupInput" class="hidden" @change="handleBackupSelect">
                    
                    <div x-show="!backupFile" class="space-y-2 sm:space-y-3">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full border border-slate-100 bg-white text-amber-500 shadow-sm sm:h-16 sm:w-16">
                            <svg class="h-6 w-6 sm:h-8 sm:w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <p class="text-sm font-medium text-slate-700">Pilih atau letakkan file backup</p>
                        <p class="text-xs text-slate-400">Semua ekstensi file (Max 50MB)</p>
                    </div>

                    <div x-show="backupFile" class="space-y-3 w-full px-4" x-cloak>
                        <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto shadow-sm text-amber-600 mb-2">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-800 truncate px-4" x-text="backupFileName"></p>
                        <p class="text-xs font-medium text-amber-600">Klik untuk mengganti file</p>
                    </div>
                </div>

                <div x-show="backupFile" x-collapse class="mt-4 space-y-4" x-cloak>
                    <div>
                        <input type="text" name="rename" placeholder="Nama file baru (Opsional)" class="w-full text-sm border-slate-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all bg-slate-50">
                    </div>
                    <button type="button" @click="submitBackup" :disabled="isUploadingBackup" class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-transparent bg-amber-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-amber-500 disabled:opacity-90">
                        <svg x-show="isUploadingBackup" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isUploadingBackup ? 'Mengunggah Backup' : 'Unggah Backup'"></span>
                    </button>
                    <div x-show="isUploadingBackup" x-transition.opacity.duration.150ms class="rounded-xl border border-amber-100 bg-amber-50/70 px-3 py-2" x-cloak>
                        <div class="flex items-center justify-between gap-3">
                            <span class="truncate text-xs font-semibold text-amber-700" x-text="backupDetail"></span>
                            <span class="shrink-0 text-xs font-bold tabular-nums text-amber-700" x-text="backupProgress + '%'"></span>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-amber-100">
                            <div class="h-full rounded-full bg-amber-500 transition-all duration-300" :style="`width: ${backupProgress}%`"></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <section id="file-saya" x-data="{ viewMode: '{{ request('type') === 'photo' ? 'grid' : 'table' }}' }">
        <div class="file-results-shell">
            <div class="border-b border-slate-100 bg-white px-4 py-3 sm:px-6 sm:py-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-50 text-se-primary">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7.5A2.5 2.5 0 015.5 5h3.1a2 2 0 011.4.57l1.1 1.08a2 2 0 001.4.57h6A2.5 2.5 0 0121 9.72V16.5a2.5 2.5 0 01-2.5 2.5h-13A2.5 2.5 0 013 16.5v-9z"></path></svg>
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-base font-semibold tracking-tight text-se-ink sm:text-lg">File Saya</h2>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('petugas.dashboard') }}#file-saya" method="GET" class="grid w-full gap-2 sm:gap-3 lg:w-[min(100%,28rem)]">
                        @if(request('type'))
                            <input type="hidden" name="type" value="{{ request('type') }}">
                        @endif
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama file..." class="file-search-control h-10 rounded-xl pl-9 text-xs sm:h-10">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>

                        <button type="submit" class="sr-only">Terapkan pencarian</button>
                    </form>
                </div>
            </div>

            <div class="file-tabs-shell !py-2">
                <div class="file-tab-list">
                    <a href="{{ request()->fullUrlWithQuery(['type' => null, 'page' => null]) }}#file-saya" class="file-tab {{ empty(request('type')) ? 'file-tab-active-neutral' : '' }}" title="Semua File">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span class="{{ empty(request('type')) ? '' : 'max-sm:sr-only' }}">Semua File</span>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['type' => 'photo', 'page' => null]) }}#file-saya" class="file-tab {{ request('type') === 'photo' ? 'file-tab-active-photo' : '' }}" title="Foto Laporan">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span class="{{ request('type') === 'photo' ? '' : 'max-sm:sr-only' }}">Foto Laporan</span>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['type' => 'backup', 'page' => null]) }}#file-saya" class="file-tab {{ request('type') === 'backup' ? 'file-tab-active-backup' : '' }}" title="Data Backup">
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

            <div x-show="viewMode === 'table'" x-transition.opacity.duration.150ms x-cloak class="file-panel">
                <div class="overflow-x-auto">
                    <table class="file-table !min-w-0 table-fixed md:!min-w-0">
                        <thead class="file-table-head">
                            <tr>
                                <th class="file-table-th">File</th>
                                <th class="file-table-th w-24 text-right sm:w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($files as $file)
                            <tr class="file-table-row">
                                <td class="file-table-cell">
                                    <div class="flex items-center gap-2.5 sm:gap-3">
                                        <div class="file-icon h-9 w-9 sm:h-11 sm:w-11 {{ $file->type === 'photo' ? 'file-icon-photo' : 'file-icon-backup' }}">
                                            @if($file->type === 'photo')
                                                <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            @else
                                                <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="file-name" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                                            <p class="mt-0.5 text-[11px] font-medium text-slate-500">{{ $file->human_size }} - {{ $file->created_at->format('d M H:i') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="file-table-cell text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        @if($file->status === 'uploaded')
                                            @if($file->drive_web_view_link)
                                             <a href="{{ $file->drive_web_view_link }}" target="_blank" class="file-action file-action-blue max-sm:h-8 max-sm:w-8" title="Buka di Drive">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                            </a>
                                            @endif
                                             <a href="{{ route('file.download', $file) }}" class="file-action file-action-green max-sm:h-8 max-sm:w-8" title="Download">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            </a>
                                        @endif
                                        @if($file->status !== 'uploading' && $file->status !== 'deleted')
                                             <button type="button" @click="requestDeleteFile({ id: {{ $file->id }}, name: @js($file->original_name) })" class="file-action file-action-rose max-sm:h-8 max-sm:w-8" title="Hapus">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="px-6 py-14">
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

            <div x-show="viewMode === 'grid'" x-transition.opacity.duration.150ms x-cloak class="p-4">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                    @forelse($files as $file)
                        <div
                            class="file-grid-card !overflow-visible flex flex-col {{ $file->type === 'photo' && $file->status === 'uploaded' ? 'cursor-pointer' : '' }}"
                            @if($file->type === 'photo' && $file->status === 'uploaded')
                                @click="openPhotoPreview({
                                    name: @js($file->original_name),
                                    src: @js(route('file.view', $file)),
                                    size: @js($file->human_size),
                                    date: @js($file->created_at->format('d M Y H:i'))
                                })"
                            @endif
                        >
                            <div class="relative flex aspect-[4/3] items-center justify-center overflow-hidden rounded-t-xl bg-slate-50">
                                @if($file->type === 'photo' && $file->status === 'uploaded')
                                    <img src="{{ route('file.view', $file) }}" alt="{{ $file->original_name }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async">
                                @else
                                    <div class="file-icon {{ $file->type === 'photo' ? 'file-icon-photo' : 'file-icon-backup' }}">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $file->type === 'photo' ? 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z' : 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4' }}"></path></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-start justify-between gap-2 rounded-b-xl border-t border-slate-100 bg-white px-2.5 py-2 sm:gap-3 sm:px-3">
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-semibold text-slate-900 sm:text-sm" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                                    <p class="mt-0.5 truncate text-[11px] font-medium text-slate-500 sm:text-xs">{{ $file->created_at->format('d M') }} - {{ $file->human_size }}</p>
                                </div>
                                <div x-data="{ open: false }" class="relative shrink-0" @click.stop @click.outside="open = false">
                                    <button type="button" @click="open = !open" class="inline-flex h-7 w-8 items-center justify-center rounded-full border border-slate-200/80 bg-white text-slate-500 shadow-sm shadow-slate-950/5 transition hover:border-orange-200 hover:bg-orange-50 hover:text-se-primary focus:outline-none focus:ring-4 focus:ring-orange-500/10" title="Aksi file" aria-label="Aksi file">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M6 12h.01M12 12h.01M18 12h.01"></path>
                                        </svg>
                                    </button>
                                    <div x-show="open" x-transition.origin.bottom.right x-cloak class="absolute bottom-full right-0 z-20 mb-1 w-32 overflow-hidden rounded-xl border border-slate-100 bg-white py-1 shadow-lg shadow-slate-950/10">
                                        @if($file->status === 'uploaded' && $file->drive_web_view_link)
                                            <a href="{{ $file->drive_web_view_link }}" target="_blank" class="flex w-full items-center gap-1.5 px-2.5 py-2 text-left text-xs font-medium text-slate-600 transition hover:bg-blue-50 hover:text-blue-600">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                Buka Drive
                                            </a>
                                        @endif
                                        @if($file->status === 'uploaded')
                                            <a href="{{ route('file.download', $file) }}" class="flex w-full items-center gap-1.5 px-2.5 py-2 text-left text-xs font-medium text-slate-600 transition hover:bg-green-50 hover:text-green-600">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                Download
                                            </a>
                                        @endif
                                        @if($file->status !== 'uploading' && $file->status !== 'deleted')
                                            <button type="button" @click="open = false; requestDeleteFile({ id: {{ $file->id }}, name: @js($file->original_name) })" class="flex w-full items-center gap-1.5 px-2.5 py-2 text-left text-xs font-medium text-slate-600 transition hover:bg-rose-50 hover:text-rose-600">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Hapus
                                            </button>
                                        @endif
                                    </div>
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
            <div class="border-t border-slate-100 bg-slate-50/70 px-3 py-4 sm:px-6">
                {{ $files->fragment('file-saya')->links() }}
            </div>
            @endif
        </div>
    </section>

    <template x-teleport="body">
        <div
            x-show="deleteModalOpen"
            x-transition.opacity.duration.150ms
            x-cloak
            class="fixed inset-0 z-[80] flex min-h-dvh items-center justify-center bg-slate-900/45 px-4 py-6 backdrop-blur-[2px]"
            @keydown.escape.window="closeDeleteFileModal()"
        >
            <button
                type="button"
                class="absolute inset-0 cursor-default"
                aria-label="Tutup modal"
                @click="closeDeleteFileModal()"
            ></button>

            <div
                x-show="deleteModalOpen"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-[0.98]"
                class="relative w-full max-w-[30rem] overflow-hidden rounded-2xl border border-white/80 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.28)] ring-1 ring-slate-950/5"
                role="dialog"
                aria-modal="true"
            >
                <div class="flex items-start gap-4 px-6 pb-5 pt-6">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600 ring-4 ring-rose-50/80">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg font-bold leading-7 text-slate-950">Hapus file permanen?</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-600">
                            File ini akan dihapus dari database aplikasi dan Google Drive.
                        </p>
                        <div class="mt-3 inline-flex max-w-full items-center rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700">
                            <span class="truncate" x-text="deleteFileTarget.name"></span>
                        </div>
                    </div>
                    <button type="button" @click="closeDeleteFileModal()" class="-mr-2 -mt-2 flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="mx-6 mb-5 rounded-xl border border-rose-100 bg-rose-50/70 p-4 text-sm text-rose-800">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-rose-600 ring-1 ring-rose-100">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold leading-5">Data tidak bisa dikembalikan dari aplikasi.</div>
                            <p class="mt-1.5 text-xs leading-5 text-rose-700">
                                Pastikan file sudah tidak diperlukan sebelum melanjutkan penghapusan.
                            </p>
                        </div>
                    </div>
                </div>

                <div x-show="deleteFileError" x-cloak class="mx-6 mb-5 rounded-xl border border-rose-200 bg-white px-4 py-3 text-xs font-semibold leading-5 text-rose-700">
                    <span x-text="deleteFileError"></span>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50/80 px-6 py-4 sm:flex-row sm:justify-end">
                    <button type="button" @click="closeDeleteFileModal()" :disabled="deleteFileLoading" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60">
                        Batal
                    </button>
                    <button type="button" @click="confirmDeleteFile()" :disabled="deleteFileLoading" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-rose-600 px-5 text-sm font-semibold text-white shadow-sm shadow-rose-600/20 transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-80">
                        <svg x-show="deleteFileLoading" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="deleteFileLoading ? 'Menghapus' : 'Hapus permanen'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div
            x-show="photoPreviewOpen"
            x-transition.opacity.duration.150ms
            x-cloak
            class="fixed inset-0 z-[80] flex min-h-dvh items-center justify-center bg-slate-950/70 px-3 py-5 backdrop-blur-[2px] sm:px-6"
            @keydown.escape.window="closePhotoPreview()"
        >
            <button
                type="button"
                class="absolute inset-0 cursor-default"
                aria-label="Tutup preview"
                @click="closePhotoPreview()"
            ></button>

            <div
                x-show="photoPreviewOpen"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-[0.98]"
                class="relative flex max-h-[92dvh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-white/10 bg-slate-950 shadow-[0_24px_80px_rgba(0,0,0,0.38)]"
                role="dialog"
                aria-modal="true"
            >
                <div class="flex items-center justify-between gap-3 border-b border-white/10 bg-slate-950/95 px-4 py-3 text-white sm:px-5">
                    <div class="min-w-0">
                        <h2 class="truncate text-sm font-semibold sm:text-base" x-text="photoPreview.name"></h2>
                        <p class="mt-0.5 truncate text-xs text-slate-300">
                            <span x-text="photoPreview.date"></span>
                            <span class="mx-1.5 text-slate-500">-</span>
                            <span x-text="photoPreview.size"></span>
                        </p>
                    </div>
                    <button type="button" @click="closePhotoPreview()" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/5 text-slate-200 transition hover:bg-white/10 hover:text-white" aria-label="Tutup">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="flex min-h-0 flex-1 items-center justify-center bg-slate-900 p-3 sm:p-5">
                    <img
                        :src="photoPreview.src"
                        :alt="photoPreview.name"
                        class="max-h-[calc(92dvh-5.5rem)] w-auto max-w-full rounded-xl object-contain shadow-2xl shadow-black/30"
                    >
                </div>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('uploadManager', () => ({
        dragPhoto: false,
        photoFile: null,
        photoPreview: null,
        isUploadingPhoto: false,
        photoProgress: 0,
        photoDetail: 'Mengunggah',

        dragBackup: false,
        backupFile: null,
        backupFileName: '',
        isUploadingBackup: false,
        backupProgress: 0,
        backupDetail: 'Mengunggah',

        progressTimers: {},
        deleteModalOpen: false,
        deleteFileTarget: { id: null, name: '' },
        deleteFileLoading: false,
        deleteFileError: '',
        photoPreviewOpen: false,
        photoPreview: { name: '', src: '', size: '', date: '' },

        init() {},

        openPhotoPreview(photo) {
            this.photoPreview = photo;
            this.photoPreviewOpen = true;
        },

        closePhotoPreview() {
            this.photoPreviewOpen = false;
            this.photoPreview = { name: '', src: '', size: '', date: '' };
        },

        requestDeleteFile(file) {
            this.deleteFileTarget = file;
            this.deleteFileError = '';
            this.deleteModalOpen = true;
        },

        closeDeleteFileModal() {
            if (this.deleteFileLoading) return;

            this.deleteModalOpen = false;
            this.deleteFileTarget = { id: null, name: '' };
            this.deleteFileError = '';
        },

        confirmDeleteFile() {
            if (!this.deleteFileTarget.id || this.deleteFileLoading) return;

            this.deleteFileLoading = true;
            this.deleteFileError = '';

            fetch(`/petugas/files/${this.deleteFileTarget.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                    return;
                }

                this.deleteFileError = data.message || 'Gagal menghapus file.';
            })
            .catch(() => {
                this.deleteFileError = 'Terjadi kesalahan koneksi.';
            })
            .finally(() => {
                this.deleteFileLoading = false;
            });
        },

        handlePhotoSelect(e) {
            const files = e.target.files;
            if (files.length > 0) this.setPhoto(files[0]);
        },
        dropPhoto(e) {
            this.dragPhoto = false;
            if (e.dataTransfer.files.length > 0) {
                this.setPhoto(e.dataTransfer.files[0]);
                this.$refs.photoInput.files = e.dataTransfer.files;
            }
        },
        setPhoto(file) {
            if (!file.type.startsWith('image/')) {
                alert('Pilih file gambar yang valid.');
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                alert('Ukuran foto maksimal 10MB.');
                return;
            }
            this.photoFile = file;
            this.photoPreview = URL.createObjectURL(file);
        },
        submitPhoto() {
            if (!this.photoFile) return;
            this.isUploadingPhoto = true;

            const file = this.photoFile;
            const formData = new FormData(document.getElementById('formPhoto'));
            formData.set('photo', file);

            this.uploadWithProgress({
                endpoint: "{{ route('petugas.files.upload.photo') }}",
                formData,
                file,
                type: 'photo',
                progressKey: 'photo',
                onSuccess: () => {
                    this.photoDetail = 'Berhasil';
                    this.photoProgress = 100;
                    this.photoFile = null;
                    this.photoPreview = null;
                    this.$refs.photoInput.value = '';
                    document.getElementById('formPhoto').reset();
                    setTimeout(() => window.location.reload(), 700);
                },
                onComplete: () => {
                    this.isUploadingPhoto = false;
                },
            });
        },

        // Backup
        handleBackupSelect(e) {
            const files = e.target.files;
            if (files.length > 0) this.setBackup(files[0]);
        },
        dropBackup(e) {
            this.dragBackup = false;
            if (e.dataTransfer.files.length > 0) {
                this.setBackup(e.dataTransfer.files[0]);
                this.$refs.backupInput.files = e.dataTransfer.files;
            }
        },
        setBackup(file) {
            if (file.size > 50 * 1024 * 1024) {
                alert('Ukuran file maksimal 50MB.');
                return;
            }
            this.backupFile = file;
            this.backupFileName = file.name;
        },
        submitBackup() {
            if (!this.backupFile) return;
            this.isUploadingBackup = true;

            const file = this.backupFile;
            const formData = new FormData(document.getElementById('formBackup'));
            formData.set('backup', file);

            this.uploadWithProgress({
                endpoint: "{{ route('petugas.files.upload.backup') }}",
                formData,
                file,
                type: 'backup',
                progressKey: 'backup',
                onSuccess: () => {
                    this.backupDetail = 'Berhasil';
                    this.backupProgress = 100;
                    this.backupFile = null;
                    this.backupFileName = '';
                    this.$refs.backupInput.value = '';
                    document.getElementById('formBackup').reset();
                    setTimeout(() => window.location.reload(), 700);
                },
                onComplete: () => {
                    this.isUploadingBackup = false;
                },
            });
        },

        uploadWithProgress({ endpoint, formData, file, type, progressKey, onSuccess, onComplete }) {
            this[`${progressKey}Progress`] = 0;
            this[`${progressKey}Detail`] = 'Mengirim';
            this.setUploadProgress(progressKey, 3, 'Mengirim');

            const xhr = new XMLHttpRequest();
            xhr.open('POST', endpoint);
            xhr.timeout = 180000;
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.upload.addEventListener('progress', (event) => {
                if (!event.lengthComputable) {
                    this.setUploadProgress(progressKey, 10, 'Mengunggah');
                    return;
                }

                const percent = Math.round((event.loaded / event.total) * 100);
                const mappedPercent = Math.min(60, Math.max(5, Math.round(percent * 0.6)));
                this.setUploadProgress(
                    progressKey,
                    mappedPercent,
                    percent >= 100 ? 'Memproses' : 'Mengunggah'
                );

                if (percent >= 100) {
                    this.startServerProcessingProgress(progressKey, type);
                }
            });

            xhr.upload.addEventListener('load', () => {
                this.setUploadProgress(progressKey, 60, 'Memproses');
                this.startServerProcessingProgress(progressKey, type);
            });

            xhr.onload = () => {
                this.stopServerProcessingProgress(progressKey);
                const data = this.parseUploadResponse(xhr);

                if (xhr.status >= 200 && xhr.status < 300 && data.success) {
                    this.setUploadProgress(progressKey, 100, 'Berhasil');
                    if (onSuccess) onSuccess();
                    return;
                }

                this.setUploadProgress(progressKey, 100, 'Gagal');
                alert(this.uploadErrorMessage(data));
            };

            xhr.onerror = () => {
                this.stopServerProcessingProgress(progressKey);
                this.setUploadProgress(progressKey, 100, 'Gagal');
                alert('Terjadi kesalahan koneksi.');
            };

            xhr.ontimeout = () => {
                this.stopServerProcessingProgress(progressKey);
                this.setUploadProgress(progressKey, 100, 'Gagal');
                alert('Server terlalu lama memproses upload. Coba file yang lebih kecil atau ulangi beberapa saat lagi.');
            };

            xhr.onloadend = () => {
                if (onComplete) onComplete();
            };

            this.setUploadProgress(progressKey, 5, 'Mengunggah');
            xhr.send(formData);
        },

        parseUploadResponse(xhr) {
            try {
                return JSON.parse(xhr.responseText || '{}');
            } catch (error) {
                return {};
            }
        },

        uploadErrorMessage(data) {
            if (data.message) return data.message;
            if (data.errors) return Object.values(data.errors).flat().join(' ');
            return 'Gagal mengunggah file.';
        },

        setUploadProgress(key, progress, detail) {
            const progressProp = `${key}Progress`;
            const detailProp = `${key}Detail`;
            this[progressProp] = Math.min(100, Math.max(this[progressProp] || 0, progress));
            this[detailProp] = detail;
        },

        startServerProcessingProgress(key, type) {
            if (this.progressTimers[key]) return;

            this.setUploadProgress(key, 62, type === 'photo' ? 'Kompresi' : 'Proses');
            this.progressTimers[key] = setInterval(() => {
                const progressProp = `${key}Progress`;
                const current = this[progressProp] || 0;

                if (current >= 94) return;

                const next = current < 78
                    ? current + 4
                    : current < 90
                        ? current + 2
                        : current + 1;

                this.setUploadProgress(key, next, 'Drive');
            }, 900);
        },

        stopServerProcessingProgress(key) {
            if (!this.progressTimers[key]) return;

            clearInterval(this.progressTimers[key]);
            delete this.progressTimers[key];
        }
    }));
});
</script>
@endpush
