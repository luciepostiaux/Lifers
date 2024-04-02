<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersoImage extends Model
{
    use HasFactory;
    
    protected $fillable = ['image_path'];

    public function perso()
    {
        return $this->belongsTo(Perso::class, 'perso_id');
    }
}
