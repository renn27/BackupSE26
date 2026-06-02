<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            if (Schema::hasColumn('files', 'category')) {
                $table->dropColumn('category');
            }

            if (Schema::hasColumn('files', 'description')) {
                $table->dropColumn('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            if (! Schema::hasColumn('files', 'category')) {
                $table->string('category')->nullable();
            }

            if (! Schema::hasColumn('files', 'description')) {
                $table->string('description')->nullable();
            }
        });
    }
};
