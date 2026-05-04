<h1>Onboarding: <?= e($record['candidate_name']) ?> for <?= e($record['job_title']) ?></h1>

<p><strong>Candidate:</strong> <?= e($record['candidate_name']) ?></p>
<p><strong>Job:</strong> <?= e($record['job_title']) ?></p>
<p><strong>Offer Type:</strong> <?= e($record['offer_type']) ?></p>

<h2>Manage Onboarding</h2>
<?php include __DIR__ . '/form.php'; ?>
