<?php

namespace App\Policies;

use App\Core\Auth;
use App\Core\Database;
use App\Enums\UserRole;

final class OfferPolicy
{
    public static function viewAny(): bool
    {
        return Auth::hasRole(UserRole::HR_ADMIN->value);
    }

    public static function create(): bool
    {
        return Auth::hasRole(UserRole::HR_ADMIN->value);
    }

    public static function send(): bool
    {
        return Auth::hasRole(UserRole::HR_ADMIN->value);
    }

    public static function view(int $offerId): bool
    {
        if (Auth::hasRole(UserRole::HR_ADMIN->value)) {
            return true;
        }

        if (Auth::hasRole(UserRole::CANDIDATE->value)) {
            $offer = Database::fetch(
                'SELECT a.candidate_id FROM offers o JOIN applications a ON o.application_id = a.application_id WHERE o.offer_id = ?',
                [$offerId]
            );

            return $offer && Auth::id() === (int)$offer['candidate_id'];
        }

        return false;
    }

    public static function respond(int $offerId): bool
    {
        if (!Auth::hasRole(UserRole::CANDIDATE->value)) {
            return false;
        }

        $offer = Database::fetch(
            'SELECT a.candidate_id FROM offers o JOIN applications a ON o.application_id = a.application_id WHERE o.offer_id = ?',
            [$offerId]
        );

        return $offer && Auth::id() === (int)$offer['candidate_id'];
    }
}
