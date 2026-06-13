<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\BusinessesImport;
use App\Models\Business;
use App\Models\ExcelUploadLog;
use App\Models\User;
use App\Models\UserVillageAssignment;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AdminBusinessController extends Controller
{
    private const ESTIMATED_IMPORT_ROWS = 48235;
    private const IMPORT_TIMEOUT_SECONDS = 1200;
    private const PROGRESS_TTL_SECONDS = 2400;

    public function index()
    {
        $users = User::where('role', 'petugas')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $villages = Village::orderBy('nmkec')
            ->orderBy('nmdesa')
            ->get();

        $assignments = UserVillageAssignment::with(['user', 'village'])
            ->latest('assigned_at')
            ->get();

        $uploadLogs = ExcelUploadLog::with('uploader')
            ->latest('uploaded_at')
            ->take(5)
            ->get();

        $totalBusinesses = Business::count();
        $statusCounts = DB::table('business_statuses')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');
        $recordedStatuses = (int) $statusCounts->sum();
        $summary = [
            'businesses' => $totalBusinesses,
            'villages' => $villages->count(),
            'assigned_villages' => UserVillageAssignment::distinct('village_id')->count('village_id'),
            'recorded' => $recordedStatuses,
            'unrecorded' => max(0, $totalBusinesses - $recordedStatuses),
            'progress' => $totalBusinesses > 0 ? round(($recordedStatuses / $totalBusinesses) * 100, 1) : 0,
            'statuses' => [
                'tidak_ditemukan' => (int) ($statusCounts['tidak_ditemukan'] ?? 0),
                'ditemukan' => (int) ($statusCounts['ditemukan'] ?? 0),
                'baru' => (int) ($statusCounts['baru'] ?? 0),
                'tutup' => (int) ($statusCounts['tutup'] ?? 0),
                'ganda' => (int) ($statusCounts['ganda'] ?? 0),
            ],
        ];

        $regionMonitoring = DB::table('villages')
            ->leftJoin('businesses', 'businesses.village_id', '=', 'villages.id')
            ->leftJoin('business_statuses', 'business_statuses.business_id', '=', 'businesses.id')
            ->select(
                'villages.nmkec',
                DB::raw('COUNT(DISTINCT villages.id) as villages_count'),
                DB::raw('COUNT(businesses.id) as businesses_count'),
                DB::raw('COUNT(business_statuses.id) as recorded_count'),
                DB::raw("SUM(CASE WHEN business_statuses.status = 'tidak_ditemukan' THEN 1 ELSE 0 END) as tidak_ditemukan_count"),
                DB::raw("SUM(CASE WHEN business_statuses.status = 'ditemukan' THEN 1 ELSE 0 END) as ditemukan_count"),
                DB::raw("SUM(CASE WHEN business_statuses.status = 'baru' THEN 1 ELSE 0 END) as baru_count"),
                DB::raw("SUM(CASE WHEN business_statuses.status = 'tutup' THEN 1 ELSE 0 END) as tutup_count"),
                DB::raw("SUM(CASE WHEN business_statuses.status = 'ganda' THEN 1 ELSE 0 END) as ganda_count")
            )
            ->groupBy('villages.nmkec')
            ->orderBy('villages.nmkec')
            ->get()
            ->map(function ($row) {
                $row->progress = $row->businesses_count > 0
                    ? round(($row->recorded_count / $row->businesses_count) * 100, 1)
                    : 0;
                $row->unrecorded_count = max(0, $row->businesses_count - $row->recorded_count);

                return $row;
            });

        $villageMonitoring = DB::table('villages')
            ->leftJoin('businesses', 'businesses.village_id', '=', 'villages.id')
            ->leftJoin('business_statuses', 'business_statuses.business_id', '=', 'businesses.id')
            ->select(
                'villages.nmkec',
                'villages.nmdesa',
                'villages.kdkec',
                'villages.kddesa',
                DB::raw('COUNT(businesses.id) as businesses_count'),
                DB::raw('COUNT(business_statuses.id) as recorded_count'),
                DB::raw("SUM(CASE WHEN business_statuses.status = 'tidak_ditemukan' THEN 1 ELSE 0 END) as tidak_ditemukan_count"),
                DB::raw("SUM(CASE WHEN business_statuses.status = 'ditemukan' THEN 1 ELSE 0 END) as ditemukan_count"),
                DB::raw("SUM(CASE WHEN business_statuses.status = 'baru' THEN 1 ELSE 0 END) as baru_count"),
                DB::raw("SUM(CASE WHEN business_statuses.status = 'tutup' THEN 1 ELSE 0 END) as tutup_count"),
                DB::raw("SUM(CASE WHEN business_statuses.status = 'ganda' THEN 1 ELSE 0 END) as ganda_count")
            )
            ->groupBy('villages.nmkec', 'villages.nmdesa', 'villages.kdkec', 'villages.kddesa')
            ->orderBy('villages.nmkec')
            ->orderBy('villages.nmdesa')
            ->get()
            ->map(function ($row) {
                $row->progress = $row->businesses_count > 0
                    ? round(($row->recorded_count / $row->businesses_count) * 100, 1)
                    : 0;
                $row->unrecorded_count = max(0, $row->businesses_count - $row->recorded_count);

                return $row;
            })
            ->groupBy('nmkec');

        $userAssignments = DB::table('user_village_assignments')
            ->join('businesses', 'businesses.village_id', '=', 'user_village_assignments.village_id')
            ->leftJoin('business_statuses', 'business_statuses.business_id', '=', 'businesses.id')
            ->select(
                'user_village_assignments.user_id',
                DB::raw('COUNT(businesses.id) as businesses_count'),
                DB::raw('COUNT(business_statuses.id) as recorded_count'),
                DB::raw("SUM(CASE WHEN business_statuses.status = 'tidak_ditemukan' THEN 1 ELSE 0 END) as tidak_ditemukan_count"),
                DB::raw("SUM(CASE WHEN business_statuses.status = 'ditemukan' THEN 1 ELSE 0 END) as ditemukan_count"),
                DB::raw("SUM(CASE WHEN business_statuses.status = 'baru' THEN 1 ELSE 0 END) as baru_count"),
                DB::raw("SUM(CASE WHEN business_statuses.status = 'tutup' THEN 1 ELSE 0 END) as tutup_count"),
                DB::raw("SUM(CASE WHEN business_statuses.status = 'ganda' THEN 1 ELSE 0 END) as ganda_count")
            )
            ->groupBy('user_village_assignments.user_id')
            ->get()
            ->keyBy('user_id');

        $userMonitoring = User::where('role', 'petugas')
            ->with(['villages' => function ($query) {
                $query->orderBy('nmkec')->orderBy('nmdesa');
            }])
            ->withCount(['villages as assigned_villages_count'])
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(function (User $user) use ($userAssignments) {
                $stats = $userAssignments->get($user->id);
                $businessCount = $stats ? (int) $stats->businesses_count : 0;
                $recordedCount = $stats ? (int) $stats->recorded_count : 0;

                return (object) [
                    'name' => $user->name,
                    'email' => $user->email,
                    'assigned_villages' => $user->villages->map(fn ($village) => trim($village->nmkec . ' - ' . $village->nmdesa))->values(),
                    'assigned_villages_count' => $user->assigned_villages_count,
                    'businesses_count' => $businessCount,
                    'recorded_count' => $recordedCount,
                    'unrecorded_count' => max(0, $businessCount - $recordedCount),
                    'progress' => $businessCount > 0 ? round(($recordedCount / $businessCount) * 100, 1) : 0,
                    'tidak_ditemukan_count' => $stats ? (int) $stats->tidak_ditemukan_count : 0,
                    'ditemukan_count' => $stats ? (int) $stats->ditemukan_count : 0,
                    'baru_count' => $stats ? (int) $stats->baru_count : 0,
                    'tutup_count' => $stats ? (int) $stats->tutup_count : 0,
                    'ganda_count' => $stats ? (int) $stats->ganda_count : 0,
                ];
            });

        return view('admin.businesses.index', compact(
            'users',
            'villages',
            'assignments',
            'uploadLogs',
            'summary',
            'regionMonitoring',
            'villageMonitoring',
            'userMonitoring',
        ));
    }

    public function uploadStore(Request $request)
    {
        ini_set('max_execution_time', (string) self::IMPORT_TIMEOUT_SECONDS);
        set_time_limit(self::IMPORT_TIMEOUT_SECONDS);

        $validated = $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'import_id' => ['nullable', 'string', 'max:80'],
        ]);

        $file = $validated['excel_file'];
        $progressKey = $this->progressKey($request, $validated['import_id'] ?? null);

        if ($progressKey) {
            Cache::put($progressKey, [
                'phase' => 'uploaded',
                'percent' => 0,
                'processed' => 0,
                'estimated_rows' => self::ESTIMATED_IMPORT_ROWS,
                'inserted' => 0,
                'updated' => 0,
                'skipped' => 0,
                'message' => 'File sudah diterima server. Import akan dimulai.',
                'updated_at' => now()->format('H:i:s'),
                'timeout_seconds' => self::IMPORT_TIMEOUT_SECONDS,
            ], self::PROGRESS_TTL_SECONDS);
        }

        $import = new BusinessesImport($progressKey, self::ESTIMATED_IMPORT_ROWS);

        try {
            Excel::import($import, $file);
        } catch (\Throwable $exception) {
            $phase = $import->cancelled ? 'cancelled' : 'failed';
            $statusCode = $import->cancelled ? 409 : 500;

            if ($progressKey) {
                Cache::put($progressKey, [
                    'phase' => $phase,
                    'percent' => $import->cancelled ? min(98, (int) floor(($import->totalRows / self::ESTIMATED_IMPORT_ROWS) * 100)) : 0,
                    'processed' => $import->totalRows,
                    'estimated_rows' => self::ESTIMATED_IMPORT_ROWS,
                    'inserted' => $import->inserted,
                    'updated' => $import->updated,
                    'skipped' => $import->skipped,
                    'message' => $exception->getMessage(),
                    'updated_at' => now()->format('H:i:s'),
                    'timeout_seconds' => self::IMPORT_TIMEOUT_SECONDS,
                ], self::PROGRESS_TTL_SECONDS);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $import->cancelled ? 'Import Monitoring SBR dibatalkan.' : 'Import Monitoring SBR gagal.',
                    'error' => $exception->getMessage(),
                    'result' => $import->result(),
                ], $statusCode);
            }

            throw $exception;
        }

        $result = $import->result();
        $import->markFinished();

        ExcelUploadLog::create([
            'uploaded_by' => $request->user()->id,
            'filename' => $file->getClientOriginalName(),
            'total_rows' => $result['total_rows'],
            'inserted' => $result['inserted'],
            'updated' => $result['updated'],
            'skipped' => $result['skipped'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Import Monitoring SBR selesai.',
                'result' => $result,
            ]);
        }

        return back()->with('upload_result', $result);
    }

    public function uploadProgress(Request $request, string $importId)
    {
        $progress = Cache::get($this->progressKey($request, $importId));

        return response()->json($progress ?? [
            'phase' => 'waiting',
            'percent' => 0,
            'processed' => 0,
            'estimated_rows' => self::ESTIMATED_IMPORT_ROWS,
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'message' => 'Menunggu proses import dimulai.',
            'updated_at' => null,
            'timeout_seconds' => self::IMPORT_TIMEOUT_SECONDS,
        ]);
    }

    public function cancelUpload(Request $request, string $importId)
    {
        $progressKey = $this->progressKey($request, $importId);
        $progress = Cache::get($progressKey, []);

        Cache::put($progressKey . ':cancel', true, self::PROGRESS_TTL_SECONDS);
        Cache::put($progressKey, array_merge([
            'phase' => 'cancelling',
            'percent' => 0,
            'processed' => 0,
            'estimated_rows' => self::ESTIMATED_IMPORT_ROWS,
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
        ], $progress, [
            'phase' => 'cancelling',
            'message' => 'Permintaan batal dikirim. Import akan berhenti pada pengecekan row berikutnya.',
            'updated_at' => now()->format('H:i:s'),
            'timeout_seconds' => self::IMPORT_TIMEOUT_SECONDS,
        ]), self::PROGRESS_TTL_SECONDS);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan batal dikirim.',
        ]);
    }

    public function assignUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'village_id' => ['required', 'exists:villages,id'],
        ]);

        UserVillageAssignment::firstOrCreate(
            [
                'user_id' => $validated['user_id'],
                'village_id' => $validated['village_id'],
            ],
            [
                'assigned_by' => $request->user()->id,
                'assigned_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Assignment desa berhasil disimpan.',
        ]);
    }

    public function removeAssignment(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'village_id' => ['required', 'exists:villages,id'],
        ]);

        UserVillageAssignment::where('user_id', $validated['user_id'])
            ->where('village_id', $validated['village_id'])
            ->delete();

        return response()->json(['success' => true]);
    }

    public function getVillagesByUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $villages = Village::whereHas('users', function ($query) use ($validated) {
            $query->where('users.id', $validated['user_id']);
        })
            ->orderBy('nmkec')
            ->orderBy('nmdesa')
            ->get(['villages.id', 'kdkec', 'nmkec', 'kddesa', 'nmdesa']);

        return response()->json(['success' => true, 'villages' => $villages]);
    }

    public function startExport(Request $request)
    {
        $exportId = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->input('export_id', \Illuminate\Support\Str::random(16)));
        $progressKey = 'monitoring_sbr_export:' . $request->user()->id . ':' . $exportId;

        // Ensure exports directory exists
        \Illuminate\Support\Facades\Storage::disk('local')->makeDirectory('exports');

        $totalRows = Business::count();

        // Set initial progress
        Cache::put($progressKey, [
            'phase' => 'processing',
            'percent' => 0,
            'processed' => 0,
            'total' => $totalRows,
            'updated_at' => now()->format('H:i:s'),
        ], 600);

        $filename = 'exports/Update_Monitoring_SBR_SE2026_' . $exportId . '.xlsx';

        // Run the export and store the file locally
        Excel::store(new \App\Exports\ProgressMonitoringExport($progressKey, $totalRows), $filename, 'local');

        // Mark as finished
        Cache::put($progressKey, [
            'phase' => 'finished',
            'percent' => 100,
            'processed' => $totalRows,
            'total' => $totalRows,
            'download_url' => route('admin.monitoring-sbr.export.download', ['exportId' => $exportId]),
            'updated_at' => now()->format('H:i:s'),
        ], 600);

        return response()->json([
            'success' => true,
            'download_url' => route('admin.monitoring-sbr.export.download', ['exportId' => $exportId]),
        ]);
    }

    public function exportProgress(Request $request, string $exportId)
    {
        $exportId = preg_replace('/[^a-zA-Z0-9_-]/', '', $exportId);
        $progressKey = 'monitoring_sbr_export:' . $request->user()->id . ':' . $exportId;
        $progress = Cache::get($progressKey);

        return response()->json($progress ?? [
            'phase' => 'waiting',
            'percent' => 0,
            'processed' => 0,
            'total' => 0,
            'updated_at' => null,
        ]);
    }

    public function downloadExport(Request $request, string $exportId)
    {
        $exportId = preg_replace('/[^a-zA-Z0-9_-]/', '', $exportId);
        $filename = 'exports/Update_Monitoring_SBR_SE2026_' . $exportId . '.xlsx';

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($filename)) {
            abort(404, 'File export tidak ditemukan atau sudah kadaluarsa.');
        }

        $filePath = \Illuminate\Support\Facades\Storage::disk('local')->path($filename);
        $downloadName = 'Update Monitoring SBR_SE2026_' . now('Asia/Jakarta')->format('Ymd_His') . '.xlsx';

        return response()->download($filePath, $downloadName)->deleteFileAfterSend();
    }

    private function progressKey(Request $request, ?string $importId): ?string
    {
        if (! $importId) {
            return null;
        }

        return 'monitoring_sbr_import:' . $request->user()->id . ':' . preg_replace('/[^a-zA-Z0-9_-]/', '', $importId);
    }
}
