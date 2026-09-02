<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LifeGauge extends Model
{
    use HasFactory;

    protected $primaryKey = 'lifer_id';

    public $incrementing = false;

    protected $fillable = [
        'lifer_id',
        'hunger',
        'thirst',
        'clean',
        'happiness',
        'entertainment',
        'physical_condition',
        'health',
    ];

    protected $casts = [
        'hunger' => 'integer',
        'thirst' => 'integer',
        'clean' => 'integer',
        'happiness' => 'integer',
        'entertainment' => 'integer',
        'physical_condition' => 'integer',
        'health' => 'integer',
    ];

    public function lifer()
    {
        return $this->belongsTo(Lifer::class);
    }
}
