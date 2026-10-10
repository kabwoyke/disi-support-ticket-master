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

// Presence channel: lets both sides see who is online and exchange typing whispers.
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);

    if (! $chat) {
        return false;
    }

    if (auth()->guard('support')->check()) {
        $member = auth()->guard('support')->user();
        $type = 'admin';
    } elseif ((int) $user->id === (int) $chat->user_id) {
        $member = $user;
        $type = 'user';
    } else {
        return false;
    }

    return [
        'id' => $member->id,
        'type' => $type,
        'name' => $member->display_name,
    ];
}, ['guards' => ['web', 'support']]);
