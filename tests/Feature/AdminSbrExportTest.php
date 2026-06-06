<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Village;
use App\Models\Business;
use App\Models\BusinessStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class AdminSbrExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_allows_superadmin_to_export_sbr_data(): void
    {
        Excel::fake();

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

        $this->travelTo(now());
        $filename = 'Update Monitoring SBR_SE2026_' . now()->format('Ymd_His') . '.xlsx';

        $response = $this->actingAs($admin)
            ->get(route('admin.monitoring-sbr.export'));

        $response->assertStatus(200);

        Excel::assertDownloaded($filename);
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
            ->get(route('admin.monitoring-sbr.export'));

        $response->assertStatus(403);
    }
}
