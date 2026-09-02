<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rewind extends Model
{
    use HasFactory;

    protected $fillable = ['price', 'image_path'];

    protected $casts = ['price' => 'decimal:2'];

    public function lifers()
    {
        return $this->belongsToMany(Lifer::class, 'lifer_rewind')->withTimestamps();
    }
}
