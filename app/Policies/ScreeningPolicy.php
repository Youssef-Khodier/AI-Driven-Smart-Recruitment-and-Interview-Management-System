<?php

namespace App\Policies;

use App\Core\Auth;
use App\Enums\UserRole;
use App\Enums\AccountStatus;

class ScreeningPolicy {
    private static function isHrAdmin(): bool {
        $user = Auth::user();
        // Fallback for array or object syntax since we don't know the exact Auth structure
        if (!$user) return false;
        $role = is_array($user) ? ($user['role'] ?? '') : ($user->role ?? '');
        $status = is_array($user) ? ($user['status'] ?? '') : ($user->status ?? '');
        return $role === 'HR_ADMIN' && $status === 'ACTIVE';
    }

    public static function canConfigure(): bool { return self::isHrAdmin(); }
    public static function canRecalculate(): bool { return self::isHrAdmin(); }
    public static function canTriage(): bool { return self::isHrAdmin(); }
    public static function canViewShortlist(): bool { return self::isHrAdmin(); }
    public static function canManageDuplicates(): bool { return self::isHrAdmin(); }
    public static function canViewAudit(): bool { return self::isHrAdmin(); }
}
