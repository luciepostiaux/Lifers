<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiferStudyEnrollment extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_LEFT = 'left';

    protected $fillable = [
        'lifer_id',
        'study_id',
        'started_at',
        'ends_at',
        'ended_at',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function lifer()
    {
        return $this->belongsTo(Lifer::class);
    }

    public function study()
    {
        return $this->belongsTo(Study::class);
    }
}
