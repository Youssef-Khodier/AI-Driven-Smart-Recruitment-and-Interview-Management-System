<h1>Offer for <?= e($offer['candidate_name']) ?> - <?= e($offer['job_title']) ?></h1>

<p><strong>Status:</strong> <?= e($offer['status']) ?></p>
<p><strong>Sequence:</strong> <?= e($offer['offer_sequence']) ?></p>
<p><strong>Type:</strong> <?= e($offer['offer_type']) ?></p>
<p><strong>CTC:</strong> $<?= number_format($offer['ctc'], 2) ?></p>
<p><strong>Bonus:</strong> $<?= number_format($offer['bonus'], 2) ?></p>
<p><strong>Stock Options:</strong> $<?= number_format($offer['stock_options'], 2) ?></p>
<p><strong>Expiry:</strong> <?= e($offer['expiry_date']) ?></p>
<p><strong>Created At:</strong> <?= e($offer['created_at']) ?></p>
<p><strong>Sent At:</strong> <?= e($offer['sent_at'] ?? 'N/A') ?></p>
<p><strong>Accepted At:</strong> <?= e($offer['accepted_at'] ?? 'N/A') ?></p>
<p><strong>Rejected At:</strong> <?= e($offer['rejected_at'] ?? 'N/A') ?></p>

<?php if ($offer['status'] === 'DRAFT'): ?>
    <form method="POST" action="<?= e(url('hr.offers.send', [$offer['offer_id']])) ?>">
        <?= csrf_field() ?>
        <button type="submit">Send Offer</button>
    </form>
<?php endif; ?>

<?php if ($offer['status'] === 'ACCEPTED'): ?>
    <?php if ($onboarding): ?>
        <p><a class="button" href="<?= e(url('hr.onboarding.show', [$onboarding['onboarding_id']])) ?>">Manage Onboarding</a></p>
    <?php else: ?>
        <p><a class="button" href="<?= e(url('hr.onboarding.create', [$offer['offer_id']])) ?>">Create Onboarding</a></p>
    <?php endif; ?>
<?php endif; ?>
