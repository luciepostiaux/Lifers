<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SicknessCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'sickness_id',
        'condition_type',
        'threshold',
    ];

    public function sickness()
    {
        return $this->belongsTo(Sickness::class);
    }
}
