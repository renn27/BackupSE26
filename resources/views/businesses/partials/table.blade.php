@php
    $statusMeta = [
        'tidak_ditemukan' => ['label' => 'Tidak Ditemukan', 'class' => 'bg-slate-100 text-slate-600 ring-slate-200'],
        'ditemukan' => ['label' => 'Ditemukan', 'class' => 'bg-green-50 text-green-700 ring-green-200'],
        'baru' => ['label' => 'Baru', 'class' => 'bg-blue-50 text-blue-700 ring-blue-200'],
        'tutup' => ['label' => 'Tutup', 'class' => 'bg-rose-50 text-rose-700 ring-rose-200'],
        'ganda' => ['label' => 'Ganda', 'class' => 'bg-amber-50 text-amber-700 ring-amber-200'],
    ];
@endphp

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 text-xs sm:text-sm">
        <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-3 py-2.5 sm:px-4 sm:py-3">No</th>
                <th class="px-3 py-2.5 sm:px-4 sm:py-3">ID SBR</th>
                <th class="px-3 py-2.5 sm:px-4 sm:py-3">Nama Usaha</th>
                <th class="w-1 whitespace-nowrap px-3 py-2.5 sm:px-4 sm:py-3">Status</th>
                <th class="px-3 py-2.5 sm:px-4 sm:py-3">Desa</th>
                <th class="px-3 py-2.5 sm:px-4 sm:py-3">Kecamatan</th>
                <th class="px-3 py-2.5 sm:px-4 sm:py-3">Alamat</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
            @forelse($businesses as $business)
                @php
                    $currentStatus = $business->status?->status;
                    $meta = $currentStatus ? ($statusMeta[$currentStatus] ?? ['label' => 'Status Lama', 'class' => 'bg-slate-100 text-slate-600 ring-slate-200']) : ['label' => 'Belum Dicatat', 'class' => 'bg-white text-slate-500 ring-slate-200'];
                @endphp
                <tr id="business-row-{{ $business->id }}"
                    class="status-open cursor-pointer transition hover:bg-slate-50"
                    data-id="{{ $business->id }}"
                    data-idsbr="{{ e($business->idsbr) }}"
                    data-name="{{ e($business->nama_usaha) }}"
                    data-address="{{ e($business->alamat_usaha ?? '-') }}"
                    data-village="{{ e($business->village->nmdesa) }}"
                    data-district="{{ e($business->village->nmkec) }}"
                    data-status="{{ $currentStatus }}"
                    data-status-owner-id="{{ $business->status?->updated_by_user_id }}"
                    data-note="{{ e($business->status?->catatan ?? '') }}"
                    data-updated-by="{{ e($business->status?->updated_by_name ?? '-') }}"
                    data-updated-at="{{ $business->status?->updated_at?->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y H:i') ?? '-' }}">
                    <td class="px-3 py-3 text-slate-500 sm:px-4 sm:py-4">{{ $businesses->firstItem() + $loop->index }}</td>
                    <td class="whitespace-nowrap px-3 py-3 font-mono text-[11px] font-medium text-slate-600 sm:px-4 sm:py-4 sm:text-xs">{{ $business->idsbr }}</td>
                    <td class="min-w-[180px] max-w-[240px] px-3 py-3 font-medium text-se-ink sm:px-4 sm:py-4">
                        <span class="line-clamp-2 leading-5">{{ $business->nama_usaha }}</span>
                    </td>
                    <td class="w-1 whitespace-nowrap px-3 py-3 sm:px-4 sm:py-4">
                        <span data-status-badge="{{ $business->id }}" class="inline-flex whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium ring-1 {{ $meta['class'] }}">{{ $meta['label'] }}</span>
                    </td>
                    <td class="px-3 py-3 text-slate-600 sm:px-4 sm:py-4">{{ $business->village->nmdesa }}</td>
                    <td class="px-3 py-3 text-slate-600 sm:px-4 sm:py-4">{{ $business->village->nmkec }}</td>
                    <td class="max-w-[280px] px-3 py-3 text-slate-600 sm:px-4 sm:py-4">
                        <span class="line-clamp-2 leading-5">{{ $business->alamat_usaha ?? '-' }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-slate-500">Belum ada usaha untuk ditampilkan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
    <p>Menampilkan {{ $businesses->count() }} dari {{ number_format($businesses->total()) }} usaha</p>
    {{ $businesses->links() }}
</div>
