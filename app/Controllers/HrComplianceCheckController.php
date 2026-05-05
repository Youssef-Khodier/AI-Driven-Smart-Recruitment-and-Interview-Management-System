<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Enums\NotificationType;
use App\Enums\UserRole;
use App\Repositories\NotificationRepository;
use App\Repositories\OfferRepository;

final class HrComplianceCheckController extends Controller
{
    public function run(Request $request): Response
    {
        $actor = $this->requireRole(UserRole::HR_ADMIN->value);
        $feedbackCreated = 0;
        $expiringCreated = 0;
        $expiredCreated = 0;
        $offersExpired = 0;

        foreach (NotificationRepository::findMissingFeedbackReminders() as $row) {
            $created = NotificationRepository::createUnique(
                (int) $row['interviewer_id'],
                'Feedback Reminder',
                'Please submit feedback for ' . $row['candidate_name'] . ' from the interview scheduled on ' . date('Y-m-d H:i', strtotime($row['scheduled_at'])) . '.',
                NotificationType::FEEDBACK_REMINDER->value,
                (int) $row['interview_id'],
                'INTERVIEW'
            );
            if ($created !== null) {
                $feedbackCreated++;
            }
        }

        foreach (NotificationRepository::findOffersExpiringWithin48Hours() as $row) {
            $created = NotificationRepository::createUnique(
                (int) $row['created_by'],
                'Offer Expiring Soon',
                'The offer for ' . $row['candidate_name'] . ' (' . $row['job_title'] . ') expires on ' . date('Y-m-d H:i', strtotime($row['expiry_date'])) . '.',
                NotificationType::OFFER_EXPIRING_SOON->value,
                (int) $row['offer_id'],
                'OFFER'
            );
            if ($created !== null) {
                $expiringCreated++;
            }
        }

        foreach (NotificationRepository::findExpiredSentOffers() as $row) {
            if (OfferRepository::enforceExpiryForOffer((int) $row['offer_id'], (int) $actor['user_id'])) {
                $offersExpired++;
            }
            $created = NotificationRepository::createUnique(
                (int) $row['created_by'],
                'Offer Expired',
                'The offer for ' . $row['candidate_name'] . ' (' . $row['job_title'] . ') expired on ' . date('Y-m-d H:i', strtotime($row['expiry_date'])) . '.',
                NotificationType::OFFER_EXPIRED->value,
                (int) $row['offer_id'],
                'OFFER'
            );
            if ($created !== null) {
                $expiredCreated++;
            }
        }

        Session::flash('status', "Checks complete. Feedback reminders: $feedbackCreated. Expiring offer alerts: $expiringCreated. Expired offer alerts: $expiredCreated. Offers expired: $offersExpired.");

        return $this->redirect(url('hr.dashboard'));
    }
}
