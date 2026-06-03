@extends('layouts.app')

@section('title', 'Tanya KonDef')

@section('content')
@php
    $notebookUrl = 'https://notebooklm.google.com/notebook/1c715acd-c98d-4e47-9414-3ab09e96fe67';
@endphp

<div class="mx-auto max-w-3xl space-y-5 sm:space-y-8">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-950/5 sm:rounded-3xl sm:p-6">
        <div class="flex items-start gap-3 sm:gap-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-se-subtle text-se-rust ring-1 ring-amber-200/70 sm:h-12 sm:w-12 sm:rounded-2xl">
                <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.84L3 20l1.33-3.1A7.45 7.45 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
            </span>
            <div class="min-w-0">
                <h1 class="text-lg font-semibold tracking-tight text-se-ink sm:text-2xl">Tanya KonDef</h1>
                <p class="mt-1 text-sm leading-6 text-slate-500">Buka NotebookLM untuk bertanya seputar konsep dan definisi SE2026.</p>
            </div>
        </div>
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-950/5 sm:rounded-3xl sm:p-6">
        <div class="rounded-2xl border border-amber-200 bg-se-subtle p-4 sm:rounded-3xl sm:p-6">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3 sm:gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-se-primary text-white shadow-sm shadow-se-primary/25 sm:h-16 sm:w-16">
                        <svg class="h-6 w-6 sm:h-8 sm:w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.84L3 20l1.33-3.1A7.45 7.45 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-se-rust sm:text-xl sm:tracking-tight">Notebook Tanya KonDef</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-600">Terhubung ke notebook referensi. Akses akan dibuka di tab baru.</p>
                    </div>
                </div>

                <a href="{{ $notebookUrl }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-se-primary px-6 text-sm font-semibold text-white shadow-sm shadow-se-primary/20 transition hover:bg-se-rust">
                    Buka Sekarang
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
