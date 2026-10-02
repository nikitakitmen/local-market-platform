<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Диалог между покупателем и производителем.
 */
class Conversation extends Model
{
    protected $fillable = ['buyer_id', 'producer_id', 'last_message_at'];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function producer(): BelongsTo
    {
        return $this->belongsTo(Producer::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /** Пользователь — участник диалога (покупатель или владелец производителя). */
    public function hasParticipant(User $user): bool
    {
        return $this->buyer_id === $user->id || $this->producer->user_id === $user->id;
    }

    /** Пользователь отвечает в диалоге от имени производителя. */
    public function isProducerSide(User $user): bool
    {
        return $this->producer->user_id === $user->id;
    }

    /** Отметить прочитанными сообщения собеседника. */
    public function markAsReadFor(User $user): void
    {
        $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function unreadCountFor(User $user): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function addMessage(User $sender, string $body): Message
    {
        $message = $this->messages()->create([
            'sender_id' => $sender->id,
            'body' => trim($body),
        ]);

        $this->update(['last_message_at' => $message->created_at]);

        return $message;
    }
}
