/**
 * Alpine helper for the ticket chat screens.
 *
 * Handles what Livewire can't do cheaply: presence (who is online), typing
 * whispers, and keeping the message list scrolled to the newest message.
 * Messages themselves still travel through the Livewire/Reverb listeners.
 */
const register = () => {
    window.Alpine.data('chatRoom', ({ chatId, me }) => ({
        online: false,
        typing: false,
        typingTimer: null,
        lastWhisper: 0,
        channel: null,

        init() {
            this.scrollToBottom();

            // Keep the view pinned to the latest message whenever Livewire re-renders it.
            this.$watch('$wire.messages', () => this.$nextTick(() => this.scrollToBottom()));

            document.addEventListener('visibilitychange', this.onVisible = () => {
                if (!document.hidden) this.$wire.markRead();
            });

            if (!chatId || !window.Echo) return;

            this.channel = window.Echo.join(`chat.${chatId}`)
                .here(members => (this.online = members.some(m => m.type !== me)))
                .joining(member => { if (member.type !== me) this.online = true; })
                .leaving(member => {
                    if (member.type !== me) { this.online = false; this.typing = false; }
                })
                .listenForWhisper('typing', e => {
                    if (e.type === me) return;
                    this.typing = true;
                    clearTimeout(this.typingTimer);
                    this.typingTimer = setTimeout(() => (this.typing = false), 3000);
                });
        },

        destroy() {
            document.removeEventListener('visibilitychange', this.onVisible);
            clearTimeout(this.typingTimer);
            if (chatId && window.Echo) window.Echo.leave(`chat.${chatId}`);
        },

        /** Call from the text input's @input; throttled to one whisper / 1.5s. */
        notifyTyping() {
            const now = Date.now();
            if (!this.channel || now - this.lastWhisper < 1500) return;
            this.lastWhisper = now;
            this.channel.whisper('typing', { type: me });
        },

        scrollToBottom() {
            const el = this.$refs.messages;
            if (el) el.scrollTop = el.scrollHeight;
        },
    }));
};

if (window.Alpine) register();
else document.addEventListener('alpine:init', register);

/**
 * Layout-level listener: shows a toast and bumps a nav badge when a chat
 * notification arrives on the user's private notification channel.
 */
const registerNotifier = () => {
    window.Alpine.data('chatNotifier', ({ channel, unread }) => ({
        unread,
        nav: false, // mobile sidebar drawer (support layout)
        toast: null,
        toastTimer: null,

        init() {
            if (!window.Echo) return;

            window.Echo.private(channel).notification(n => {
                if (n.kind !== 'chat') return;

                // Already looking at this conversation: the chat view handles it.
                const open = new URLSearchParams(location.search).get('ticket');
                if (open && String(open) === String(n.chat_id)
                    && /\/chat\//.test(location.pathname) && !document.hidden) return;

                this.unread++;
                this.toast = n;
                this.beep();
                clearTimeout(this.toastTimer);
                this.toastTimer = setTimeout(() => (this.toast = null), 8000);
            });
        },

        destroy() {
            clearTimeout(this.toastTimer);
            if (window.Echo) window.Echo.leave(channel);
        },

        beep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain); gain.connect(ctx.destination);
                osc.frequency.value = 880; gain.gain.value = 0.04;
                osc.start(); osc.stop(ctx.currentTime + 0.15);
            } catch (e) { /* audio blocked until user interacts; ignore */ }
        },
    }));
};

if (window.Alpine) registerNotifier();
else document.addEventListener('alpine:init', registerNotifier);
