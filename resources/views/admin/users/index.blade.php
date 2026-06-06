@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div
    class="space-y-6"
    x-data="{
        deleteModalOpen: false,
        deleteUser: { name: '', email: '', files: 0, formId: '' },
        openDeleteModal(user) {
            this.deleteUser = user;
            this.deleteModalOpen = true;
        },
        submitDelete() {
            document.getElementById(this.deleteUser.formId)?.submit();
        }
    }"
>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="pl-10 pr-4 py-2 w-full sm:w-64 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            @php
                $statusLabelsList = [
                    'active' => 'Aktif',
                    'suspended' => 'Nonaktif',
                ];
                $selectedStatusLabel = 'Semua Status';
                if (request('status') && isset($statusLabelsList[request('status')])) {
                    $selectedStatusLabel = $statusLabelsList[request('status')];
                }
            @endphp
            <div x-data="{ open: false, selectedLabel: '{{ $selectedStatusLabel }}', selectedValue: '{{ request('status') }}' }" 
                 @click.outside="open = false" 
                 class="relative min-w-[150px]">
                <button type="button" @click="open = !open" 
                        class="flex w-full items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-2 text-left text-sm text-slate-700 outline-none transition hover:border-se-primary/30 hover:bg-slate-50 focus:border-se-primary/40 focus:ring-4 focus:ring-orange-100/70">
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
                     class="absolute top-full left-0 z-30 mt-1.5 w-full rounded-lg border border-slate-200 bg-white py-1 shadow-lg shadow-slate-900/5 focus:outline-none">
                    <button type="button" @click="selectedValue = ''; selectedLabel = 'Semua Status'; open = false; $nextTick(() => { $refs.statusSelect.dispatchEvent(new Event('change')) })"
                            class="flex w-full items-center px-3 py-2 text-left text-sm transition hover:bg-orange-50 hover:text-orange-700 focus:bg-orange-50 focus:text-orange-700 focus:outline-none"
                            :class="selectedValue === '' ? 'bg-orange-50 text-orange-700 font-bold' : 'text-slate-700 font-medium'">
                        Semua Status
                    </button>
                    @foreach($statusLabelsList as $val => $label)
                        <button type="button" @click="selectedValue = '{{ $val }}'; selectedLabel = '{{ $label }}'; open = false; $nextTick(() => { $refs.statusSelect.dispatchEvent(new Event('change')) })"
                                class="flex w-full items-center px-3 py-2 text-left text-sm transition hover:bg-orange-50 hover:text-orange-700 focus:bg-orange-50 focus:text-orange-700 focus:outline-none"
                                :class="selectedValue === '{{ $val }}' ? 'bg-orange-50 text-orange-700 font-bold' : 'text-slate-700 font-medium'">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                <select x-ref="statusSelect" name="status" x-model="selectedValue" class="hidden" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    @foreach($statusLabelsList as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium">Filter</button>
        </form>
    </div>

    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500 font-medium">
                    <tr>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Total File</th>
                        <th class="px-6 py-4 text-center">Storage</th>
                        <th class="px-6 py-4">Terakhir Login</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                    <tr onclick="window.location='{{ route('admin.files.index', ['user_id' => $user->id]) }}'" class="group cursor-pointer hover:bg-slate-50/80 transition-colors" title="Lihat file yang diupload oleh {{ $user->name }}">
                        <td class="px-6 py-4 flex items-center gap-3">
                            <img class="w-10 h-10 rounded-full border border-slate-200" src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}" alt="">
                            <div>
                                <p class="font-semibold text-slate-800 group-hover:text-blue-700 transition-colors">{{ $user->name }}</p>
                                <p class="text-xs text-slate-500">{{ $user->email }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($user->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center font-medium text-slate-600">{{ $user->files()->count() }}</td>
                        <td class="px-6 py-4 text-center text-slate-600">
                            {{ number_format($user->total_storage_used / 1048576, 2) }} MB
                        </td>
                        <td class="px-6 py-4 text-slate-500 text-xs">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->format('d M Y H:i') }}<br>
                                <span class="text-slate-400">{{ $user->last_login_ip }}</span>
                            @else
                                Belum pernah
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right" onclick="event.stopPropagation()">
                            <div class="flex items-center justify-end gap-2">
                                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-medium border {{ $user->status === 'active' ? 'text-amber-600 border-amber-200 hover:bg-amber-50' : 'text-emerald-600 border-emerald-200 hover:bg-emerald-50' }} transition-colors" title="{{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        {{ $user->status === 'active' ? 'Suspend' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <form id="delete-user-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="button"
                                        @click="openDeleteModal({
                                            name: @js($user->name),
                                            email: @js($user->email),
                                            files: {{ $user->files()->withTrashed()->count() }},
                                            formId: 'delete-user-{{ $user->id }}'
                                        })"
                                        class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 transition-colors"
                                        title="Hapus"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Tidak ada user yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <template x-teleport="body">
        <div
            x-show="deleteModalOpen"
            x-transition.opacity.duration.150ms
            x-cloak
            class="fixed inset-0 z-[80] flex min-h-dvh items-center justify-center bg-slate-900/45 px-4 py-6 backdrop-blur-[2px]"
            @keydown.escape.window="deleteModalOpen = false"
        >
            <button
                type="button"
                class="absolute inset-0 cursor-default"
                aria-label="Tutup modal"
                @click="deleteModalOpen = false"
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
                        <h2 class="text-lg font-bold leading-7 text-slate-950">Hapus user permanen?</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-600">
                            Akun <span class="font-semibold text-slate-950" x-text="deleteUser.name"></span> akan dihapus bersama seluruh file dan data terkait.
                        </p>
                        <div class="mt-3 inline-flex max-w-full items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">
                            <span class="truncate" x-text="deleteUser.email"></span>
                        </div>
                    </div>
                    <button type="button" @click="deleteModalOpen = false" class="-mr-2 -mt-2 flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
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
                            <div class="font-semibold leading-5" x-text="`${deleteUser.files} file akan ikut dihapus`"></div>
                            <p class="mt-1.5 text-xs leading-5 text-rose-700">
                                Record database, sesi, assignment, log user, dan file Google Drive yang tercatat akan dihapus permanen.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50/80 px-6 py-4 sm:flex-row sm:justify-end">
                    <button type="button" @click="deleteModalOpen = false" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="button" @click="submitDelete()" class="inline-flex h-11 items-center justify-center rounded-xl bg-rose-600 px-5 text-sm font-semibold text-white shadow-sm shadow-rose-600/20 transition hover:bg-rose-700">
                        Hapus permanen
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
