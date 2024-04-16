<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    // Spécifiez les colonnes qui peuvent être remplies massivement.
    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'category',
        'image',
    ];

    public function effects()
    {
        return $this->hasMany(ActivityEffect::class);
    }
}
