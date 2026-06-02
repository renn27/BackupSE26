<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'drive_file_id', 'drive_folder_id',
        'original_name', 'stored_name', 'type', 'mime_type',
        'size_bytes', 'compressed_size_bytes',
        'drive_web_view_link', 'status', 'upload_error',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'compressed_size_bytes' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size_bytes;
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    public function getCompressionRatioAttribute(): ?string
    {
        if (!$this->compressed_size_bytes || !$this->size_bytes) return null;
        $ratio = (1 - ($this->compressed_size_bytes / $this->size_bytes)) * 100;
        return round($ratio, 1) . '%';
    }

    public function isPhoto(): bool { return $this->type === 'photo'; }
    public function isBackup(): bool { return $this->type === 'backup'; }
}
