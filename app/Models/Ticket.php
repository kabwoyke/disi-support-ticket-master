<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    //

    protected $fillable = [
    'subject',
    'categoryId',
    'priority',
    'equipmentId',
    'departmentId',
    'description',
    'attachment_url',
    'deskId',
    'userId',
    'status'
];


protected function casts(): array
{
    return ['attachment_url' => 'array'];
}

public const CLOSED_STATUSES = ['RESOLVED', 'CLOSED'];

protected static function booted(): void
{
    // Whenever a ticket reaches a final state, free the agent's slot
    // (covers the dashboard resolve button and Filament edits/bulk close).
    static::updated(function (Ticket $ticket) {
        if ($ticket->wasChanged('status')
            && in_array($ticket->status, self::CLOSED_STATUSES, true)
            && ! in_array($ticket->getOriginal('status'), self::CLOSED_STATUSES, true)) {
            app(\App\Services\TicketAssigner::class)->release($ticket);
        }
    });
}

/** Resolved/closed tickets keep their chat history but accept no new messages. */
public function isChatClosed(): bool
{
    return in_array($this->status, self::CLOSED_STATUSES, true);
}

public function category(): BelongsTo
{
    return $this->belongsTo(TicketCategory::class, 'categoryId');
}

public function equipment(): BelongsTo
{
    return $this->belongsTo(Equipment::class, 'equipmentId');
}

public function department(): BelongsTo
{
    return $this->belongsTo(Department::class, 'departmentId');
}


public function ticket_assignment(){
    return $this->hasMany(TicketAssignment::class , 'ticketId');
}

public function desk(){
    return $this->belongsTo(Desk::class , 'deskId');
}

public function user(){
    return $this->belongsTo(User::class , 'userId');
}

}
