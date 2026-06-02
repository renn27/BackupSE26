<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    protected $fillable = ['kdkec', 'nmkec', 'kddesa', 'nmdesa'];

    protected function casts(): array
    {
        return [
            'kdkec' => 'integer',
            'kddesa' => 'integer',
        ];
    }

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_village_assignments')
            ->withPivot(['assigned_by', 'assigned_at']);
    }
}
