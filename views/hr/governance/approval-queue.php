<div class="max-w-5xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Approval Queue</h1>
            <p class="text-text-muted mt-2 text-lg">Pending job requisitions requiring your approval.</p>
        </div>
        <a href="<?= e(url('hr.dashboard')) ?>" class="text-info hover:underline text-sm font-medium">&larr; Back to Dashboard</a>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="bg-success-bg border border-success text-success px-4 py-3 rounded-lg mb-6 shadow-sm flex items-center">
            <span class="material-symbols-outlined mr-2">check_circle</span>
            <?= e($_SESSION['flash_success']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="bg-error-bg border border-error text-error px-4 py-3 rounded-lg mb-6 shadow-sm flex items-center">
            <span class="material-symbols-outlined mr-2">error</span>
            <?= e($_SESSION['flash_error']) ?>
        </div>
    <?php endif; ?>

    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-bg-alt border-b border-border-base text-text-muted text-sm uppercase tracking-wider">
                    <th class="px-6 py-4 font-semibold">Title</th>
                    <th class="px-6 py-4 font-semibold">Creator</th>
                    <th class="px-6 py-4 font-semibold">Submitted Date</th>
                    <th class="px-6 py-4 font-semibold text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
                <?php foreach ($requisitions as $req): ?>
                    <tr class="hover:bg-bg-alt transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-primary font-medium"><?= e($req['title']) ?></div>
                            <div class="text-sm text-text-muted">Job ID: <?= e($req['job_id']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <span class="material-symbols-outlined text-text-muted mr-2 text-xl">person</span>
                                <span class="text-primary"><?= e($req['creator_name']) ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-text-muted text-sm">
                            <?= e(date('M j, Y, g:i a', strtotime($req['created_at']))) ?>
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <a href="<?= e(url('hr.requisitions.show', ['id' => $req['job_id']])) ?>" class="text-info hover:text-blue-700 font-medium text-sm transition-colors">
                                View Details
                            </a>
                            <a href="<?= e(url('hr.approvals.form', ['id' => $req['job_id']])) ?>" class="bg-primary hover:bg-primary-light text-white px-3 py-1.5 rounded-lg text-sm font-medium transition-colors shadow-sm inline-flex items-center">
                                Review
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($requisitions)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-text-muted">No pending approvals at this time.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
