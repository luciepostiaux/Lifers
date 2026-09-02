<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyChild extends Model
{
    public const STATUS_EXPECTED = 'expected';

    public const STATUS_DEPENDENT = 'dependent';

    public const STATUS_ORPHANED = 'orphaned';

    public const STATUS_AVAILABLE = 'available';

    public const STATUS_CLAIMED = 'claimed';

    public const STATUS_DEAD = 'dead';

    protected $fillable = [
        'pregnancy_id',
        'biological_mother_lifer_id',
        'biological_father_lifer_id',
        'claimed_lifer_id',
        'birth_order',
        'first_name',
        'last_name',
        'sex',
        'status',
        'conceived_at',
        'born_at',
        'adult_at',
        'died_at',
        'death_cause',
    ];

    protected $casts = [
        'birth_order' => 'integer',
        'conceived_at' => 'datetime',
        'born_at' => 'datetime',
        'adult_at' => 'datetime',
        'died_at' => 'datetime',
    ];

    public function pregnancy()
    {
        return $this->belongsTo(FamilyPregnancy::class, 'pregnancy_id');
    }

    public function biologicalMother()
    {
        return $this->belongsTo(Lifer::class, 'biological_mother_lifer_id');
    }

    public function biologicalFather()
    {
        return $this->belongsTo(Lifer::class, 'biological_father_lifer_id');
    }

    public function claimedLifer()
    {
        return $this->belongsTo(Lifer::class, 'claimed_lifer_id');
    }

    public function gauges()
    {
        return $this->hasOne(FamilyChildGauge::class, 'child_id');
    }

    public function guardians()
    {
        return $this->belongsToMany(Lifer::class, 'family_child_guardians', 'child_id', 'lifer_id')
            ->withPivot(['type', 'has_custody', 'adopted_at', 'renounced_at'])
            ->withTimestamps();
    }

    public function calculateAge(): ?int
    {
        if (! $this->born_at) {
            return null;
        }

        return min(18, intdiv((int) floor($this->born_at->diffInDays(now(), true)), 3));
    }
}
