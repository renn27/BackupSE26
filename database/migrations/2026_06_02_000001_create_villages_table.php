<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('kdkec');
            $table->string('nmkec', 100);
            $table->unsignedTinyInteger('kddesa');
            $table->string('nmdesa', 100);
            $table->timestamps();

            $table->unique(['kdkec', 'kddesa'], 'unique_village');
            $table->index('nmkec', 'idx_nmkec');
            $table->index('nmdesa', 'idx_nmdesa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};
