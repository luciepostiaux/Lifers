<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiferSicknessTriggerState extends Model
{
    protected $table = 'lifer_sickness_trigger_states';

    public $incrementing = false;

    protected $fillable = ['lifer_id', 'sickness_id', 'started_on', 'last_checked_on'];

    protected $casts = [
        'started_on' => 'date',
        'last_checked_on' => 'date',
    ];
}
