<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'user_id',
        'type',
        'data',
        'read',
    ];

    protected $casts = [
        'data' => 'array',
        'read' => 'boolean'
    ];

    /**
     * Récupère l'utilisateur associé à la notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
