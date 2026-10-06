<?php

use Livewire\Component;
use App\Models\Ticket;
new class extends Component
{
    public ?Ticket $selectedTicket = null;
    public bool $showModal = false;

    public function viewTicket($ticketId)
    {
        $this->selectedTicket = Ticket::with(['category', 'department', 'equipment', 'desk', 'ticket_assignment.support_team'])
            ->where('userId', auth()->id())
            ->find($ticketId);

        if ($this->selectedTicket) {
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedTicket = null;
    }

    public function render()
    {
        $userTickets = Ticket::with(['category', 'department', 'equipment', 'desk' , 'ticket_assignment.support_team'])
            ->where('userId', auth()->id())
            ->latest()
            ->get();

        return view('pages.⚡view-tickets', [
            'userTickets' => $userTickets
        ])->layout('layouts::user');
    }
};
?>

<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-base-content">My Support Tickets</h2>
            <p class="text-xs text-base-content/70 mt-1">Track and manage your submitted IT support requests.</p>
        </div>
        <a wire:navigate href="{{ route('create-ticket') }}" class="btn btn-primary text-white text-sm font-semibold shadow">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Raise New Ticket
        </a>
    </div>

    <!-- Table Container -->
    <div class="bg-base-100 rounded-box shadow-md border border-base-300 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full text-base-content">
                <!-- Table Head -->
                <thead class="bg-base-200/50 text-base-content/70 text-xs uppercase font-semibold">
                    <tr>
                        <th>Ticket ID</th>
                        <th>Subject</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Location / Equip.</th>
                        <th>Date Submitted</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody>
                    @forelse ($userTickets as $ticket)
                        <tr class="hover:bg-base-200/40 transition-colors border-b border-base-200">
                            <!-- ID -->
                            <td class="font-mono text-xs font-bold text-primary">
                                #{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Subject & Description snippet -->
                            <td class="max-w-xs">
                                <p class="font-semibold text-sm truncate text-base-content">{{ $ticket->subject }}</p>
                                <p class="text-xs text-base-content/60 truncate">{{ $ticket->description }}</p>
                            </td>

                            <!-- Category -->
                            <td class="text-xs font-medium">
                                {{ $ticket->category->category_name ?? 'N/A' }}
                            </td>

                            <!-- Priority Badge -->
                            <td>
                                @switch($ticket->priority)
                                    @case('HIGH')
                                        <span class="badge badge-error text-white badge-sm font-semibold">HIGH</span>
                                        @break
                                    @case('MODERATE')
                                        <span class="badge badge-warning text-white badge-sm font-semibold">MODERATE</span>
                                        @break
                                    @default
                                        <span class="badge badge-ghost badge-sm text-base-content/70">LOW</span>
                                @endswitch
                            </td>

                            <!-- Status Badge -->
                            <td>
                                @switch($ticket->status)
                                    @case('OPEN')
                                        <span class="badge badge-info badge-outline badge-sm font-semibold">OPEN</span>
                                        @break
                                    @case('IN-PROGRESS')
                                        <span class="badge badge-warning badge-outline badge-sm font-semibold">IN-PROGRESS</span>
                                        @break
                                    @case('RESOLVED')
                                        <span class="badge badge-success text-white badge-sm font-semibold">RESOLVED</span>
                                        @break
                                    @case('CLOSED')
                                        <span class="badge badge-neutral text-white badge-sm">CLOSED</span>
                                        @break
                                    @default
                                        <span class="badge badge-ghost badge-sm">{{ $ticket->status }}</span>
                                @endswitch
                            </td>

                            <!-- Location & Equipment -->
                            <td class="text-xs space-y-0.5">
                                <div class="text-base-content/90 font-medium">
                                    {{ $ticket->department->department_name ?? 'No Dept' }}
                                    @if($ticket->desk)
                                        <span class="text-base-content/50">({{ $ticket->desk->desk_name }})</span>
                                    @endif
                                </div>
                                @if($ticket->equipment)
                                    <div class="text-[11px] text-base-content/60 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                                        {{ $ticket->equipment->name }}
                                    </div>
                                @endif
                            </td>

                            <!-- Date Created -->
                            <td class="text-xs text-base-content/70 whitespace-nowrap">
                                <div>{{ $ticket->created_at->format('M d, Y') }}</div>
                                <div class="text-[10px] text-base-content/50">{{ $ticket->created_at->diffForHumans() }}</div>
                            </td>

                            <!-- Action Buttons -->
                            <td class="text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        wire:click="viewTicket({{ $ticket->id }})"
                                        class="btn btn-outline btn-primary btn-xs font-medium"
                                    >
                                        View
                                    </button>

                                    <a
                                        wire:navigate
                                        href="{{ route('user-chat', ['ticket' => $ticket->id]) }}"
                                        class="btn btn-secondary btn-xs text-white font-medium gap-1"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                        Chat
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <!-- Empty State -->
                        <tr>
                            <td colspan="8" class="text-center py-12">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-base-200 flex items-center justify-center text-base-content/40">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-base-content/70">No tickets submitted yet</p>
                                    <p class="text-xs text-base-content/50">When you submit an IT support request, it will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ticket Detail Modal -->
    @if($showModal && $selectedTicket)
        <div class="modal modal-open" role="dialog">
            <div class="modal-box max-w-2xl bg-base-100 border border-base-300 text-base-content">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-3 border-b border-base-200">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-sm font-bold text-primary">
                            #{{ str_pad($selectedTicket->id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                        <h3 class="font-bold text-lg text-base-content">{{ $selectedTicket->subject }}</h3>
                    </div>
                    <button wire:click="closeModal" class="btn btn-sm btn-circle btn-ghost">✕</button>
                </div>

                <!-- Modal Body -->
                <div class="py-4 space-y-4 text-sm">
                    <!-- Status & Metadata Badges -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase text-base-content/60">Status:</span>
                        <span class="badge badge-info badge-outline font-semibold">{{ $selectedTicket->status }}</span>

                        <span class="text-xs font-semibold uppercase text-base-content/60 ml-2">Priority:</span>
                        <span class="badge {{ $selectedTicket->priority === 'HIGH' ? 'badge-error text-white' : ($selectedTicket->priority === 'MODERATE' ? 'badge-warning text-white' : 'badge-ghost') }}">
                            {{ $selectedTicket->priority }}
                        </span>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-2 gap-3 bg-base-200/50 p-3 rounded-box text-xs">
                        <div>
                            <span class="text-base-content/60 block">Category</span>
                            <span class="font-semibold text-base-content">{{ $selectedTicket->category->category_name ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-base-content/60 block">Department</span>
                            <span class="font-semibold text-base-content">{{ $selectedTicket->department->department_name ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-base-content/60 block">Desk Location</span>
                            <span class="font-semibold text-base-content">{{ $selectedTicket->desk->desk_name ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-base-content/60 block">Equipment</span>
                            <span class="font-semibold text-base-content">{{ $selectedTicket->equipment->name ?? 'None Specified' }}</span>
                        </div>
                        <div>
                            <span class="text-base-content/60 block">Submitted</span>
                            <span class="font-semibold text-base-content">{{ $selectedTicket->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="space-y-1">
                        <h4 class="font-semibold text-xs text-base-content/70 uppercase">Description</h4>
                        <div class="p-3 bg-base-200/30 rounded-box border border-base-200 text-xs leading-relaxed text-base-content">
                            {{ $selectedTicket->description }}
                        </div>
                    </div>

                    <!-- Attachments -->
                    @php
                        $attachments = is_string($selectedTicket->attachment_url)
                            ? json_decode($selectedTicket->attachment_url, true)
                            : $selectedTicket->attachment_url;
                    @endphp

                    @if(!empty($attachments) && is_array($attachments))
                        <div class="space-y-1">
                            <h4 class="font-semibold text-xs text-base-content/70 uppercase">Attachments</h4>
                            <div class="flex flex-wrap gap-2 pt-1">
                                @foreach($attachments as $file)
                                    <a
                                        href="{{ Storage::url($file) }}"
                                        target="_blank"
                                        class="btn btn-xs btn-outline btn-primary gap-1"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        View File
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Modal Actions -->
                <div class="modal-action border-t border-base-200 pt-3 flex items-center justify-between">
                    <a
                        wire:navigate
                        href="{{ route('user-chat', ['ticket' => $selectedTicket->id]) }}"
                        class="btn btn-secondary text-white btn-sm gap-1"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        Open Ticket Chat
                    </a>
                    <button wire:click="closeModal" class="btn btn-ghost btn-sm">Close</button>
                </div>
            </div>
            <div wire:click="closeModal" class="modal-backdrop bg-neutral/60"></div>
        </div>
    @endif
</div>
