<?php

use App\Models\ChatConversation;
use App\Models\MobileUser;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('App.Models.MobileUser.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('mobile.chat.user.{mobileUserId}', function ($user, $mobileUserId) {
    return $user instanceof MobileUser && (int) $user->id === (int) $mobileUserId;
});

Broadcast::channel('mobile.chat.conversation.{conversationId}', function ($user, $conversationId) {
    if ($user instanceof MobileUser) {
        return ChatConversation::query()
            ->where('id', (int) $conversationId)
            ->where('mobile_user_id', $user->id)
            ->exists();
    }

    if ($user instanceof User) {
        return true;
    }

    return false;
});

Broadcast::channel('admin.mobile.chat', function ($user) {
    return $user instanceof User;
});
