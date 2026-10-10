<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Tells the other party in a chat that their messages have been seen.
 */
class MessagesRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /**
     * @param  string  $readerType  'user' or 'admin' — who just read the messages
     */
    public function __construct(
        public int $chatId,
        public string $readerType,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin-chat.' . $this->chatId)];
    }

    public function broadcastAs(): string
    {
        return 'MessagesRead';
    }

    public function broadcastWith(): array
    {
        return ['chatId' => $this->chatId, 'readerType' => $this->readerType];
    }
}
