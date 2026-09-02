<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diploma extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function lifers()
    {
        return $this->belongsToMany(Lifer::class, 'lifer_diplomas')
            ->withPivot(['earned_at', 'is_public']);
    }
}
