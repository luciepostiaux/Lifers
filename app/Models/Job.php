<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'jobs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description_1',
        'description_2',
        'salary',
        'img_job',
        'diplomas_id',
        'places_id',
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
        'salary' => 'decimal:2',
    ];

    /**
     * Get the diploma associated with the job.
     */
    public function diploma()
    {
        return $this->belongsTo(Diploma::class, 'diplomas_id');
    }

    /**
     * Get the place associated with the job.
     */
    public function place()
    {
        return $this->belongsTo(Place::class, 'places_id');
    }
}
