<h1>Assessment Results for <?= e($requisition['title']) ?></h1>
<table><thead><tr><th>Candidate</th><th>Assessment</th><th>Status</th><th>Score</th><th>Integrity Events</th><th></th></tr></thead><tbody>
<?php foreach ($attempts as $attempt): ?><tr><td><?= e($attempt['name']) ?><br><span class="muted"><?= e($attempt['email']) ?></span></td><td><?= e($attempt['assessment_title']) ?></td><td><?= e($attempt['status']) ?></td><td><?= e($attempt['score'] ?? '-') ?></td><td><?= e($attempt['event_count']) ?></td><td><a href="<?= e(url('hr.candidate-assessments.show', [$attempt['ca_id']])) ?>">Review</a></td></tr><?php endforeach; ?>
</tbody></table>
