<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = [
        'idsbr',
        'village_id',
        'nama_usaha',
        'alamat_usaha',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'idsbr' => 'integer',
            'village_id' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function status()
    {
        return $this->hasOne(BusinessStatus::class);
    }
}
