<h1><?= e($title) ?></h1>

<div>
    <h2>Schedule Details</h2>
    <p><strong>Status:</strong> <?= e($interview['status']) ?></p>
    <p><strong>Type:</strong> <?= e($interview['interview_type']) ?></p>
    <p><strong>Scheduled:</strong> <?= e($interview['scheduled_at']) ?></p>
    <p><strong>Duration:</strong> <?= e($interview['duration_minutes']) ?> minutes</p>
</div>

<div>
    <h2>Candidate & Job</h2>
    <p><strong>Candidate:</strong> <?= e($interview['candidate_name']) ?></p>
    <p><strong>Email:</strong> <?= e($interview['candidate_email']) ?></p>
    <p><strong>Job:</strong> <?= e($interview['job_title']) ?></p>
</div>

<div>
    <h2>Panel Assignments</h2>
    <ul>
        <?php foreach ($interview['assignments'] as $assignment): ?>
            <li><?= e($assignment['interviewer_name']) ?> (<?= e($assignment['role_in_panel']) ?>)</li>
        <?php endforeach; ?>
    </ul>
</div>

<div>
    <h2>Feedback (State: <?= e($interview['completion_state']) ?>)</h2>
    <?php if ($interview['status'] === \App\Enums\InterviewStatus::SCHEDULED->value): ?>
        <form method="POST" action="<?= e(url('hr.interviews.complete', [$interview['interview_id']])) ?>" style="display:inline;">
            <?= csrf_field() ?>
            <button type="submit">Mark Completed</button>
        </form>
    <?php endif; ?>
    <ul>
        <?php foreach ($interview['feedback'] as $f): ?>
            <li>
                <strong><?= e($f['interviewer_name']) ?></strong> (<?= e($f['submitted_at']) ?>)<br>
                Scores: Tech <?= e($f['technical_score']) ?>, Comm <?= e($f['communication_score']) ?>, Culture <?= e($f['culture_fit_score']) ?>, Overall <?= e($f['overall_score']) ?><br>
                Comments: <?= nl2br(e($f['comments'])) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<div>
    <h2>Audit History</h2>
    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>Actor</th>
                <th>Action</th>
                <th>Changes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($interview['audit_records'] as $record): ?>
                <tr>
                    <td><?= e($record['created_at']) ?></td>
                    <td><?= e($record['actor_name']) ?></td>
                    <td><?= e($record['action']) ?></td>
                    <td><pre><?= e($record['changed_fields'] ?? '[]') ?></pre></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p><a href="<?= e(url('hr.interviews.index')) ?>">Back to Interviews</a></p>
