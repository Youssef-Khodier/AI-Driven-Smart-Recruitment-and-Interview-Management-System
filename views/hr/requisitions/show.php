<h1><?= e($requisition['title']) ?></h1>
<p><strong>Status:</strong> <?= e($requisition['status']) ?> | <strong>Department:</strong> <?= e($requisition['department_name']) ?></p>
<p><?= nl2br(e($requisition['description'])) ?></p>
<h2>Requirements</h2><p><?= nl2br(e($requisition['requirements'])) ?></p>
<p class="actions"><a class="button" href="<?= e(url('hr.requisitions.edit', [$requisition['job_id']])) ?>">Edit</a><a class="button" href="<?= e(url('hr.applications.index', [$requisition['job_id']])) ?>">Review applicants</a><a class="button" href="<?= e(url('hr.assessments.index', [$requisition['job_id']])) ?>">Manage assessments</a><a class="button" href="<?= e(url('hr.assessment-results.index', [$requisition['job_id']])) ?>">Assessment results</a></p>
<div class="actions">
<?php foreach ([['submit','Submit for approval'],['approve','Approve'],['open','Open'],['close','Close']] as [$action,$label]): ?><form class="inline" method="POST" action="<?= e(url('hr.requisitions.' . $action, [$requisition['job_id']])) ?>"><?= csrf_field() ?><button type="submit"><?= e($label) ?></button></form><?php endforeach; ?>
</div>
<h2>Assessments</h2><ul><?php foreach ($assessments as $assessment): ?><li><a href="<?= e(url('hr.assessments.show', [$assessment['assessment_id']])) ?>"><?= e($assessment['title']) ?></a> (<?= $assessment['is_active'] ? 'Active' : 'Inactive' ?>)</li><?php endforeach; ?></ul>
<h2>Status History</h2><table><tr><th>From</th><th>To</th><th>By</th><th>When</th></tr><?php foreach ($history as $row): ?><tr><td><?= e($row['old_status'] ?? '-') ?></td><td><?= e($row['new_status']) ?></td><td><?= e($row['actor_name']) ?></td><td><?= e($row['created_at']) ?></td></tr><?php endforeach; ?></table>
