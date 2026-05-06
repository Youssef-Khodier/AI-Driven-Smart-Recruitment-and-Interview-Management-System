<div class="max-w-5xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Feedback Governance</h1>
            <p class="text-text-muted mt-2 text-lg">Manage concern flags, normalization snapshots, and governance oversight.</p>
        </div>
        <a href="<?= e(url('hr.dashboard')) ?>" class="text-info hover:underline text-sm font-medium">&larr; Back to Dashboard</a>
    </div>

    <!-- Open Concern Flags -->
    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border-base bg-bg-alt">
            <h2 class="text-xl font-semibold text-primary flex items-center">
                <span class="material-symbols-outlined mr-2 text-error">flag</span>
                Open Concern Flags (<?= count($openFlags) ?>)
            </h2>
        </div>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-bg-alt border-b border-border-base text-text-muted text-sm uppercase tracking-wider">
                    <th class="px-6 py-3 font-semibold">Candidate</th>
                    <th class="px-6 py-3 font-semibold">Job</th>
                    <th class="px-6 py-3 font-semibold">Category</th>
                    <th class="px-6 py-3 font-semibold">Severity</th>
                    <th class="px-6 py-3 font-semibold">Created</th>
                    <th class="px-6 py-3 font-semibold text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
                <?php foreach ($openFlags as $flag): ?>
                    <tr class="hover:bg-bg-alt transition-colors">
                        <td class="px-6 py-4 text-primary font-medium"><?= e($flag['candidate_name']) ?></td>
                        <td class="px-6 py-4 text-text-muted"><?= e($flag['job_title'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4"><span class="bg-warning-bg text-warning px-2 py-1 rounded text-xs font-medium"><?= e($flag['category']) ?></span></td>
                        <td class="px-6 py-4"><span class="text-error font-medium text-sm"><?= e($flag['severity']) ?></span></td>
                        <td class="px-6 py-4 text-text-muted text-sm"><?= e(date('M j, Y', strtotime($flag['created_at']))) ?></td>
                        <td class="px-6 py-4 text-right">
                            <a href="<?= e(url('hr.governance.show', [$flag['application_id']])) ?>" class="bg-primary hover:bg-primary-light text-white px-3 py-1.5 rounded-lg text-sm font-medium transition-colors shadow-sm">Review</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($openFlags)): ?>
                    <tr><td colspan="6" class="px-6 py-8 text-center text-text-muted">No open concern flags.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent Snapshots -->
    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border-base bg-bg-alt">
            <h2 class="text-xl font-semibold text-primary flex items-center">
                <span class="material-symbols-outlined mr-2 text-info">analytics</span>
                Recent Normalized Snapshots
            </h2>
        </div>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-bg-alt border-b border-border-base text-text-muted text-sm uppercase tracking-wider">
                    <th class="px-6 py-3 font-semibold">Candidate</th>
                    <th class="px-6 py-3 font-semibold">Job</th>
                    <th class="px-6 py-3 font-semibold">Aggregate Score</th>
                    <th class="px-6 py-3 font-semibold">Status</th>
                    <th class="px-6 py-3 font-semibold">Created</th>
                    <th class="px-6 py-3 font-semibold text-right">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
                <?php foreach ($recentSnapshots as $snap): ?>
                    <tr class="hover:bg-bg-alt transition-colors">
                        <td class="px-6 py-4 text-primary font-medium"><?= e($snap['candidate_name']) ?></td>
                        <td class="px-6 py-4 text-text-muted"><?= e($snap['job_title']) ?></td>
                        <td class="px-6 py-4 font-bold text-lg"><?= e(number_format($snap['aggregate_score'], 1)) ?></td>
                        <td class="px-6 py-4"><span class="px-2 py-1 rounded text-xs font-medium <?= $snap['normalization_status'] === 'APPLIED' ? 'bg-success-bg text-success' : 'bg-warning-bg text-warning' ?>"><?= e($snap['normalization_status']) ?></span></td>
                        <td class="px-6 py-4 text-text-muted text-sm"><?= e(date('M j, Y', strtotime($snap['created_at']))) ?></td>
                        <td class="px-6 py-4 text-right">
                            <a href="<?= e(url('hr.governance.show', [$snap['application_id']])) ?>" class="text-info hover:underline text-sm font-medium">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recentSnapshots)): ?>
                    <tr><td colspan="6" class="px-6 py-8 text-center text-text-muted">No snapshots yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
