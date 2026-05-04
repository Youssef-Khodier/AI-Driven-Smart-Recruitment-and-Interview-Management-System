<h1>Open Jobs</h1>
<table><thead><tr><th>Title</th><th>Department</th><th>Description</th><th></th></tr></thead><tbody>
<?php foreach ($jobs as $job): ?><tr><td><?= e($job['title']) ?></td><td><?= e($job['department_name']) ?></td><td><?= e(str_limit($job['description'])) ?></td><td><a href="<?= e(url('candidate.jobs.show', [$job['job_id']])) ?>">View</a></td></tr><?php endforeach; ?>
</tbody></table>
