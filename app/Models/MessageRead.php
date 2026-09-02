<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MessageRead extends Pivot
{
    protected $table = 'message_reads';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['message_id', 'reader_lifer_id', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];
}
