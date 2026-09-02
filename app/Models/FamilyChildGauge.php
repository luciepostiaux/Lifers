<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyChildGauge extends Model
{
    protected $primaryKey = 'child_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'child_id',
        'hunger',
        'hygiene',
        'affection',
        'red_since',
        'last_decreased_on',
    ];

    protected $casts = [
        'hunger' => 'integer',
        'hygiene' => 'integer',
        'affection' => 'integer',
        'red_since' => 'date',
        'last_decreased_on' => 'date',
    ];

    public function child()
    {
        return $this->belongsTo(FamilyChild::class, 'child_id');
    }
}
