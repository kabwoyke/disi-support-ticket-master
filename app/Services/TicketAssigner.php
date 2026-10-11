<?php

namespace App\Services;

use App\Models\SupportTeam;
use App\Models\Ticket;
use App\Models\TicketAssignment;
use App\Notifications\NotifyTicket;
use Illuminate\Support\Facades\DB;

class TicketAssigner
{
    /**
     * Assign a ticket to the best available agent in its category.
     *
     * "Best" = lowest load (open tickets / capacity), then whoever was
     * assigned a ticket least recently, so work is spread evenly.
     * Returns null (ticket stays in the queue) when nobody has capacity.
     */
    public function assign(Ticket $ticket, ?int $preferredAgentId = null): ?SupportTeam
    {
        $agent = DB::transaction(function () use ($ticket, $preferredAgentId) {
            // Never double-assign a ticket that already has a live assignment.
            $alreadyAssigned = TicketAssignment::where('ticketId', $ticket->id)
                ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
                ->exists();

            if ($alreadyAssigned) {
                return null;
            }

            $eligible = fn () => SupportTeam::query()
                ->where('available', true)
                ->where('ticket_category_id', $ticket->categoryId)
                ->whereColumn('ticket_count', '<', 'max_ticket_capacity');

            // The requester's chosen agent, if they are still free; otherwise fall back.
            $agent = $preferredAgentId
                ? $eligible()->whereKey($preferredAgentId)->lockForUpdate()->first()
                : null;

            $agent ??= $eligible()
                ->withMax('ticket_assignment', 'created_at')
                ->orderByRaw('ticket_count * 1.0 / max_ticket_capacity')
                ->orderBy('ticket_assignment_max_created_at') // never-assigned (NULL) first
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (! $agent) {
                return null;
            }

            TicketAssignment::create([
                'teamId'   => $agent->id,
                'ticketId' => $ticket->id,
            ]);

            $agent->increment('ticket_count');

            if ($agent->ticket_count >= $agent->max_ticket_capacity) {
                $agent->update(['available' => false]);
            }

            return $agent;
        });

        $agent?->notify(new NotifyTicket($ticket, 'created'));

        return $agent;
    }

    /**
     * Free the agent's slot once a ticket is resolved/closed, then hand the
     * freed capacity to the oldest ticket still waiting in that category.
     */
    public function release(Ticket $ticket): void
    {
        $categoryId = $ticket->categoryId;

        DB::transaction(function () use ($ticket) {
            $assignments = TicketAssignment::where('ticketId', $ticket->id)
                ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
                ->lockForUpdate()
                ->get();

            foreach ($assignments as $assignment) {
                $assignment->update(['status' => $ticket->status]);

                $agent = SupportTeam::lockForUpdate()->find($assignment->teamId);

                if (! $agent) {
                    continue;
                }

                $wasFull = $agent->ticket_count >= $agent->max_ticket_capacity;

                $agent->ticket_count = max(0, $agent->ticket_count - 1);

                // Only switch availability back on if capacity was what turned it
                // off, so an agent an admin set to unavailable stays that way.
                if ($wasFull) {
                    $agent->available = true;
                }

                $agent->save();
            }
        });

        $this->assignWaiting($categoryId);
    }

    /** Assign queued (unassigned, still OPEN) tickets while agents have room. */
    public function assignWaiting(int $categoryId): void
    {
        Ticket::where('categoryId', $categoryId)
            ->where('status', 'OPEN')
            ->whereDoesntHave('ticket_assignment')
            ->oldest('id')
            ->each(function (Ticket $ticket) {
                if (! $this->assign($ticket)) {
                    return false; // no capacity left, stop
                }
            });
    }
}
