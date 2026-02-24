/**
 * Trade conversation live updates (Echo).
 * Loaded only on conversation show page when broadcasting is enabled.
 */
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

if (typeof window !== 'undefined' && window.CONVERSATION_ID && window.ECHO_CONFIG && window.ECHO_CONFIG.key) {
    window.Pusher = Pusher;
    if (!window.Echo) {
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

    const channel = window.Echo.private('conversation.' + window.CONVERSATION_ID);

    channel.listen('.MessageSent', (e) => {
        console.log('MessageSent event received:', e);
        if (typeof window.conversationAppendMessage === 'function') {
            window.conversationAppendMessage(e);
        }
    });

    channel.listen('.MessageStatusUpdated', (e) => {
        console.log('MessageStatusUpdated event received:', e);
        if (typeof window.conversationUpdateStatus === 'function' && e.message_id) {
            window.conversationUpdateStatus(e.message_id, e.status);
        }
    });

    channel.listen('.UserTyping', (e) => {
        console.log('UserTyping event received:', e);
        if (e.user_id === window.AUTH_ID) return;
        if (typeof window.conversationShowTyping === 'function') {
            window.conversationShowTyping(e.user_name, e.typing);
        }
    });

    channel.listen('.UserPresenceUpdated', (e) => {
        console.log('UserPresenceUpdated event received:', e);
        if (e.user_id === window.AUTH_ID) return;
        if (typeof window.conversationUpdatePresence === 'function') {
            window.conversationUpdatePresence(e);
        }
    });
    
    // Error handling
    channel.error((error) => {
        console.error('Echo channel error:', error);
    });

    // Typing: send to server when user types
    let lastTypingSent = 0;
    window.addEventListener('conversation-typing', (e) => {
        const typing = e.detail && e.detail.typing;
        const now = Date.now();
        
        // Throttle typing events to prevent spam (max once per 500ms for "typing: true")
        if (typing && now - lastTypingSent < 500) return;
        if (typing) lastTypingSent = now;
        
        fetch(window.ECHO_CONFIG.typingUrl || ('/trading/conversations/' + window.CONVERSATION_ID + '/typing'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ typing }),
        }).catch((err) => {
            console.error('Typing event error:', err);
        });
    });

    // Presence heartbeat - every 10s for real-time online status
    const presenceInterval = setInterval(() => {
        if (!document.hidden) {
            fetch(window.ECHO_CONFIG.presenceUrl || ('/trading/conversations/' + window.CONVERSATION_ID + '/presence'), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).catch(() => {});
        }
    }, 10000);
    window.addEventListener('beforeunload', () => clearInterval(presenceInterval));
}
