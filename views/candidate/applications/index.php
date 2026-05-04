<h1>My Applications</h1>
<table><thead><tr><th>Job</th><th>Status</th><th>Match</th><th>Applied</th><th></th></tr></thead><tbody>
<?php foreach ($applications as $application): ?><tr><td><?= e($application['title']) ?></td><td><?= e($application['status']) ?></td><td><?= e($application['match_score']) ?>%</td><td><?= e($application['applied_at']) ?></td><td><a href="<?= e(url('candidate.applications.show', [$application['application_id']])) ?>">View</a></td></tr><?php endforeach; ?>
</tbody></table>
