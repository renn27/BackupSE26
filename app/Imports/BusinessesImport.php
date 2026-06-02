<?php

namespace App\Imports;

use App\Models\Business;
use App\Models\Village;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BusinessesImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private const PROGRESS_TTL_SECONDS = 2400;

    public int $totalRows = 0;
    public int $inserted = 0;
    public int $updated = 0;
    public int $skipped = 0;
    public bool $cancelled = false;

    private array $villageCache = [];

    public function __construct(
        private ?string $progressKey = null,
        private ?int $estimatedRows = null,
    ) {
        $this->writeProgress('processing');
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if ($this->shouldCancel()) {
                $this->cancelled = true;
                $this->writeProgress('cancelled');

                throw new \RuntimeException('Import dibatalkan oleh pengguna.');
            }

            $this->totalRows++;

            $idsbr = $this->clean($row['idsbr'] ?? null);
            $nmdesa = $this->clean($row['nmdesa'] ?? null);
            $kdkec = $this->clean($row['kdkec'] ?? null);
            $kddesa = $this->clean($row['kddesa'] ?? null);

            if ($idsbr === '') {
                $this->skipped++;
                continue;
            }

            $kdkec = $this->codeOrFallback($kdkec);
            $kddesa = $this->codeOrFallback($kddesa);
            $nmdesa = $nmdesa === '' ? 'DESA X' : $nmdesa;
            $idsbr = (int) $idsbr;

            $latitude = $this->validCoordinate($row['latitude_gc'] ?? null, -6.0, -2.0);
            $longitude = $this->validCoordinate($row['longitude_gc'] ?? null, 103.0, 107.0);

            $village = $this->village($kdkec, $kddesa, $row, $nmdesa);

            $business = Business::updateOrCreate(
                ['idsbr' => $idsbr],
                [
                    'village_id' => $village->id,
                    'nama_usaha' => $this->clean($row['nama_usaha'] ?? '') ?: '-',
                    'alamat_usaha' => $this->nullableClean($row['alamat_usaha'] ?? null),
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]
            );

            $business->wasRecentlyCreated ? $this->inserted++ : $this->updated++;

            if ($this->totalRows % 25 === 0) {
                $this->writeProgress('processing');
            }
        }

        $this->writeProgress('processing');
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function result(): array
    {
        return [
            'total_rows' => $this->totalRows,
            'inserted' => $this->inserted,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
        ];
    }

    public function markFinished(): void
    {
        $this->writeProgress('finished');
    }

    private function writeProgress(string $phase): void
    {
        if (! $this->progressKey) {
            return;
        }

        $estimatedRows = max(1, $this->estimatedRows ?? $this->totalRows);
        $percent = $phase === 'finished'
            ? 100
            : min(98, (int) floor(($this->totalRows / $estimatedRows) * 100));

        Cache::put($this->progressKey, [
            'phase' => $phase,
            'percent' => $phase === 'cancelled' ? $percent : $percent,
            'processed' => $this->totalRows,
            'estimated_rows' => $estimatedRows,
            'inserted' => $this->inserted,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'message' => $phase === 'finished'
                ? 'Import selesai diproses.'
                : ($phase === 'cancelled'
                    ? 'Import dibatalkan. Data yang sudah diproses tetap tersimpan.'
                    : 'Import sedang membaca Excel dan menyimpan data ke database.'),
            'updated_at' => now()->format('H:i:s'),
        ], self::PROGRESS_TTL_SECONDS);
    }

    private function shouldCancel(): bool
    {
        return $this->progressKey && Cache::get($this->progressKey . ':cancel') === true;
    }

    private function village(int $kdkec, int $kddesa, Collection $row, string $nmdesa): Village
    {
        $key = $kdkec . ':' . $kddesa;

        if (isset($this->villageCache[$key])) {
            return $this->villageCache[$key];
        }

        return $this->villageCache[$key] = Village::firstOrCreate(
            ['kdkec' => $kdkec, 'kddesa' => $kddesa],
            [
                'nmkec' => mb_strtoupper($this->clean($row['nmkec'] ?? '') ?: 'KECAMATAN X'),
                'nmdesa' => mb_strtoupper($nmdesa),
            ]
        );
    }

    private function codeOrFallback(string $value): int
    {
        return $value === '' || ! is_numeric($value) ? 0 : (int) $value;
    }

    private function clean(mixed $value): string
    {
        return trim((string) $value);
    }

    private function nullableClean(mixed $value): ?string
    {
        $value = $this->clean($value);

        return $value === '' ? null : $value;
    }

    private function validCoordinate(mixed $value, float $min, float $max): ?float
    {
        $value = str_replace(',', '.', $this->clean($value));

        if ($value === '' || ! is_numeric($value)) {
            return null;
        }

        $coordinate = (float) $value;

        return $coordinate >= $min && $coordinate <= $max ? $coordinate : null;
    }
}
