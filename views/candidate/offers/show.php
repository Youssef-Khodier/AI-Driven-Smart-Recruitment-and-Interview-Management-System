<h1>Your Offer for <?= e($offer['job_title']) ?></h1>

<p><strong>Status:</strong> <?= e($offer['status']) ?></p>
<p><strong>Offer Type:</strong> <?= e($offer['offer_type']) ?></p>
<p><strong>CTC:</strong> $<?= number_format($offer['ctc'], 2) ?></p>
<?php if ($offer['bonus'] > 0): ?><p><strong>Bonus:</strong> $<?= number_format($offer['bonus'], 2) ?></p><?php endif; ?>
<?php if ($offer['stock_options'] > 0): ?><p><strong>Stock Options:</strong> $<?= number_format($offer['stock_options'], 2) ?></p><?php endif; ?>
<p><strong>Please respond by:</strong> <?= e($offer['expiry_date']) ?></p>

<?php if ($offer['status'] === 'SENT'): ?>
    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
        <form method="POST" action="<?= e(url('candidate.offers.accept', [$offer['offer_id']])) ?>">
            <?= csrf_field() ?>
            <button type="submit" style="background-color: green; color: white;">Accept Offer</button>
        </form>
        <form method="POST" action="<?= e(url('candidate.offers.reject', [$offer['offer_id']])) ?>" onsubmit="return confirm('Are you sure you want to reject this offer?');">
            <?= csrf_field() ?>
            <button type="submit" style="background-color: red; color: white;">Reject Offer</button>
        </form>
    </div>
<?php endif; ?>
