<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Events\AdminChat;
use App\Events\MessagesRead;
use App\Models\ChatMessage;
use App\Models\Chat;
use App\Models\Ticket;
use App\Notifications\NewChatMessage;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    private const SUPPORT = 'App\Models\SupportTeam';

    #[Url]
    public $ticket = '';

    public array $messages = [];
    public string $adminReply = '';
    public $attachment = null;
    public string $search = '';
    public int $limit = 50;
    public bool $hasMore = false;

    public function mount(): void
    {
        if (!$this->ticket) {
            $this->ticket = request()->query('ticket', '');
        }

        $this->loadMessages();
        $this->markRead();
    }

    protected function format(ChatMessage $msg): array
    {
        $fromAdmin = $msg->sender_type === self::SUPPORT;
        $sender = $msg->sender;

        return [
            'id' => 'msg_' . $msg->id,
            'db_id' => $msg->id,
            'sender' => $fromAdmin ? 'admin' : 'user',
            'name' => $sender?->display_name ?? ($fromAdmin ? 'Support' : 'User'),
            'avatar' => $sender?->avatar_url,
            'text' => $msg->message,
            'attachment' => $msg->attachment_path ? Storage::disk('public')->url($msg->attachment_path) : null,
            'file_name' => $msg->attachment_path ? basename($msg->attachment_path) : null,
            'time' => $msg->created_at->timezone('Africa/Nairobi')->format('g:i A'),
            'date' => $msg->created_at->timezone('Africa/Nairobi')->format('D, j M Y'),
            'read' => $msg->read_at !== null,
        ];
    }

    public function loadMessages(): void
    {
        if (!$this->ticket) {
            $this->messages = [];
            $this->hasMore = false;
            return;
        }

        $this->hasMore = ChatMessage::where('chat_id', $this->ticket)->count() > $this->limit;

        $this->messages = ChatMessage::with('sender')
            ->where('chat_id', $this->ticket)
            ->latest('id')
            ->limit($this->limit)
            ->get()
            ->reverse()
            ->values()
            ->map(fn ($msg) => $this->format($msg))
            ->all();
    }

    public function loadEarlier(): void
    {
        $this->limit += 50;
        $this->loadMessages();
    }

    public function getListeners(): array
    {
        if (!$this->ticket) {
            return [];
        }

        return [
            "echo-private:admin-chat.{$this->ticket},.UserChat" => 'receiveUserMessage',
            "echo-private:admin-chat.{$this->ticket},.MessagesRead" => 'onMessagesRead',
        ];
    }

    public function receiveUserMessage(array $event): void
    {
        $dbId = $event['messageId'] ?? null;

        if ($dbId && collect($this->messages)->contains('db_id', $dbId)) {
            return;
        }

        if ($dbId && ($msg = ChatMessage::with('sender')->find($dbId))) {
            $this->messages[] = $this->format($msg);
            $this->markRead();
        }
    }

    /** The user opened the chat: flag everything we sent as seen. */
    public function onMessagesRead(array $event): void
    {
        if (($event['readerType'] ?? null) !== 'user') {
            return;
        }

        $this->messages = array_map(
            fn ($m) => $m['sender'] === 'admin' ? [...$m, 'read' => true] : $m,
            $this->messages
        );
    }

    /** Mark the customer's messages as read and let them know. */
    public function markRead(): void
    {
        if (!$this->ticket) {
            return;
        }

        $updated = ChatMessage::where('chat_id', $this->ticket)
            ->where('sender_type', 'App\Models\User')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($updated) {
            broadcast(new MessagesRead((int) $this->ticket, 'admin'))->toOthers();
        }

        // Opening the chat clears its notifications and refreshes the nav badge.
        $agent = auth('support')->user();
        $agent->unreadNotifications()
            ->where('type', NewChatMessage::class)
            ->where('data->chat_id', (int) $this->ticket)
            ->update(['read_at' => now()]);

        $this->dispatch('chat-unread', count: $agent->unreadNotifications()->where('type', NewChatMessage::class)->count());
    }

    public function sendAdminReply(): void
    {
        $this->validate([
            'adminReply' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:10240',
        ]);

        if (empty(trim($this->adminReply)) && !$this->attachment) {
            return;
        }

        $ticket = $this->ticket ? Ticket::find($this->ticket) : null;

        if (!$ticket) {
            return;
        }

        // Resolved/closed tickets are read-only.
        if ($ticket->isChatClosed()) {
            $this->reset(['adminReply', 'attachment']);
            return;
        }

        $chat = Chat::firstOrCreate(
            ['id' => $ticket->id],
            ['user_id' => $ticket->userId, 'support_id' => auth('support')->id()]
        );

        $storedPath = $this->attachment?->store('chat-attachments', 'public');

        $saved = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_type' => self::SUPPORT,
            'sender_id' => auth('support')->id(),
            'message' => trim($this->adminReply),
            'attachment_path' => $storedPath,
        ]);

        $attachmentUrl = $storedPath ? Storage::disk('public')->url($storedPath) : null;

        AdminChat::dispatch($saved->message, (int) $chat->id, $attachmentUrl, $saved->id);

        $this->messages[] = $this->format($saved->setRelation('sender', auth('support')->user()));

        // Tell the customer there's a new reply.
        $ticket->user?->notify(new NewChatMessage($saved, auth('support')->user()->display_name, false));

        $this->reset(['adminReply', 'attachment']);
    }

    /** Conversations for the sidebar, newest activity first, with unread counts. */
    protected function conversations()
    {
        $chats = Chat::with('user')
            ->withCount(['messages as unread_count' => fn ($q) => $q
                ->where('sender_type', 'App\Models\User')
                ->whereNull('read_at')])
            ->withMax('messages', 'created_at')
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('id', 'like', '%' . ltrim($this->search, '#T-0') . '%')
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"))))
            ->orderByDesc('messages_max_created_at')
            ->limit(50)
            ->get();

        $last = ChatMessage::whereIn('chat_id', $chats->pluck('id'))
            ->latest('id')->get()->unique('chat_id')->keyBy('chat_id');

        return $chats->each(fn ($c) => $c->last_message = $last[$c->id] ?? null);
    }

    public function render()
    {
        return view('pages::ict.features.⚡chat', [
            'conversations' => $this->conversations(),
            'ticketDetail' => $this->ticket
                ? Ticket::with(['user', 'desk', 'department'])->find($this->ticket)
                : null,
        ])->layout('layouts::support');
    }
};
?>

