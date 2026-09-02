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

    /** @return array<string, mixed> */
    public function communityPayload(): array
    {
        $this->loadMissing('sender.user.roles');

        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_lifer_id' => $this->sender_lifer_id,
            'content' => $this->content,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'sender' => $this->sender ? [
                'id' => $this->sender->id,
                'first_name' => $this->sender->first_name,
                'last_name' => $this->sender->last_name,
                'staff_role' => $this->sender->staffRole(),
            ] : null,
        ];
    }
}
