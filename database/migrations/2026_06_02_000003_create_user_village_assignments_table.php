<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_village_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('village_id')->constrained('villages')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('assigned_at')->useCurrent();

            $table->unique(['user_id', 'village_id'], 'unique_assignment');
            $table->index('user_id', 'idx_user');
            $table->index('village_id', 'idx_village_assignment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_village_assignments');
    }
};
