<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityEffect extends Model
{
    protected $fillable = ['activity_id', 'gauge', 'effect'];

    protected $casts = ['effect' => 'integer'];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