<div>
    <div
        x-data="chatRoom({ chatId: @js($ticket ?: null), me: 'admin' })"
        class="h-[calc(100vh-5rem)] max-w-7xl mx-auto p-4 flex gap-4"
    >

        <!-- Conversation list -->
        <div class="w-full md:w-80 lg:w-96 bg-base-100 rounded-box border border-base-200 shadow-sm flex flex-col shrink-0 {{ $ticket ? 'hidden md:flex' : '' }}">
            <div class="p-4 border-b border-base-200 space-y-3">
                <h2 class="text-lg font-bold text-base-content">Support Chats</h2>
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by user or ticket..."
                        class="input input-sm input-bordered w-full pl-9 text-xs"
                    />
                    <svg class="w-4 h-4 absolute left-3 top-2.5 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
            </div>

            <div wire:poll.15s class="flex-1 overflow-y-auto divide-y divide-base-200">
                @forelse ($conversations as $chat)
                    @php $active = (string) $chat->id === (string) $ticket; @endphp
                    <a
                        wire:key="chat_{{ $chat->id }}"
                        wire:navigate
                        href="{{ route('support-chat', ['ticket' => $chat->id]) }}"
                        class="w-full p-3 flex items-start gap-3 transition-all hover:bg-base-200/60 {{ $active ? 'bg-base-200/60 border-l-4 border-primary' : '' }}"
                    >
                        <div class="avatar shrink-0">
                            <div class="w-10 rounded-full">
                                <img src="{{ $chat->user?->avatar_url }}" alt="{{ $chat->user?->display_name }}" />
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="text-xs font-bold text-base-content truncate">{{ $chat->user?->display_name ?? 'Unknown user' }}</h3>
                                <span class="text-[10px] text-base-content/50 shrink-0">
                                    {{ $chat->last_message?->created_at->timezone('Africa/Nairobi')->diffForHumans(short: true) }}
                                </span>
                            </div>
                            <span class="font-mono text-[10px] font-semibold text-primary block">#T-{{ str_pad($chat->id, 5, '0', STR_PAD_LEFT) }}</span>
                            <div class="flex items-center justify-between gap-2 mt-0.5">
                                <p class="text-xs text-base-content/70 truncate">
                                    @if ($chat->last_message)
                                        {{ $chat->last_message->message !== '' ? $chat->last_message->message : '[Attachment]' }}
                                    @else
                                        New support request
                                    @endif
                                </p>
                                @if ($chat->unread_count > 0 && !$active)
                                    <span class="badge badge-primary badge-sm text-white">{{ $chat->unread_count }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <p class="p-6 text-center text-xs text-base-content/50">No conversations yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Chat window -->
        <div class="flex-1 bg-base-100 rounded-box border border-base-200 shadow-sm flex flex-col overflow-hidden {{ $ticket ? '' : 'hidden md:flex' }}">
            @if (!$ticketDetail)
                <div class="flex-1 flex items-center justify-center text-center text-xs text-base-content/40">
                    <div>
                        <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.5 9.5 0 01-4.086-.915L3 20l1.086-4.342A7.95 7.95 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        <p>Select a conversation to start replying.</p>
                    </div>
                </div>
            @else
                <!-- Header -->
                <div class="p-4 border-b border-base-200 flex items-center justify-between bg-base-100">
                    <div class="flex items-center gap-3">
                        <a wire:navigate href="{{ route('support-chat') }}" class="btn btn-sm btn-ghost btn-circle md:hidden" title="Back">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        </a>
                        <div class="avatar" :class="online && 'online'">
                            <div class="w-10 rounded-full">
                                <img src="{{ $ticketDetail->user?->avatar_url }}" alt="{{ $ticketDetail->user?->display_name }}" />
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-sm font-bold text-base-content">{{ $ticketDetail->user?->display_name }}</h2>
                                <span class="font-mono text-xs text-primary font-semibold">#T-{{ str_pad($ticketDetail->id, 5, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <p class="text-xs text-base-content/60">
                                <span x-show="typing" x-cloak class="text-primary">typing...</span>
                                <span x-show="!typing">
                                    <span x-text="online ? 'Online' : 'Offline'" :class="online ? 'text-success' : ''"></span>
                                    &bull; {{ $ticketDetail->department?->department_name }} &bull; {{ $ticketDetail->desk?->desk_name }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="badge badge-warning text-white font-semibold text-xs hidden sm:inline-flex">{{ $ticketDetail->priority }}</span>
                        <span class="badge badge-outline text-xs">{{ $ticketDetail->status }}</span>
                    </div>
                </div>

                <!-- Messages -->
                <div x-ref="messages" class="flex-1 overflow-y-auto p-4 space-y-4 bg-base-200/20">
                    @if ($hasMore)
                        <div class="text-center">
                            <button type="button" wire:click="loadEarlier" class="btn btn-xs btn-ghost">Load earlier messages</button>
                        </div>
                    @endif

                    @php $lastDate = null; @endphp
                    @forelse ($messages as $msg)
                        @if ($msg['date'] !== $lastDate)
                            <div class="divider text-[10px] text-base-content/40 font-semibold uppercase" wire:key="d_{{ $msg['id'] }}">
                                {{ $msg['date'] === now('Africa/Nairobi')->format('D, j M Y') ? 'Today' : $msg['date'] }}
                            </div>
                            @php $lastDate = $msg['date']; @endphp
                        @endif
                        <x-chat.bubble :msg="$msg" :mine="$msg['sender'] === 'admin'" />
                    @empty
                        <div class="text-center text-xs text-base-content/40 py-8">
                            <p>No messages yet.</p>
                            <p class="mt-1">Send a reply to start the conversation.</p>
                        </div>
                    @endforelse

                    <div x-show="typing" x-cloak class="chat chat-start">
                        <div class="chat-bubble chat-bubble-neutral"><span class="loading loading-dots loading-xs"></span></div>
                    </div>
                </div>

                <!-- Composer -->
                @if ($ticketDetail->isChatClosed())
                <div class="p-4 bg-base-200/60 border-t border-base-200 text-center text-xs text-base-content/60">
                    This ticket is {{ strtolower($ticketDetail->status) }}. The conversation is read-only.
                </div>
                @else
                <div class="p-3 bg-base-100 border-t border-base-200 space-y-2">
                    @if ($attachment)
                        <div class="flex items-center justify-between bg-base-200 px-3 py-1.5 rounded-lg text-xs">
                            <span class="truncate max-w-xs font-mono text-base-content/80">{{ $attachment->getClientOriginalName() }}</span>
                            <button type="button" wire:click="$set('attachment', null)" class="text-error font-bold text-xs hover:underline">Remove</button>
                        </div>
                    @endif
                    @error('attachment') <p class="text-error text-xs">{{ $message }}</p> @enderror

                    <form wire:submit.prevent="sendAdminReply" class="flex items-center gap-2">
                        <label class="btn btn-ghost btn-circle btn-sm text-base-content/60 hover:text-primary cursor-pointer" title="Attach file">
                            <input type="file" wire:model="attachment" class="hidden" />
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                        </label>

                        <input
                            wire:model="adminReply"
                            x-on:input="notifyTyping()"
                            x-on:focus="$wire.markRead()"
                            type="text"
                            placeholder="Type your reply as support..."
                            autocomplete="off"
                            class="input input-sm input-bordered flex-1 text-xs focus:outline-none focus:border-primary"
                        />

                        <button type="submit" class="btn btn-sm btn-primary text-white font-semibold gap-1" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="sendAdminReply">Send</span>
                            <span wire:loading wire:target="sendAdminReply" class="loading loading-spinner loading-xs"></span>
                        </button>
                    </form>
                </div>
                @endif
            @endif
        </div>
    </div>
</div>
