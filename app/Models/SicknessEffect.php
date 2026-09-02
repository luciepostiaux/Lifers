<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SicknessEffect extends Model
{
    protected $fillable = ['sickness_id', 'gauge', 'effect'];

    protected $casts = ['effect' => 'integer'];

    public function sickness()
    {
        return $this->belongsTo(Sickness::class);
    }
}
