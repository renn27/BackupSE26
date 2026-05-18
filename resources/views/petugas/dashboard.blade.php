@extends('layouts.app')

@section('title', 'Dashboard Petugas')

@section('content')
<div class="space-y-5 sm:space-y-8" x-data="uploadManager()">
    
    <!-- Welcome Card -->
    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-sm shadow-slate-950/5 sm:p-6">
        <div class="absolute inset-y-0 right-0 hidden w-72 bg-gradient-to-l from-amber-50 via-orange-50/60 to-transparent lg:block"></div>
        <div class="relative flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div class="mb-4 hidden h-1 w-16 rounded-full bg-gradient-to-r from-se-primary to-se-gold sm:block"></div>
                <h2 class="text-lg font-bold tracking-tight text-se-ink sm:text-2xl">Selamat datang, {{ explode(' ', auth()->user()->name)[0] }}!</h2>
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
                    <p class="mt-1.5 text-lg font-bold leading-none tracking-tight text-se-rust sm:mt-2 sm:text-2xl">{{ $stats['total_photos'] + $stats['total_backups'] }}</p>
                </div>
                <div class="border-l border-amber-100 bg-amber-50/30 px-3.5 py-2.5 sm:bg-transparent sm:px-4 sm:py-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 sm:text-xs">Storage</p>
                            <p class="mt-0.5 truncate text-sm font-bold tracking-tight text-se-rust sm:text-lg">{{ $formatBytes($storageUsed) }} <span class="text-[10px] font-semibold text-slate-500 sm:text-xs">/ 15 GB</span></p>
                        </div>
                        <span class="shrink-0 rounded-full bg-white px-2 py-0.5 text-[10px] font-bold text-se-rust shadow-sm ring-1 ring-amber-100 sm:text-[11px]">{{ number_format($storagePercent, 1) }}%</span>
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
                        <input type="text" name="category" placeholder="Kategori/Tags (Opsional)" class="w-full text-sm border-slate-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all bg-slate-50">
                    </div>
                    <div>
                        <textarea name="description" placeholder="Deskripsi foto (Opsional)" rows="2" class="w-full text-sm border-slate-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all bg-slate-50"></textarea>
                    </div>
                    <button type="button" @click="submitPhoto" :disabled="isUploadingPhoto" class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-transparent bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70">
                        <svg x-show="isUploadingPhoto" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isUploadingPhoto ? 'Mengunggah...' : 'Unggah Foto'"></span>
                    </button>
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
                        <input type="text" name="category" placeholder="Kategori/Bulan (Opsional)" class="w-full text-sm border-slate-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all bg-slate-50">
                    </div>
                    <div>
                        <textarea name="description" placeholder="Catatan backup (Opsional)" rows="2" class="w-full text-sm border-slate-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all bg-slate-50"></textarea>
                    </div>
                    <button type="button" @click="submitBackup" :disabled="isUploadingBackup" class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-transparent bg-amber-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70">
                        <svg x-show="isUploadingBackup" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isUploadingBackup ? 'Mengunggah...' : 'Unggah Backup'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Upload Progress -->
    <div x-show="uploadingFiles.length > 0" x-transition.opacity.duration.150ms class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm shadow-slate-950/5 sm:p-4" style="display: none;">
        <div class="mb-3 flex items-center justify-between gap-3">
            <h3 class="text-sm font-bold text-se-ink">Progres Upload</h3>
            <span class="text-xs font-semibold text-slate-400" x-text="uploadingFiles.length + ' file'"></span>
        </div>
        <div class="space-y-3">
            <template x-for="file in uploadingFiles" :key="file.uid">
                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border bg-white shadow-sm"
                             :class="file.type === 'photo' ? 'border-blue-100 text-blue-600' : 'border-amber-100 text-amber-600'">
                            <svg x-show="file.type === 'photo'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <svg x-show="file.type === 'backup'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900" x-text="file.name"></p>
                                    <p class="mt-0.5 text-xs font-medium text-slate-500" x-text="file.detail"></p>
                                </div>
                                <span class="shrink-0 rounded-full border px-2 py-0.5 text-[11px] font-bold"
                                      :class="statusBadgeClass(file)"
                                      x-text="statusLabel(file)"></span>
                            </div>
                            <div class="mt-3 flex items-center gap-3">
                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200">
                                    <div class="h-full rounded-full transition-all duration-300"
                                         :class="progressBarClass(file)"
                                         :style="'width: ' + file.progress + '%'"></div>
                                </div>
                                <span class="w-10 text-right text-xs font-bold tabular-nums text-slate-500" x-text="file.progress + '%'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Recent Files Sections (Tabs) -->
    <div x-data="{ activeTab: 'photo' }" class="mt-6 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm sm:mt-8 sm:rounded-3xl">
        <div class="flex border-b border-slate-100 bg-slate-50/50">
            <button @click="activeTab = 'photo'" :class="activeTab === 'photo' ? 'border-b-2 border-blue-600 text-blue-600 bg-white' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'" class="flex min-h-12 flex-1 items-center justify-center gap-2 px-3 py-3 text-xs font-bold transition-all sm:px-6 sm:py-4 sm:text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Foto Terbaru
            </button>
            <button @click="activeTab = 'backup'" :class="activeTab === 'backup' ? 'border-b-2 border-amber-500 text-amber-600 bg-white' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'" class="flex min-h-12 flex-1 items-center justify-center gap-2 px-3 py-3 text-xs font-bold transition-all sm:px-6 sm:py-4 sm:text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                Backup Terbaru
            </button>
        </div>

        <div class="p-4 sm:p-6">
            <!-- Tab Foto -->
            <div x-show="activeTab === 'photo'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <div class="mb-4 flex justify-end sm:mb-6">
                    <a href="{{ route('petugas.files.index', ['type' => 'photo']) }}" class="hidden min-h-10 items-center justify-center rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-semibold text-blue-600 transition-colors hover:text-blue-700 sm:inline-flex">Lihat Semua Foto</a>
                </div>
                
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 md:grid-cols-4 lg:grid-cols-5">
                    @forelse($recentPhotos as $photo)
                        <div class="{{ $loop->iteration > 4 ? 'hidden sm:flex' : 'flex' }} bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden group hover:shadow-md transition-all relative aspect-square flex-col justify-between">
                            <div class="flex-1 relative overflow-hidden bg-slate-100">
                                <img src="{{ route('file.view', $photo) }}" alt="{{ $photo->original_name }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
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
                    <a href="{{ route('petugas.files.index', ['type' => 'photo']) }}" class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-semibold text-blue-600 transition-colors hover:text-blue-700 sm:hidden">Lihat Semua Foto</a>
                @endif
            </div>
            
            <!-- Tab Backup -->
            <div x-show="activeTab === 'backup'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;" x-cloak>
                <div class="mb-4 flex justify-end sm:mb-6">
                    <a href="{{ route('petugas.files.index', ['type' => 'backup']) }}" class="hidden min-h-10 items-center justify-center rounded-lg bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-600 transition-colors hover:text-amber-700 sm:inline-flex">Lihat Semua Backup</a>
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
                    <a href="{{ route('petugas.files.index', ['type' => 'backup']) }}" class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-600 transition-colors hover:text-amber-700 sm:hidden">Lihat Semua Backup</a>
                @endif
            </div>
        </div>
    </div>
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

        dragBackup: false,
        backupFile: null,
        backupFileName: '',
        isUploadingBackup: false,

        uploadingFiles: [],
        reloadScheduled: false,

        init() {
            // Check pending uploads periodically
            setInterval(() => this.checkStatuses(), 3000);
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
                onSuccess: () => {
                    this.photoFile = null;
                    this.photoPreview = null;
                    this.$refs.photoInput.value = '';
                    document.getElementById('formPhoto').reset();
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
                onSuccess: () => {
                    this.backupFile = null;
                    this.backupFileName = '';
                    this.$refs.backupInput.value = '';
                    document.getElementById('formBackup').reset();
                },
                onComplete: () => {
                    this.isUploadingBackup = false;
                },
            });
        },

        uploadWithProgress({ endpoint, formData, file, type, onSuccess, onComplete }) {
            const uploadItem = {
                uid: 'upload-' + Date.now() + '-' + Math.random().toString(36).slice(2),
                id: null,
                name: file.name,
                type,
                progress: 0,
                status: 'uploading',
                detail: 'Mengirim file ke server...',
            };

            this.uploadingFiles.unshift(uploadItem);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', endpoint);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.upload.addEventListener('progress', (event) => {
                if (!event.lengthComputable) {
                    uploadItem.progress = Math.max(uploadItem.progress, 5);
                    return;
                }

                const percent = Math.round((event.loaded / event.total) * 100);
                uploadItem.progress = Math.min(percent, 99);
                uploadItem.detail = percent >= 100
                    ? 'Menyimpan sementara di server...'
                    : 'Mengunggah ke server...';
            });

            xhr.onload = () => {
                const data = this.parseUploadResponse(xhr);

                if (xhr.status >= 200 && xhr.status < 300 && data.success) {
                    uploadItem.id = data.file_id;
                    uploadItem.progress = 100;
                    uploadItem.status = 'processing';
                    uploadItem.detail = data.message || 'Memproses file ke Google Drive...';
                    if (onSuccess) onSuccess();
                    return;
                }

                uploadItem.progress = 100;
                uploadItem.status = 'failed';
                uploadItem.detail = this.uploadErrorMessage(data);
            };

            xhr.onerror = () => {
                uploadItem.progress = 100;
                uploadItem.status = 'failed';
                uploadItem.detail = 'Terjadi kesalahan koneksi.';
            };

            xhr.onloadend = () => {
                if (onComplete) onComplete();
            };

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

        statusLabel(file) {
            if (file.status === 'uploaded') return 'Berhasil';
            if (file.status === 'failed') return 'Gagal';
            if (file.status === 'processing') return 'Proses';
            return 'Upload';
        },

        statusBadgeClass(file) {
            if (file.status === 'uploaded') return 'border-green-200 bg-green-50 text-green-700';
            if (file.status === 'failed') return 'border-rose-200 bg-rose-50 text-rose-700';
            if (file.status === 'processing') return 'border-amber-200 bg-amber-50 text-amber-700';
            return 'border-blue-200 bg-blue-50 text-blue-700';
        },

        progressBarClass(file) {
            if (file.status === 'uploaded') return 'bg-green-500';
            if (file.status === 'failed') return 'bg-rose-500';
            if (file.status === 'processing') return 'bg-amber-500';
            return 'bg-blue-600';
        },

        checkStatuses() {
            if (this.uploadingFiles.length === 0) return;

            this.uploadingFiles
                .filter(file => file.id && file.status === 'processing')
                .forEach((file) => {
                fetch(`/petugas/files/${file.id}/status`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'uploaded') {
                        file.status = 'uploaded';
                        file.progress = 100;
                        file.detail = 'File berhasil tersedia di Google Drive.';
                        this.scheduleReloadIfReady();
                    } else if (data.status === 'failed') {
                        file.status = 'failed';
                        file.progress = 100;
                        file.detail = data.upload_error || 'Upload ke Google Drive gagal.';
                        this.scheduleReloadIfReady();
                    } else {
                        file.detail = 'Mengunggah ke Google Drive...';
                    }
                });
            });
        },

        scheduleReloadIfReady() {
            if (this.reloadScheduled || this.uploadingFiles.length === 0) return;

            const allFinished = this.uploadingFiles.every(file => file.status === 'uploaded' || file.status === 'failed');
            const hasSuccess = this.uploadingFiles.some(file => file.status === 'uploaded');

            if (allFinished && hasSuccess) {
                this.reloadScheduled = true;
                setTimeout(() => window.location.reload(), 1200);
            }
        }
    }));
});
</script>
@endpush
