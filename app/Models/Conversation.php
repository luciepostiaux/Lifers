<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Conversation extends Model
{
    use HasFactory;

    public const TYPE_GENERAL = 'general';

    public const TYPE_PRIVATE = 'private';

    public const TYPE_GROUP = 'group';

    public const KEY_STAFF = 'staff';

    protected $fillable = ['name', 'type', 'key'];

    public function isStaffConversation(): bool
    {
        return $this->key === self::KEY_STAFF;
    }

    public static function privateKey(int $firstLiferId, int $secondLiferId): string
    {
        $ids = [$firstLiferId, $secondLiferId];
        sort($ids, SORT_NUMERIC);

        return 'private:'.$ids[0].':'.$ids[1];
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function messagesVisibleTo(Lifer $lifer): HasMany
    {
        $messages = $this->messages();

        if ($this->type !== self::TYPE_GENERAL) {
            return $messages;
        }

        $historyFromMessageId = DB::table('conversation_lifer')
            ->where('conversation_id', $this->id)
            ->where('lifer_id', $lifer->id)
            ->value('history_from_message_id');

        if ($historyFromMessageId !== null) {
            $messages->where('messages.id', '>=', $historyFromMessageId);
        }

        return $messages;
    }

    public function joinGeneralWithoutPastHistory(Lifer $lifer): void
    {
        DB::table('conversation_lifer')->insertOrIgnore([
            'conversation_id' => $this->id,
            'lifer_id' => $lifer->id,
            'history_from_message_id' => ((int) $this->messages()->max('id')) + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function markReceivedMessagesAsReadBy(Lifer $lifer): void
    {
        if ($this->type !== self::TYPE_PRIVATE) {
            return;
        }

        $messageIds = $this->messages()
            ->where('sender_lifer_id', '<>', $lifer->id)
            ->whereNotExists(function ($query) use ($lifer) {
                $query->selectRaw('1')
                    ->from('message_reads')
                    ->whereColumn('message_reads.message_id', 'messages.id')
                    ->where('message_reads.reader_lifer_id', $lifer->id);
            })
            ->pluck('id');

        if ($messageIds->isEmpty()) {
            return;
        }

        $readAt = now();

        DB::table('message_reads')->insertOrIgnore(
            $messageIds->map(fn (int $messageId) => [
                'message_id' => $messageId,
                'reader_lifer_id' => $lifer->id,
                'read_at' => $readAt,
            ])->all(),
        );
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function lifers()
    {
        return $this->belongsToMany(Lifer::class, 'conversation_lifer')
            ->withPivot('history_from_message_id')
            ->withTimestamps();
    }
}
