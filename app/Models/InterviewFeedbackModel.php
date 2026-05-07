<?php

namespace App\Models;

use App\Core\Database;
use App\Enums\InterviewAssignmentRole;

final class InterviewFeedbackModel
{
    public static function alreadySubmitted(int $interviewId, int $interviewerId): bool
    {
        $result = Database::fetch(
            "SELECT COUNT(*) as count FROM interview_feedback WHERE interview_id = ? AND interviewer_id = ?",
            [$interviewId, $interviewerId]
        );
        return (int) ($result['count'] ?? 0) > 0;
    }

    public static function create(array $data, int $actorUserId): int
    {
        return Database::transaction(function () use ($data, $actorUserId) {
            $feedbackId = Database::insert('interview_feedback', [
                'interview_id' => $data['interview_id'],
                'interviewer_id' => $data['interviewer_id'],
                'technical_score' => $data['technical_score'],
                'communication_score' => $data['communication_score'],
                'culture_fit_score' => $data['culture_fit_score'],
                'overall_score' => $data['overall_score'],
                'comments' => $data['comments'],
                'submitted_at' => date('Y-m-d H:i:s'),
            ]);

            InterviewAuditModel::record($data['interview_id'], $actorUserId, \App\Enums\InterviewAuditAction::FEEDBACK_SUBMITTED->value, [
                'interviewer_id' => $data['interviewer_id'],
                'technical_score' => $data['technical_score'],
                'communication_score' => $data['communication_score'],
                'culture_fit_score' => $data['culture_fit_score'],
                'overall_score' => $data['overall_score'],
                'comments' => $data['comments'],
            ]);

            return $feedbackId;
        });
    }

    public static function forInterview(int $interviewId): array
    {
        return Database::fetchAll(
            "SELECT f.*, u.name AS interviewer_name 
             FROM interview_feedback f
             JOIN users u ON u.user_id = f.interviewer_id
             WHERE f.interview_id = ?
             ORDER BY f.submitted_at ASC",
            [$interviewId]
        );
    }

    public static function completionState(int $interviewId): string
    {
        $assignments = Database::fetchAll(
            "SELECT * FROM interviewers_assignment WHERE interview_id = ?",
            [$interviewId]
        );

        $officialCount = 0;
        foreach ($assignments as $assignment) {
            if (in_array($assignment['role_in_panel'], InterviewAssignmentRole::officialScorerValues())) {
                $officialCount++;
            }
        }

        if ($officialCount === 0) {
            return 'NONE';
        }

        $feedbackCountArray = Database::fetch(
            "SELECT COUNT(*) as count FROM interview_feedback WHERE interview_id = ?",
            [$interviewId]
        );
        $feedbackCount = (int) ($feedbackCountArray['count'] ?? 0);

        if ($feedbackCount === 0) {
            return 'NONE';
        }

        if ($feedbackCount >= $officialCount) {
            return 'COMPLETE';
        }

        return 'PARTIAL';
    }
}
