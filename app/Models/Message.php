<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['conversation_id', 'sender_lifer_id', 'content'];

    protected $touches = ['conversation'];

    public function sender()
    {
        return $this->belongsTo(Lifer::class, 'sender_lifer_id');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function readers()
    {
        return $this->belongsToMany(Lifer::class, 'message_reads', 'message_id', 'reader_lifer_id')
            ->withPivot('read_at');
    }
}
