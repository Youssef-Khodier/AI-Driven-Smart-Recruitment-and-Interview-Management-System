<h1><?= e($title) ?></h1>

<?php if (empty($interviews)): ?>
    <p>You have no assigned interviews.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Candidate</th>
                <th>Job Title</th>
                <th>Scheduled For</th>
                <th>Duration</th>
                <th>Status</th>
                <th>Your Role</th>
                <th>Feedback</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($interviews as $interview): ?>
                <tr>
                    <td><?= e($interview['candidate_name']) ?></td>
                    <td><?= e($interview['job_title']) ?></td>
                    <td><?= e($interview['scheduled_at']) ?></td>
                    <td><?= e($interview['duration_minutes']) ?> min</td>
                    <td><?= e($interview['status']) ?></td>
                    <td><?= e($interview['role_in_panel']) ?></td>
                    <td>
                        <?php 
                        $alreadySubmitted = \App\Repositories\InterviewFeedbackRepository::alreadySubmitted($interview['interview_id'], $actor['user_id']);
                        if ($alreadySubmitted) {
                            echo "Submitted";
                        } elseif ($interview['status'] === \App\Enums\InterviewStatus::COMPLETED->value && in_array($interview['role_in_panel'], \App\Enums\InterviewAssignmentRole::officialScorerValues())) {
                            echo "Pending";
                        } elseif ($interview['role_in_panel'] === \App\Enums\InterviewAssignmentRole::OBSERVER->value) {
                            echo "N/A (Observer)";
                        } else {
                            echo "Not ready";
                        }
                        ?>
                    </td>
                    <td>
                        <a href="<?= e(url('interviewer.interviews.show', [$interview['interview_id']])) ?>">View Briefing</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
