<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';

    protected $fillable = ['requester_lifer_id', 'recipient_lifer_id', 'status'];

    protected static function booted(): void
    {
        static::saving(function (Friendship $friendship) {
            $friendship->lower_lifer_id = min(
                $friendship->requester_lifer_id,
                $friendship->recipient_lifer_id,
            );
            $friendship->higher_lifer_id = max(
                $friendship->requester_lifer_id,
                $friendship->recipient_lifer_id,
            );
        });
    }

    public function requester()
    {
        return $this->belongsTo(Lifer::class, 'requester_lifer_id');
    }

    public function recipient()
    {
        return $this->belongsTo(Lifer::class, 'recipient_lifer_id');
    }
}
