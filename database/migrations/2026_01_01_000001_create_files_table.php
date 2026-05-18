<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('drive_file_id');                 // ID file di Google Drive
            $table->string('drive_folder_id');               // ID folder di Drive user
            $table->string('original_name');                 // Nama file asli
            $table->string('stored_name');                   // Nama file di Drive
            $table->enum('type', ['photo', 'backup']);
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');        // Ukuran asli
            $table->unsignedBigInteger('compressed_size_bytes')->nullable(); // Ukuran setelah kompres
            $table->string('drive_web_view_link')->nullable();
            $table->enum('status', ['uploading', 'uploaded', 'failed', 'deleted'])->default('uploading');
            $table->text('upload_error')->nullable();
            $table->string('description')->nullable();
            $table->string('category')->nullable();          // Kategori bebas dari user
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'status']);
            $table->index('drive_file_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
