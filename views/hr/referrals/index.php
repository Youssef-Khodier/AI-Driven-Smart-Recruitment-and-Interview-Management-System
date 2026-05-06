<div class="max-w-5xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Referral Tracking</h1>
            <p class="text-text-muted mt-2 text-lg">Track referral sources and manage reward attribution.</p>
        </div>
        <a href="<?= e(url('hr.dashboard')) ?>" class="text-info hover:underline text-sm font-medium">&larr; Back to Dashboard</a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-card-surface border border-border-base rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-primary"><?= e($summary['total_referrals']) ?></div>
            <div class="text-sm text-text-muted">Total Referrals</div>
        </div>
        <div class="bg-card-surface border border-border-base rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-warning"><?= e($summary['pending_rewards'] ?? 0) ?></div>
            <div class="text-sm text-text-muted">Pending Rewards</div>
        </div>
        <div class="bg-card-surface border border-border-base rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-success"><?= e($summary['approved_rewards'] ?? 0) ?></div>
            <div class="text-sm text-text-muted">Approved</div>
        </div>
        <div class="bg-card-surface border border-border-base rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-info"><?= number_format((float)($summary['total_paid_amount'] ?? 0), 2) ?></div>
            <div class="text-sm text-text-muted">Total Paid</div>
        </div>
    </div>

    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-bg-alt border-b border-border-base text-text-muted text-sm uppercase tracking-wider">
                    <th class="px-6 py-3 font-semibold">Candidate</th>
                    <th class="px-6 py-3 font-semibold">Job</th>
                    <th class="px-6 py-3 font-semibold">Referrer</th>
                    <th class="px-6 py-3 font-semibold">Source</th>
                    <th class="px-6 py-3 font-semibold">Reward</th>
                    <th class="px-6 py-3 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
                <?php foreach ($referrals as $ref): ?>
                    <tr class="hover:bg-bg-alt transition-colors">
                        <td class="px-6 py-4 text-primary font-medium"><?= e($ref['candidate_name']) ?></td>
                        <td class="px-6 py-4 text-text-muted text-sm"><?= e($ref['job_title']) ?></td>
                        <td class="px-6 py-4 text-sm">
                            <?= e($ref['referrer_user_name'] ?? $ref['referrer_name'] ?? 'External') ?>
                            <?php if ($ref['referrer_email']): ?>
                                <div class="text-xs text-text-muted"><?= e($ref['referrer_email']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4"><span class="px-2 py-1 rounded text-xs font-medium bg-info/10 text-info"><?= e($ref['referral_source']) ?></span></td>
                        <td class="px-6 py-4">
                            <?php if ($ref['reward_status']): ?>
                                <span class="px-2 py-1 rounded text-xs font-medium <?= $ref['reward_status'] === 'PAID' ? 'bg-success-bg text-success' : ($ref['reward_status'] === 'PENDING' ? 'bg-warning-bg text-warning' : 'bg-bg-alt text-text-muted') ?>">
                                    <?= e($ref['reward_status']) ?> <?= $ref['reward_amount'] ? '— $' . number_format($ref['reward_amount'], 2) : '' ?>
                                </span>
                            <?php else: ?>
                                <span class="text-text-muted text-sm">No reward</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <?php if ($ref['reward_status'] === 'PENDING'): ?>
                                <form method="POST" action="<?= e(url('hr.referrals.approve-reward', [$ref['referral_id']])) ?>" class="inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="bg-success hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium">Approve</button>
                                </form>
                                <form method="POST" action="<?= e(url('hr.referrals.reject-reward', [$ref['referral_id']])) ?>" class="inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="bg-error hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-medium">Reject</button>
                                </form>
                            <?php elseif ($ref['reward_status'] === 'APPROVED'): ?>
                                <form method="POST" action="<?= e(url('hr.referrals.mark-paid', [$ref['referral_id']])) ?>" class="inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="bg-info hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium">Mark Paid</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($referrals)): ?>
                    <tr><td colspan="6" class="px-6 py-8 text-center text-text-muted">No referrals recorded yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
