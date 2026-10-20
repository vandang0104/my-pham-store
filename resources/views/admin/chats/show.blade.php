@extends('layouts.admin')

@section('title', 'Chat: '.$conversation->displayName())
@section('admin_col', 'col-lg-10')

@section('content')
    <div data-admin-chat data-url="{{ route('admin.chats.messages', $conversation) }}" data-send-url="{{ route('admin.chats.reply', $conversation) }}"
        data-last-id="{{ $conversation->messages->last()?->id ?? 0 }}">
        <div class="chat-messages border p-3 mb-3" data-chat-messages aria-live="polite">
            @forelse ($conversation->messages as $message)
                <div class="chat-message {{ $message->sender_type === 'staff' ? 'chat-message--mine' : 'chat-message--other' }}">
                    <p class="chat-message__body mb-1">{{ $message->body }}</p>
                    <span class="chat-message__time small text-muted">{{ $message->created_at->format('H:i d/m') }}</span>
                </div>
            @empty
                <p class="text-muted small mb-0" data-chat-empty>Chưa có tin nhắn.</p>
            @endforelse
        </div>

        <form data-chat-form novalidate>
            <label for="chat-body" class="visually-hidden">Trả lời</label>
            <textarea id="chat-body" name="body" rows="3" maxlength="1000" class="form-control mb-2" placeholder="Nhập câu trả lời…" required></textarea>
            <p class="text-danger small mb-2" data-chat-error role="alert" hidden></p>
            <button type="submit" class="btn btn-primary">Gửi trả lời</button>
        </form>
    </div>

    <div class="d-flex gap-3 align-items-center mt-5">
        <a href="{{ route('admin.chats.index') }}">← Danh sách hội thoại</a>
        @if ($conversation->status === 'open')
            <form method="POST" action="{{ route('admin.chats.close', $conversation) }}" class="ms-auto">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-outline-secondary btn-sm">Đóng hội thoại</button>
            </form>
        @endif
    </div>
@endsection
