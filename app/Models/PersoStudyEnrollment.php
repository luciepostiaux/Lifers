<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersoStudyEnrollment extends Model
{
    protected $table = 'perso_study_enrollments';

    protected $fillable = [
        'perso_id', 'study_id', 'start_date', 'end_date',
    ];

    public function perso()
    {
        return $this->belongsTo(Perso::class);
    }

    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    protected $casts = [
        'end_date' => 'datetime',
    ];
}
