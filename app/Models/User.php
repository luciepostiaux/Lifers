<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'consentement_newsletter',
        'date_consentement',
        'consentement_rgpd',
        'last_login_at',
        'consecutive_login_days',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */

    public function perso()
    {
        return $this->hasOne(Perso::class);
    }


    public function events()
    {
        return $this->belongsToMany(Event::class, 'events_has_users', 'user_id', 'event_id')->withTimestamps();
    }

    public function rewinds()
    {
        return $this->belongsToMany(Rewind::class, 'rewinds_has_users', 'user_id', 'rewind_id')->withTimestamps();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class)->withTimestamps();
    }
    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Envoie un message à une conversation.
     *
     * @param  \App\Models\Conversation $conversation
     * @param  string $content
     * @return \App\Models\Message
     */
    public function sendMessageToConversation(Conversation $conversation, $content)
    {
        $message = new Message(['content' => $content]);
        $message->conversation()->associate($conversation);
        $message->sender()->associate($this);
        $message->save();

        return $message;
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('read', false);
    }

    public function sentSmsMessages()
    {
        return $this->hasMany(SmsMessage::class, 'sender_id');
    }

    public function receivedSmsMessages()
    {
        return $this->hasMany(SmsMessage::class, 'receiver_id');
    }
}
