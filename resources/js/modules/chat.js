/**
 * Простой чат без WebSocket: сообщения отправляются AJAX-запросом,
 * новые сообщения подгружаются периодическим опросом сервера (polling) каждые 4 секунды.
 */
import { request } from './http';
import { showToast } from './toast';

const POLL_INTERVAL = 4000;

export function initChat() {
    const box = document.querySelector('[data-chat]');
    if (!box) return;

    const list = box.querySelector('[data-chat-messages]');
    const form = box.querySelector('[data-chat-form]');
    const textarea = form.querySelector('textarea');
    const pollUrl = box.dataset.pollUrl;
    let lastId = parseInt(box.dataset.lastId || '0', 10);
    let polling = false;

    const scrollDown = () => {
        list.scrollTop = list.scrollHeight;
    };

    const renderMessage = (message) => {
        if (list.querySelector(`[data-message-id="${message.id}"]`)) return;

        list.querySelector('[data-chat-empty]')?.remove();

        const wrapper = document.createElement('div');
        wrapper.className = `chat-message${message.mine ? ' is-mine' : ''}`;
        wrapper.dataset.messageId = message.id;

        const bubble = document.createElement('div');
        bubble.className = 'chat-message__bubble';
        bubble.textContent = message.body; // textContent — защита от XSS

        const meta = document.createElement('div');
        meta.className = 'chat-message__meta';
        meta.textContent = message.time;

        if (message.mine) {
            const status = document.createElement('i');
            status.className = `bi ${message.read ? 'bi-check2-all text-primary' : 'bi-check2'}`;
            status.dataset.readStatus = '';
            meta.appendChild(status);
        }

        wrapper.append(bubble, meta);
        list.appendChild(wrapper);
        lastId = Math.max(lastId, message.id);
    };

    /** Отметить мои сообщения прочитанными (двойная галочка). */
    const markRead = (readUpTo) => {
        if (!readUpTo) return;
        list.querySelectorAll('.chat-message.is-mine').forEach((el) => {
            if (parseInt(el.dataset.messageId, 10) <= readUpTo) {
                const icon = el.querySelector('[data-read-status]');
                if (icon) icon.className = 'bi bi-check2-all text-primary';
            }
        });
    };

    const poll = async () => {
        if (polling || document.hidden) return;
        polling = true;
        try {
            const data = await request(`${pollUrl}?after=${lastId}`);
            const nearBottom = list.scrollHeight - list.scrollTop - list.clientHeight < 80;
            data.messages.forEach(renderMessage);
            markRead(data.read_up_to);
            if (data.messages.length && nearBottom) scrollDown();
        } catch (e) {
            // сеть недоступна — попробуем при следующем опросе
        } finally {
            polling = false;
        }
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const body = textarea.value.trim();
        if (!body) return;

        const button = form.querySelector('[type="submit"]');
        button.disabled = true;

        try {
            const data = await request(form.action, { method: 'POST', body: { body } });
            renderMessage(data.message);
            textarea.value = '';
            textarea.style.height = '';
            scrollDown();
        } catch (error) {
            showToast(error.message, 'error');
        } finally {
            button.disabled = false;
            textarea.focus();
        }
    });

    // Enter — отправить, Shift+Enter — новая строка
    textarea.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            form.requestSubmit();
        }
    });

    textarea.addEventListener('input', () => {
        textarea.style.height = 'auto';
        textarea.style.height = `${Math.min(textarea.scrollHeight, 128)}px`;
    });

    scrollDown();
    setInterval(poll, POLL_INTERVAL);
}
