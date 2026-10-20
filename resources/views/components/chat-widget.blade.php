{{-- Chat trực tuyến phía khách: polling 5 giây khi khung đang mở (resources/js/chat.js). Nội dung tin gán bằng textContent. --}}
<div data-chat data-url="{{ route('chat.messages') }}" data-send-url="{{ route('chat.send') }}">
    <button type="button" class="btn btn-primary chat-launcher" data-bs-toggle="offcanvas" data-bs-target="#chat-panel" aria-controls="chat-panel">
        Chat
    </button>

    <div class="offcanvas offcanvas-end chat-panel" id="chat-panel" tabindex="-1" aria-labelledby="chat-panel-title">
        <div class="offcanvas-header">
            <h2 id="chat-panel-title" class="h5 mb-0">Chat với Mộc Lam</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng chat"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <div class="chat-messages flex-grow-1 mb-3" data-chat-messages aria-live="polite">
                <p class="text-muted small mb-0" data-chat-empty>Gửi câu hỏi, nhân viên sẽ trả lời trong giờ làm việc.</p>
            </div>
            <form data-chat-form novalidate>
                <div class="mb-2" data-chat-name hidden>
                    <label for="chat-name" class="form-label">Tên của bạn</label>
                    <input id="chat-name" name="name" type="text" maxlength="100" class="form-control" autocomplete="name">
                </div>
                <label for="chat-body" class="visually-hidden">Tin nhắn</label>
                <textarea id="chat-body" name="body" rows="2" maxlength="1000" class="form-control mb-2" placeholder="Nhập tin nhắn…" required></textarea>
                <p class="text-danger small mb-2" data-chat-error role="alert" hidden></p>
                <button type="submit" class="btn btn-primary w-100">Gửi</button>
            </form>
        </div>
    </div>
</div>
