<?php

namespace App\View\Components;

use App\Services\ChatService;
use Illuminate\View\Component;
use Illuminate\View\View;

/** Nút "Chat" nổi góc phải + khung chat (offcanvas). Ẩn khi Setting chat_enabled = '0'. */
class ChatWidget extends Component
{
    public function shouldRender(): bool
    {
        return ChatService::enabled();
    }

    public function render(): View
    {
        return view('components.chat-widget');
    }
}
