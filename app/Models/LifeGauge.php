<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LifeGauge extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'life_gauges';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'perso_id',
        'hunger',
        'thirst',
        'clean',
        'happiness',
        'entertainment',
        'physical_condition',
        'health',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'hunger' => 'integer',
        'thirst' => 'integer',
        'clean' => 'integer',
        'happiness' => 'integer',
        'entertainment' => 'integer',
        'physical_condition' => 'integer',
        'health' => 'integer',
    ];

    public function perso()
    {
        return $this->belongsTo(Perso::class, 'perso_id');
    }
}
