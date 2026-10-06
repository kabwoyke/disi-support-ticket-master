<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Events\UserChat;
use App\Models\ChatMessage;
use App\Models\Chat;
use App\Models\Ticket;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public string $userChat = '';
    public $attachment = null;

    #[Url]
    public $ticket = '';

    public array $messages = [];

    public function mount(): void
    {
        if (!$this->ticket) {
            $this->ticket = request()->query('ticket', '');
        }

        $this->loadMessages();
    }

    public function loadMessages(): void
    {
        if (!$this->ticket) {
            return;
        }

        $this->messages = ChatMessage::where('chat_id', $this->ticket)
            ->oldest()
            ->get()
            ->map(fn ($msg) => [
                'id' => 'msg_' . $msg->id,
                'sender' => $msg->sender_type === 'App\Models\SupportTeam' ? 'admin' : 'user',
                'text' => $msg->message,
                // Specify the 'public' disk explicitly when generating public URLs
                'attachment' => $msg->attachment_path ? Storage::disk('public')->url($msg->attachment_path) : null,
                'time' => $msg->created_at->timezone('Africa/Nairobi')->format('g:i A'),
            ])
            ->toArray();
    }

    public function getListeners(): array
    {
        if (!$this->ticket) {
            return [];
        }

        return [
            "echo-private:admin-chat.{$this->ticket},.AdminChat" => 'receiveAdminMessage',
        ];
    }

    public function receiveAdminMessage(array $event): void
    {
        $attachment = $event['attachment'] ?? null;

        // Convert path to public URL if it's a raw relative path
        if ($attachment && !str_starts_with($attachment, 'http')) {
            $attachment = Storage::disk('public')->url($attachment);
        }

        if (!empty($event['adminText']) || !empty($attachment)) {
            $this->messages[] = [
                'id' => uniqid('msg_'),
                'sender' => 'admin',
                'text' => $event['adminText'] ?? '',
                'attachment' => $attachment,
                'time' => now()->timezone('Africa/Nairobi')->format('g:i A'),
            ];
        }
    }

    public function send(): void
    {
        $this->validate([
            'userChat' => 'nullable|string',
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

        $storedPath = null;
        if ($this->attachment) {
            // Store directly under public disk
            $storedPath = $this->attachment->store('chat-attachments', 'public');
        }

        $savedMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_type' => 'App\Models\User',
            'sender_id' => auth()->id(),
            'message' => $this->userChat ?? '',
            'attachment_path' => $storedPath,
        ]);

        $attachmentUrl = $storedPath ? Storage::disk('public')->url($storedPath) : null;

        // Broadcast relative path or URL
        broadcast(new UserChat($this->userChat, (int) $chat->id, $attachmentUrl));

        $this->messages[] = [
            'id' => 'msg_' . $savedMessage->id,
            'sender' => 'user',
            'text' => $this->userChat,
            'attachment' => $attachmentUrl,
            'time' => $savedMessage->created_at->timezone('Africa/Nairobi')->format('g:i A'),
        ];

        $this->reset(['userChat', 'attachment']);
    }

    public function render()
    {
        $ticketDetail = Ticket::find($this->ticket);

        return view('pages::users.⚡chat', ['ticketDetail' => $ticketDetail])
            ->layout('layouts::user');
    }
};
?>
<div>
    <div class="h-[calc(100vh-5rem)] max-w-4xl mx-auto p-4 flex flex-col">
        <!-- Main Chat Card -->
        <div class="flex-1 bg-base-100 rounded-box border border-base-200 shadow-sm flex flex-col overflow-hidden">

            <!-- Header: Ticket Overview -->
            <div class="p-4 border-b border-base-200 bg-base-100 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <a href="#" class="btn btn-sm btn-ghost btn-circle" title="Back to Tickets">
                        <svg class="w-5 h-5 text-base-content/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-bold text-primary">#T-00{{ $ticketDetail->id }}</span>
                            <h1 class="text-sm font-bold text-base-content">{{ $ticketDetail->description }}</h1>
                        </div>
                    </div>
                </div>

                <!-- Status & Priority Badges -->
                <div class="flex items-center gap-2">
                    <span class="badge badge-warning text-white font-semibold text-xs">{{ strtoupper($ticketDetail->priority) }}</span>
                    <span class="badge badge-info badge-outline text-xs">{{ $ticketDetail->status }}</span>
                </div>
            </div>

            <!-- Chat Conversation Body -->
            <div
                x-data="{ scrollToBottom() { $el.scrollTop = $el.scrollHeight } }"
                x-init="scrollToBottom()"
                x-effect="scrollToBottom()"
                class="flex-1 overflow-y-auto p-4 space-y-4 bg-base-200/20"
            >
                <!-- Timestamp Divider -->
                <div class="divider text-[10px] text-base-content/40 font-semibold uppercase">Today</div>

                @forelse ($messages as $msg)
                    @if ($msg['sender'] === 'user')
                        <!-- User Outgoing Bubble -->
                        <div wire:key="{{ $msg['id'] }}" class="chat chat-end transition-all duration-300">
                            <div class="chat-header text-xs text-base-content/60 mb-1">
                                You
                                <time class="text-[10px] opacity-50 ml-1">{{ $msg['time'] }}</time>
                            </div>
                            <div class="chat-bubble chat-bubble-primary text-white text-xs leading-relaxed space-y-2">
                                @if(!empty($msg['text']))
                                    <p>{{ $msg['text'] }}</p>
                                @endif

                                @if(!empty($msg['attachment']))
                                    <div class="pt-1">
                                        @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $msg['attachment']))
                                            <a href="{{ $msg['attachment'] }}" target="_blank">
                                                <img src="{{ $msg['attachment'] }}" class="max-w-xs rounded border border-white/20 hover:opacity-90 transition-opacity" />
                                            </a>
                                        @else
                                            <a href="{{ $msg['attachment'] }}" target="_blank" class="flex items-center gap-2 underline text-white font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                View Attachment
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="chat-footer text-[10px] opacity-50 mt-1">Sent</div>
                        </div>
                    @else
                        <!-- Admin Incoming Bubble -->
                        <div wire:key="{{ $msg['id'] }}" class="chat chat-start transition-all duration-300">
                            <div class="chat-image avatar">
                                <div class="w-8 rounded-full">
                                    <img src="https://media.licdn.com/dms/image/v2/D4E03AQGWlFXHRfXxDw/profile-displayphoto-shrink_200_200/profile-displayphoto-shrink_200_200/0/1674839788606?e=2147483647&v=beta&t=fartG9Fh-rvivnWKxgT4xKADm9jgjdLXxyK3BUjHiOI" alt="Support Admin" />
                                </div>
                            </div>
                            <div class="chat-header text-xs text-base-content/60 mb-1">
                                ICT Support Admin
                                <time class="text-[10px] opacity-50 ml-1">{{ $msg['time'] }}</time>
                            </div>
                            <div class="chat-bubble chat-bubble-neutral text-xs leading-relaxed space-y-2">
                                @if(!empty($msg['text']))
                                    <p>{{ $msg['text'] }}</p>
                                @endif

                                @if(!empty($msg['attachment']))
                                    <div class="pt-1">
                                        @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $msg['attachment']))
                                            <a href="{{ $msg['attachment'] }}" target="_blank">
                                                <img src="{{ $msg['attachment'] }}" class="max-w-xs rounded border border-base-300 hover:opacity-90 transition-opacity" />
                                            </a>
                                        @else
                                            <a href="{{ $msg['attachment'] }}" target="_blank" class="flex items-center gap-2 underline text-primary font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                View Attachment
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="text-center text-xs text-base-content/40 py-8">
                        Type your message below to send an update to the support team.
                    </div>
                @endforelse
            </div>

            <!-- Chat Input Area -->
            <div class="p-3 bg-base-100 border-t border-base-200 space-y-2">
                <!-- Preview active upload before submission -->
                @if ($attachment)
                    <div class="flex items-center justify-between bg-base-200 px-3 py-1.5 rounded-lg text-xs">
                        <span class="truncate max-w-xs font-mono text-base-content/80">{{ $attachment->getClientOriginalName() }}</span>
                        <button type="button" wire:click="$set('attachment', null)" class="text-error font-bold text-xs hover:underline">Remove</button>
                    </div>
                @endif

                <form class="flex items-center gap-2" wire:submit="send">
                    <!-- File Trigger Button -->
                    <label class="btn btn-ghost btn-circle btn-sm text-base-content/60 hover:text-primary cursor-pointer" title="Attach file">
                        <input type="file" wire:model="attachment" class="hidden" />
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                    </label>

                    <!-- Text Input Field -->
                    <input
                        type="text"
                        wire:model="userChat"
                        placeholder="Type your reply here..."
                        class="input input-sm input-bordered flex-1 text-xs focus:outline-none focus:border-primary"
                    />

                    <!-- Submit Button with Loading State -->
                    <button type="submit" class="btn btn-sm btn-primary text-white font-semibold gap-1" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="send">Send</span>
                        <span wire:loading wire:target="send" class="loading loading-spinner loading-xs"></span>
                        <svg wire:loading.remove wire:target="send" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9-7-9-7-9 7 9 7zm0 0v-8" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
