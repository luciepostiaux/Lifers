<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    use HasFactory;

    protected $fillable = [
        'lifer_id',
        'animal_type_id',
        'name',
        'born_at',
        'is_alive',
        'died_at',
    ];

    protected $casts = [
        'born_at' => 'datetime',
        'is_alive' => 'boolean',
        'died_at' => 'datetime',
    ];

    public function lifer()
    {
        return $this->belongsTo(Lifer::class);
    }

    public function type()
    {
        return $this->belongsTo(AnimalType::class, 'animal_type_id');
    }
}
