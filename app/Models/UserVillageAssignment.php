<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVillageAssignment extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'village_id', 'assigned_by', 'assigned_at'];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'village_id' => 'integer',
            'assigned_by' => 'integer',
            'assigned_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function village()
    {
        return $this->belongsTo(Village::class);
    }
}
