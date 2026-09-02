<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Lifer extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_DEAD = 'dead';

    public const SEX_MALE = 'male';

    public const SEX_FEMALE = 'female';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'sex',
        'born_at',
        'status',
        'died_at',
        'age_at_death',
        'death_cause',
    ];

    protected $casts = [
        'born_at' => 'datetime',
        'died_at' => 'datetime',
        'age_at_death' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gameState()
    {
        return $this->hasOne(LiferGameState::class);
    }

    public function lifeGauge()
    {
        return $this->hasOne(LifeGauge::class);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function diplomas()
    {
        return $this->belongsToMany(Diploma::class, 'lifer_diplomas')
            ->withPivot(['earned_at', 'is_public']);
    }

    public function studyEnrollments()
    {
        return $this->hasMany(LiferStudyEnrollment::class);
    }

    public function activeStudyEnrollment()
    {
        return $this->hasOne(LiferStudyEnrollment::class)
            ->where('status', LiferStudyEnrollment::STATUS_ACTIVE);
    }

    public function employment()
    {
        return $this->hasOne(LiferEmployment::class);
    }

    public function sicknesses()
    {
        return $this->belongsToMany(Sickness::class, 'lifer_sicknesses')
            ->withPivot(['contracted_at', 'expected_recovery_at', 'last_effect_applied_on', 'fatal_at'])
            ->withTimestamps();
    }

    public function subscriptions()
    {
        return $this->hasMany(LiferSubscription::class);
    }

    public function dailyJournalAccesses()
    {
        return $this->hasMany(DailyJournalAccess::class);
    }

    public function animals()
    {
        return $this->hasMany(Animal::class);
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_lifer')
            ->withPivot('history_from_message_id')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_lifer_id');
    }

    public function unreadPrivateMessagesCount(): int
    {
        return DB::table('messages')
            ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
            ->join('conversation_lifer', function ($join) {
                $join->on('conversation_lifer.conversation_id', '=', 'conversations.id')
                    ->where('conversation_lifer.lifer_id', $this->id);
            })
            ->leftJoin('message_reads', function ($join) {
                $join->on('message_reads.message_id', '=', 'messages.id')
                    ->where('message_reads.reader_lifer_id', $this->id);
            })
            ->where('conversations.type', Conversation::TYPE_PRIVATE)
            ->where('messages.sender_lifer_id', '<>', $this->id)
            ->whereNull('message_reads.message_id')
            ->count();
    }

    public function profile()
    {
        return $this->hasOne(LiferProfile::class);
    }

    public function profileImages()
    {
        return $this->hasMany(LiferImage::class);
    }

    public function authoredProfileComments()
    {
        return $this->hasMany(ProfileComment::class, 'author_lifer_id');
    }

    public function receivedProfileComments()
    {
        return $this->hasMany(ProfileComment::class, 'receiver_lifer_id');
    }

    public function sentFamilyRequests()
    {
        return $this->hasMany(FamilyRequest::class, 'requester_lifer_id');
    }

    public function receivedFamilyRequests()
    {
        return $this->hasMany(FamilyRequest::class, 'recipient_lifer_id');
    }

    public function marriagesAsFirstPartner()
    {
        return $this->hasMany(LiferMarriage::class, 'first_lifer_id');
    }

    public function marriagesAsSecondPartner()
    {
        return $this->hasMany(LiferMarriage::class, 'second_lifer_id');
    }

    public function pregnanciesAsMother()
    {
        return $this->hasMany(FamilyPregnancy::class, 'mother_lifer_id');
    }

    public function pregnanciesAsFather()
    {
        return $this->hasMany(FamilyPregnancy::class, 'father_lifer_id');
    }

    public function familyChildren()
    {
        return $this->belongsToMany(FamilyChild::class, 'family_child_guardians', 'lifer_id', 'child_id')
            ->withPivot(['type', 'has_custody', 'adopted_at', 'renounced_at'])
            ->withTimestamps();
    }

    public function familyFavorites()
    {
        return $this->belongsToMany(self::class, 'lifer_family_favorites', 'owner_lifer_id', 'favorite_lifer_id')
            ->withTimestamps();
    }

    public function activeMarriage(): ?LiferMarriage
    {
        return LiferMarriage::query()
            ->where('status', LiferMarriage::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->where('first_lifer_id', $this->id)
                    ->orWhere('second_lifer_id', $this->id);
            })
            ->first();
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeDead($query)
    {
        return $query->where('status', self::STATUS_DEAD);
    }

    public function calculateAge(): int
    {
        if ($this->status === self::STATUS_DEAD && $this->age_at_death !== null) {
            return $this->age_at_death;
        }

        $days = (int) floor($this->born_at->diffInDays(now(), true));

        return 18 + intdiv($days, 3);
    }
}
