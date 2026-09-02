<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wearable extends Model
{
    use HasFactory;

    protected $fillable = [
        'body_type_id',
        'category',
        'name',
        'description',
        'price',
        'image_path',
    ];

    protected $casts = ['price' => 'decimal:2'];

    public function bodyType()
    {
        return $this->belongsTo(BodyType::class);
    }
}
