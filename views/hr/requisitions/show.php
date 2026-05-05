<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary flex items-center gap-3">
                <?= e($requisition['title']) ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold tracking-wide <?= $requisition['status'] === 'OPEN' ? 'bg-success-bg text-success border border-success/20' : ($requisition['status'] === 'DRAFT' ? 'bg-gray-100 text-gray-800 border border-gray-200' : 'bg-blue-100 text-blue-800 border border-blue-200') ?>">
                    <?= e($requisition['status']) ?>
                </span>
            </h1>
            <div class="text-sm text-text-muted mt-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">business</span> <strong>Department:</strong> <?= e($requisition['department_name']) ?>
            </div>
        </div>
        <a href="<?= e(url('hr.requisitions.index')) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1 shrink-0">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to List
        </a>
    </div>

    <!-- Management Actions -->
    <div class="bg-surface-container-low border border-border-base rounded-lg p-4 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap gap-2">
            <a class="inline-flex items-center justify-center px-4 py-2 border border-outline-variant rounded-md shadow-sm text-sm font-medium text-primary bg-white hover:bg-surface-container-highest transition-colors gap-2" href="<?= e(url('hr.requisitions.edit', [$requisition['job_id']])) ?>">
                <span class="material-symbols-outlined text-[18px]">edit</span> Edit
            </a>
            <a class="inline-flex items-center justify-center px-4 py-2 border border-outline-variant rounded-md shadow-sm text-sm font-medium text-primary bg-white hover:bg-surface-container-highest transition-colors gap-2" href="<?= e(url('hr.applications.index', [$requisition['job_id']])) ?>">
                <span class="material-symbols-outlined text-[18px]">group</span> Review applicants
            </a>
            <a class="inline-flex items-center justify-center px-4 py-2 border border-outline-variant rounded-md shadow-sm text-sm font-medium text-primary bg-white hover:bg-surface-container-highest transition-colors gap-2" href="<?= e(url('hr.assessments.index', [$requisition['job_id']])) ?>">
                <span class="material-symbols-outlined text-[18px]">quiz</span> Manage assessments
            </a>
            <a class="inline-flex items-center justify-center px-4 py-2 border border-outline-variant rounded-md shadow-sm text-sm font-medium text-primary bg-white hover:bg-surface-container-highest transition-colors gap-2" href="<?= e(url('hr.assessment-results.index', [$requisition['job_id']])) ?>">
                <span class="material-symbols-outlined text-[18px]">analytics</span> Assessment results
            </a>
        </div>
        <div class="flex flex-wrap gap-2 border-t sm:border-t-0 sm:border-l border-border-base pt-3 sm:pt-0 sm:pl-4 w-full sm:w-auto">
            <?php foreach ([['submit','Submit for approval'],['approve','Approve'],['open','Open'],['close','Close']] as [$action,$label]): ?>
                <form class="inline m-0" method="POST" action="<?= e(url('hr.requisitions.' . $action, [$requisition['job_id']])) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 border border-transparent rounded text-xs font-medium text-white <?= $action === 'close' ? 'bg-error hover:bg-red-700' : 'bg-secondary hover:bg-blue-800' ?> transition-colors shadow-sm">
                        <?= e($label) ?>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Details -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6">
                <h2 class="text-lg font-semibold text-primary mb-3 border-b border-border-base pb-2">Description</h2>
                <div class="text-primary leading-relaxed whitespace-pre-wrap"><?= nl2br(e($requisition['description'])) ?></div>
                
                <h2 class="text-lg font-semibold text-primary mt-6 mb-3 border-b border-border-base pb-2">Requirements</h2>
                <div class="text-primary leading-relaxed whitespace-pre-wrap"><?= nl2br(e($requisition['requirements'])) ?></div>
            </div>
            
            <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
                <div class="p-6 border-b border-border-base">
                    <h2 class="text-lg font-semibold text-primary m-0 flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary">history</span> Status History
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-surface-container-lowest text-text-muted uppercase tracking-wider border-b border-border-base">
                            <tr>
                                <th class="px-6 py-3 font-medium">From</th>
                                <th class="px-6 py-3 font-medium">To</th>
                                <th class="px-6 py-3 font-medium">By</th>
                                <th class="px-6 py-3 font-medium text-right">When</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-base">
                            <?php foreach ($history as $row): ?>
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="px-6 py-3 text-text-muted"><?= e($row['old_status'] ?? '-') ?></td>
                                <td class="px-6 py-3 font-medium text-primary"><?= e($row['new_status']) ?></td>
                                <td class="px-6 py-3 text-primary"><?= e($row['actor_name']) ?></td>
                                <td class="px-6 py-3 text-text-muted text-right whitespace-nowrap"><?= e(date('M d, Y H:i', strtotime($row['created_at']))) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($history)): ?>
                                <tr><td colspan="4" class="px-6 py-4 text-center text-text-muted italic">No history recorded.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Side Info -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6">
                <h2 class="text-lg font-semibold text-primary mb-4 border-b border-border-base pb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary">quiz</span> Active Assessments
                </h2>
                <?php if (empty($assessments)): ?>
                    <p class="text-text-muted text-sm italic">No assessments linked.</p>
                <?php else: ?>
                    <ul class="space-y-3">
                        <?php foreach ($assessments as $assessment): ?>
                            <li class="flex items-center justify-between bg-surface-container-lowest p-3 rounded border border-border-base">
                                <a href="<?= e(url('hr.assessments.show', [$assessment['assessment_id']])) ?>" class="text-secondary hover:underline font-medium text-sm truncate pr-2">
                                    <?= e($assessment['title']) ?>
                                </a>
                                <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium uppercase tracking-wide <?= $assessment['is_active'] ? 'bg-success-bg text-success' : 'bg-gray-100 text-gray-500' ?>">
                                    <?= $assessment['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
