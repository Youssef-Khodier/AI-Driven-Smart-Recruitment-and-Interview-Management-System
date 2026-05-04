<h1>Job Requisitions</h1>
<p><a class="button" href="<?= e(url('hr.requisitions.create')) ?>">Create requisition</a></p>
<p class="muted"><a href="<?= e(url('hr.requisitions.index')) ?>">All</a> <?php foreach (['DRAFT','PENDING','APPROVED','OPEN','CLOSED'] as $s): ?> | <a href="<?= e(url('hr.requisitions.index')) ?>?status=<?= e($s) ?>"><?= e($s) ?></a><?php endforeach; ?></p>
<table><thead><tr><th>Title</th><th>Department</th><th>Status</th><th>Created By</th><th></th></tr></thead><tbody>
<?php foreach ($requisitions as $row): ?><tr><td><?= e($row['title']) ?></td><td><?= e($row['department_name']) ?></td><td><?= e($row['status']) ?></td><td><?= e($row['creator_name']) ?></td><td><a href="<?= e(url('hr.requisitions.show', [$row['job_id']])) ?>">View</a></td></tr><?php endforeach; ?>
</tbody></table>
