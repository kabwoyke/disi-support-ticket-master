<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('App.Models.SupportTeam.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['support']]);

Broadcast::channel('user-chat', function ($user) {
    // Return true if any authenticated user can join
    return true;
});

Broadcast::channel('admin-chat.{chatId}', function ($user, $chatId) {
    // Grant access to support staff guard
    if (auth()->guard('support')->check()) {
        return true;
    }

    // Verify web user owns the chat
    $chat = Chat::find($chatId);

    return $chat && ((int) $user->id === (int) $chat->user_id);
}, ['guards' => ['web', 'support']]);
