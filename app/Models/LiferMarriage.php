<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiferMarriage extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_DIVORCED = 'divorced';

    public const STATUS_WIDOWED = 'widowed';

    protected $fillable = [
        'first_lifer_id',
        'second_lifer_id',
        'lower_lifer_id',
        'higher_lifer_id',
        'status',
        'started_at',
        'ended_at',
        'end_reason',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function firstLifer()
    {
        return $this->belongsTo(Lifer::class, 'first_lifer_id');
    }

    public function secondLifer()
    {
        return $this->belongsTo(Lifer::class, 'second_lifer_id');
    }

    public function spouseOf(Lifer $lifer): ?Lifer
    {
        return $this->first_lifer_id === $lifer->id ? $this->secondLifer : $this->firstLifer;
    }
}
