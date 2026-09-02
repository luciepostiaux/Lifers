<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyRequest extends Model
{
    public const TYPE_MARRIAGE = 'marriage';

    public const TYPE_INTIMACY_PROTECTED = 'intimacy_protected';

    public const TYPE_BABY_ATTEMPT = 'baby_attempt';

    public const TYPE_CHILD_ABANDONMENT = 'child_abandonment';

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'requester_lifer_id',
        'recipient_lifer_id',
        'child_id',
        'type',
        'status',
        'metadata',
        'responded_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'responded_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(Lifer::class, 'requester_lifer_id');
    }

    public function recipient()
    {
        return $this->belongsTo(Lifer::class, 'recipient_lifer_id');
    }

    public function child()
    {
        return $this->belongsTo(FamilyChild::class, 'child_id');
    }

    public function intimacyEvent()
    {
        return $this->hasOne(LiferIntimacyEvent::class, 'request_id');
    }
}
