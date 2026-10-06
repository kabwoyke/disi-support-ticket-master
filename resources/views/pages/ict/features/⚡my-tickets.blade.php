<?php

use Livewire\Component;
use App\Models\Ticket;
use App\Models\TicketAssignment;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public string $search = '';

    public ?Ticket $selectedTicket = null;
    public bool $showModal = false;

    // Reset pagination when searching or changing filters
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function viewTicket($ticketId): void
    {
        $this->selectedTicket = Ticket::with(['user', 'category', 'department', 'equipment', 'desk'])
            ->find($ticketId);

        if ($this->selectedTicket) {
            $this->showModal = true;
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedTicket = null;
    }

    public function with(): array
    {
        // Obtain authenticated support user ID across support guard or fallback auth
        $supportId = auth('support')->id() ?? auth()->id();

        $assignments = TicketAssignment::with([
            'ticket.user',
            'ticket.category',
            'ticket.department',
            'ticket.equipment',
            'ticket.desk'
        ])
            // Filter using correct support team ID or user ID column
            ->where('teamId', $supportId)
            ->when($this->search, function ($query) {
                $query->whereHas('ticket', function ($q) {
                    $q->where('subject', 'like', '%' . $this->search . '%')
                      ->orWhere('id', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->whereHas('ticket', function ($q) {
                    $q->where('status', $this->statusFilter);
                });
            })
            ->latest()
            ->paginate(10);

        return [
            'assignments' => $assignments,
        ];
    }

    public function render()
    {
        return view("pages.ict.features.⚡my-tickets")->layout("layouts::support");
    }
};
?>

<div class="p-6 space-y-6 max-w-7xl mx-auto">


    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-base-300 pb-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-base-content">My Assigned Tickets</h1>
            <p class="text-sm text-base-content/70">Manage and track IT support tickets assigned to you.</p>
        </div>
    </div>

    <!-- Toolbar: Search & Filter -->
    <div class="bg-base-100 border border-base-200 rounded-box p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full sm:w-80">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Search ticket # or subject..."
                class="input input-bordered input-sm sm:input-md w-full pl-10"
            />
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 11-14 0 0114 0z"/>
            </svg>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
            <select wire:model.live="statusFilter" class="select select-bordered select-sm sm:select-md w-full sm:w-auto">
                <option value="">All Statuses</option>
                <option value="OPEN">Open</option>
                <option value="IN-PROGRESS">In Progress</option>
                <option value="RESOLVED">Resolved</option>
                <option value="CLOSED">Closed</option>
            </select>
        </div>
    </div>

    <!-- Ticket Table Card -->
    <div class="bg-base-100 border border-base-200 rounded-box shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full text-left">
                <thead class="bg-base-200/50 text-xs text-base-content/70 uppercase">
                    <tr>
                        <th>Ticket ID</th>
                        <th>Subject</th>
                        <th>Requester</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Assigned Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-200 text-sm">
                    @forelse($assignments as $assignment)
                        @php
                            $ticket = $assignment->ticket;
                        @endphp
                        <tr wire:key="assignment-{{ $assignment->id }}" class="hover:bg-base-200/30 transition-colors">
                            <!-- Ticket Number -->
                            <td class="font-mono text-xs font-bold text-primary whitespace-nowrap">
                                #{{ str_pad($ticket->id ?? 0, 5, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Subject & Title -->
                            <td>
                                <div class="font-semibold text-base-content line-clamp-1" title="{{ $ticket->subject ?? 'No Subject Provided' }}">
                                    {{ $ticket->subject ?? 'No Subject Provided' }}
                                </div>
                            </td>

                            <!-- Requester -->
                            <td class="whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-full w-6">
                                            <span class="text-[10px]">{{ strtoupper(substr($ticket->user->name ?? 'U', 0, 1)) }}</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-medium text-base-content/80">{{ $ticket->user->name ?? 'System User' }}</span>
                                </div>
                            </td>


                            <!-- Category -->
                            <td class="whitespace-nowrap">
                                <span class="badge badge-outline badge-xs font-medium text-base-content/70">
                                    {{ $ticket->category->category_name ?? 'General Support' }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="whitespace-nowrap">
                                @switch(strtoupper($ticket->status ?? 'OPEN'))
                                    @case('IN-PROGRESS')
                                    @case('IN_PROGRESS')
                                        <span class="badge badge-warning badge-sm font-medium">In Progress</span>
                                        @break
                                    @case('RESOLVED')
                                        <span class="badge badge-success badge-sm font-medium">Resolved</span>
                                        @break
                                    @case('CLOSED')
                                        <span class="badge badge-ghost badge-sm font-medium">Closed</span>
                                        @break
                                    @default
                                        <span class="badge badge-info badge-sm font-medium">Open</span>
                                @endswitch
                            </td>

                            <!-- Assigned Date -->
                            <td class="text-xs text-base-content/70 whitespace-nowrap">
                                {{ $assignment->created_at ? $assignment->created_at->format('M d, Y') : 'N/A' }}
                            </td>

                            <!-- Actions -->
                            <td class="text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        wire:click="viewTicket({{ $ticket->id }})"
                                        class="btn btn-ghost btn-xs text-primary gap-1"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        View
                                    </button>

                                    <a
                                        wire:navigate
                                        href="{{ route('support-chat', ['ticket' => $ticket->id]) }}"
                                        class="btn btn-secondary btn-xs text-white font-medium gap-1"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                        Chat
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-base-content/60">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-10 h-10 text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="font-medium text-sm">No assigned tickets found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        @if ($assignments->hasPages())
            <div class="p-4 border-t border-base-200">
                {{ $assignments->links() }}
            </div>
        @endif
    </div>

    <!-- Ticket Detail Modal -->
    @if($showModal && $selectedTicket)
        <div class="modal modal-open" role="dialog">
            <div class="modal-box max-w-2xl bg-base-100 border border-base-300 text-base-content shadow-xl">
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
                    <!-- Status & Priority Badges -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase text-base-content/60">Status:</span>
                        <span class="badge badge-info badge-outline font-semibold">{{ $selectedTicket->status }}</span>

                        <span class="text-xs font-semibold uppercase text-base-content/60 ml-2">Priority:</span>
                        <span class="badge {{ $selectedTicket->priority === 'HIGH' ? 'badge-error text-white' : ($selectedTicket->priority === 'MODERATE' ? 'badge-warning text-white' : 'badge-ghost') }}">
                            {{ $selectedTicket->priority ?? 'LOW' }}
                        </span>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-2 gap-3 bg-base-200/50 p-3 rounded-box text-xs">
                        <div>
                            <span class="text-base-content/60 block">Requester</span>
                            <span class="font-semibold text-base-content">{{ $selectedTicket->user->name ?? 'System User' }}</span>
                        </div>
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
                        href="{{ route('support-chat', ['ticket' => $selectedTicket->id]) }}"
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
