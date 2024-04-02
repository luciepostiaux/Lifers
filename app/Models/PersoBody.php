<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersoBody extends Model
{
    use HasFactory;


    protected $fillable = ['img_perso', 'description'];

    public function gender()
    {
        return $this->belongsTo(Gender::class, 'genders_id');
    }


}
