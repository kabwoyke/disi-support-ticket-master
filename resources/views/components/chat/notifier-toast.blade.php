{{-- Toast shown by the chatNotifier Alpine component (defined in resources/js/chat-room.js). --}}
<div x-show="toast" x-cloak x-transition class="toast toast-end z-50">
    <a x-bind:href="toast?.url" wire:navigate @click="toast = null"
       class="alert bg-base-100 border border-base-300 shadow-lg w-80 flex items-start gap-3 hover:bg-base-200">
        <svg class="w-5 h-5 text-primary shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.5 9.5 0 01-4.086-.915L3 20l1.086-4.342A7.95 7.95 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        <div class="min-w-0 text-left">
            <p class="text-xs font-bold text-base-content truncate">New message from <span x-text="toast?.sender_name"></span></p>
            <p class="text-xs text-base-content/70 truncate" x-text="toast?.preview"></p>
        </div>
    </a>
</div>
