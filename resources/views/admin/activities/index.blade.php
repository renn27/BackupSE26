@extends('layouts.app')

@section('title', 'Semua Aktivitas')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-950/5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-se-ink">Semua Aktivitas</h2>
                <p class="mt-1 text-sm text-slate-500">Riwayat aktivitas login, upload, dan penghapusan file.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-amber-200 hover:bg-amber-50 hover:text-se-rust">
                Kembali
            </a>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-950/5">
        <div class="divide-y divide-slate-100">
            @forelse($activities as $log)
                <div class="flex gap-3 px-5 py-4">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50">
                        @if($log->action === 'login')
                            <svg class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        @elseif(str_starts_with($log->action, 'upload'))
                            <svg class="h-4 w-4 text-se-rust" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        @elseif($log->action === 'delete_file')
                            <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        @else
                            <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ $log->user->name ?? 'Sistem' }}</p>
                            <p class="shrink-0 text-xs font-medium text-slate-400">{{ $log->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">
                            @if($log->action === 'login') Login ke sistem
                            @elseif($log->action === 'upload_photo') Mengunggah foto <span class="font-medium text-slate-700">"{{ $log->properties['filename'] ?? '' }}"</span>
                            @elseif($log->action === 'upload_backup') Mengunggah backup <span class="font-medium text-slate-700">"{{ $log->properties['filename'] ?? '' }}"</span>
                            @elseif($log->action === 'delete_file') Menghapus file <span class="font-medium text-slate-700">"{{ $log->properties['filename'] ?? '' }}"</span>
                            @else {{ $log->action }}
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center text-sm font-medium text-slate-400">
                    Belum ada aktivitas.
                </div>
            @endforelse
        </div>

        @if($activities->hasPages())
            <div class="border-t border-slate-100 bg-slate-50/70 px-5 py-4">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
