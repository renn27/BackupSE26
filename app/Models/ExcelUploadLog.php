<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcelUploadLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'uploaded_by',
        'filename',
        'total_rows',
        'inserted',
        'updated',
        'skipped',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_by' => 'integer',
            'total_rows' => 'integer',
            'inserted' => 'integer',
            'updated' => 'integer',
            'skipped' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
