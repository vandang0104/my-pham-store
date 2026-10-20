// Chat trực tuyến (khách và admin): polling 5 giây bằng fetch, chỉ khi đang mở. Tin gán bằng textContent (không chèn HTML).

const POLL_MS = 5000;

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function renderMessage(list, message, mineType) {
    const item = document.createElement('div');
    item.className = `chat-message ${message.from === mineType ? 'chat-message--mine' : 'chat-message--other'}`;
    const body = document.createElement('p');
    body.className = 'chat-message__body mb-1';
    body.textContent = message.body;
    const time = document.createElement('span');
    time.className = 'chat-message__time small text-muted';
    time.textContent = message.time;
    item.append(body, time);
    list.append(item);
    list.scrollTop = list.scrollHeight;
}

/**
 * @param {HTMLElement} root  phần tử có data-url (GET ?sau=), data-send-url (POST)
 * @param {string} mineType   'customer' hoặc 'staff' — tin của mình căn phải
 */
function createChat(root, mineType) {
    const list = root.querySelector('[data-chat-messages]');
    const form = root.querySelector('[data-chat-form]');
    const nameField = root.querySelector('[data-chat-name]');
    const error = root.querySelector('[data-chat-error]');
    const empty = root.querySelector('[data-chat-empty]');
    let lastId = Number(root.dataset.lastId ?? 0);
    let timer = null;

    const append = (messages) => messages.forEach((m) => {
        if (m.id > lastId) {
            empty?.remove();
            renderMessage(list, m, mineType);
            lastId = m.id;
        }
    });

    const poll = async () => {
        const res = await fetch(`${root.dataset.url}?sau=${lastId}`, { headers: { Accept: 'application/json' } });
        if (!res.ok) {
            return;
        }
        const data = await res.json();
        if (nameField) {
            nameField.hidden = !data.needs_name;
        }
        append(data.messages);
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        error.hidden = true;
        const res = await fetch(root.dataset.sendUrl, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
            body: new FormData(form),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            error.textContent = data.message ?? 'Không gửi được tin nhắn, vui lòng thử lại.';
            error.hidden = false;
            return;
        }
        form.querySelector('textarea').value = '';
        if (nameField) {
            nameField.hidden = true;
        }
        append([data.message]);
    });

    return {
        start() {
            poll();
            timer = setInterval(poll, POLL_MS);
        },
        stop() {
            clearInterval(timer);
        },
    };
}

export function initChat() {
    // Khách: chỉ polling khi khung chat (offcanvas) đang mở
    const widget = document.querySelector('[data-chat]');
    if (widget) {
        const chat = createChat(widget, 'customer');
        const panel = widget.querySelector('#chat-panel');
        panel.addEventListener('shown.bs.offcanvas', () => chat.start());
        panel.addEventListener('hidden.bs.offcanvas', () => chat.stop());
    }

    // Admin: trang xem hội thoại polling ngay khi mở
    const admin = document.querySelector('[data-admin-chat]');
    if (admin) {
        const messages = admin.querySelector('[data-chat-messages]');
        messages.scrollTop = messages.scrollHeight;
        createChat(admin, 'staff').start();
    }
}
