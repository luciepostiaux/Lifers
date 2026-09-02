<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_description',
        'long_description',
        'salary',
        'image_path',
        'required_diploma_id',
        'place_id',
    ];

    protected $casts = ['salary' => 'decimal:2'];

    public function requiredDiploma()
    {
        return $this->belongsTo(Diploma::class, 'required_diploma_id');
    }

    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    public function actions()
    {
        return $this->hasMany(JobAction::class);
    }
}
