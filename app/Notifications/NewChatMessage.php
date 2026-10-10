<?php

namespace App\Notifications;

use App\Models\ChatMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the other party whenever a chat message arrives.
 * `$toSupport` decides which chat page the notification links to.
 */
class NewChatMessage extends Notification
{
    use Queueable;

    public function __construct(
        public ChatMessage $message,
        public string $senderName,
        public bool $toSupport,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $chatId = $this->message->chat_id;

        return [
            'kind' => 'chat',
            'chat_id' => $chatId,
            'message_id' => $this->message->id,
            'sender_name' => $this->senderName,
            'preview' => str($this->message->message ?: '[Attachment]')->limit(80)->toString(),
            'url' => $this->toSupport
                ? route('support-chat', ['ticket' => $chatId], false)
                : route('user-chat', ['ticket' => $chatId], false),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
