@props(['msg', 'mine' => false])

@php
    $isImage = !empty($msg['attachment']) && preg_match('/\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i', $msg['attachment']);
@endphp

<div wire:key="{{ $msg['id'] }}" class="chat {{ $mine ? 'chat-end' : 'chat-start' }}">
    @unless ($mine)
        <div class="chat-image avatar">
            <div class="w-8 rounded-full">
                <img src="{{ $msg['avatar'] }}" alt="{{ $msg['name'] }}" />
            </div>
        </div>
    @endunless

    <div class="chat-header text-xs text-base-content/60 mb-1">
        {{ $mine ? 'You' : $msg['name'] }}
        <time class="text-[10px] opacity-50 ml-1">{{ $msg['time'] }}</time>
    </div>

    <div class="chat-bubble {{ $mine ? 'chat-bubble-primary text-white' : 'chat-bubble-neutral' }} text-xs leading-relaxed space-y-2">
        @if (!empty($msg['text']))
            <p class="whitespace-pre-line break-words">{{ $msg['text'] }}</p>
        @endif

        @if (!empty($msg['attachment']))
            @if ($isImage)
                <a href="{{ $msg['attachment'] }}" target="_blank" rel="noopener">
                    <img src="{{ $msg['attachment'] }}" loading="lazy"
                         class="max-w-xs rounded border {{ $mine ? 'border-white/20' : 'border-base-300' }} hover:opacity-90 transition-opacity" />
                </a>
            @else
                <a href="{{ $msg['attachment'] }}" target="_blank" rel="noopener"
                   class="flex items-center gap-2 underline font-medium {{ $mine ? 'text-white' : 'text-primary' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    {{ $msg['file_name'] ?? 'View attachment' }}
                </a>
            @endif
        @endif
    </div>

    @if ($mine)
        <div class="chat-footer text-[10px] mt-1 {{ !empty($msg['read']) ? 'text-info' : 'opacity-50' }}">
            {{ !empty($msg['read']) ? '✓✓ Seen' : '✓ Delivered' }}
        </div>
    @endif
</div>
