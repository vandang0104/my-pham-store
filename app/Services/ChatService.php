<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Chat trực tuyến tự làm (polling): user có một hội thoại theo user_id; khách vãng lai theo guest_token lưu
 * trong session (phải nhập tên trước tin đầu). Nhân viên trả lời ở /admin/chats.
 */
class ChatService
{
    public const SESSION_KEY = 'chat.guest_token';

    public function __construct(private readonly Session $session) {}

    public static function enabled(): bool
    {
        return Setting::get('chat_enabled', '1') === '1';
    }

    /** Hội thoại hiện tại của người đang xem (null nếu chưa từng chat) */
    public function current(?User $user): ?ChatConversation
    {
        if ($user !== null) {
            return ChatConversation::query()->where('user_id', $user->id)->latest('id')->first();
        }

        $token = $this->session->get(self::SESSION_KEY);

        return $token ? ChatConversation::query()->where('guest_token', $token)->first() : null;
    }

    /** Lấy hội thoại hiện tại hoặc mở mới (khách vãng lai cần tên) */
    public function open(?User $user, ?string $guestName): ChatConversation
    {
        if ($conversation = $this->current($user)) {
            return $conversation;
        }

        if ($user !== null) {
            return ChatConversation::query()->create(['user_id' => $user->id, 'status' => ChatConversation::STATUS_OPEN]);
        }

        $token = Str::random(40);
        $this->session->put(self::SESSION_KEY, $token);

        return ChatConversation::query()->create([
            'guest_token' => $token,
            'guest_name' => $guestName,
            'status' => ChatConversation::STATUS_OPEN,
        ]);
    }

    /** Gửi tin; khách nhắn vào hội thoại đã đóng thì mở lại */
    public function send(ChatConversation $conversation, string $senderType, ?User $sender, string $body): ChatMessage
    {
        $message = $conversation->messages()->create([
            'sender_type' => $senderType,
            'sender_id' => $sender?->id,
            'body' => $body,
        ]);

        $conversation->forceFill([
            'last_message_at' => $message->created_at,
            'status' => $senderType === ChatMessage::FROM_CUSTOMER ? ChatConversation::STATUS_OPEN : $conversation->status,
        ])->save();

        return $message;
    }

    /** @return Collection<int, ChatMessage> tin có id > $afterId */
    public function messagesAfter(ChatConversation $conversation, int $afterId = 0): Collection
    {
        return $conversation->messages()->where('id', '>', $afterId)->get();
    }

    /** Người đọc ($readerType) đã xem: đánh dấu đã đọc các tin của phía bên kia */
    public function markRead(ChatConversation $conversation, string $readerType): void
    {
        $other = $readerType === ChatMessage::FROM_STAFF ? ChatMessage::FROM_CUSTOMER : ChatMessage::FROM_STAFF;

        $conversation->messages()->where('sender_type', $other)->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function close(ChatConversation $conversation): void
    {
        $conversation->forceFill(['status' => ChatConversation::STATUS_CLOSED])->save();
    }
}
