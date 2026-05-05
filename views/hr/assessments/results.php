<div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
    <div class="px-6 py-5 border-b border-border-base flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-container-low">
        <h1 class="text-2xl font-bold text-primary flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary">analytics</span>
            Assessment Results for <?= e($requisition['title']) ?>
        </h1>
        <a href="<?= e(url('hr.requisitions.show', [$requisition['job_id']])) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1 shrink-0">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to Requisition
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-lowest text-text-muted text-xs uppercase tracking-wider border-b border-border-base">
                <tr>
                    <th class="px-6 py-3 font-medium">Candidate</th>
                    <th class="px-6 py-3 font-medium">Assessment</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium text-center">Score</th>
                    <th class="px-6 py-3 font-medium text-center">Integrity Events</th>
                    <th class="px-6 py-3 font-medium text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base text-sm">
            <?php foreach ($attempts as $attempt): ?>
                <tr class="hover:bg-surface-container-lowest transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-medium text-primary"><?= e($attempt['name']) ?></div>
                        <div class="text-xs text-text-muted"><?= e($attempt['email']) ?></div>
                    </td>
                    <td class="px-6 py-4 text-primary"><?= e($attempt['assessment_title']) ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium border <?= $attempt['status'] === 'COMPLETED' ? 'bg-success-bg text-success border-success/20' : 'bg-blue-50 text-blue-800 border-blue-200' ?>">
                            <?= e($attempt['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 font-bold text-primary text-center text-base"><?= e($attempt['score'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-center">
                        <?php if ($attempt['event_count'] > 0): ?>
                            <span class="inline-flex items-center gap-1 text-warning bg-warning-bg px-2 py-1 rounded border border-warning/20 font-medium">
                                <span class="material-symbols-outlined text-[14px]">warning</span> <?= e($attempt['event_count']) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-text-muted">0</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a class="inline-flex items-center justify-center px-3 py-1.5 border border-outline-variant rounded-md shadow-sm text-xs font-medium text-primary bg-white hover:bg-surface-container-low transition-colors" href="<?= e(url('hr.candidate-assessments.show', [$attempt['ca_id']])) ?>">
                            Review
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($attempts)): ?>
                <tr><td colspan="6" class="px-6 py-8 text-center text-text-muted">No assessment results available.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
