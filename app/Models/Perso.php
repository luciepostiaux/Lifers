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

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::created(function ($perso) {
    //         LifeGauge::create([
    //             'perso_id' => $perso->id,
    //             'hunger' => 100,
    //             'thirst' => 100,
    //             'clean' => 100,
    //             'happiness' => 100,
    //             'health' => 100,
    //         ]);
    //     });
    // }


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
        return $this->hasOne(LifeGauge::class, 'perso_id');
    }


    public function persoOutfit()
    {
        return $this->belongsTo(PersoOutfit::class, 'perso_outfits_id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'jobs_id');
    }

    public function enrolledStudies()
    {
        return $this->hasMany(PersoStudyEnrollment::class);
    }
    public function resignFromStudy()
    {
        foreach ($this->enrolledStudies as $enrollment) {
            $enrollment->delete();
        }
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

    public function sicknesses()
    {
        return $this->belongsToMany(Sickness::class, 'perso_has_sickness', 'perso_id', 'sickness_id')
            ->withTimestamps();
    }

    // Méthode pour envoyer une demande d'amitié
    public function sendFriendRequestTo($friendId)
    {
        $this->friends()->attach($friendId, ['is_accepted' => false]);
    }

    // Méthode pour accepter une demande d'amitié
    public function acceptFriendRequestFrom($friendId)
    {
        $this->friends()->updateExistingPivot($friendId, ['is_accepted' => true]);
    }

    // Méthode pour vérifier si deux persos sont amis
    public function isFriendWith($friendId)
    {
        return $this->friends()->where('id', $friendId)->wherePivot('is_accepted', true)->exists();
    }

    // Modifier la relation 'friends' pour inclure le pivot 'is_accepted'
    public function friends()
    {
        return $this->belongsToMany(Perso::class, 'friendships', 'perso_id', 'friend_id')->withPivot('is_accepted')->withTimestamps();
    }
    public function body()
    {
        return $this->belongsTo(PersoBody::class, 'perso_bodies_id');
    }

    public function calculateAge()
    {
        $startingAge = 18;
        $createdAt = $this->birth_date;
        $now = now();
        $daysDifference = $createdAt->diffInDays($now);
        $ageIncrement = intdiv($daysDifference, 3);
        return $startingAge + $ageIncrement;
    }

    public function currentStudy()
    {
        return $this->enrolledStudies()->latest('start_date')->first();
    }
    public function associatedStudies()
    {
        $currentStudyDiplomaId = $this->currentStudy()->diploma->id;

        return Study::where('diplomas_required_id', $currentStudyDiplomaId)->get();
    }
}
