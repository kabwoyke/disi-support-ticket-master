<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserChat implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $userText;
    public string|int $chatId;
    public ?string $attachment;

    /**
     * Create a new event instance.
     */
    public function __construct(string $userText, string|int $chatId, ?string $attachment = null)
    {
        $this->userText = $userText;
        $this->chatId = $chatId;
        $this->attachment = $attachment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin-chat.' . $this->chatId),
        ];
    }

    /**
     * Set explicit broadcast event name.
     */
    public function broadcastAs(): string
    {
        return 'UserChat';
    }

    /**
     * Data to broadcast with the event.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'userText'   => $this->userText,
            'chatId'     => $this->chatId,
            'attachment' => $this->attachment, // <-- Added here
        ];
    }
}
