@extends('layouts.app')

@section('title', 'Monitoring SBR')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-se-ink">Monitoring SBR</h1>
            <p class="mt-1 text-sm text-slate-500">Upload data usaha dan kelola penugasan desa untuk petugas.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm shadow-slate-950/5">
            {{ number_format($villages->count()) }} desa tersedia
        </div>
    </div>

    @if(session('upload_result'))
        @php
            $result = session('upload_result');
        @endphp
        <div class="rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-800 shadow-sm">
            Upload selesai. Inserted: <b>{{ number_format($result['inserted']) }}</b>,
            Updated: <b>{{ number_format($result['updated']) }}</b>,
            Skipped: <b>{{ number_format($result['skipped']) }}</b>.
        </div>
    @endif

    @php
        $recordedPercent = min(100, max(0, (float) $summary['progress']));
        $unrecordedPercent = max(0, 100 - $recordedPercent);
        $assignedVillagePercent = $summary['villages'] > 0
            ? min(100, round(($summary['assigned_villages'] / $summary['villages']) * 100, 1))
            : 0;
        $statusTotal = max(1, array_sum($summary['statuses']));
        $tidakDitemukanStop = round(($summary['statuses']['tidak_ditemukan'] / $statusTotal) * 100, 1);
        $ditemukanStop = round((($summary['statuses']['tidak_ditemukan'] + $summary['statuses']['ditemukan']) / $statusTotal) * 100, 1);
        $baruStop = round((($summary['statuses']['tidak_ditemukan'] + $summary['statuses']['ditemukan'] + $summary['statuses']['baru']) / $statusTotal) * 100, 1);
        $tutupStop = round((($summary['statuses']['tidak_ditemukan'] + $summary['statuses']['ditemukan'] + $summary['statuses']['baru'] + $summary['statuses']['tutup']) / $statusTotal) * 100, 1);
        $statusItems = [
            ['label' => 'Tidak Ditemukan', 'value' => $summary['statuses']['tidak_ditemukan'], 'color' => 'bg-slate-500', 'text' => 'text-slate-700', 'soft' => 'bg-slate-50'],
            ['label' => 'Ditemukan', 'value' => $summary['statuses']['ditemukan'], 'color' => 'bg-green-500', 'text' => 'text-green-700', 'soft' => 'bg-green-50'],
            ['label' => 'Baru', 'value' => $summary['statuses']['baru'], 'color' => 'bg-blue-500', 'text' => 'text-blue-700', 'soft' => 'bg-blue-50'],
            ['label' => 'Tutup', 'value' => $summary['statuses']['tutup'], 'color' => 'bg-rose-500', 'text' => 'text-rose-700', 'soft' => 'bg-rose-50'],
            ['label' => 'Ganda', 'value' => $summary['statuses']['ganda'], 'color' => 'bg-amber-500', 'text' => 'text-amber-700', 'soft' => 'bg-amber-50'],
        ];
    @endphp

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-950/5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-se-ink">Dashboard Monitoring</h2>
                <p class="mt-1 text-sm text-slate-500">Ringkasan pencatatan status usaha SBR per wilayah dan petugas.</p>
            </div>
            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-medium text-se-rust">
                <span class="h-2 w-2 rounded-full bg-se-primary"></span>
                {{ $summary['progress'] }}% tercatat
            </div>
        </div>

        <div class="mt-5 grid gap-4 xl:grid-cols-[minmax(0,1fr)_280px]">
            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Progress Pencatatan</p>
                        <div class="mt-2 flex flex-wrap items-end gap-x-3 gap-y-1">
                            <p class="text-4xl font-semibold leading-none text-se-ink">{{ $summary['progress'] }}%</p>
                            <p class="pb-1 text-sm text-slate-500">{{ number_format($summary['recorded']) }} dari {{ number_format($summary['businesses']) }} usaha</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm lg:min-w-[320px]">
                        <div class="rounded-2xl bg-white p-3 ring-1 ring-slate-200">
                            <p class="text-xs text-slate-500">Sudah dicatat</p>
                            <p class="mt-1 text-xl font-semibold text-green-700">{{ number_format($summary['recorded']) }}</p>
                        </div>
                        <div class="rounded-2xl bg-white p-3 ring-1 ring-slate-200">
                            <p class="text-xs text-slate-500">Belum dicatat</p>
                            <p class="mt-1 text-xl font-semibold text-slate-700">{{ number_format($summary['unrecorded']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 h-3 overflow-hidden rounded-full bg-white ring-1 ring-slate-200">
                    <div class="h-full rounded-full bg-se-primary" style="width: {{ $recordedPercent }}%"></div>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-se-primary"></span>Tercatat {{ $recordedPercent }}%</span>
                    <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-slate-300"></span>Belum {{ $unrecordedPercent }}%</span>
                    <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-amber-400"></span>Desa ditugaskan {{ $assignedVillagePercent }}%</span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Komposisi Status</p>
                <div class="mx-auto mt-4 flex h-36 w-36 items-center justify-center rounded-full"
                    style="background: conic-gradient(#64748b 0 {{ $tidakDitemukanStop }}%, #22c55e {{ $tidakDitemukanStop }}% {{ $ditemukanStop }}%, #3b82f6 {{ $ditemukanStop }}% {{ $baruStop }}%, #f43f5e {{ $baruStop }}% {{ $tutupStop }}%, #f59e0b {{ $tutupStop }}% 100%);">
                    <div class="flex h-24 w-24 flex-col items-center justify-center rounded-full bg-white text-center shadow-sm">
                        <span class="text-2xl font-semibold text-se-ink">{{ number_format(array_sum($summary['statuses'])) }}</span>
                        <span class="text-xs text-slate-500">status</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Usaha</p>
                <p class="mt-2 text-2xl font-semibold text-se-ink">{{ number_format($summary['businesses']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Desa</p>
                <p class="mt-2 text-2xl font-semibold text-se-ink">{{ number_format($summary['villages']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Desa Ditugaskan</p>
                <p class="mt-2 text-2xl font-semibold text-se-rust">{{ number_format($summary['assigned_villages']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Coverage Desa</p>
                <p class="mt-2 text-2xl font-semibold text-se-rust">{{ $assignedVillagePercent }}%</p>
            </div>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            @foreach($statusItems as $status)
                @php($statusPercent = round(($status['value'] / $statusTotal) * 100, 1))
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $status['label'] }}</p>
                            <p class="mt-2 text-2xl font-semibold {{ $status['text'] }}">{{ number_format($status['value']) }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-xs {{ $status['soft'] }} {{ $status['text'] }}">{{ $statusPercent }}%</span>
                    </div>
                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full {{ $status['color'] }}" style="width: {{ $statusPercent }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="space-y-6">
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-950/5">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-semibold text-se-ink">Monitoring Per Wilayah</h2>
                <p class="mt-1 text-sm text-slate-500">Progress pencatatan status berdasarkan kecamatan.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[920px] divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="w-[34%] px-4 py-3">Kecamatan</th>
                            <th class="w-[10%] px-4 py-3">Desa</th>
                            <th class="w-[12%] px-4 py-3">Usaha</th>
                            <th class="w-[12%] px-4 py-3">Tercatat</th>
                            <th class="w-[32%] px-4 py-3">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($regionMonitoring as $region)
                            @php($accordionId = 'region-' . \Illuminate\Support\Str::slug($region->nmkec))
                            <tr class="cursor-pointer transition hover:bg-slate-50" data-region-toggle="{{ $accordionId }}">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span data-region-icon="{{ $accordionId }}" class="inline-flex h-6 w-6 items-center justify-center rounded-lg border border-slate-200 text-xs font-medium text-se-rust">+</span>
                                        <span class="font-medium text-se-ink">{{ $region->nmkec }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ number_format($region->villages_count) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ number_format($region->businesses_count) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ number_format($region->recorded_count) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex min-w-[120px] items-center gap-2">
                                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-se-primary" style="width: {{ min(100, $region->progress) }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium text-se-rust">{{ $region->progress }}%</span>
                                    </div>
                                </td>
                            </tr>
                            <tr id="{{ $accordionId }}" class="hidden bg-slate-50/70">
                                <td colspan="5" class="px-4 py-4">
                                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                        <table class="w-full min-w-[820px] divide-y divide-slate-200 text-xs">
                                            <thead class="bg-slate-50 text-left font-medium uppercase tracking-wide text-slate-500">
                                                <tr>
                                                    <th class="w-[30%] px-3 py-2">Desa</th>
                                                    <th class="w-[12%] px-3 py-2">Kode</th>
                                                    <th class="w-[12%] px-3 py-2">Usaha</th>
                                                    <th class="w-[12%] px-3 py-2">Tercatat</th>
                                                    <th class="w-[12%] px-3 py-2">Belum</th>
                                                    <th class="w-[22%] px-3 py-2">Progress</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @foreach($villageMonitoring->get($region->nmkec, collect()) as $villageRow)
                                                    <tr>
                                                        <td class="px-3 py-2 font-medium text-se-ink">{{ $villageRow->nmdesa }}</td>
                                                        <td class="px-3 py-2 font-mono text-slate-500">{{ $villageRow->kdkec }}.{{ $villageRow->kddesa }}</td>
                                                        <td class="px-3 py-2 text-slate-600">{{ number_format($villageRow->businesses_count) }}</td>
                                                        <td class="px-3 py-2 text-slate-600">{{ number_format($villageRow->recorded_count) }}</td>
                                                        <td class="px-3 py-2 text-slate-600">{{ number_format($villageRow->unrecorded_count) }}</td>
                                                        <td class="px-3 py-2">
                                                            <div class="flex min-w-[120px] items-center gap-2">
                                                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                                                                    <div class="h-full rounded-full bg-se-primary" style="width: {{ min(100, $villageRow->progress) }}%"></div>
                                                                </div>
                                                                <span class="font-medium text-se-rust">{{ $villageRow->progress }}%</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada data wilayah.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-950/5">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-semibold text-se-ink">Monitoring Per Petugas</h2>
                <p class="mt-1 text-sm text-slate-500">Progress dihitung dari usaha pada desa yang ditugaskan ke petugas.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[920px] divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="w-[34%] px-4 py-3">Petugas</th>
                            <th class="w-[10%] px-4 py-3">Desa</th>
                            <th class="w-[12%] px-4 py-3">Usaha</th>
                            <th class="w-[12%] px-4 py-3">Tercatat</th>
                            <th class="w-[32%] px-4 py-3">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($userMonitoring as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-se-ink">{{ $item->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->email }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ number_format($item->assigned_villages_count) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ number_format($item->businesses_count) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ number_format($item->recorded_count) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex min-w-[120px] items-center gap-2">
                                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-se-primary" style="width: {{ min(100, $item->progress) }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium text-se-rust">{{ $item->progress }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada petugas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-950/5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-se-ink">Upload Excel</h2>
                    <p class="mt-1 text-sm text-slate-500">Format kolom mengikuti data SBR: idsbr, desa, kecamatan, usaha, alamat, dan koordinat.</p>
                </div>
            </div>

            <form id="sbr-upload-form" action="{{ route('admin.monitoring-sbr.upload') }}" method="POST" enctype="multipart/form-data" class="mt-5 flex flex-col gap-3 lg:flex-row">
                @csrf
                <input type="file" name="excel_file" accept=".xlsx,.xls" required class="min-h-12 flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-white file:px-4 file:py-2 file:text-sm file:font-medium file:text-se-rust">
                <button id="sbr-upload-button" class="min-h-12 rounded-2xl bg-se-primary px-6 text-sm font-medium text-white shadow-sm shadow-se-primary/25 transition hover:bg-se-rust lg:w-52">
                    Upload & Proses
                </button>
            </form>
            @error('excel_file')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror

            <div id="sbr-upload-progress" class="mt-4 hidden rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p id="sbr-progress-title" class="text-sm font-medium text-se-ink">Menyiapkan upload...</p>
                        <p id="sbr-progress-detail" class="mt-1 text-xs text-slate-500">0 row diproses</p>
                    </div>
                    <span id="sbr-progress-percent" class="text-sm font-medium text-se-rust">0%</span>
                </div>
                <div class="mt-3 h-3 overflow-hidden rounded-full bg-white ring-1 ring-slate-200">
                    <div id="sbr-progress-bar" class="h-full w-0 rounded-full bg-se-primary transition-all duration-300"></div>
                </div>
                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-slate-500">Counter diperbarui saat server selesai memproses batch row.</p>
                    <button id="sbr-cancel-button" type="button" class="hidden rounded-xl border border-rose-200 bg-white px-4 py-2 text-xs font-medium text-rose-600 transition hover:bg-rose-50">
                        Batalkan Import
                    </button>
                </div>
                <div class="mt-4 grid gap-2 text-xs text-slate-600 sm:grid-cols-3">
                    <div id="sbr-step-upload" class="rounded-xl bg-white px-3 py-2 ring-1 ring-slate-200">1. Upload file</div>
                    <div id="sbr-step-import" class="rounded-xl bg-white px-3 py-2 ring-1 ring-slate-200">2. Import database</div>
                    <div id="sbr-step-finish" class="rounded-xl bg-white px-3 py-2 ring-1 ring-slate-200">3. Simpan log</div>
                </div>
                <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                    <div class="rounded-xl bg-white px-3 py-2 text-green-700 ring-1 ring-slate-200">Inserted <span id="sbr-progress-inserted">0</span></div>
                    <div class="rounded-xl bg-white px-3 py-2 text-amber-700 ring-1 ring-slate-200">Updated <span id="sbr-progress-updated">0</span></div>
                    <div class="rounded-xl bg-white px-3 py-2 text-slate-600 ring-1 ring-slate-200">Skipped <span id="sbr-progress-skipped">0</span></div>
                </div>
                <div class="mt-3 grid gap-2 text-xs text-slate-500 sm:grid-cols-3">
                    <div>Durasi: <span id="sbr-progress-elapsed" class="font-medium text-se-ink">00:00</span></div>
                    <div>Status server: <span id="sbr-progress-server" class="font-medium text-se-ink">menunggu</span></div>
                    <div>Update terakhir: <span id="sbr-progress-updated-at" class="font-medium text-se-ink">-</span></div>
                </div>
                <p id="sbr-progress-timeout" class="mt-2 text-xs text-slate-500">Batas waktu import: 20 menit.</p>
                <div id="sbr-progress-error" class="mt-3 hidden rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"></div>
            </div>

            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">File</th>
                            <th class="px-4 py-3">Inserted</th>
                            <th class="px-4 py-3">Updated</th>
                            <th class="px-4 py-3">Skipped</th>
                            <th class="px-4 py-3">Uploader</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($uploadLogs as $log)
                            <tr>
                                <td class="px-4 py-3 text-slate-500">{{ $log->uploaded_at?->format('d M Y H:i') }}</td>
                                <td class="max-w-[220px] truncate px-4 py-3 font-medium text-se-ink">{{ $log->filename }}</td>
                                <td class="px-4 py-3 text-green-700">{{ number_format($log->inserted) }}</td>
                                <td class="px-4 py-3 text-amber-700">{{ number_format($log->updated) }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ number_format($log->skipped) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $log->uploader?->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada riwayat upload.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-950/5">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-se-ink">Assignment Petugas</h2>
                    <p class="mt-1 text-sm text-slate-500">Cari petugas dan wilayah, lalu tambahkan desa ke daftar kerja.</p>
                </div>
                <span id="assignment-summary" class="inline-flex w-fit rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-500">Belum ada petugas dipilih</span>
            </div>

            <div class="mt-5 grid gap-4 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-se-ink">1. Pilih Petugas</p>
                            <p class="mt-1 text-xs text-slate-500">Cari nama atau email petugas.</p>
                        </div>
                    </div>
                    <label class="relative mt-4 block">
                        <span class="text-xs font-medium uppercase tracking-wide text-slate-500">Petugas</span>
                        <input id="assignment-user-search" type="search" autocomplete="off" placeholder="Ketik nama atau email..." class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-10 text-sm text-se-ink outline-none transition placeholder:text-slate-400 focus:border-se-primary/40 focus:ring-4 focus:ring-orange-100/70">
                        <span class="pointer-events-none absolute bottom-3.5 right-4 text-xs text-slate-400">&#9662;</span>
                        <div id="assignment-user-dropdown" class="absolute left-0 right-0 z-30 mt-2 hidden max-h-64 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-1 text-sm shadow-lg shadow-slate-950/10"></div>
                        <select id="assignment-user" class="hidden">
                            <option value="">Pilih petugas</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-se-ink">2. Tambah Wilayah</p>
                            <p class="mt-1 text-xs text-slate-500">Cari kecamatan atau desa yang akan ditugaskan.</p>
                        </div>
                    </div>
                    <div class="mt-3 grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                        <label class="relative block">
                            <span class="text-xs font-medium uppercase tracking-wide text-slate-500">Wilayah</span>
                            <input id="assignment-village-search" type="search" autocomplete="off" placeholder="Ketik kecamatan atau desa..." class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-10 text-sm text-se-ink outline-none transition placeholder:text-slate-400 focus:border-se-primary/40 focus:ring-4 focus:ring-orange-100/70">
                            <span class="pointer-events-none absolute bottom-3.5 right-4 text-xs text-slate-400">&#9662;</span>
                            <div id="assignment-village-dropdown" class="absolute left-0 right-0 z-30 mt-2 hidden max-h-64 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-1 text-sm shadow-lg shadow-slate-950/10"></div>
                            <select id="assignment-village" class="hidden">
                                <option value="">Pilih desa</option>
                                @foreach($villages as $village)
                                    <option value="{{ $village->id }}">{{ $village->nmkec }} - {{ $village->nmdesa }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button id="assignment-add" type="button" class="min-h-12 rounded-2xl bg-se-primary px-6 text-sm font-medium text-white shadow-sm shadow-se-primary/25 transition hover:bg-se-rust">
                            Tambah
                        </button>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 xl:col-span-2">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-se-ink">Desa Ter-assign</p>
                            <p class="mt-1 text-xs text-slate-500">Wilayah kerja petugas terpilih.</p>
                        </div>
                        <span id="assignment-count" class="inline-flex w-fit rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-500">0 desa</span>
                    </div>
                    <div id="assignment-list" class="mt-4 grid gap-2 text-sm text-slate-500 md:grid-cols-2 xl:grid-cols-3">
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-center text-sm text-slate-500 md:col-span-2 xl:col-span-3">
                            Pilih petugas untuk melihat assignment.
                        </div>
                    </div>
                </div>

                <p id="assignment-message" class="min-h-5 text-sm xl:col-span-2"></p>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const uploadForm = document.getElementById('sbr-upload-form');
const uploadButton = document.getElementById('sbr-upload-button');
const progressBox = document.getElementById('sbr-upload-progress');
const progressTitle = document.getElementById('sbr-progress-title');
const progressDetail = document.getElementById('sbr-progress-detail');
const progressPercent = document.getElementById('sbr-progress-percent');
const progressBar = document.getElementById('sbr-progress-bar');
const progressInserted = document.getElementById('sbr-progress-inserted');
const progressUpdated = document.getElementById('sbr-progress-updated');
const progressSkipped = document.getElementById('sbr-progress-skipped');
const progressElapsed = document.getElementById('sbr-progress-elapsed');
const progressServer = document.getElementById('sbr-progress-server');
const progressUpdatedAt = document.getElementById('sbr-progress-updated-at');
const progressTimeout = document.getElementById('sbr-progress-timeout');
const progressError = document.getElementById('sbr-progress-error');
const cancelButton = document.getElementById('sbr-cancel-button');
const stepUpload = document.getElementById('sbr-step-upload');
const stepImport = document.getElementById('sbr-step-import');
const stepFinish = document.getElementById('sbr-step-finish');
const userSearch = document.getElementById('assignment-user-search');
const userDropdown = document.getElementById('assignment-user-dropdown');
const userSelect = document.getElementById('assignment-user');
const villageSearch = document.getElementById('assignment-village-search');
const villageDropdown = document.getElementById('assignment-village-dropdown');
const villageSelect = document.getElementById('assignment-village');
const list = document.getElementById('assignment-list');
const count = document.getElementById('assignment-count');
const message = document.getElementById('assignment-message');
const assignmentSummary = document.getElementById('assignment-summary');
const userOptions = Array.from(userSelect.options).slice(1).map(option => ({value: option.value, label: option.textContent}));
const villageOptions = Array.from(villageSelect.options).slice(1).map(option => ({value: option.value, label: option.textContent}));
let elapsedTimer = null;
let uploadStartedAt = null;
let currentImportId = null;
let currentXhr = null;

document.querySelectorAll('[data-region-toggle]').forEach((row) => {
    row.addEventListener('click', () => {
        const target = document.getElementById(row.dataset.regionToggle);
        const icon = document.querySelector(`[data-region-icon="${row.dataset.regionToggle}"]`);
        const open = target.classList.toggle('hidden');
        icon.textContent = open ? '+' : '-';
    });
});

function formatNumber(value) {
    return new Intl.NumberFormat('id-ID').format(value || 0);
}

function setUploadProgress(percent, title, detail = '') {
    const safePercent = Math.max(0, Math.min(100, Math.round(percent || 0)));
    progressBox.classList.remove('hidden');
    progressTitle.textContent = title;
    progressDetail.textContent = detail;
    progressPercent.textContent = `${safePercent}%`;
    progressBar.style.width = `${safePercent}%`;
}

function setProgressError(text = '') {
    progressError.textContent = text;
    progressError.classList.toggle('hidden', !text);
}

function setStepState(activeStep) {
    const inactive = 'rounded-xl bg-white px-3 py-2 ring-1 ring-slate-200';
    const active = 'rounded-xl bg-se-subtle px-3 py-2 text-se-rust ring-1 ring-amber-200/70';
    const done = 'rounded-xl bg-green-50 px-3 py-2 text-green-700 ring-1 ring-green-200';

    stepUpload.className = activeStep === 'upload' ? active : (activeStep === 'import' || activeStep === 'finish' ? done : inactive);
    stepImport.className = activeStep === 'import' ? active : (activeStep === 'finish' ? done : inactive);
    stepFinish.className = activeStep === 'finish' ? active : inactive;
}

function updateImportStats(progress) {
    progressInserted.textContent = formatNumber(progress.inserted);
    progressUpdated.textContent = formatNumber(progress.updated);
    progressSkipped.textContent = formatNumber(progress.skipped);
    progressServer.textContent = progress.phase || 'menunggu';
    progressUpdatedAt.textContent = progress.updated_at || '-';
    progressTimeout.textContent = `Batas waktu import: ${Math.round((progress.timeout_seconds || 1200) / 60)} menit.`;
}

function startProgressPolling(importId) {
    const progressUrl = `{{ url('/admin/monitoring-sbr/upload') }}/${encodeURIComponent(importId)}/progress`;
    const timer = setInterval(async () => {
        let response;
        let progress;

        try {
            response = await fetch(progressUrl);
            progress = await response.json();
        } catch (error) {
            progressServer.textContent = 'polling tertahan';
            progressDetail.textContent = 'Server masih memproses. Jika memakai php artisan serve, progress row bisa baru muncul setelah import selesai.';
            return;
        }

        updateImportStats(progress);

        if (progress.phase === 'failed' || progress.phase === 'cancelled') {
            clearInterval(timer);
            setStepState('import');
            cancelButton.classList.add('hidden');
            const cancelled = progress.phase === 'cancelled';
            setUploadProgress(
                cancelled ? visualPercentFromProgress(progress) : 100,
                cancelled ? 'Import dibatalkan' : 'Import gagal',
                progress.message || (cancelled ? 'Import dihentikan.' : 'Terjadi error saat memproses Excel.')
            );
            setProgressError(cancelled ? '' : (progress.message || 'Terjadi error saat memproses Excel.'));
            uploadButton.disabled = false;
            uploadButton.textContent = 'Upload & Proses';
            return;
        }

        const visualPercent = visualPercentFromProgress(progress);
        setStepState(progress.phase === 'finished' ? 'finish' : 'import');
        setUploadProgress(
            visualPercent,
            progress.phase === 'finished' ? 'Import selesai' : (progress.phase === 'cancelling' ? 'Membatalkan import...' : 'Memproses data SBR...'),
            `${formatNumber(progress.processed)} dari sekitar ${formatNumber(progress.estimated_rows)} row diproses. ${progress.message || ''}`
        );

        if (progress.phase === 'finished') {
            clearInterval(timer);
            cancelButton.classList.add('hidden');
        }
    }, 900);

    return timer;
}

function visualPercentFromProgress(progress) {
    return progress.phase === 'finished' ? 100 : 15 + ((progress.percent || 0) * 0.85);
}

uploadForm.addEventListener('submit', (event) => {
    event.preventDefault();

    const importId = (window.crypto?.randomUUID ? window.crypto.randomUUID() : `${Date.now()}-${Math.random()}`).replace(/[^a-zA-Z0-9_-]/g, '');
    currentImportId = importId;
    const formData = new FormData(uploadForm);
    formData.append('import_id', importId);
    uploadButton.disabled = true;
    uploadButton.textContent = 'Mengunggah...';
    uploadStartedAt = Date.now();
    clearInterval(elapsedTimer);
    elapsedTimer = setInterval(() => {
        const seconds = Math.floor((Date.now() - uploadStartedAt) / 1000);
        const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
        const rest = String(seconds % 60).padStart(2, '0');
        progressElapsed.textContent = `${minutes}:${rest}`;
    }, 1000);
    setStepState('upload');
    setProgressError('');
    cancelButton.disabled = false;
    cancelButton.textContent = 'Batalkan Import';
    cancelButton.classList.remove('hidden');
    progressServer.textContent = 'mengunggah';
    progressUpdatedAt.textContent = '-';
    setUploadProgress(0, 'Mengirim file Excel...', 'Menunggu file diterima server');
    updateImportStats({inserted: 0, updated: 0, skipped: 0});
    const pollingTimer = startProgressPolling(importId);

    const xhr = new XMLHttpRequest();
    currentXhr = xhr;
    xhr.open('POST', uploadForm.action);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.upload.addEventListener('progress', (event) => {
        if (!event.lengthComputable) return;

        setUploadProgress((event.loaded / event.total) * 15, 'Mengirim file Excel...', `${formatNumber(event.loaded)} / ${formatNumber(event.total)} byte`);

        if (event.loaded === event.total) {
            setStepState('import');
            progressServer.textContent = 'request import berjalan';
            setUploadProgress(15, 'File terkirim, menunggu proses import...', 'Server sedang membaca Excel dan menyimpan sekitar 48 ribu row.');
        }
    });
    xhr.onload = () => {
        clearInterval(pollingTimer);
        clearInterval(elapsedTimer);
        cancelButton.classList.add('hidden');
        uploadButton.disabled = false;
        uploadButton.textContent = 'Upload & Proses';

        if (xhr.status >= 200 && xhr.status < 300) {
            const data = safeJson(xhr.responseText);
            updateImportStats(data.result || {});
            setStepState('finish');
            setUploadProgress(100, 'Import selesai', 'Halaman akan memuat ulang untuk memperbarui riwayat upload.');
            setTimeout(() => window.location.reload(), 900);
            return;
        }

        const data = safeJson(xhr.responseText);
        const errorText = data.error || data.message || extractHtmlError(xhr.responseText) || 'Periksa format file, ukuran file, atau konfigurasi server.';
        const cancelled = xhr.status === 409;
        setProgressError(cancelled ? '' : errorText);
        setUploadProgress(cancelled ? 15 : 100, cancelled ? 'Import dibatalkan' : 'Upload atau import gagal', errorText);
    };
    xhr.onerror = () => {
        clearInterval(pollingTimer);
        clearInterval(elapsedTimer);
        cancelButton.classList.add('hidden');
        uploadButton.disabled = false;
        uploadButton.textContent = 'Upload & Proses';
        setProgressError('Koneksi terputus saat upload/import. Coba lagi atau cek log server.');
        setUploadProgress(100, 'Upload gagal', 'Koneksi terputus saat upload.');
    };
    xhr.send(formData);
});

cancelButton.addEventListener('click', async () => {
    if (!currentImportId) return;

    cancelButton.disabled = true;
    cancelButton.textContent = 'Membatalkan...';
    progressServer.textContent = 'cancelling';
    setUploadProgress(parseInt(progressPercent.textContent, 10) || 15, 'Membatalkan import...', 'Permintaan batal dikirim ke server.');

    try {
        await fetch(`{{ url('/admin/monitoring-sbr/upload') }}/${encodeURIComponent(currentImportId)}/cancel`, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json'}
        });
    } catch (error) {
        setProgressError('Permintaan batal belum bisa dikirim. Jika memakai php artisan serve, request cancel bisa tertahan sampai import selesai.');
        cancelButton.disabled = false;
        cancelButton.textContent = 'Batalkan Import';
    }
});

function safeJson(text) {
    try {
        return JSON.parse(text || '{}');
    } catch (error) {
        return {};
    }
}

function extractHtmlError(text) {
    if (!text) return '';

    const match = text.match(/<title>(.*?)<\/title>/i);
    return match ? match[1].replace(/\s+/g, ' ').trim() : '';
}

function setMessage(text, ok = true) {
    message.textContent = text;
    message.className = `min-h-5 text-sm xl:col-span-2 ${ok ? 'text-green-700' : 'text-rose-600'}`;
}

function emptyAssignmentState(text) {
    return `<div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-center text-sm text-slate-500 md:col-span-2 xl:col-span-3">${text}</div>`;
}

function selectedOptionText(select) {
    return select.selectedIndex > 0 ? select.options[select.selectedIndex].textContent : '';
}

function setSelectValue(select, value) {
    const option = Array.from(select.options).find(item => item.value === value);
    select.value = option ? value : '';
}

function renderCombobox(input, dropdown, select, options, emptyText, onPick = null) {
    const query = input.value.trim().toLowerCase();
    const matches = options
        .filter(option => option.label.toLowerCase().includes(query))
        .slice(0, 40);

    dropdown.innerHTML = '';

    if (!matches.length) {
        const empty = document.createElement('div');
        empty.className = 'px-3 py-3 text-sm text-slate-500';
        empty.textContent = emptyText;
        dropdown.appendChild(empty);
        dropdown.classList.remove('hidden');
        return;
    }

    matches.forEach(option => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `block w-full rounded-xl px-3 py-2 text-left text-sm transition hover:bg-se-subtle ${select.value === option.value ? 'bg-se-subtle text-se-rust' : 'text-se-ink'}`;
        button.textContent = option.label;
        button.addEventListener('mousedown', event => {
            event.preventDefault();
            input.value = option.label;
            setSelectValue(select, option.value);
            dropdown.classList.add('hidden');
            setMessage('');
            onPick?.(option);
        });
        dropdown.appendChild(button);
    });

    dropdown.classList.remove('hidden');
}

function resetUserAssignmentState(text = 'Pilih petugas untuk melihat assignment.') {
    list.innerHTML = emptyAssignmentState(text);
    count.textContent = '0 desa';
    assignmentSummary.textContent = 'Belum ada petugas dipilih';
}

async function loadAssignments() {
    const userId = userSelect.value;
    setMessage('');

    if (!userId) {
        resetUserAssignmentState();
        return;
    }

    assignmentSummary.textContent = selectedOptionText(userSelect);

    const response = await fetch(`{{ route('admin.monitoring-sbr.assignments') }}?user_id=${userId}`);
    const data = await response.json();
    const villages = data.villages || [];

    count.textContent = `${villages.length} desa`;
    list.innerHTML = villages.length ? villages.map(village => `
        <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-3">
            <div class="min-w-0">
                <p class="truncate font-medium text-se-ink">${village.nmdesa}</p>
                <p class="truncate text-xs text-slate-500">${village.nmkec}</p>
            </div>
            <button type="button" data-village-id="${village.id}" class="assignment-remove rounded-xl border border-rose-200 px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50">Hapus</button>
        </div>
    `).join('') : emptyAssignmentState('Belum ada desa yang di-assign.');
}

userSearch.addEventListener('focus', () => {
    renderCombobox(userSearch, userDropdown, userSelect, userOptions, 'Petugas tidak ditemukan.', loadAssignments);
});

userSearch.addEventListener('input', () => {
    userSelect.value = '';
    resetUserAssignmentState(userSearch.value.trim() ? 'Pilih petugas dari hasil pencarian.' : 'Pilih petugas untuk melihat assignment.');
    renderCombobox(userSearch, userDropdown, userSelect, userOptions, 'Petugas tidak ditemukan.', loadAssignments);
});

villageSearch.addEventListener('focus', () => {
    renderCombobox(villageSearch, villageDropdown, villageSelect, villageOptions, 'Desa tidak ditemukan.');
});

villageSearch.addEventListener('input', () => {
    villageSelect.value = '';
    renderCombobox(villageSearch, villageDropdown, villageSelect, villageOptions, 'Desa tidak ditemukan.');
});

document.addEventListener('click', event => {
    if (!userSearch.contains(event.target) && !userDropdown.contains(event.target)) {
        userDropdown.classList.add('hidden');
    }

    if (!villageSearch.contains(event.target) && !villageDropdown.contains(event.target)) {
        villageDropdown.classList.add('hidden');
    }
});

document.getElementById('assignment-add').addEventListener('click', async () => {
    if (!userSelect.value || !villageSelect.value) {
        setMessage('Pilih petugas dan desa terlebih dahulu.', false);
        return;
    }

    const response = await fetch(`{{ route('admin.monitoring-sbr.assign') }}`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json'},
        body: JSON.stringify({user_id: userSelect.value, village_id: villageSelect.value})
    });

    const data = await response.json();
    setMessage(data.message || 'Assignment disimpan.', response.ok);
    villageSelect.value = '';
    villageSearch.value = '';
    await loadAssignments();
});

list.addEventListener('click', async (event) => {
    const button = event.target.closest('.assignment-remove');
    if (!button) return;

    const response = await fetch(`{{ route('admin.monitoring-sbr.unassign') }}`, {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json'},
        body: JSON.stringify({user_id: userSelect.value, village_id: button.dataset.villageId})
    });

    setMessage(response.ok ? 'Assignment dihapus.' : 'Gagal menghapus assignment.', response.ok);
    await loadAssignments();
});
</script>
@endpush
