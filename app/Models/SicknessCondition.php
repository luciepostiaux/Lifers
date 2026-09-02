<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SicknessCondition extends Model
{
    protected $fillable = ['sickness_id', 'gauge', 'operator', 'threshold'];

    protected $casts = ['threshold' => 'integer'];

    public function sickness()
    {
        return $this->belongsTo(Sickness::class);
    }
}
