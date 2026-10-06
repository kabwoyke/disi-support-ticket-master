<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Events\AdminChat;
use App\Models\ChatMessage;
use App\Models\Chat;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    #[Url]
    public $ticket = '';

    public array $messages = [];
    public string $adminReply = '';
    public $attachment = null;

    public function mount(): void
    {
        if (!$this->ticket) {
            $this->ticket = request()->query('ticket', '');
        }

        $this->loadMessages();
    }

    protected function getActiveChat(): ?Chat
    {
        if (!$this->ticket) {
            return null;
        }

        return Chat::firstOrCreate(
            ['id' => $this->ticket],
            ['support_id' => auth('support')->id()]
        );
    }

    public function loadMessages(): void
    {
        if (!$this->ticket) {
            $this->messages = [];
            return;
        }

        $this->messages = ChatMessage::where('chat_id', $this->ticket)
            ->oldest()
            ->get()
            ->map(fn ($msg) => [
                'id' => 'msg_' . $msg->id,
                'sender' => $msg->sender_type === 'App\Models\SupportTeam'
                    ? 'admin'
                    : 'user',
                'text' => $msg->message,
                'attachment' => $msg->attachment_path
                    ? Storage::url($msg->attachment_path)
                    : null,
                'time' => $msg->created_at
                    ->timezone('Africa/Nairobi')
                    ->format('g:i A'),
            ])
            ->toArray();
    }

    public function getLastMessage(): ?array
    {
        if (empty($this->messages)) {
            return null;
        }

        return $this->messages[array_key_last($this->messages)];
    }

    public function getListeners(): array
    {
        if (!$this->ticket) {
            return [];
        }

        return [
            "echo-private:admin-chat.{$this->ticket},.UserChat" => 'recieveUserMessage',
        ];
    }

    public function recieveUserMessage(array $event): void
    {
        if (!empty($event['userText']) || !empty($event['attachment'])) {
            $this->messages[] = [
                'id' => uniqid('msg_'),
                'sender' => 'user',
                'text' => $event['userText'] ?? '',
                'attachment' => $event['attachment'] ?? null,
                'time' => now()
                    ->timezone('Africa/Nairobi')
                    ->format('g:i A'),
            ];
        }
    }

    public function sendAdminReply(): void
    {
        $this->validate([
            'adminReply' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        if (empty(trim($this->adminReply)) && !$this->attachment) {
            return;
        }

        $chat = $this->getActiveChat();

        if (!$chat) {
            return;
        }

        // Store attachment if selected
        $storedPath = null;

        if ($this->attachment) {
            $storedPath = $this->attachment->store(
                'chat-attachments',
                'public'
            );
        }

        // Save message to database
        $savedMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_type' => 'App\Models\SupportTeam',
            'sender_id' => auth('support')->id(),
            'message' => $this->adminReply ?? '',
            'attachment_path' => $storedPath,
        ]);

        $attachmentUrl = $storedPath
            ? Storage::url($storedPath)
            : null;

        // Broadcast to user
        AdminChat::dispatch(
            $this->adminReply,
            (int) $chat->id,
            $attachmentUrl
        );

        // Append to local state
        $this->messages[] = [
            'id' => 'msg_' . $savedMessage->id,
            'sender' => 'admin',
            'text' => $this->adminReply,
            'attachment' => $attachmentUrl,
            'time' => $savedMessage->created_at
                ->timezone('Africa/Nairobi')
                ->format('g:i A'),
        ];

        $this->reset([
            'adminReply',
            'attachment',
        ]);
    }

    public function render()
    {
        return view('pages::ict.features.⚡chat')
            ->layout('layouts::support');
    }
};
?>

@php
    /*
     * Safely get the latest message.
     *
     * When a user has not started the chat yet,
     * $messages is empty and this will be null.
     */
    $lastMessage = !empty($messages)
        ? $messages[array_key_last($messages)]
        : null;
@endphp

