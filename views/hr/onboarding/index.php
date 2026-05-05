<div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
    <div class="px-6 py-5 border-b border-border-base flex justify-between items-center bg-surface-container-low">
        <h1 class="text-2xl font-bold text-primary flex items-center gap-2">
            <span class="material-symbols-outlined text-purple-700 text-[28px]">how_to_reg</span>
            Onboarding
        </h1>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-lowest text-text-muted text-xs uppercase tracking-wider border-b border-border-base">
                <tr>
                    <th class="px-6 py-3 font-medium">Candidate</th>
                    <th class="px-6 py-3 font-medium">Job</th>
                    <th class="px-6 py-3 font-medium">Offer Status</th>
                    <th class="px-6 py-3 font-medium">Onboarding Status</th>
                    <th class="px-6 py-3 font-medium">Start Date</th>
                    <th class="px-6 py-3 font-medium text-center">Docs Completed</th>
                    <th class="px-6 py-3 font-medium text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base text-sm">
                <?php foreach ($records as $record): ?>
                    <tr class="hover:bg-surface-container-lowest transition-colors">
                        <td class="px-6 py-4 font-medium text-primary"><?= e($record['candidate_name']) ?></td>
                        <td class="px-6 py-4 text-text-muted"><?= e($record['job_title']) ?></td>
                        <td class="px-6 py-4 text-text-muted"><?= e($record['offer_type']) ?></td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold tracking-wide border <?= $record['status'] === 'COMPLETED' ? 'bg-success-bg text-success border-success/20' : ($record['status'] === 'TERMINATED' ? 'bg-error-bg text-error border-error/20' : 'bg-blue-50 text-blue-800 border-blue-200') ?>">
                                <?= e($record['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium <?= empty($record['start_date']) ? 'text-text-muted italic' : 'text-primary' ?>">
                            <?= e($record['start_date'] ?? 'TBD') ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if ($record['documents_completed']): ?>
                                <span class="material-symbols-outlined text-success text-[20px]">check_circle</span>
                            <?php else: ?>
                                <span class="text-text-muted font-medium">No</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a class="inline-flex items-center justify-center px-3 py-1.5 border border-outline-variant rounded-md shadow-sm text-xs font-medium text-primary bg-white hover:bg-surface-container-low transition-colors" href="<?= e(url('hr.onboarding.show', [$record['onboarding_id']])) ?>">
                                Manage
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($records)): ?>
                    <tr><td colspan="7" class="px-6 py-8 text-center text-text-muted">No onboarding records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
