<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Study extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'studies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description_1',
        'price',
        'duration',
        'img_study',
        'description_2',
        'diplomas_id',
        'diplomas_required_id',
        'places_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Get the diploma associated with the study.
     */
    public function diploma()
    {
        return $this->belongsTo(Diploma::class, 'diplomas_id');
    }

    /**
     * Get the required diploma for the study.
     */
    public function requiredDiploma()
    {
        return $this->belongsTo(Diploma::class, 'diplomas_required_id');
    }

    /**
     * Get the place associated with the study.
     */
    public function place()
    {
        return $this->belongsTo(Place::class, 'places_id');
    }
}
