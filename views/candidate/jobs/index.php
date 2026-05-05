<div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
    <div class="px-6 py-5 border-b border-border-base flex justify-between items-center">
        <h1 class="text-2xl font-bold text-primary">Open Jobs</h1>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low text-text-muted text-sm uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4 font-medium border-b border-border-base">Title</th>
                    <th class="px-6 py-4 font-medium border-b border-border-base">Department</th>
                    <th class="px-6 py-4 font-medium border-b border-border-base">Description</th>
                    <th class="px-6 py-4 font-medium border-b border-border-base text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
            <?php foreach ($jobs as $job): ?>
                <tr class="hover:bg-surface-container-lowest transition-colors">
                    <td class="px-6 py-4 font-medium text-primary"><?= e($job['title']) ?></td>
                    <td class="px-6 py-4 text-text-muted"><?= e($job['department_name']) ?></td>
                    <td class="px-6 py-4 text-text-muted max-w-md truncate"><?= e(str_limit($job['description'])) ?></td>
                    <td class="px-6 py-4 text-right">
                        <a class="inline-flex items-center gap-1 text-secondary hover:text-blue-800 font-medium" href="<?= e(url('candidate.jobs.show', [$job['job_id']])) ?>">
                            View <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($jobs)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-text-muted">No open jobs available at the moment.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
