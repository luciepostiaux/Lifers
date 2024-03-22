<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityEffect extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id', 'effect_type', 'effect_value'
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
