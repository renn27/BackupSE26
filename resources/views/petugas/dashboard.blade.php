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
                    <span class="p-2 bg-blue-50 text-blue-600 rounded-lg">
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
                        <p class="text-xs text-slate-400">JPG, PNG, WebP (Max 10MB)<br>Akan dikompresi otomatis</p>
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
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-lg">
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

    <!-- Recent Files Sections (Tabs) -->
    <div x-data="{ activeTab: 'photo' }" class="mt-6 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm sm:mt-8 sm:rounded-3xl">
        <div class="flex border-b border-slate-100 bg-slate-50/50">
            <button @click="activeTab = 'photo'" :class="activeTab === 'photo' ? 'border-b-2 border-blue-600 text-blue-600 bg-white' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'" class="flex min-h-12 flex-1 items-center justify-center gap-2 px-3 py-3 text-xs font-semibold transition-all sm:px-6 sm:py-4 sm:text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Foto Terbaru
            </button>
            <button @click="activeTab = 'backup'" :class="activeTab === 'backup' ? 'border-b-2 border-amber-500 text-amber-600 bg-white' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'" class="flex min-h-12 flex-1 items-center justify-center gap-2 px-3 py-3 text-xs font-semibold transition-all sm:px-6 sm:py-4 sm:text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                Backup Terbaru
            </button>
        </div>

        <div class="p-4 sm:p-6">
            <!-- Tab Foto -->
            <div x-show="activeTab === 'photo'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <div class="mb-4 flex justify-end sm:mb-6">
                    <a href="{{ route('petugas.dashboard', ['type' => 'photo']) }}#file-saya" class="hidden min-h-10 items-center justify-center rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-semibold text-blue-600 transition-colors hover:text-blue-700 sm:inline-flex">Lihat Semua Foto</a>
                </div>
                
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 md:grid-cols-4 lg:grid-cols-5">
                    @forelse($recentPhotos as $photo)
                        <div class="{{ $loop->iteration > 4 ? 'hidden sm:flex' : 'flex' }} bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden group hover:shadow-md transition-all relative aspect-square flex-col justify-between">
                            <div class="flex-1 relative overflow-hidden bg-slate-100">
                                <img src="{{ route('file.view', $photo) }}" alt="{{ $photo->original_name }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy" decoding="async">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <p class="absolute bottom-3 left-3 right-3 text-xs font-medium text-white truncate text-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10" title="{{ $photo->original_name }}">{{ $photo->original_name }}</p>
                            </div>
                            <div class="p-3 bg-white flex items-center justify-between z-20">
                                <span class="text-[10px] font-semibold text-slate-400">{{ $photo->created_at->format('d M') }}</span>
                                <div class="flex gap-1">
                                    @if($photo->drive_web_view_link)
                                    <a href="{{ $photo->drive_web_view_link }}" target="_blank" class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors" title="Buka di Drive">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-slate-400 bg-slate-50 border border-slate-100 border-dashed rounded-2xl">
                            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <p class="font-medium">Belum ada foto yang diunggah.</p>
                        </div>
                    @endforelse
                </div>
                @if($recentPhotos->count() > 0)
                    <a href="{{ route('petugas.dashboard', ['type' => 'photo']) }}#file-saya" class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-semibold text-blue-600 transition-colors hover:text-blue-700 sm:hidden">Lihat Semua Foto</a>
                @endif
            </div>
            
            <!-- Tab Backup -->
            <div x-show="activeTab === 'backup'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;" x-cloak>
                <div class="mb-4 flex justify-end sm:mb-6">
                    <a href="{{ route('petugas.dashboard', ['type' => 'backup']) }}#file-saya" class="hidden min-h-10 items-center justify-center rounded-lg bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-600 transition-colors hover:text-amber-700 sm:inline-flex">Lihat Semua Backup</a>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 md:gap-4">
                    @forelse($recentBackups as $backup)
                    <div class="{{ $loop->iteration > 4 ? 'hidden sm:flex' : 'flex' }} group items-center justify-between rounded-2xl border border-slate-100 bg-white p-3 transition-all hover:border-amber-200 hover:shadow-sm sm:p-4">
                        <div class="flex items-center gap-4 overflow-hidden">
                            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-800 truncate" title="{{ $backup->original_name }}">{{ $backup->original_name }}</p>
                                <div class="flex items-center gap-2 mt-1 text-xs font-medium text-slate-500">
                                    <span class="bg-slate-100 px-2 py-0.5 rounded text-slate-600">{{ $backup->human_size }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $backup->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            @if($backup->drive_web_view_link)
                            <a href="{{ $backup->drive_web_view_link }}" target="_blank" class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition-colors" title="Buka di Drive">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full py-12 text-center text-slate-400 bg-slate-50 border border-slate-100 border-dashed rounded-2xl">
                        <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <p class="font-medium">Belum ada file backup.</p>
                    </div>
                    @endforelse
                </div>
                @if($recentBackups->count() > 0)
                    <a href="{{ route('petugas.dashboard', ['type' => 'backup']) }}#file-saya" class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-600 transition-colors hover:text-amber-700 sm:hidden">Lihat Semua Backup</a>
                @endif
            </div>
        </div>
    </div>

    <section id="file-saya" x-data="{ viewMode: '{{ request('type') === 'photo' ? 'grid' : 'table' }}' }">
        <div class="file-results-shell">
            <div class="border-b border-slate-100 bg-white px-4 py-4 sm:px-6 sm:py-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 text-slate-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-lg font-semibold tracking-tight text-se-ink sm:text-xl">File Saya</h2>
                                <p class="mt-0.5 hidden text-sm text-slate-500 sm:block">Kelola seluruh foto laporan dan data backup.</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('petugas.dashboard') }}#file-saya" method="GET" class="grid w-full grid-cols-[minmax(0,1fr)_auto] gap-2 sm:gap-3 lg:w-[min(100%,52rem)] lg:grid-cols-[minmax(0,1fr)_12rem_auto]">
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

                        <button type="submit" class="file-primary-button min-w-28 px-3 sm:min-w-32 lg:px-5">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            Filter
                        </button>
                    </form>
                </div>
            </div>

            <div class="file-tabs-shell">
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
                    <table class="file-table">
                        <thead class="file-table-head">
                            <tr>
                                <th class="file-table-th">File</th>
                                <th class="file-table-th text-center">Status</th>
                                <th class="file-table-th hidden w-36 md:table-cell">Ukuran</th>
                                <th class="file-table-th hidden md:table-cell">Waktu Upload</th>
                                <th class="file-table-th text-right">Aksi</th>
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
                                            <p class="mt-0.5 text-[11px] font-medium text-slate-500 md:hidden">{{ $file->human_size }} - {{ $file->created_at->format('d M H:i') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="file-table-cell text-center">
                                    <span class="file-status-badge {{ $file->status === 'uploaded' ? 'file-status-success' : ($file->status === 'uploading' ? 'file-status-process' : ($file->status === 'failed' ? 'file-status-failed' : 'file-status-muted')) }}">
                                        {{ $file->status === 'uploaded' ? 'Berhasil' : ($file->status === 'uploading' ? 'Proses' : ($file->status === 'failed' ? 'Gagal' : 'Dihapus')) }}
                                    </span>
                                </td>
                                <td class="file-table-cell hidden md:table-cell"><span class="file-size-value">{{ $file->human_size }}</span></td>
                                <td class="file-table-cell hidden text-xs font-medium text-slate-500 md:table-cell">{{ $file->created_at->format('d M Y H:i') }}</td>
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

            <div x-show="viewMode === 'grid'" x-transition.opacity.duration.150ms x-cloak class="p-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                    @forelse($files as $file)
                        <div class="file-grid-card flex flex-col">
                            <div class="relative flex aspect-[4/3] items-center justify-center overflow-hidden bg-slate-50">
                                @if($file->type === 'photo' && $file->status === 'uploaded')
                                    <img src="{{ route('file.view', $file) }}" alt="{{ $file->original_name }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 hover:scale-105" loading="lazy" decoding="async">
                                @else
                                    <div class="file-icon {{ $file->type === 'photo' ? 'file-icon-photo' : 'file-icon-backup' }}">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $file->type === 'photo' ? 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z' : 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4' }}"></path></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center justify-between gap-3 border-t border-slate-100 bg-white px-3 py-2">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                                    <p class="mt-0.5 text-xs font-medium text-slate-500">{{ $file->created_at->format('d M') }} - {{ $file->human_size }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-0.5">
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

        init() {},

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

function deleteFile(id) {
    if (!confirm('Yakin ingin menghapus file ini? File akan dihapus permanen dari Google Drive.')) return;

    fetch(`/petugas/files/${id}`, {
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
        } else {
            alert(data.message || 'Gagal menghapus file.');
        }
    })
    .catch(() => {
        alert('Terjadi kesalahan koneksi.');
    });
}
</script>
@endpush
