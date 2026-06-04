<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE business_statuses MODIFY status ENUM('aktif','tidak_aktif','pindah','tidak_ditemukan','ditemukan','baru','tutup','ganda') NOT NULL");
        }

        DB::table('business_statuses')->where('status', 'aktif')->update(['status' => 'ditemukan']);
        DB::table('business_statuses')->where('status', 'tidak_aktif')->update(['status' => 'tutup']);
        DB::table('business_statuses')->where('status', 'pindah')->update(['status' => 'tidak_ditemukan']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE business_statuses MODIFY status ENUM('tidak_ditemukan','ditemukan','baru','tutup','ganda') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE business_statuses MODIFY status ENUM('aktif','tidak_aktif','pindah','tidak_ditemukan','ditemukan','baru','tutup','ganda') NOT NULL");
        }

        DB::table('business_statuses')->where('status', 'ditemukan')->update(['status' => 'aktif']);
        DB::table('business_statuses')->where('status', 'tutup')->update(['status' => 'tidak_aktif']);
        DB::table('business_statuses')->whereIn('status', ['baru', 'ganda'])->update(['status' => 'tidak_ditemukan']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE business_statuses MODIFY status ENUM('aktif','tidak_aktif','pindah','tidak_ditemukan') NOT NULL");
        }
    }
};