<div>
    <div class="h-[calc(100vh-5rem)] max-w-7xl mx-auto p-4 flex gap-4">

        <!-- ============================================================
             SIDEBAR: CONVERSATION / TICKET LIST
        ============================================================= -->
        <div class="w-full md:w-80 lg:w-96 bg-base-100 rounded-box border border-base-200 shadow-sm flex flex-col shrink-0">

            <!-- Sidebar Header & Search -->
            <div class="p-4 border-b border-base-200 space-y-3">

                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-base-content">
                        Support Chats
                    </h2>
                </div>

                <div class="relative">
                    <input
                        type="text"
                        placeholder="Search conversation or user..."
                        class="input input-sm input-bordered w-full pl-9 text-xs"
                    />

                    <svg
                        class="w-4 h-4 absolute left-3 top-2.5 text-base-content/40"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>
                </div>

            </div>


            <!-- ========================================================
                 CONVERSATIONS LIST
            ========================================================= -->
            <div class="flex-1 overflow-y-auto divide-y divide-base-200">

                <button
                    class="w-full p-3 text-left flex items-start gap-3 bg-base-200/60 border-l-4 border-primary transition-all"
                >

                    <!-- Avatar -->
                    <div class="avatar online shrink-0">
                        <div class="w-10 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">

                            <img
                                src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRoeZOBBidNgNcGNpV6v4cwHkHPfhp98-J75q0kd2z1Qv9Da0Cimld3WA&s=10"
                                alt="profile"
                            />

                        </div>
                    </div>


                    <!-- Conversation Information -->
                    <div class="flex-1 min-w-0">

                        <div class="flex items-center justify-between">

                            <h3 class="text-xs font-bold text-base-content truncate">
                                Mary Atieno
                            </h3>

                            <!-- SAFE: works even when there are no messages -->
                            <span class="text-[10px] text-base-content/50">
                                {{ $lastMessage['time'] ?? 'New' }}
                            </span>

                        </div>


                        <span class="font-mono text-[10px] font-semibold text-primary block">
                            #T-00104
                        </span>


                        <!-- =================================================
                             LAST MESSAGE PREVIEW
                        ================================================== -->
                        <p class="text-xs text-base-content/70 truncate mt-0.5">

                            @if ($lastMessage)

                                @if (!empty($lastMessage['text']))

                                    {{ $lastMessage['text'] }}

                                @elseif (!empty($lastMessage['attachment']))

                                    [Attachment]

                                @else

                                    Support Needed

                                @endif

                            @else

                                New support request

                            @endif

                        </p>

                    </div>

                </button>

            </div>

        </div>


        <!-- ============================================================
             MAIN CHAT WINDOW
        ============================================================= -->
        <div class="flex-1 bg-base-100 rounded-box border border-base-200 shadow-sm flex flex-col overflow-hidden">


            <!-- ========================================================
                 CHAT HEADER
            ========================================================= -->
            <div class="p-4 border-b border-base-200 flex items-center justify-between bg-base-100">

                <div class="flex items-center gap-3">

                    <div class="avatar online">

                        <div class="w-10 rounded-full">

                            <img
                                src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRoeZOBBidNgNcGNpV6v4cwHkHPfhp98-J75q0kd2z1Qv9Da0Cimld3WA&s=10"
                                alt="Avatar"
                            />

                        </div>

                    </div>


                    <div>

                        <div class="flex items-center gap-2">

                            <h2 class="text-sm font-bold text-base-content">
                                Mary Atieno
                            </h2>

                            <span class="font-mono text-xs text-primary font-semibold">
                                #T-00104
                            </span>

                        </div>

                        <p class="text-xs text-base-content/60">
                            Quality Control &bull; Desk 04
                        </p>

                    </div>

                </div>


                <div class="flex items-center gap-2">

                    <span class="badge badge-warning text-white font-semibold text-xs hidden sm:inline-flex">
                        HIGH PRIORITY
                    </span>

                    <span class="badge badge-outline text-xs">
                        IN PROGRESS
                    </span>

                    <button
                        class="btn btn-sm btn-ghost btn-circle"
                        title="View Ticket Details"
                    >

                        <svg
                            class="w-5 h-5 text-base-content/70"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>

                    </button>

                </div>

            </div>


            <!-- ========================================================
                 MESSAGES BODY
            ========================================================= -->
            <div
                x-data="{
                    scrollToBottom() {
                        $el.scrollTop = $el.scrollHeight
                    }
                }"
                x-init="scrollToBottom()"
                x-effect="scrollToBottom()"
                class="flex-1 overflow-y-auto p-4 space-y-4 bg-base-200/20"
            >

                <div class="divider text-[10px] text-base-content/40 font-semibold uppercase">
                    Today
                </div>


                @forelse ($messages as $msg)

                    @if ($msg['sender'] === 'user')

                        <!-- =================================================
                             USER MESSAGE
                        ================================================== -->
                        <div
                            wire:key="{{ $msg['id'] }}"
                            class="chat chat-start transition-all duration-300"
                        >

                            <div class="chat-image avatar">

                                <div class="w-8 rounded-full">

                                    <img
                                        src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRoeZOBBidNgNcGNpV6v4cwHkHPfhp98-J75q0kd2z1Qv9Da0Cimld3WA&s=10"
                                        alt="User"
                                    />

                                </div>

                            </div>


                            <div class="chat-header text-xs text-base-content/60 mb-1">

                                Mary Atieno

                                <time class="text-[10px] opacity-50 ml-1">
                                    {{ $msg['time'] }}
                                </time>

                            </div>


                            <div class="chat-bubble chat-bubble-neutral text-xs leading-relaxed space-y-2">

                                @if (!empty($msg['text']))

                                    <p>
                                        {{ $msg['text'] }}
                                    </p>

                                @endif


                                @if (!empty($msg['attachment']))

                                    <div class="pt-1">

                                        @if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $msg['attachment']))

                                            <a
                                                href="{{ $msg['attachment'] }}"
                                                target="_blank"
                                            >

                                                <img
                                                    src="{{ $msg['attachment'] }}"
                                                    class="max-w-xs rounded border border-base-300 hover:opacity-90 transition-opacity"
                                                />

                                            </a>

                                        @else

                                            <a
                                                href="{{ $msg['attachment'] }}"
                                                target="_blank"
                                                class="flex items-center gap-2 underline text-primary font-medium"
                                            >

                                                <svg
                                                    class="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                                                    />
                                                </svg>

                                                View Attachment

                                            </a>

                                        @endif

                                    </div>

                                @endif

                            </div>

                        </div>

                    @else

                        <!-- =================================================
                             ADMIN MESSAGE
                        ================================================== -->
                        <div
                            wire:key="{{ $msg['id'] }}"
                            class="chat chat-end transition-all duration-300"
                        >

                            <div class="chat-image avatar">

                                <div class="w-8 rounded-full">

                                    <img
                                        src="https://media.licdn.com/dms/image/v2/D4E03AQGWlFXHRfXxDw/profile-displayphoto-shrink_200_200/profile-displayphoto-shrink_200_200/0/1674839788606?e=2147483647&v=beta&t=fartG9Fh-rvivnWKxgT4xKADm9jgjdLXxyK3BUjHiOI"
                                        alt="profile"
                                    />

                                </div>

                            </div>


                            <div class="chat-header text-xs text-base-content/60 mb-1">

                                Support Admin (You)

                                <time class="text-[10px] opacity-50 ml-1">
                                    {{ $msg['time'] }}
                                </time>

                            </div>


                            <div class="chat-bubble chat-bubble-primary text-white text-xs leading-relaxed space-y-2">

                                @if (!empty($msg['text']))

                                    <p>
                                        {{ $msg['text'] }}
                                    </p>

                                @endif


                                @if (!empty($msg['attachment']))

                                    <div class="pt-1">

                                        @if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $msg['attachment']))

                                            <a
                                                href="{{ $msg['attachment'] }}"
                                                target="_blank"
                                            >

                                                <img
                                                    src="{{ $msg['attachment'] }}"
                                                    class="max-w-xs rounded border border-white/20 hover:opacity-90 transition-opacity"
                                                />

                                            </a>

                                        @else

                                            <a
                                                href="{{ $msg['attachment'] }}"
                                                target="_blank"
                                                class="flex items-center gap-2 underline text-white font-medium"
                                            >

                                                <svg
                                                    class="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415-6.585a6 6 0 108.486 8.486L20.5 13"
                                                    />
                                                </svg>

                                                View Attachment

                                            </a>

                                        @endif

                                    </div>

                                @endif

                            </div>


                            <div class="chat-footer text-[10px] opacity-50 mt-1">
                                Delivered
                            </div>

                        </div>

                    @endif

                @empty

                    <!-- =================================================
                         EMPTY CHAT STATE
                    ================================================== -->
                    <div class="text-center text-xs text-base-content/40 py-8">

                        <div class="flex justify-center mb-3">

                            <svg
                                class="w-10 h-10 opacity-30"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.5 9.5 0 01-4.086-.915L3 20l1.086-4.342A7.95 7.95 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                />
                            </svg>

                        </div>

                        <p>
                            No messages yet.
                        </p>

                        <p class="mt-1">
                            Send a reply to start the conversation.
                        </p>

                    </div>

                @endforelse

            </div>


            <!-- ========================================================
                 INPUT FOOTER
            ========================================================= -->
            <div class="p-3 bg-base-100 border-t border-base-200 space-y-2">

                <!-- Attachment Preview -->
                @if ($attachment)

                    <div class="flex items-center justify-between bg-base-200 px-3 py-1.5 rounded-lg text-xs">

                        <span class="truncate max-w-xs font-mono text-base-content/80">
                            {{ $attachment->getClientOriginalName() }}
                        </span>

                        <button
                            type="button"
                            wire:click="$set('attachment', null)"
                            class="text-error font-bold text-xs hover:underline"
                        >
                            Remove
                        </button>

                    </div>

                @endif


                <!-- Message Form -->
                <form
                    wire:submit.prevent="sendAdminReply"
                    class="flex items-center gap-2"
                >

                    <!-- File Attachment -->
                    <label
                        class="btn btn-ghost btn-circle btn-sm text-base-content/60 hover:text-primary cursor-pointer"
                        title="Attach File"
                    >

                        <input
                            type="file"
                            wire:model="attachment"
                            class="hidden"
                        />

                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                            />
                        </svg>

                    </label>


                    <!-- Message Input -->
                    <input
                        wire:model="adminReply"
                        type="text"
                        placeholder="Type your reply as support admin..."
                        class="input input-sm input-bordered flex-1 text-xs focus:outline-none focus:border-primary"
                    />


                    <!-- Send Button -->
                    <button
                        type="submit"
                        class="btn btn-sm btn-primary text-white font-semibold gap-1"
                        wire:loading.attr="disabled"
                    >

                        <span
                            wire:loading.remove
                            wire:target="sendAdminReply"
                        >
                            Send
                        </span>


                        <span
                            wire:loading
                            wire:target="sendAdminReply"
                            class="loading loading-spinner loading-xs"
                        ></span>


                        <svg
                            wire:loading.remove
                            wire:target="sendAdminReply"
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 19l9-7-9-7-9 7 9 7zm0 0v-8"
                            />
                        </svg>

                    </button>

                </form>

            </div>

        </div>

    </div>
</div>

