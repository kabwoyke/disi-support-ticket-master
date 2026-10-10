<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Events\UserChat;
use App\Events\MessagesRead;
use App\Models\ChatMessage;
use App\Models\Chat;
use App\Models\Ticket;
use App\Models\SupportTeam;
use App\Models\TicketAssignment;
use App\Notifications\NewChatMessage;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    private const SUPPORT = 'App\Models\SupportTeam';

    public string $userChat = '';
    public $attachment = null;

    #[Url]
    public $ticket = '';

    public array $messages = [];
    public int $limit = 50;
    public bool $hasMore = false;

    public function mount(): void
    {
        if (!$this->ticket) {
            $this->ticket = request()->query('ticket', '');
        }

        // Only the ticket owner may open its chat.
        if ($this->ticket) {
            $ticket = Ticket::findOrFail($this->ticket);
            abort_unless((int) $ticket->userId === (int) auth()->id(), 403);
        }

        $this->loadMessages();
        $this->markRead();
    }

    protected function format(ChatMessage $msg): array
    {
        $fromAdmin = $msg->sender_type === self::SUPPORT;
        $sender = $fromAdmin ? $msg->sender : auth()->user();

        return [
            'id' => 'msg_' . $msg->id,
            'db_id' => $msg->id,
            'sender' => $fromAdmin ? 'admin' : 'user',
            'name' => $fromAdmin ? ($sender?->display_name ?? 'ICT Support') : 'You',
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
            return;
        }

        $total = ChatMessage::where('chat_id', $this->ticket)->count();
        $this->hasMore = $total > $this->limit;

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
            "echo-private:admin-chat.{$this->ticket},.AdminChat" => 'receiveAdminMessage',
            "echo-private:admin-chat.{$this->ticket},.MessagesRead" => 'onMessagesRead',
        ];
    }

    public function receiveAdminMessage(array $event): void
    {
        $dbId = $event['messageId'] ?? null;

        // Ignore duplicates (e.g. event replayed after a reconnect).
        if ($dbId && collect($this->messages)->contains('db_id', $dbId)) {
            return;
        }

        if ($dbId && ($msg = ChatMessage::with('sender')->find($dbId))) {
            $this->messages[] = $this->format($msg);
            $this->markRead();
        }
    }

    /** The support agent opened the chat: flag everything we sent as seen. */
    public function onMessagesRead(array $event): void
    {
        if (($event['readerType'] ?? null) !== 'admin') {
            return;
        }

        $this->messages = array_map(
            fn ($m) => $m['sender'] === 'user' ? [...$m, 'read' => true] : $m,
            $this->messages
        );
    }

    /** Mark support messages as read and let the agent know. */
    public function markRead(): void
    {
        if (!$this->ticket) {
            return;
        }

        $updated = ChatMessage::where('chat_id', $this->ticket)
            ->where('sender_type', self::SUPPORT)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($updated) {
            broadcast(new MessagesRead((int) $this->ticket, 'user'))->toOthers();
        }

        // Opening the chat clears its notifications and refreshes the nav badge.
        auth()->user()->unreadNotifications()
            ->where('type', NewChatMessage::class)
            ->where('data->chat_id', (int) $this->ticket)
            ->update(['read_at' => now()]);

        $this->dispatch('chat-unread', count: auth()->user()->unreadNotifications()->where('type', NewChatMessage::class)->count());
    }

    public function send(): void
    {
        $this->validate([
            'userChat' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:10240',
        ]);

        if (empty(trim($this->userChat)) && !$this->attachment) {
            return;
        }

        if (!$this->ticket) {
            return;
        }

        $chat = Chat::firstOrCreate([
            'id' => $this->ticket,
        ], [
            'user_id' => auth()->id(),
        ]);

        $storedPath = $this->attachment?->store('chat-attachments', 'public');

        $saved = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_type' => 'App\Models\User',
            'sender_id' => auth()->id(),
            'message' => trim($this->userChat),
            'attachment_path' => $storedPath,
        ]);

        $attachmentUrl = $storedPath ? Storage::disk('public')->url($storedPath) : null;

        broadcast(new UserChat($saved->message, (int) $chat->id, $attachmentUrl, $saved->id));

        $this->messages[] = $this->format($saved);

        // Tell the assigned agent(s) there's a new message.
        $teamIds = TicketAssignment::where('ticketId', $chat->id)->pluck('teamId');
        $agents = $chat->support_id
            ? SupportTeam::whereKey($chat->support_id)->get()
            : ($teamIds->isNotEmpty() ? SupportTeam::whereIn('id', $teamIds)->get() : SupportTeam::all());
        Notification::send($agents, new NewChatMessage($saved, auth()->user()->display_name, true));

        $this->reset(['userChat', 'attachment']);
    }

    public function render()
    {
        return view('pages::users.⚡chat', ['ticketDetail' => Ticket::find($this->ticket)])
            ->layout('layouts::user');
    }
};
?>
<div>
    <div
        x-data="chatRoom({ chatId: @js($ticket ?: null), me: 'user' })"
        class="h-[calc(100vh-5rem)] max-w-4xl mx-auto p-4 flex flex-col"
    >
        <div class="flex-1 bg-base-100 rounded-box border border-base-200 shadow-sm flex flex-col overflow-hidden">

            <!-- Header -->
            <div class="p-4 border-b border-base-200 bg-base-100 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <a wire:navigate href="{{ route('get-tickets') }}" class="btn btn-sm btn-ghost btn-circle" title="Back to Tickets">
                        <svg class="w-5 h-5 text-base-content/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </a>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-bold text-primary">#T-{{ str_pad($ticketDetail?->id, 5, '0', STR_PAD_LEFT) }}</span>
                            <h1 class="text-sm font-bold text-base-content">{{ $ticketDetail?->subject ?? $ticketDetail?->description }}</h1>
                        </div>
                        <p class="text-[11px] flex items-center gap-1" :class="online ? 'text-success' : 'text-base-content/50'">
                            <span class="w-1.5 h-1.5 rounded-full" :class="online ? 'bg-success' : 'bg-base-content/30'"></span>
                            <span x-text="typing ? 'Support is typing...' : (online ? 'Support is online' : 'Support is offline')"></span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="badge badge-warning text-white font-semibold text-xs">{{ strtoupper($ticketDetail?->priority) }}</span>
                    <span class="badge badge-info badge-outline text-xs">{{ $ticketDetail?->status }}</span>
                </div>
            </div>

            <!-- Conversation -->
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
                    <x-chat.bubble :msg="$msg" :mine="$msg['sender'] === 'user'" />
                @empty
                    <div class="text-center text-xs text-base-content/40 py-8">
                        Type your message below to send an update to the support team.
                    </div>
                @endforelse

                <!-- Typing indicator -->
                <div x-show="typing" x-cloak class="chat chat-start">
                    <div class="chat-bubble chat-bubble-neutral"><span class="loading loading-dots loading-xs"></span></div>
                </div>
            </div>

            <!-- Composer -->
            <div class="p-3 bg-base-100 border-t border-base-200 space-y-2">
                @if ($attachment)
                    <div class="flex items-center justify-between bg-base-200 px-3 py-1.5 rounded-lg text-xs">
                        <span class="truncate max-w-xs font-mono text-base-content/80">{{ $attachment->getClientOriginalName() }}</span>
                        <button type="button" wire:click="$set('attachment', null)" class="text-error font-bold text-xs hover:underline">Remove</button>
                    </div>
                @endif
                @error('attachment') <p class="text-error text-xs">{{ $message }}</p> @enderror

                <form class="flex items-center gap-2" wire:submit="send">
                    <label class="btn btn-ghost btn-circle btn-sm text-base-content/60 hover:text-primary cursor-pointer" title="Attach file">
                        <input type="file" wire:model="attachment" class="hidden" />
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                    </label>

                    <input
                        type="text"
                        wire:model="userChat"
                        x-on:input="notifyTyping()"
                        x-on:focus="$wire.markRead()"
                        placeholder="Type your reply here..."
                        autocomplete="off"
                        class="input input-sm input-bordered flex-1 text-xs focus:outline-none focus:border-primary"
                    />

                    <button type="submit" class="btn btn-sm btn-primary text-white font-semibold gap-1" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="send">Send</span>
                        <span wire:loading wire:target="send" class="loading loading-spinner loading-xs"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
