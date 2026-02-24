/**
 * Trade conversation live updates (Echo).
 * Single conversation page: CONVERSATION_ID set → subscribe once.
 * Messages index (SPA): CONVERSATION_ID 0/null + ECHO_CONFIG → expose chatEchoSubscribe(convId) / chatEchoUnsubscribe().
 */
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

function initEcho() {
    if (window.Echo) return;
    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: window.ECHO_CONFIG.key,
        cluster: window.ECHO_CONFIG.cluster || 'mt1',
        wsHost: window.ECHO_CONFIG.wsHost || undefined,
        wsPort: window.ECHO_CONFIG.wsPort || undefined,
        wssPort: window.ECHO_CONFIG.wssPort || undefined,
        forceTLS: (window.ECHO_CONFIG.scheme || 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: window.ECHO_CONFIG.authEndpoint || '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
        },
    });
}

function bindChannel(channel, convId) {
    channel.listen('.MessageSent', (e) => {
        if (typeof window.conversationAppendMessage === 'function') window.conversationAppendMessage(e);
    });
    channel.listen('.MessageStatusUpdated', (e) => {
        if (typeof window.conversationUpdateStatus === 'function' && e.message_id) window.conversationUpdateStatus(e.message_id, e.status);
    });
    channel.listen('.MessageUnsent', (e) => {
        if (typeof window.conversationHandleUnsent === 'function' && e.message_id) window.conversationHandleUnsent(e.message_id);
    });
    channel.listen('.UserTyping', (e) => {
        if (e.user_id === window.AUTH_ID) return;
        if (typeof window.conversationShowTyping === 'function') window.conversationShowTyping(e.user_name, e.typing);
    });
    channel.listen('.UserPresenceUpdated', (e) => {
        if (e.user_id === window.AUTH_ID) return;
        if (typeof window.conversationUpdatePresence === 'function') window.conversationUpdatePresence(e);
    });
    channel.error((err) => console.error('Echo channel error:', err));
}

if (typeof window !== 'undefined' && window.ECHO_CONFIG && window.ECHO_CONFIG.key) {
    initEcho();
    const convId = window.CONVERSATION_ID;
    if (convId && convId > 0) {
        const channel = window.Echo.private('conversation.' + convId);
        bindChannel(channel, convId);
        let lastTypingSent = 0;
        window.addEventListener('conversation-typing', (e) => {
            const typing = e.detail && e.detail.typing;
            const now = Date.now();
            if (typing && now - lastTypingSent < 500) return;
            if (typing) lastTypingSent = now;
            const url = window.ECHO_CONFIG.typingUrl || ('/trading/conversations/' + convId + '/typing');
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ typing }),
            }).catch(() => {});
        });
        const presenceInterval = setInterval(() => {
            if (!document.hidden) {
                fetch(window.ECHO_CONFIG.presenceUrl || ('/trading/conversations/' + convId + '/presence'), {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'X-Requested-With': 'XMLHttpRequest' },
                }).catch(() => {});
            }
        }, 10000);
        window.addEventListener('beforeunload', () => clearInterval(presenceInterval));
    } else {
        let currentChannel = null;
        let currentConvId = null;
        window.chatEchoSubscribe = function (id) {
            if (currentChannel && currentConvId) window.Echo.leave('conversation.' + currentConvId);
            currentConvId = id;
            if (!id) return;
            currentChannel = window.Echo.private('conversation.' + id);
            bindChannel(currentChannel, id);
        };
        window.chatEchoUnsubscribe = function () {
            if (currentChannel && currentConvId) {
                window.Echo.leave('conversation.' + currentConvId);
                currentChannel = null;
                currentConvId = null;
            }
        };
    }
}
