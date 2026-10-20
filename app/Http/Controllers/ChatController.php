<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatMessageRequest;
use App\Models\ChatMessage;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * API chat phía khách (JSON, polling 5 giây ở resources/js/chat.js).
 * Chỉ đọc/ghi hội thoại của chính mình (user_id hoặc guest_token trong session). Chat tắt → 404.
 */
class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    /** GET /chat/tin-nhan?sau=ID — tin mới hơn ID; đánh dấu tin của nhân viên là đã đọc */
    public function index(Request $request): JsonResponse
    {
        abort_unless(ChatService::enabled(), 404);
        $conversation = $this->chat->current($request->user());

        if ($conversation === null) {
            return response()->json(['messages' => [], 'needs_name' => $request->user() === null]);
        }

        $this->chat->markRead($conversation, ChatMessage::FROM_CUSTOMER);

        return response()->json([
            'messages' => $this->chat->messagesAfter($conversation, (int) $request->query('sau', 0))->map->toChatArray()->values(),
            'needs_name' => false,
        ]);
    }

    /** POST /chat/tin-nhan — gửi tin; khách vãng lai chưa có hội thoại phải kèm tên */
    public function store(ChatMessageRequest $request): JsonResponse
    {
        abort_unless(ChatService::enabled(), 404);
        $user = $request->user();

        if ($user === null && $this->chat->current(null) === null && ! $request->validated('name')) {
            throw ValidationException::withMessages(['name' => __('chat.name_required')]);
        }

        $conversation = $this->chat->open($user, $request->validated('name'));
        $message = $this->chat->send($conversation, ChatMessage::FROM_CUSTOMER, $user, $request->validated('body'));

        return response()->json(['message' => $message->toChatArray()], 201);
    }
}
