<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
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

class BusinessesSheet implements FromCollection, WithHeadings, WithTitle, WithCustomStartCell, WithStyles
{
    public function title(): string
    {
        return 'Data Usaha';
    }

    public function startCell(): string
    {
        return 'A3';
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
            'Petugas',
            'Tanggal',
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

    public function styles(Worksheet $sheet)
    {
        // Title block in row 1
        $sheet->setCellValue('A1', 'DATA PROGRESS MONITORING SBR - DATA USAHA');
        $sheet->mergeCells('A1:H1');
        $sheet->getRowDimension('1')->setRowHeight(32);
        
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF68B24'], // Background orange khas SE2026
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ]);

        // Empty row 2 height
        $sheet->getRowDimension('2')->setRowHeight(12);

        // Header Row (Row 3)
        $sheet->getRowDimension('3')->setRowHeight(25);
        $sheet->getStyle('A3:H3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE0700B'], // Darker orange accent for column headers
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ]);

        // Set clean column widths (not too wide)
        $widths = [
            'A' => 15, // ID SBR
            'B' => 24, // Nama Usaha
            'C' => 16, // Status
            'D' => 18, // Desa
            'E' => 18, // Kecamatan
            'F' => 35, // Alamat
            'G' => 20, // Petugas
            'H' => 22, // Tanggal
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Apply grid styles for data rows
        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= 4) {
            // Border style
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FFDDDDDD'],
                    ],
                ],
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ];
            $sheet->getStyle('A4:H' . $highestRow)->applyFromArray($styleArray);
            
            // Text wrapping and alignments
            $sheet->getStyle('A4:H' . $highestRow)->getAlignment()->setWrapText(true);
            
            // Align center for specific columns
            $sheet->getStyle('A4:A' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C4:C' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D4:E' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H4:H' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Zebra striping for rows
            for ($row = 4; $row <= $highestRow; $row++) {
                $sheet->getRowDimension($row)->setRowHeight(20);
                if ($row % 2 === 1) {
                    $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFFF9F4'], // Soft tint orange
                        ]
                    ]);
                }
            }
        }
    }
}

class OfficersSheet implements FromCollection, WithHeadings, WithTitle, WithCustomStartCell, WithStyles
{
    public function title(): string
    {
        return 'Progress Per Petugas';
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function headings(): array
    {
        return [
            'Akun Gmail',
            'Nama',
            'Wilayah Tugas',
            'Total Usaha',
            'Sudah Dicatat',
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

    public function styles(Worksheet $sheet)
    {
        // Title block in row 1
        $sheet->setCellValue('A1', 'DATA PROGRESS MONITORING SBR - PROGRESS PER PETUGAS');
        $sheet->mergeCells('A1:E1');
        $sheet->getRowDimension('1')->setRowHeight(32);
        
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF68B24'], // Background orange khas SE2026
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ]);

        // Empty row 2 height
        $sheet->getRowDimension('2')->setRowHeight(12);

        // Header Row (Row 3)
        $sheet->getRowDimension('3')->setRowHeight(25);
        $sheet->getStyle('A3:E3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE0700B'], // Darker orange accent for column headers
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ]);

        // Set clean column widths (not too wide)
        $widths = [
            'A' => 28, // Akun Gmail
            'B' => 25, // Nama
            'C' => 16, // Wilayah Tugas
            'D' => 15, // Total Usaha
            'E' => 15, // Sudah Dicatat
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Apply grid styles for data rows
        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= 4) {
            // Border style
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FFDDDDDD'],
                    ],
                ],
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ];
            $sheet->getStyle('A4:E' . $highestRow)->applyFromArray($styleArray);
            
            // Text wrapping and alignments
            $sheet->getStyle('A4:E' . $highestRow)->getAlignment()->setWrapText(true);
            
            // Align center/right for specific columns
            $sheet->getStyle('C4:E' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Zebra striping for rows
            for ($row = 4; $row <= $highestRow; $row++) {
                $sheet->getRowDimension($row)->setRowHeight(20);
                if ($row % 2 === 1) {
                    $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFFF9F4'], // Soft tint orange
                        ]
                    ]);
                }
            }
        }
    }
}
