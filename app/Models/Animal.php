<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    use HasFactory;

    protected $table = 'animals';

    protected $fillable = [
        'name',
        'sex',
        'birth_date',
        'animals_types_id',
        'perso_id',
    ];

    protected $casts = [
        'sex' => 'integer',
        'birth_date' => 'date',
    ];

    public function type()
    {
        return $this->belongsTo(AnimalsType::class, 'animals_types_id');
    }

    public function perso()
    {
        return $this->belongsTo(Perso::class, 'perso_id');
    }
}
