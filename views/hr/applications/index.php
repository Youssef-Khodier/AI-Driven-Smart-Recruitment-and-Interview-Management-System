<div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
    <div class="px-6 py-5 border-b border-border-base flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-container-low">
        <h1 class="text-2xl font-bold text-primary flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary">group</span>
            Applications for <?= e($requisition['title']) ?>
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
                    <th class="px-6 py-3 font-medium">Current Status</th>
                    <th class="px-6 py-3 font-medium">Simulated Match</th>
                    <th class="px-6 py-3 font-medium">Update Status & Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
            <?php foreach ($applications as $application): ?>
                <tr class="hover:bg-surface-container-lowest transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-medium text-primary"><?= e($application['name']) ?></div>
                        <div class="text-sm text-text-muted"><?= e($application['email']) ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border border-blue-200 bg-blue-50 text-blue-800">
                            <?= e($application['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <span class="text-primary font-bold"><?= e($application['match_score']) ?>%</span>
                            <div class="w-16 h-2 bg-surface-container-high rounded-full overflow-hidden">
                                <div class="bg-secondary h-full" style="width: <?= e($application['match_score']) ?>%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <form method="POST" action="<?= e(url('hr.applications.update', [$application['application_id']])) ?>" class="flex flex-col sm:flex-row items-start sm:items-center gap-2 mb-3">
                            <?= csrf_field() ?><?= method_field('PUT') ?>
                            <select name="status" class="border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary text-sm py-1.5 w-full sm:w-auto">
                                <?php foreach (['APPLIED','SCREENING','ASSESSMENT','INTERVIEW','OFFER','REJECTED','HIRED'] as $status): ?>
                                    <option value="<?= e($status) ?>"<?= selected($application['status'], $status) ?>><?= e($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input name="reason" placeholder="Reason (optional)" class="border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary text-sm py-1.5 w-full sm:w-48">
                            <button type="submit" class="bg-white border border-outline-variant text-primary px-3 py-1.5 rounded-md hover:bg-surface-container-highest transition-colors text-xs font-medium whitespace-nowrap shadow-sm">
                                Update
                            </button>
                        </form>
                        
                        <div class="flex flex-wrap gap-2 text-sm mt-2">
                            <?php if ($application['status'] === 'INTERVIEW'): ?>
                                <a href="<?= e(url('hr.interviews.create', [$application['application_id']])) ?>" class="text-secondary hover:text-blue-800 flex items-center gap-1 font-medium bg-secondary/10 px-2 py-1 rounded">
                                    <span class="material-symbols-outlined text-[16px]">event</span> Schedule interview
                                </a>
                            <?php endif; ?>
                            <a href="<?= e(url('hr.evaluations.show', [$application['application_id']])) ?>" class="text-purple-700 hover:text-purple-900 flex items-center gap-1 font-medium bg-purple-100 px-2 py-1 rounded">
                                <span class="material-symbols-outlined text-[16px]">gavel</span> Final Evaluation
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($applications)): ?>
                <tr><td colspan="4" class="px-6 py-8 text-center text-text-muted">No applications found for this requisition.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
