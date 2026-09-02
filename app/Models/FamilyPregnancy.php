<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyPregnancy extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_LOST = 'lost';

    protected $fillable = [
        'intimacy_event_id',
        'mother_lifer_id',
        'father_lifer_id',
        'children_count',
        'status',
        'conceived_at',
        'due_at',
        'completed_at',
    ];

    protected $casts = [
        'children_count' => 'integer',
        'conceived_at' => 'datetime',
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function intimacyEvent()
    {
        return $this->belongsTo(LiferIntimacyEvent::class, 'intimacy_event_id');
    }

    public function mother()
    {
        return $this->belongsTo(Lifer::class, 'mother_lifer_id');
    }

    public function father()
    {
        return $this->belongsTo(Lifer::class, 'father_lifer_id');
    }

    public function children()
    {
        return $this->hasMany(FamilyChild::class, 'pregnancy_id')->orderBy('birth_order');
    }
}
