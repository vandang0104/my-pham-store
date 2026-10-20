<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatMessageRequest;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Admin/staff trả lời chat: danh sách (chưa đọc lên đầu), xem + trả lời (polling 5 giây), đóng hội thoại */
class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    public function index(): View
    {
        return view('admin.chats.index', [
            'conversations' => ChatConversation::query()
                ->with('user')
                ->withCount(['messages as unread_count' => fn ($q) => $q->where('sender_type', ChatMessage::FROM_CUSTOMER)->whereNull('read_at')])
                ->orderByDesc('unread_count')
                ->orderByDesc('last_message_at')
                ->paginate(20),
        ]);
    }

    public function show(ChatConversation $conversation): View
    {
        $conversation->load(['user', 'messages']);
        $this->chat->markRead($conversation, ChatMessage::FROM_STAFF);

        return view('admin.chats.show', ['conversation' => $conversation]);
    }

    /** GET JSON — tin mới hơn ?sau=ID (polling) */
    public function messages(Request $request, ChatConversation $conversation): JsonResponse
    {
        $this->chat->markRead($conversation, ChatMessage::FROM_STAFF);

        return response()->json([
            'messages' => $this->chat->messagesAfter($conversation, (int) $request->query('sau', 0))->map->toChatArray()->values(),
        ]);
    }

    public function reply(ChatMessageRequest $request, ChatConversation $conversation): JsonResponse
    {
        $message = $this->chat->send($conversation, ChatMessage::FROM_STAFF, $request->user(), $request->validated('body'));

        return response()->json(['message' => $message->toChatArray()], 201);
    }

    public function close(ChatConversation $conversation): RedirectResponse
    {
        $this->chat->close($conversation);

        return redirect()->route('admin.chats.index')->with('status', __('chat.closed'));
    }
}
