<?php

namespace App\Repositories;

use App\Core\Database;
use App\Enums\OfferStatus;
use App\Enums\PostOfferAuditAction;

final class OfferRepository
{
    public static function find(int $offerId): ?array
    {
        return Database::fetch('SELECT * FROM offers WHERE offer_id = ?', [$offerId]);
    }

    public static function findByApplicationId(int $applicationId): array
    {
        return Database::fetchAll('SELECT * FROM offers WHERE application_id = ? ORDER BY offer_sequence ASC', [$applicationId]);
    }

    public static function getActiveOffer(int $applicationId): ?array
    {
        return Database::fetch(
            'SELECT * FROM offers WHERE application_id = ? AND status IN (?, ?) ORDER BY offer_id DESC LIMIT 1',
            [$applicationId, OfferStatus::DRAFT->value, OfferStatus::SENT->value]
        );
    }

    public static function getOffersWithDetails(): array
    {
        return Database::fetchAll(
            'SELECT o.*, a.job_id, j.title as job_title, c.candidate_id, u.name as candidate_name 
             FROM offers o 
             JOIN applications a ON o.application_id = a.application_id
             JOIN job_requisitions j ON a.job_id = j.job_id
             JOIN candidates c ON a.candidate_id = c.candidate_id
             JOIN users u ON c.candidate_id = u.user_id
             ORDER BY o.created_at DESC'
        );
    }

    public static function createDraft(int $applicationId, string $offerType, float $ctc, float $bonus, float $stockOptions, string $expiryDate, int $createdBy, ?int $replacesOfferId = null): int
    {
        $existingOffers = self::findByApplicationId($applicationId);
        $sequence = count($existingOffers) + 1;

        return Database::insert('offers', [
            'application_id' => $applicationId,
            'offer_sequence' => $sequence,
            'replaces_offer_id' => $replacesOfferId,
            'offer_type' => $offerType,
            'ctc' => $ctc,
            'bonus' => $bonus,
            'stock_options' => $stockOptions,
            'expiry_date' => $expiryDate,
            'created_by' => $createdBy,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function send(int $offerId, int $actorId): void
    {
        Database::transaction(function () use ($offerId, $actorId) {
            $offer = self::find($offerId);
            Database::update('offers', [
                'status' => OfferStatus::SENT->value,
                'sent_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'offer_id = ?', [$offerId]);

            FinalEvaluationRepository::updateApplicationStatus($offer['application_id'], 'OFFER', $actorId, 'Offer sent');
        });
    }

    public static function enforceExpiryForOffer(int $offerId, int $actorUserId): bool
    {
        return Database::transaction(function () use ($offerId, $actorUserId): bool {
            $offer = self::find($offerId);
            if ($offer && $offer['status'] === OfferStatus::SENT->value && strtotime($offer['expiry_date']) < time()) {
                Database::update('offers', [
                    'status' => OfferStatus::EXPIRED->value,
                    'expired_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ], 'offer_id = ?', [$offerId]);
                
                FinalEvaluationRepository::updateApplicationStatus($offer['application_id'], 'REJECTED', $actorUserId, 'Offer expired');
                
                PostOfferAuditRepository::record($offer['application_id'], $offerId, null, $actorUserId, PostOfferAuditAction::OFFER_EXPIRE->value, [
                    'status' => ['old' => OfferStatus::SENT->value, 'new' => OfferStatus::EXPIRED->value]
                ]);

                return true;
            }

            return false;
        });
    }

    public static function accept(int $offerId, int $actorId): void
    {
        Database::transaction(function () use ($offerId, $actorId) {
            $offer = self::find($offerId);
            Database::update('offers', [
                'status' => OfferStatus::ACCEPTED->value,
                'accepted_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'offer_id = ?', [$offerId]);

            FinalEvaluationRepository::updateApplicationStatus($offer['application_id'], 'HIRED', $actorId, 'Offer accepted');
        });
    }

    public static function reject(int $offerId, int $actorId): void
    {
        Database::transaction(function () use ($offerId, $actorId) {
            $offer = self::find($offerId);
            Database::update('offers', [
                'status' => OfferStatus::REJECTED->value,
                'rejected_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'offer_id = ?', [$offerId]);

            FinalEvaluationRepository::updateApplicationStatus($offer['application_id'], 'REJECTED', $actorId, 'Offer rejected');
        });
    }
}
