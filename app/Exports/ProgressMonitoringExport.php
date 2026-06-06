<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProgressMonitoringExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new BusinessesSheet(),
            new OfficersSheet(),
        ];
    }
}

class BusinessesSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function title(): string
    {
        return 'Data Usaha';
    }

    public function headings(): array
    {
        return [
            'ID SBR',
            'Nama Usaha',
            'Status',
            'Desa',
            'Kecamatan',
            'Alamat',
            'Nama Petugas yang menandainya',
            'Tanggal Ditandai petugas',
        ];
    }

    public function collection()
    {
        return Business::with(['village', 'status.updatedBy'])
            ->get()
            ->map(function ($business) {
                $status = $business->status;
                
                $statusText = 'Belum Dicatat';
                if ($status) {
                    $statusText = match ($status->status) {
                        'ditemukan' => 'Ditemukan',
                        'tidak_ditemukan' => 'Tidak Ditemukan',
                        'pindah' => 'Pindah',
                        'baru' => 'Baru',
                        'tutup' => 'Tutup',
                        'ganda' => 'Ganda',
                        default => ucwords(str_replace('_', ' ', $status->status)),
                    };
                }

                $petugasName = $status ? ($status->updated_by_name ?? ($status->updatedBy ? $status->updatedBy->name : '-')) : '-';
                $tanggalDitandai = $status ? ($status->updated_at ? $status->updated_at->format('Y-m-d H:i:s') : ($status->created_at ? $status->created_at->format('Y-m-d H:i:s') : '-')) : '-';

                return [
                    'idsbr' => $business->idsbr,
                    'nama_usaha' => $business->nama_usaha,
                    'status' => $statusText,
                    'desa' => $business->village ? $business->village->nmdesa : '-',
                    'kecamatan' => $business->village ? $business->village->nmkec : '-',
                    'alamat' => $business->alamat_usaha,
                    'petugas' => $petugasName,
                    'tanggal' => $tanggalDitandai,
                ];
            });
    }
}

class OfficersSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function title(): string
    {
        return 'Progress Per Petugas';
    }

    public function headings(): array
    {
        return [
            'Gmail Dia',
            'Nama Dia',
            'Desa',
            'Total Usaha yang Dia Harus Ubah Statusnya',
            'Berapa yang Sudah Dicatatnya',
        ];
    }

    public function collection()
    {
        return User::where('role', 'petugas')
            ->withCount(['villages as assigned_villages_count'])
            ->orderBy('name')
            ->get()
            ->map(function (User $user) {
                $villageIds = $user->villages()->pluck('villages.id');
                $businessCount = Business::whereIn('village_id', $villageIds)->count();
                $recordedCount = DB::table('businesses')
                    ->join('business_statuses', 'business_statuses.business_id', '=', 'businesses.id')
                    ->whereIn('businesses.village_id', $villageIds)
                    ->count();

                return [
                    'email' => $user->email,
                    'name' => $user->name,
                    'assigned_villages_count' => $user->assigned_villages_count,
                    'businesses_count' => $businessCount,
                    'recorded_count' => $recordedCount,
                ];
            });
    }
}
