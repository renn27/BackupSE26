<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idsbr')->unique();
            $table->foreignId('village_id')->constrained('villages')->restrictOnDelete();
            $table->string('nama_usaha');
            $table->text('alamat_usaha')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->index('village_id', 'idx_village');
            $table->index('nama_usaha', 'idx_nama_usaha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
