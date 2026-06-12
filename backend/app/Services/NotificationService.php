<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function send(User $user, string $type, string $message, ?string $link = null): Notification
    {
        return Notification::create([
            'user_id'    => $user->id,
            'type'       => $type,
            'message'    => $message,
            'read_at'    => null,
            'created_at' => now(),
        ]);
    }

    public function markAsRead(Notification $notification): void
    {
        $notification->update(['read_at' => now()]);
    }

    public function markAllAsRead(User $user): void
    {
        $user->notifications()->whereNull('read_at')->update(['read_at' => now()]);
    }
}
