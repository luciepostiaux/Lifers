<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sickness extends Model
{
    use HasFactory;

    protected $table = 'sickness';

    protected $fillable = [
        'name',
        'description',
        'duration',
        'chance',
        'type',
        'needs_doctor',
        'self_resolving',
    ];

    protected $casts = [
        'duration' => 'integer',
        'chance' => 'array',
    ];


    /**
     * Les personnages affectés par cette maladie.
     */
    public function persos()
    {
        return $this->belongsToMany(Perso::class, 'perso_has_sickness', 'sickness_id', 'perso_id')
            ->withTimestamps();
    }
    public function gauges()
    {
        return $this->belongsToMany(SicknessGauge::class, 'sickness_has_sickness_gauges', 'sickness_id', 'sickness_gauges_id')
            ->withPivot('effect_value')
            ->withTimestamps();
    }
    public function conditions()
    {
        return $this->hasMany(SicknessCondition::class);
    }
}
