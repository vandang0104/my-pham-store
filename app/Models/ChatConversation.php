<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Hội thoại chat: của user (user_id) hoặc khách vãng lai (guest_token trong session + guest_name) */
#[Fillable(['user_id', 'guest_token', 'guest_name', 'status', 'last_message_at'])]
class ChatConversation extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    protected function casts(): array
    {
        return ['last_message_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<ChatMessage, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id')->orderBy('id');
    }

    /** Tên hiển thị cho nhân viên */
    public function displayName(): string
    {
        return $this->user?->name ?? ($this->guest_name ? $this->guest_name.' (khách)' : 'Khách');
    }
}
