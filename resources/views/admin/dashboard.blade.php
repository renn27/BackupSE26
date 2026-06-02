@extends('layouts.app')

@section('title', 'Dashboard Superadmin')

@section('content')
<div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Card 1 -->
        <div class="p-6 bg-white border border-slate-100 rounded-2xl shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4 opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Petugas</p>
                    <p class="text-2xl font-semibold text-slate-800">{{ $stats['active_users'] }} <span class="text-sm font-normal text-slate-400">/ {{ $stats['total_users'] }} aktif</span></p>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="p-6 bg-white border border-slate-100 rounded-2xl shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute right-0 top-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-4 -mt-4 opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Foto</p>
                    <p class="text-2xl font-semibold text-slate-800">{{ number_format($stats['total_photos']) }}</p>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="p-6 bg-white border border-slate-100 rounded-2xl shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute right-0 top-0 w-24 h-24 bg-amber-50 rounded-bl-full -mr-4 -mt-4 opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Backup</p>
                    <p class="text-2xl font-semibold text-slate-800">{{ number_format($stats['total_backups']) }}</p>
                </div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="p-6 bg-white border border-slate-100 rounded-2xl shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute right-0 top-0 w-24 h-24 bg-green-50 rounded-bl-full -mr-4 -mt-4 opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">Storage Digunakan</p>
                    <p class="text-2xl font-semibold text-slate-800">
                        @if($stats['total_size_bytes'] >= 1073741824)
                            {{ number_format($stats['total_size_bytes'] / 1073741824, 2) }} <span class="text-sm font-normal text-slate-400">GB</span>
                        @elseif($stats['total_size_bytes'] >= 1048576)
                            {{ number_format($stats['total_size_bytes'] / 1048576, 2) }} <span class="text-sm font-normal text-slate-400">MB</span>
                        @else
                            {{ number_format($stats['total_size_bytes'] / 1024, 2) }} <span class="text-sm font-normal text-slate-400">KB</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert for Failed Files -->
    @if($stats['failed_files'] > 0)
    <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-rose-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div>
            <h3 class="text-sm font-semibold text-rose-800">Perhatian</h3>
            <p class="text-sm text-rose-700 mt-1">Terdapat {{ $stats['failed_files'] }} file yang gagal diunggah ke Google Drive. <a href="{{ route('admin.files.index', ['status' => 'failed']) }}" class="underline font-medium hover:text-rose-900">Lihat Detail</a></p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart -->
        <div class="lg:col-span-2 flex min-h-[420px] flex-col bg-white border border-slate-100 rounded-2xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Aktivitas Upload 7 Hari Terakhir</h3>
            <div class="min-h-0 flex-1">
                <canvas id="uploadChart"></canvas>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-6 overflow-hidden flex flex-col">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-slate-800">Aktivitas Terkini</h3>
                <a href="{{ route('admin.activities.index') }}" class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-amber-200 hover:bg-amber-50 hover:text-se-rust">
                    Lihat Semua
                </a>
            </div>
            <div class="flex-1 overflow-y-auto pr-2 space-y-4">
                @forelse($recentActivity as $log)
                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 border border-slate-200">
                            @if($log->action === 'login')
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            @elseif(str_starts_with($log->action, 'upload'))
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            @elseif($log->action === 'delete_file')
                                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            @else
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @endif
                        </div>
                        <div class="w-px h-full bg-slate-100 mt-2"></div>
                    </div>
                    <div class="pb-2">
                        <p class="text-sm font-medium text-slate-800">{{ $log->user->name ?? 'Sistem' }}</p>
                        <p class="text-xs text-slate-500">
                            @if($log->action === 'login') Login ke sistem
                            @elseif($log->action === 'upload_photo') Mengunggah foto <span class="font-medium">"{{ $log->properties['filename'] ?? '' }}"</span>
                            @elseif($log->action === 'upload_backup') Mengunggah backup <span class="font-medium">"{{ $log->properties['filename'] ?? '' }}"</span>
                            @elseif($log->action === 'delete_file') Menghapus file <span class="font-medium">"{{ $log->properties['filename'] ?? '' }}"</span>
                            @else {{ $log->action }}
                            @endif
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-slate-400 text-sm">Belum ada aktivitas.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Files Table (Tabs) -->
    <div x-data="{ activeTab: 'photo' }" class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden mt-8">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-lg font-semibold text-slate-800">File Terbaru Diunggah</h3>
            <a href="{{ route('admin.files.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Lihat Semua</a>
        </div>
        
        <!-- Tab Navigation -->
        <div class="flex border-b border-slate-100 bg-white">
            <button @click="activeTab = 'photo'" :class="activeTab === 'photo' ? 'border-b-2 border-blue-600 text-blue-600 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'" class="flex-1 py-3 px-4 text-sm font-semibold flex items-center justify-center gap-2 transition-all -mb-px">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Foto Laporan
            </button>
            <button @click="activeTab = 'backup'" :class="activeTab === 'backup' ? 'border-b-2 border-amber-500 text-amber-600 bg-amber-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'" class="flex-1 py-3 px-4 text-sm font-semibold flex items-center justify-center gap-2 transition-all -mb-px">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                Data Backup
            </button>
        </div>

        @php
            $recentPhotos = $recentFiles->where('type', 'photo');
            $recentBackups = $recentFiles->where('type', 'backup');
        @endphp

        <!-- Tab Foto -->
        <div x-show="activeTab === 'photo'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="overflow-x-auto" style="display: none;">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500 font-medium border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3">Nama Foto</th>
                        <th class="px-6 py-3">Petugas</th>
                        <th class="px-6 py-3">Ukuran</th>
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentPhotos as $file)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-3 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div class="truncate max-w-[200px] text-slate-700 font-medium" title="{{ $file->original_name }}">{{ $file->original_name }}</div>
                        </td>
                        <td class="px-6 py-3 text-slate-600 font-medium">{{ $file->user->name ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-slate-600">
                            <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-xs">{{ $file->human_size }}</span>
                        </td>
                        <td class="px-6 py-3 text-slate-500">{{ $file->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('file.view', $file) }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-slate-200 transition-colors" title="Lihat">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <a href="{{ route('file.download', $file) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" title="Download">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                            <svg class="w-8 h-8 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Belum ada foto yang diunggah.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Tab Backup -->
        <div x-show="activeTab === 'backup'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="overflow-x-auto" style="display: none;" x-cloak>
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500 font-medium border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3">Nama Backup</th>
                        <th class="px-6 py-3">Petugas</th>
                        <th class="px-6 py-3">Ukuran</th>
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentBackups as $file)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-3 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                            </div>
                            <div class="truncate max-w-[200px] text-slate-700 font-medium" title="{{ $file->original_name }}">{{ $file->original_name }}</div>
                        </td>
                        <td class="px-6 py-3 text-slate-600 font-medium">{{ $file->user->name ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-slate-600">
                            <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-xs">{{ $file->human_size }}</span>
                        </td>
                        <td class="px-6 py-3 text-slate-500">{{ $file->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('file.download', $file) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" title="Download">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                            <svg class="w-8 h-8 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                            Belum ada backup yang diunggah.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('uploadChart').getContext('2d');
        const chartData = @json($uploadChart);
        
        const labels = chartData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        });
        const data = chartData.map(item => item.count);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Upload',
                    data: data,
                    backgroundColor: '#f09030',
                    hoverBackgroundColor: '#f0b020',
                    borderColor: '#c85b16',
                    borderWidth: 1,
                    borderRadius: 8,
                    borderSkipped: false,
                    barPercentage: 0.42,
                    categoryPercentage: 0.72,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: { top: 4, right: 4, bottom: 0, left: 0 }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1f1a14',
                        titleColor: '#fff7ed',
                        bodyColor: '#fff7ed',
                        borderColor: '#f0b020',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1e8dc' },
                        border: { display: false },
                        ticks: {
                            stepSize: 1,
                            color: '#a8a29e',
                            padding: 10,
                            precision: 0,
                        }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: '#a8a29e',
                            padding: 8,
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
