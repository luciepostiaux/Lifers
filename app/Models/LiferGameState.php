<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiferGameState extends Model
{
    protected $primaryKey = 'lifer_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'lifer_id',
        'body_type_id',
        'hairstyle_id',
        'money',
        'description',
        'last_gauges_decreased_on',
        'last_sickness_checked_on',
        'vital_red_since',
        'vital_green_streak_days',
        'last_mortality_checked_on',
        'last_sport_activity_on',
        'last_sickness_trigger_checked_on',
    ];

    protected $casts = [
        'money' => 'decimal:2',
        'last_gauges_decreased_on' => 'date',
        'last_sickness_checked_on' => 'date',
        'vital_red_since' => 'date',
        'vital_green_streak_days' => 'integer',
        'last_mortality_checked_on' => 'date',
        'last_sport_activity_on' => 'date',
        'last_sickness_trigger_checked_on' => 'date',
    ];

    public function lifer()
    {
        return $this->belongsTo(Lifer::class);
    }

    public function bodyType()
    {
        return $this->belongsTo(BodyType::class);
    }

    public function hairstyle()
    {
        return $this->belongsTo(Hairstyle::class);
    }
}
