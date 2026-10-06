<?php

use Livewire\Component;
use Illuminate\Support\Str;

new class extends Component
{
    public array $notifications = [];
    public ?array $selectedNotification = null;
    public bool $showModal = false;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = auth()->guard('support')->user();

        if ($user) {
            $this->notifications = $user->notifications()
                ->latest()
                ->get()
                ->map(function ($notification) {
                    $data = $notification->data;

                    return [
                        'id' => $notification->id,
                        'message' => $data['message'] ?? null,
                        'ticket_id' => $data['ticket_id'] ?? null,
                        'subject' => $data['subject'] ?? null,
                        'description' => $data['description'] ?? null,
                        'priority' => $data['priority'] ?? 'LOW',
                        'status' => $data['status'] ?? 'OPEN',
                        'action_type' => $data['action_type'] ?? 'created',
                        'category' => $data['category'] ?? null,
                        'department' => $data['department'] ?? null,
                        'desk' => $data['desk'] ?? null,
                        'equipment' => $data['equipment'] ?? null,
                        'user_name' => $data['user_name'] ?? 'N/A',
                        'attachments' => is_string($data['attachments'] ?? null)
                            ? json_decode($data['attachments'], true)
                            : ($data['attachments'] ?? []),
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at->diffForHumans(),
                        'full_date' => $notification->created_at->format('M d, Y h:i A'),
                    ];
                })
                ->toArray();
        }
    }

    public function getListeners()
    {
        $supportTeamId = auth()->guard('support')->id();

        return [
            "echo-private:App.Models.SupportTeam.{$supportTeamId},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'handleNewNotification',
            "echo-private:App.Models.SupportTeam.{$supportTeamId},NotificationSent" => 'handleNewNotification',
        ];
    }

    public function handleNewNotification($event)
    {
        $data = $event['data'] ?? $event;

        array_unshift($this->notifications, [
            'id' => $event['id'] ?? (string) Str::uuid(),
            'message' => $data['message'] ?? null,
            'ticket_id' => $data['ticket_id'] ?? null,
            'subject' => $data['subject'] ?? null,
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'] ?? 'LOW',
            'status' => $data['status'] ?? 'OPEN',
            'action_type' => $data['action_type'] ?? 'created',
            'category' => $data['category'] ?? null,
            'department' => $data['department'] ?? null,
            'desk' => $data['desk'] ?? null,
            'equipment' => $data['equipment'] ?? null,
            'user_name' => $data['user_name'] ?? 'N/A',
            'attachments' => is_string($data['attachments'] ?? null)
                ? json_decode($data['attachments'], true)
                : ($data['attachments'] ?? []),
            'read_at' => null,
            'created_at' => 'Just now',
            'full_date' => now()->format('M d, Y h:i A'),
        ]);
    }

    public function viewNotificationDetails(string $id)
    {
        $notificationKey = array_search($id, array_column($this->notifications, 'id'));

        if ($notificationKey !== false) {
            $this->selectedNotification = $this->notifications[$notificationKey];
            $this->showModal = true;

            // Auto-mark as read if unread
            if (!$this->selectedNotification['read_at']) {
                $this->markAsRead($id);
                $this->selectedNotification['read_at'] = now();
            }
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedNotification = null;
    }

    public function markAsRead(string $id)
    {
        $user = auth()->guard('support')->user();

        if ($user) {
            $notification = $user->notifications()->where('id', $id)->first();
            if ($notification) {
                $notification->markAsRead();
            }
        }

        foreach ($this->notifications as &$item) {
            if ($item['id'] === $id) {
                $item['read_at'] = now();
                break;
            }
        }
    }

    public function markAllAsRead()
    {
        $user = auth()->guard('support')->user();

        if ($user) {
            $user->unreadNotifications->markAsRead();
        }

        foreach ($this->notifications as &$item) {
            $item['read_at'] = now();
        }
    }

    public function render()
    {
        return view("pages::ict.features.⚡view-notification")->layout("layouts::support");
    }
};
?>
<div class="max-w-4xl mx-auto p-6 space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-base-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-base-content">Notifications</h1>
            <p class="text-xs text-base-content/60 mt-1">
                Real-time updates and ticket dispatches. Click on any item to view details.
            </p>
        </div>

        @if(collect($notifications)->whereNull('read_at')->count() > 0)
            <button
                wire:click="markAllAsRead"
                class="btn btn-sm btn-primary text-white font-semibold shadow-sm"
            >
                Mark all as read
            </button>
        @endif
    </div>

    <!-- Notification List -->
    <div class="space-y-3">
        @forelse($notifications as $notification)
            <div
                wire:key="notification-{{ $notification['id'] }}"
                wire:click="viewNotificationDetails('{{ $notification['id'] }}')"
                class="p-4 rounded-box border transition-all cursor-pointer flex items-center justify-between gap-4 hover:border-primary/50 {{ $notification['read_at'] ? 'bg-base-100 border-base-300 opacity-80' : 'bg-base-100 border-primary/40 shadow-sm ring-1 ring-primary/20' }}"
            >
                <div class="flex items-center gap-3 min-w-0">
                    <!-- Status Icon -->
                    <div>
                        @if($notification['read_at'])
                            <svg class="w-5 h-5 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        @else
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-primary"></span>
                            </span>
                        @endif
                    </div>

                    <!-- Notification Snippet -->
                    <div class="space-y-0.5 truncate">
                        <div class="flex items-center gap-2">
                            @if(!empty($notification['ticket_id']))
                                <span class="font-mono text-xs font-bold text-primary">
                                    #{{ str_pad($notification['ticket_id'], 5, '0', STR_PAD_LEFT) }}
                                </span>
                            @endif
                            <h3 class="text-sm font-semibold text-base-content truncate">
                                {{ $notification['subject'] ?? $notification['message'] ?? 'Ticket Notification' }}
                            </h3>
                        </div>
                        <p class="text-xs text-base-content/60 truncate">
                            From: {{ $notification['user_name'] }} &bull; {{ $notification['department'] ?? 'General' }}
                        </p>
                    </div>
                </div>

                <!-- Badges & Trigger -->
                <div class="flex items-center gap-3 shrink-0">
                    <!-- Priority Badge -->
                    @switch($notification['priority'])
                        @case('HIGH')
                            <span class="badge badge-error text-white badge-sm font-semibold hidden sm:inline-flex">HIGH</span>
                            @break
                        @case('MODERATE')
                            <span class="badge badge-warning text-white badge-sm font-semibold hidden sm:inline-flex">MODERATE</span>
                            @break
                        @default
                            <span class="badge badge-ghost badge-sm hidden sm:inline-flex">LOW</span>
                    @endswitch

                    <span class="text-xs text-base-content/50 whitespace-nowrap">
                        {{ $notification['created_at'] }}
                    </span>

                    <button class="btn btn-xs btn-primary text-white font-medium">
                        View
                    </button>
                </div>
            </div>
        @empty
            <div class="text-center py-12 border border-dashed border-base-300 rounded-box">
                <svg class="w-12 h-12 mx-auto text-base-content/30 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9" />
                </svg>
                <p class="text-sm font-medium text-base-content/60">No notifications found</p>
            </div>
        @endforelse
    </div>

    <!-- Notification Details Modal -->
    @if($showModal && $selectedNotification)
        <div class="modal modal-open" role="dialog">
            <div class="modal-box max-w-2xl bg-base-100 border border-base-300 text-base-content space-y-4">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-3 border-b border-base-200">
                    <div class="flex items-center gap-2">
                        @if(!empty($selectedNotification['ticket_id']))
                            <span class="font-mono text-sm font-bold text-primary">
                                #{{ str_pad($selectedNotification['ticket_id'], 5, '0', STR_PAD_LEFT) }}
                            </span>
                        @endif
                        <h3 class="font-bold text-lg text-base-content">
                            {{ $selectedNotification['subject'] ?? $selectedNotification['message'] ?? 'Notification Details' }}
                        </h3>
                    </div>
                    <button wire:click="closeModal" class="btn btn-sm btn-circle btn-ghost">✕</button>
                </div>

                <!-- Status & Priority Badges -->
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="font-semibold text-base-content/60">Status:</span>
                    <span class="badge badge-info badge-outline font-semibold">{{ $selectedNotification['status'] }}</span>

                    <span class="font-semibold text-base-content/60 ml-2">Priority:</span>
                    @switch($selectedNotification['priority'])
                        @case('HIGH')
                            <span class="badge badge-error text-white font-semibold">HIGH</span>
                            @break
                        @case('MODERATE')
                            <span class="badge badge-warning text-white font-semibold">MODERATE</span>
                            @break
                        @default
                            <span class="badge badge-ghost font-medium">LOW</span>
                    @endswitch

                    <span class="font-semibold text-base-content/60 ml-2">Action:</span>
                    <span class="badge badge-neutral text-white uppercase">{{ $selectedNotification['action_type'] }}</span>
                </div>

                <!-- Information Grid -->
                <div class="grid grid-cols-2 gap-3 bg-base-200/50 p-3 rounded-box text-xs">
                    <div>
                        <span class="text-base-content/60 block">Submitted By</span>
                        <span class="font-semibold text-base-content">{{ $selectedNotification['user_name'] }}</span>
                    </div>
                    <div>
                        <span class="text-base-content/60 block">Category</span>
                        <span class="font-semibold text-base-content">{{ $selectedNotification['category'] ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-base-content/60 block">Department / Desk</span>
                        <span class="font-semibold text-base-content">
                            {{ $selectedNotification['department'] ?? 'N/A' }}
                            @if(!empty($selectedNotification['desk']))
                                <span class="text-base-content/60 font-normal">({{ $selectedNotification['desk'] }})</span>
                            @endif
                        </span>
                    </div>
                    <div>
                        <span class="text-base-content/60 block">Equipment</span>
                        <span class="font-semibold text-base-content">{{ $selectedNotification['equipment'] ?? 'None Specified' }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-base-content/60 block">Received At</span>
                        <span class="font-semibold text-base-content">{{ $selectedNotification['full_date'] }}</span>
                    </div>
                </div>

                <!-- Description Block -->
                @if(!empty($selectedNotification['description']))
                    <div class="space-y-1">
                        <h4 class="font-semibold text-xs text-base-content/70 uppercase">Ticket Description</h4>
                        <div class="p-3 bg-base-200/30 rounded-box border border-base-200 text-xs leading-relaxed text-base-content">
                            {{ $selectedNotification['description'] }}
                        </div>
                    </div>
                @endif

                <!-- Attachments List -->
                @if(!empty($selectedNotification['attachments']) && is_array($selectedNotification['attachments']))
                    <div class="space-y-1.5 pt-1 border-t border-base-200">
                        <h4 class="font-semibold text-xs text-base-content/70 uppercase flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            Attachments ({{ count($selectedNotification['attachments']) }})
                        </h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($selectedNotification['attachments'] as $filePath)
                                <a
                                    href="{{ Storage::url($filePath) }}"
                                    target="_blank"
                                    class="btn btn-xs btn-outline btn-primary text-xs gap-1 font-medium"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ basename($filePath) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Modal Actions -->
                <div class="modal-action border-t border-base-200 pt-3">
                    <button wire:click="closeModal" class="btn btn-primary text-white btn-sm px-5">
                        Close
                    </button>
                </div>
            </div>
            <div wire:click="closeModal" class="modal-backdrop bg-neutral/60"></div>
        </div>
    @endif
</div>
