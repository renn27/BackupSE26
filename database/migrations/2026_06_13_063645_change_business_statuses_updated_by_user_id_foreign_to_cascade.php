<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('business_statuses', function (Blueprint $table) {
            $table->dropForeign(['updated_by_user_id']);
            $table->foreign('updated_by_user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_statuses', function (Blueprint $table) {
            $table->dropForeign(['updated_by_user_id']);
            $table->foreign('updated_by_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }
};
