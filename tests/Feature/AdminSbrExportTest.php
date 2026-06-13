<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Village;
use App\Models\Business;
use App\Models\BusinessStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class AdminSbrExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_allows_superadmin_to_export_sbr_data(): void
    {
        Excel::fake();
        Storage::fake('local');

        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin.test@example.com',
            'role' => 'superadmin',
            'status' => 'active',
            'google_refresh_token' => 'mock-token',
        ]);

        $village = Village::create([
            'kdkec' => 10,
            'nmkec' => 'Kecamatan Test',
            'kddesa' => 20,
            'nmdesa' => 'Desa Test',
        ]);

        $business = Business::create([
            'idsbr' => 12345678,
            'village_id' => $village->id,
            'nama_usaha' => 'Usaha Test',
            'alamat_usaha' => 'Alamat Test',
        ]);

        BusinessStatus::create([
            'business_id' => $business->id,
            'status' => 'ditemukan',
            'updated_by_user_id' => $admin->id,
            'updated_by_name' => $admin->name,
        ]);

        $exportId = 'test-export-id';

        $response = $this->actingAs($admin)
            ->postJson(route('admin.monitoring-sbr.export.start'), [
                'export_id' => $exportId,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $filename = 'exports/Update_Monitoring_SBR_SE2026_' . $exportId . '.xlsx';
        Excel::assertStored($filename, 'local');

        // Verify progress is finished
        $progressResponse = $this->actingAs($admin)
            ->getJson(route('admin.monitoring-sbr.export.progress', ['exportId' => $exportId]));
        
        $progressResponse->assertStatus(200)
            ->assertJson([
                'phase' => 'finished',
                'percent' => 100,
            ]);
    }

    public function test_it_allows_superadmin_to_download_export(): void
    {
        Storage::fake('local');
        $this->travelTo(now());

        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin.test@example.com',
            'role' => 'superadmin',
            'status' => 'active',
            'google_refresh_token' => 'mock-token',
        ]);

        $exportId = 'test-download-id';
        $filename = 'exports/Update_Monitoring_SBR_SE2026_' . $exportId . '.xlsx';
        
        // Put a dummy file in storage
        Storage::disk('local')->put($filename, 'dummy content');

        $response = $this->actingAs($admin)
            ->get(route('admin.monitoring-sbr.export.download', ['exportId' => $exportId]));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename="Update Monitoring SBR_SE2026_' . now('Asia/Jakarta')->format('Ymd_His') . '.xlsx"');
    }

    public function test_it_denies_petugas_from_exporting_sbr_data(): void
    {
        $petugas = User::create([
            'name' => 'Petugas Test',
            'email' => 'petugas.test@example.com',
            'role' => 'petugas',
            'status' => 'active',
            'google_refresh_token' => 'mock-token',
        ]);

        $response = $this->actingAs($petugas)
            ->postJson(route('admin.monitoring-sbr.export.start'));

        $response->assertStatus(403);

        $responseProgress = $this->actingAs($petugas)
            ->getJson(route('admin.monitoring-sbr.export.progress', ['exportId' => 'some-id']));

        $responseProgress->assertStatus(403);

        $responseDownload = $this->actingAs($petugas)
            ->get(route('admin.monitoring-sbr.export.download', ['exportId' => 'some-id']));

        $responseDownload->assertStatus(403);
    }
}
