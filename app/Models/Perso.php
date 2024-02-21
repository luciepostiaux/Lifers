<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perso extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'perso';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'money',
        'user_id',
        'hairstyles_id',
        'life_gauges_id',
        'perso_outfits_id',
        'jobs_id',
        'salary',
        'studies_id',
        'mother_id',
        'father_id',
        'partner_id',
        'genders_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'money' => 'decimal:2',
        'salary' => 'decimal:2',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hairstyle()
    {
        return $this->belongsTo(Hairstyle::class, 'hairstyles_id');
    }

    public function lifeGauge()
    {
        return $this->belongsTo(LifeGauge::class, 'life_gauges_id');
    }

    public function persoOutfit()
    {
        return $this->belongsTo(PersoOutfit::class, 'perso_outfits_id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'jobs_id');
    }

    public function study()
    {
        return $this->belongsTo(Study::class, 'studies_id');
    }

    public function mother()
    {
        return $this->belongsTo(Perso::class, 'mother_id');
    }

    public function father()
    {
        return $this->belongsTo(Perso::class, 'father_id');
    }

    public function partner()
    {
        return $this->belongsTo(Perso::class, 'partner_id');
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class, 'genders_id');
    }
    public function diplomas()
    {
        return $this->belongsToMany(Diploma::class, 'perso_has_diplomas', 'perso_id', 'diplomas_id');
    }
    public function friends()
    {
        return $this->belongsToMany(Perso::class, 'friendships', 'perso_id', 'friend_id');
    }
    public function sicknesses()
    {
        return $this->belongsToMany(Sickness::class, 'perso_has_sickness', 'perso_id', 'sickness_id')
            ->withTimestamps();
    }
}
