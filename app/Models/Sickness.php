<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sickness extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'duration_days',
        'chance_by_age',
        'type',
        'needs_doctor',
        'self_resolving',
        'treatment_cost',
        'effect_timing',
        'daily_decay_multiplier',
        'fatal_after_days',
        'trigger_type',
        'trigger_days',
        'trigger_config',
        'risk_config',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'chance_by_age' => 'array',
        'needs_doctor' => 'boolean',
        'self_resolving' => 'boolean',
        'treatment_cost' => 'decimal:2',
        'daily_decay_multiplier' => 'decimal:2',
        'fatal_after_days' => 'integer',
        'trigger_days' => 'integer',
        'trigger_config' => 'array',
        'risk_config' => 'array',
    ];

    public function lifers()
    {
        return $this->belongsToMany(Lifer::class, 'lifer_sicknesses')
            ->withPivot(['contracted_at', 'expected_recovery_at', 'last_effect_applied_on', 'fatal_at'])
            ->withTimestamps();
    }

    public function conditions()
    {
        return $this->hasMany(SicknessCondition::class);
    }

    public function effects()
    {
        return $this->hasMany(SicknessEffect::class);
    }
}
