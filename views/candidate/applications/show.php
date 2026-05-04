<h1><?= e($application['title']) ?></h1>
<p><strong>Status:</strong> <?= e($application['status']) ?> | <strong>Simulated match:</strong> <?= e($application['match_score']) ?>%</p>
<h2>Assessments</h2>
<?php if ($application['status'] !== 'ASSESSMENT'): ?><p class="muted">Assessments become available when HR moves this application to ASSESSMENT.</p><?php endif; ?>
<ul><?php foreach ($assessments as $assessment): ?><li><?= e($assessment['title']) ?> (<?= e($assessment['duration_minutes']) ?> min)
<?php if (isset($attemptMap[(int) $assessment['assessment_id']])): $attempt = $attemptMap[(int) $assessment['assessment_id']]; ?><a href="<?= e($attempt['status'] === 'IN_PROGRESS' ? url('candidate.assessments.show', [$attempt['ca_id']]) : url('candidate.assessments.result', [$attempt['ca_id']])) ?>">View attempt</a><?php elseif ($application['status'] === 'ASSESSMENT'): ?><form class="inline" method="POST" action="<?= e(url('candidate.assessments.start', [$application['application_id'], $assessment['assessment_id']])) ?>"><?= csrf_field() ?><button type="submit">Start</button></form><?php endif; ?></li><?php endforeach; ?></ul>

<?php
$candidateOffers = \App\Repositories\OfferRepository::findByApplicationId($application['application_id']);
$visibleOffers = array_filter($candidateOffers, fn($o) => $o['status'] !== 'DRAFT');
if (!empty($visibleOffers)):
    $latestOffer = end($visibleOffers);
?>
<h2>Offer Status</h2>
<p>You have an offer related to this application.</p>
<p><a class="button" href="<?= e(url('candidate.offers.show', [$latestOffer['offer_id']])) ?>">View Offer</a></p>
<?php endif; ?>
