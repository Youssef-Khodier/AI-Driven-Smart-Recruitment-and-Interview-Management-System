<?php $title = 'Preview Automated Triage'; ?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Preview Automated Triage</h1>
            <p class="text-text-muted mt-1">Review the projected status changes for APPLIED candidates before executing.</p>
        </div>
        <a href="<?= e(url('hr.screening.shortlist', [$requisition['job_id']])) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to Shortlist
        </a>
    </div>

    <div class="bg-warning-bg border border-warning/30 text-warning-dark p-4 rounded-lg text-sm flex gap-3 items-start">
        <span class="material-symbols-outlined shrink-0">warning</span>
        <div>
            <strong>Warning:</strong> This action will update application statuses. All changes are audited and labeled as simulated triage. Non-APPLIED candidates are ignored.
        </div>
    </div>

    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
        <div class="p-6 border-b border-border-base bg-surface-container-lowest">
            <h2 class="text-lg font-semibold text-primary">Projected Status Changes (<?= count($preview) ?> Candidates)</h2>
        </div>
        
        <?php if (empty($preview)): ?>
            <div class="p-8 text-center text-text-muted">
                <span class="material-symbols-outlined text-[48px] opacity-20 mb-2">check_circle</span>
                <p>No APPLIED candidates found or all candidates require no status change.</p>
            </div>
        <?php else: ?>
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-surface-container-lowest text-text-muted tracking-wider border-b border-border-base">
                    <tr>
                        <th class="px-6 py-3 font-medium">Candidate</th>
                        <th class="px-6 py-3 font-medium text-center">Score</th>
                        <th class="px-6 py-3 font-medium">Projected Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-base">
                    <?php foreach ($preview as $app): ?>
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="px-6 py-3">
                                <div class="font-medium text-primary"><?= e($app['name']) ?></div>
                                <div class="text-xs text-text-muted"><?= e($app['email']) ?></div>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <span class="font-bold <?= (int)$app['match_score'] >= 50 ? 'text-success' : 'text-gray-600' ?>"><?= (int)$app['match_score'] ?></span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded text-xs font-bold <?= $app['target_status'] === 'REJECTED' ? 'bg-error/10 text-error' : 'bg-success-bg text-success' ?>">
                                    <?= e($app['target_status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if (!empty($preview)): ?>
        <form method="POST" action="<?= e(url('hr.screening.triage.execute', [$requisition['job_id']])) ?>" class="flex justify-end gap-3">
            <?= csrf_field() ?>
            <a href="<?= e(url('hr.screening.shortlist', [$requisition['job_id']])) ?>" class="px-5 py-2.5 border border-outline-variant rounded-md shadow-sm text-sm font-medium text-primary bg-white hover:bg-surface-container-highest transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-secondary text-white rounded-md shadow-sm text-sm font-medium hover:bg-blue-800 transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">check_circle</span> Confirm Triage
            </button>
        </form>
    <?php endif; ?>
</div>
