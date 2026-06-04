@extends('layouts.app')

@section('title', 'Tanya KonDef')

@section('content')
@php
    $notebooks = [
        [
            'title' => 'Tanya Kondef SE2026.P',
            'description' => 'Notebook referensi konsep dan definisi untuk SE2026.P.',
            'url' => 'https://notebooklm.google.com/notebook/9d657e4d-6844-4458-a5d3-464afe4ecc99?authuser=2',
            'icon' => 'pencil',
        ],
        [
            'title' => 'Tanya Kondef SE2026.L',
            'description' => 'Notebook referensi konsep dan definisi untuk SE2026.L.',
            'url' => 'https://notebooklm.google.com/notebook/9fc547be-358c-45da-8650-b9280c06aa3b?authuser=2',
            'icon' => 'list',
        ],
        [
            'title' => 'Tanya KBLI 2026',
            'description' => 'Notebook referensi KBLI 2026.',
            'url' => 'https://notebooklm.google.com/notebook/f12ec5bf-d166-4335-8719-ca9e425ff32b?authuser=2',
            'icon' => 'book',
        ],
    ];
@endphp

<div class="mx-auto max-w-5xl space-y-5 sm:space-y-8">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-950/5 sm:rounded-3xl sm:p-6">
        <div class="flex items-start gap-3 sm:gap-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-se-subtle text-se-rust ring-1 ring-amber-200/70 sm:h-12 sm:w-12 sm:rounded-2xl">
                <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5c5 0 8.5 3.05 8.5 7.15S17 19 12 19a10.4 10.4 0 01-3.6-.63L4 19.5l1.25-3.25a6.85 6.85 0 01-1.75-4.6C3.5 7.55 7 4.5 12 4.5z"></path>
                    <circle cx="8.7" cy="11.75" r="0.8" fill="currentColor" stroke="none"></circle>
                    <circle cx="12" cy="11.75" r="0.8" fill="currentColor" stroke="none"></circle>
                    <circle cx="15.3" cy="11.75" r="0.8" fill="currentColor" stroke="none"></circle>
                </svg>
            </span>
            <div class="min-w-0">
                <h1 class="text-lg font-semibold tracking-tight text-se-ink sm:text-2xl">Tanya KonDef</h1>
                <p class="mt-1 text-sm leading-6 text-slate-500">Buka NotebookLM untuk bertanya seputar konsep dan definisi SE2026.</p>
            </div>
        </div>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($notebooks as $notebook)
            <div class="flex h-full flex-col rounded-2xl border border-amber-200 bg-white p-4 shadow-sm shadow-slate-950/5">
                <div class="flex flex-1 items-start gap-3">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-se-primary text-white shadow-sm shadow-se-primary/25">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($notebook['icon'] === 'pencil')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L9.38 17.273a4.5 4.5 0 01-1.897 1.13L4.5 19.5l1.097-2.983a4.5 4.5 0 011.13-1.897L16.862 4.487z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 6l3 3"></path>
                            @elseif($notebook['icon'] === 'list')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h12M8 12h12M8 18h12"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h.01M4 12h.01M4 18h.01"></path>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.75c-1.7-1.2-3.85-1.8-6.5-1.8A1.5 1.5 0 004 6.45v12.3a1.2 1.2 0 001.29 1.2c2.75-.2 4.95.35 6.71 1.55"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.75c1.7-1.2 3.85-1.8 6.5-1.8A1.5 1.5 0 0120 6.45v12.3a1.2 1.2 0 01-1.29 1.2c-2.75-.2-4.95.35-6.71 1.55V6.75z"></path>
                            @endif
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-base font-semibold leading-6 text-se-rust">{{ $notebook['title'] }}</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-600">{{ $notebook['description'] }}</p>
                    </div>
                </div>

                <a href="{{ $notebook['url'] }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="mt-5 inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-se-primary px-5 text-sm font-semibold text-white shadow-sm shadow-se-primary/20 transition-colors hover:bg-se-rust focus:outline-none focus:ring-4 focus:ring-orange-500/10">
                    Buka Sekarang
                </a>
            </div>
        @endforeach
    </section>
</div>
@endsection
