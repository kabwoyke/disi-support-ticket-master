<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotifyTicket extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Ticket $ticket,
        public string $actionType = 'created'
    ) {
        // Eager load relationships if not already loaded
        $this->ticket->loadMissing(['category', 'department', 'equipment', 'desk', 'user']);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification for the database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id'     => $this->ticket->id,
            'subject'       => $this->ticket->subject,
            'description'   => $this->ticket->description,
            'priority'      => $this->ticket->priority,
            'status'        => $this->ticket->status,
            'action_type'   => $this->actionType,
            'category'      => $this->ticket->category->category_name ?? null,
            'department'    => $this->ticket->department->department_name ?? null,
            'desk'          => $this->ticket->desk->desk_name ?? null,
            'equipment'     => $this->ticket->equipment->name ?? null,
            'user_id'       => $this->ticket->userId,
            'user_name'     => $this->ticket->user->name ?? 'N/A',
            'attachments'   => is_string($this->ticket->attachment_url)
                                ? json_decode($this->ticket->attachment_url, true)
                                : $this->ticket->attachment_url,
            'created_at'    => $this->ticket->created_at->toIso8601String(),
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'ticket_id'     => $this->ticket->id,
            'subject'       => $this->ticket->subject,
            'description'   => $this->ticket->description,
            'priority'      => $this->ticket->priority,
            'status'        => $this->ticket->status,
            'action_type'   => $this->actionType,
            'category'      => $this->ticket->category->category_name ?? null,
            'department'    => $this->ticket->department->department_name ?? null,
            'user_name'     => $this->ticket->user->name ?? 'N/A',
            'created_at'    => $this->ticket->created_at->diffForHumans(),
        ]);
    }
}
