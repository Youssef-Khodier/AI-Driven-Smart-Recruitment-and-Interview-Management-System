<h1><?= e($title) ?></h1>

<?php if (empty($interviews)): ?>
    <p>No interviews scheduled yet.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Candidate</th>
                <th>Job Title</th>
                <th>Scheduled For</th>
                <th>Duration</th>
                <th>Status</th>
                <th>Panel</th>
                <th>Feedback</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($interviews as $interview): ?>
                <tr>
                    <td><?= e($interview['interview_id']) ?></td>
                    <td><?= e($interview['candidate_name']) ?></td>
                    <td><?= e($interview['job_title']) ?></td>
                    <td><?= e($interview['scheduled_at']) ?></td>
                    <td><?= e($interview['duration_minutes']) ?> min</td>
                    <td><?= e($interview['status']) ?></td>
                    <td>
                        <?php foreach ($interview['assignments'] as $assignment): ?>
                            <div><?= e($assignment['interviewer_name']) ?> (<?= e($assignment['role_in_panel']) ?>)</div>
                        <?php endforeach; ?>
                    </td>
                    <td><?= e(\App\Repositories\InterviewFeedbackRepository::completionState($interview['interview_id'])) ?></td>
                    <td>
                        <a href="<?= e(url('hr.interviews.show', [$interview['interview_id']])) ?>">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
