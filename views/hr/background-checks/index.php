<div class="max-w-5xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Background Checks</h1>
            <p class="text-text-muted mt-2 text-lg"><?= e($application['candidate_name']) ?> — <?= e($application['job_title']) ?></p>
        </div>
        <a href="<?= e(url('hr.applications.index', [$application['job_id'] ?? 0])) ?>" class="text-info hover:underline text-sm font-medium">&larr; Back</a>
    </div>

    <?php if (isset($_SESSION['flash_status'])): ?>
        <div class="bg-success-bg border border-success text-success px-4 py-3 rounded-lg shadow-sm flex items-center">
            <span class="material-symbols-outlined mr-2">check_circle</span><?= e($_SESSION['flash_status']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="bg-error-bg border border-error text-error px-4 py-3 rounded-lg shadow-sm flex items-center">
            <span class="material-symbols-outlined mr-2">error</span><?= e($_SESSION['flash_error']) ?>
        </div>
    <?php endif; ?>

    <!-- Status Badge -->
    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm p-6 flex items-center justify-between">
        <div>
            <span class="text-lg font-semibold text-primary">All Checks Passed:</span>
        </div>
        <span class="px-4 py-2 rounded-lg text-sm font-bold <?= $allPassed ? 'bg-success-bg text-success' : 'bg-warning-bg text-warning' ?>">
            <?= $allPassed ? '✓ YES' : '✗ NOT YET' ?>
        </span>
    </div>

    <!-- Request New Check -->
    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-primary mb-4">Request New Check</h2>
        <form method="POST" action="<?= e(url('hr.background-checks.request', [$application['application_id']])) ?>" class="flex gap-4 items-end">
            <?= csrf_field() ?>
            <div class="flex-1">
                <label class="block text-sm font-medium text-primary mb-1">Check Type</label>
                <select name="check_type" class="w-full border border-border-base rounded-lg px-4 py-3 bg-bg-base text-primary" required>
                    <?php foreach ($checkTypes as $type): ?>
                        <option value="<?= e($type) ?>"><?= e(str_replace('_', ' ', $type)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="bg-primary hover:bg-primary-light text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm">Request</button>
        </form>
    </div>

    <!-- Existing Checks -->
    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-bg-alt border-b border-border-base text-text-muted text-sm uppercase tracking-wider">
                    <th class="px-6 py-3 font-semibold">Type</th>
                    <th class="px-6 py-3 font-semibold">Status</th>
                    <th class="px-6 py-3 font-semibold">Requested</th>
                    <th class="px-6 py-3 font-semibold">Notes</th>
                    <th class="px-6 py-3 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
                <?php foreach ($checks as $check): ?>
                    <tr class="hover:bg-bg-alt transition-colors">
                        <td class="px-6 py-4 text-primary font-medium text-sm"><?= e(str_replace('_', ' ', $check['check_type'])) ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs font-medium
                                <?= match($check['status']) {
                                    'PASSED' => 'bg-success-bg text-success',
                                    'FAILED' => 'bg-error-bg text-error',
                                    'IN_PROGRESS' => 'bg-info/10 text-info',
                                    'CANCELLED' => 'bg-bg-alt text-text-muted',
                                    default => 'bg-warning-bg text-warning'
                                } ?>">
                                <?= e($check['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-text-muted text-sm"><?= e(date('M j, Y', strtotime($check['requested_at']))) ?><br><span class="text-xs">by <?= e($check['requested_by_name']) ?></span></td>
                        <td class="px-6 py-4 text-text-muted text-sm"><?= e($check['result_notes'] ?? '—') ?></td>
                        <td class="px-6 py-4 text-right space-x-1">
                            <?php if ($check['status'] === 'REQUESTED'): ?>
                                <form method="POST" action="<?= e(url('hr.background-checks.in-progress', [$check['background_check_id']])) ?>" class="inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="bg-info hover:bg-blue-600 text-white px-2 py-1 rounded text-xs font-medium">Start</button>
                                </form>
                            <?php endif; ?>
                            <?php if (in_array($check['status'], ['REQUESTED', 'IN_PROGRESS'])): ?>
                                <form method="POST" action="<?= e(url('hr.background-checks.complete', [$check['background_check_id']])) ?>" class="inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="result" value="PASSED">
                                    <button type="submit" class="bg-success hover:bg-green-600 text-white px-2 py-1 rounded text-xs font-medium">Pass</button>
                                </form>
                                <form method="POST" action="<?= e(url('hr.background-checks.complete', [$check['background_check_id']])) ?>" class="inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="result" value="FAILED">
                                    <button type="submit" class="bg-error hover:bg-red-600 text-white px-2 py-1 rounded text-xs font-medium">Fail</button>
                                </form>
                                <form method="POST" action="<?= e(url('hr.background-checks.cancel', [$check['background_check_id']])) ?>" class="inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="bg-bg-alt hover:bg-gray-300 text-text-muted px-2 py-1 rounded text-xs font-medium">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($checks)): ?>
                    <tr><td colspan="5" class="px-6 py-8 text-center text-text-muted">No background checks requested yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
