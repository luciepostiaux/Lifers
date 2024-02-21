<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diploma extends Model
{
    use HasFactory;

    protected $table = 'diplomas';

    protected $fillable = [
        'name',
        'description',
    ];

    protected $hidden = [];

    protected $casts = [];


    public function persos()
    {
        return $this->belongsToMany(Perso::class, 'perso_has_diplomas', 'diplomas_id', 'perso_id');
    }
}
