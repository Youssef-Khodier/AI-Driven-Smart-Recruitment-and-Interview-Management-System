<?php $title = 'Simulated Triage Results'; ?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Simulated Triage Results</h1>
            <p class="text-text-muted mt-1">Automated status updates completed for <?= e($requisition['title']) ?>.</p>
        </div>
        <a href="<?= e(url('hr.screening.shortlist', [$requisition['job_id']])) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">view_list</span> Back to Shortlist
        </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php foreach (['SCREENING', 'ASSESSMENT', 'INTERVIEW', 'REJECTED'] as $status): ?>
            <div class="bg-card-surface border border-border-base rounded-xl p-4 flex flex-col items-center justify-center text-center shadow-sm">
                <span class="text-3xl font-bold <?= $status === 'REJECTED' ? 'text-error' : 'text-primary' ?>">
                    <?= (int) ($results['summary'][$status] ?? 0) ?>
                </span>
                <span class="text-xs font-semibold text-text-muted tracking-wide mt-1 uppercase"><?= $status ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden mt-6">
        <div class="p-6 border-b border-border-base bg-surface-container-lowest">
            <h2 class="text-lg font-semibold text-primary">Candidate Detail</h2>
        </div>
        
        <?php if (empty($results['changes'])): ?>
            <div class="p-8 text-center text-text-muted">
                <p>No status changes were made.</p>
            </div>
        <?php else: ?>
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-surface-container-lowest text-text-muted tracking-wider border-b border-border-base">
                    <tr>
                        <th class="px-6 py-3 font-medium">Application ID</th>
                        <th class="px-6 py-3 font-medium text-center">Score</th>
                        <th class="px-6 py-3 font-medium">Transition</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-base">
                    <?php foreach ($results['changes'] as $change): ?>
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="px-6 py-3 font-medium text-primary">#<?= $change['application_id'] ?></td>
                            <td class="px-6 py-3 text-center font-bold text-primary"><?= $change['score'] ?></td>
                            <td class="px-6 py-3">
                                <span class="text-text-muted"><?= e($change['old_status']) ?></span>
                                <span class="material-symbols-outlined text-[14px] align-middle mx-1">arrow_forward</span>
                                <span class="font-bold <?= $change['new_status'] === 'REJECTED' ? 'text-error' : 'text-success' ?>"><?= e($change['new_status']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
