<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Study extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_description',
        'long_description',
        'price',
        'duration_days',
        'image_path',
        'awarded_diploma_id',
        'required_diploma_id',
        'place_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_days' => 'integer',
    ];

    public function awardedDiploma()
    {
        return $this->belongsTo(Diploma::class, 'awarded_diploma_id');
    }

    public function requiredDiploma()
    {
        return $this->belongsTo(Diploma::class, 'required_diploma_id');
    }

    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}
