<?php

namespace App\Policies;

use App\Enums\AccountStatus;

final class NotificationPolicy
{
    public function view(array $user, array $notification): bool
    {
        if (($user['status'] ?? null) !== AccountStatus::ACTIVE->value) {
            return false;
        }

        return (int)$user['user_id'] === (int)$notification['user_id'];
    }

    public function markRead(array $user, array $notification): bool
    {
        return $this->view($user, $notification);
    }
}