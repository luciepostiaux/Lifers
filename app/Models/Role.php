<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public const USER = 'user';

    public const MODERATOR = 'moderator';

    public const ADMIN = 'admin';

    public const ASSIGNABLE = [
        self::USER,
        self::MODERATOR,
    ];

    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
