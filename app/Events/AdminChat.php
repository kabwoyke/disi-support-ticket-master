<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminChat implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $adminText;
    public string|int $chatId;
    public ?string $attachment; // 1. Added property

    /**
     * Create a new event instance.
     */
    public function __construct(string $adminText, string|int $chatId, ?string $attachment = null) // 2. Accept nullable attachment
    {
        $this->adminText = $adminText;
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
        return 'AdminChat';
    }

    /**
     * Data to broadcast with the event.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'adminText'  => $this->adminText,
            'chatId'     => $this->chatId,
            'attachment' => $this->attachment, // 3. Include attachment in payload
        ];
    }
}
