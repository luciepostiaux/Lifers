<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SicknessGauge extends Model
{
    use HasFactory;

    protected $table = 'sickness_gauges';

    protected $fillable = [
        'name',
    ];

    public function sicknesses()
    {
        return $this->belongsToMany(Sickness::class, 'sickness_has_sickness_gauges', 'sickness_gauges_id', 'sickness_id')
            ->withPivot('effect_value')
            ->withTimestamps();
    }
}
