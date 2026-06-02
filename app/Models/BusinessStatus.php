<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessStatus extends Model
{
    protected $fillable = [
        'business_id',
        'status',
        'updated_by_user_id',
        'updated_by_name',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'business_id' => 'integer',
            'updated_by_user_id' => 'integer',
            'updated_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
