<div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
    <div class="px-6 py-5 border-b border-border-base flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-surface-container-low">
        <div>
            <h1 class="text-2xl font-bold text-primary flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary">quiz</span>
                Assessments for <?= e($requisition['title']) ?>
            </h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <a class="bg-white border border-outline-variant text-primary px-4 py-2 rounded-md hover:bg-surface-container-highest transition-colors font-medium flex items-center gap-2 text-sm shadow-sm" href="<?= e(url('hr.assessment-results.index', [$requisition['job_id']])) ?>">
                <span class="material-symbols-outlined text-[18px]">analytics</span> Review results
            </a>
            <a class="bg-secondary-container text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors font-medium flex items-center gap-2 text-sm shadow-sm" href="<?= e(url('hr.assessments.create', [$requisition['job_id']])) ?>">
                <span class="material-symbols-outlined text-[18px]">add</span> Create assessment
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-lowest text-text-muted text-xs uppercase tracking-wider border-b border-border-base">
                <tr>
                    <th class="px-6 py-3 font-medium">Title</th>
                    <th class="px-6 py-3 font-medium">Type</th>
                    <th class="px-6 py-3 font-medium">Duration</th>
                    <th class="px-6 py-3 font-medium">Active</th>
                    <th class="px-6 py-3 font-medium text-center">Questions</th>
                    <th class="px-6 py-3 font-medium text-center">Attempts</th>
                    <th class="px-6 py-3 font-medium text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
            <?php foreach ($assessments as $assessment): ?>
                <tr class="hover:bg-surface-container-lowest transition-colors">
                    <td class="px-6 py-4 font-medium text-primary"><?= e($assessment['title']) ?></td>
                    <td class="px-6 py-4 text-text-muted"><?= e($assessment['type']) ?></td>
                    <td class="px-6 py-4 text-text-muted whitespace-nowrap"><?= e($assessment['duration_minutes']) ?> min</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $assessment['is_active'] ? 'bg-success-bg text-success border border-success/20' : 'bg-gray-100 text-gray-600 border border-gray-200' ?>">
                            <?= $assessment['is_active'] ? 'Yes' : 'No' ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-primary text-center font-medium"><?= e($assessment['question_count']) ?></td>
                    <td class="px-6 py-4 text-primary text-center font-medium"><?= e($assessment['attempt_count']) ?></td>
                    <td class="px-6 py-4 text-right">
                        <a class="inline-flex items-center justify-center px-3 py-1.5 border border-outline-variant rounded-md shadow-sm text-xs font-medium text-primary bg-white hover:bg-surface-container-low transition-colors gap-1" href="<?= e(url('hr.assessments.show', [$assessment['assessment_id']])) ?>">
                            <span class="material-symbols-outlined text-[16px]">visibility</span> Open
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($assessments)): ?>
                <tr><td colspan="7" class="px-6 py-8 text-center text-text-muted">No assessments configured for this requisition.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
