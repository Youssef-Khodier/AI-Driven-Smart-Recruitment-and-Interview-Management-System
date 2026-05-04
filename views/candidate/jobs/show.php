<h1><?= e($job['title']) ?></h1>
<p><strong>Department:</strong> <?= e($job['department_name']) ?></p>
<p><?= nl2br(e($job['description'])) ?></p>
<h2>Requirements</h2><p><?= nl2br(e($job['requirements'])) ?></p>
<?php if ($application): ?><p class="alert alert-success">You already applied. Status: <?= e($application['status']) ?>.</p><?php else: ?><form method="POST" action="<?= e(url('candidate.applications.store', [$job['job_id']])) ?>"><?= csrf_field() ?><button type="submit">Apply for this job</button></form><?php endif; ?>
