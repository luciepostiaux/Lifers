<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiferIntimacyEvent extends Model
{
    public const TYPE_PROTECTED = 'protected';

    public const TYPE_BABY_ATTEMPT = 'baby_attempt';

    protected $fillable = [
        'request_id',
        'first_lifer_id',
        'second_lifer_id',
        'type',
        'conception_succeeded',
        'happened_on',
    ];

    protected $casts = [
        'conception_succeeded' => 'boolean',
        'happened_on' => 'date',
    ];

    public function request()
    {
        return $this->belongsTo(FamilyRequest::class, 'request_id');
    }

    public function firstLifer()
    {
        return $this->belongsTo(Lifer::class, 'first_lifer_id');
    }

    public function secondLifer()
    {
        return $this->belongsTo(Lifer::class, 'second_lifer_id');
    }

    public function pregnancy()
    {
        return $this->hasOne(FamilyPregnancy::class, 'intimacy_event_id');
    }
}
