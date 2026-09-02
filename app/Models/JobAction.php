<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobAction extends Model
{
    protected $fillable = [
        'job_id',
        'name',
        'description',
        'amount',
        'success_chance',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'success_chance' => 'integer',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
