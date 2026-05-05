<div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
    <div class="px-6 py-5 border-b border-border-base flex justify-between items-center">
        <h1 class="text-2xl font-bold text-primary">My Applications</h1>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low text-text-muted text-sm uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4 font-medium border-b border-border-base">Job</th>
                    <th class="px-6 py-4 font-medium border-b border-border-base">Status</th>
                    <th class="px-6 py-4 font-medium border-b border-border-base">Match</th>
                    <th class="px-6 py-4 font-medium border-b border-border-base">Applied</th>
                    <th class="px-6 py-4 font-medium border-b border-border-base text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
            <?php foreach ($applications as $application): ?>
                <tr class="hover:bg-surface-container-lowest transition-colors">
                    <td class="px-6 py-4 font-medium text-primary"><?= e($application['title']) ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <?= e($application['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-text-muted"><?= e($application['match_score']) ?>%</td>
                    <td class="px-6 py-4 text-text-muted"><?= e(date('M d, Y', strtotime($application['applied_at']))) ?></td>
                    <td class="px-6 py-4 text-right">
                        <a class="inline-flex items-center gap-1 text-secondary hover:text-blue-800 font-medium" href="<?= e(url('candidate.applications.show', [$application['application_id']])) ?>">
                            View <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($applications)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-text-muted">You have not applied for any jobs yet.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
