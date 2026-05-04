<h1>Assessments for <?= e($requisition['title']) ?></h1>
<p><a class="button" href="<?= e(url('hr.assessments.create', [$requisition['job_id']])) ?>">Create assessment</a> <a class="button" href="<?= e(url('hr.assessment-results.index', [$requisition['job_id']])) ?>">Review results</a></p>
<table><thead><tr><th>Title</th><th>Type</th><th>Duration</th><th>Active</th><th>Questions</th><th>Attempts</th><th></th></tr></thead><tbody>
<?php foreach ($assessments as $assessment): ?><tr><td><?= e($assessment['title']) ?></td><td><?= e($assessment['type']) ?></td><td><?= e($assessment['duration_minutes']) ?> min</td><td><?= $assessment['is_active'] ? 'Yes' : 'No' ?></td><td><?= e($assessment['question_count']) ?></td><td><?= e($assessment['attempt_count']) ?></td><td><a href="<?= e(url('hr.assessments.show', [$assessment['assessment_id']])) ?>">Open</a></td></tr><?php endforeach; ?>
</tbody></table>
