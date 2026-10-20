<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Tin nhắn chat: sender_type customer (khách/user) hoặc staff (admin/nhân viên) */
#[Fillable(['sender_type', 'sender_id', 'body', 'read_at'])]
class ChatMessage extends Model
{
    public const FROM_CUSTOMER = 'customer';

    public const FROM_STAFF = 'staff';

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    /** @return BelongsTo<ChatConversation, $this> */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    /** @return BelongsTo<User, $this> */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /** Dạng JSON cho polling (nội dung thô — JS gán bằng textContent nên không chèn được HTML) */
    public function toChatArray(): array
    {
        return [
            'id' => $this->id,
            'from' => $this->sender_type,
            'body' => $this->body,
            'time' => $this->created_at?->format('H:i d/m'),
        ];
    }
}
