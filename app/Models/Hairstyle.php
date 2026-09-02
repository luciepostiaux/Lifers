<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hairstyle extends Model
{
    use HasFactory;

    protected $fillable = ['body_type_id', 'name', 'image_path'];

    public function bodyType()
    {
        return $this->belongsTo(BodyType::class);
    }
}
