<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-primary"><?= e($title) ?></h1>
        <a href="<?= e(url('hr.interviews.show', [$interview['interview_id']])) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back
        </a>
    </div>

    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-text-muted block mb-1">Requested By</span>
                <span class="font-medium text-primary"><?= e($extension['requested_by_name']) ?></span>
            </div>
            <div>
                <span class="text-text-muted block mb-1">Status</span>
                <span class="font-medium text-primary"><?= e($extension['status']) ?></span>
            </div>
            <div>
                <span class="text-text-muted block mb-1">Requested Minutes</span>
                <span class="font-medium text-primary"><?= e($extension['requested_minutes']) ?></span>
            </div>
            <div>
                <span class="text-text-muted block mb-1">Requested At</span>
                <span class="font-medium text-primary"><?= e(date('M d, Y H:i', strtotime($extension['requested_at']))) ?></span>
            </div>
        </div>
        <div>
            <span class="text-text-muted block mb-1 text-sm">Reason</span>
            <p class="text-primary bg-surface-container-low p-3 rounded border border-border-base"><?= nl2br(e($extension['request_reason'])) ?></p>
        </div>
    </div>

    <?php if ($extension['status'] === \App\Enums\InterviewExtensionStatus::PENDING->value): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <form method="POST" action="<?= e(url('hr.interviews.extensions.approve', [$interview['interview_id'], $extension['extension_request_id']])) ?>" class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 space-y-4">
                <?= csrf_field() ?>
                <h2 class="text-lg font-semibold text-primary">Approve</h2>
                <div>
                    <label class="block text-sm font-medium text-primary mb-1">Approved Minutes</label>
                    <input type="number" name="approved_minutes" min="1" value="<?= e($extension['requested_minutes']) ?>" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-primary mb-1">Decision Reason</label>
                    <textarea name="decision_reason" rows="4" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">Approved for session continuity.</textarea>
                </div>
                <button class="w-full bg-secondary-container text-white px-4 py-2 rounded-md hover:bg-blue-700 font-medium">Approve Extension</button>
            </form>

            <form method="POST" action="<?= e(url('hr.interviews.extensions.deny', [$interview['interview_id'], $extension['extension_request_id']])) ?>" class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 space-y-4">
                <?= csrf_field() ?>
                <h2 class="text-lg font-semibold text-primary">Deny</h2>
                <div>
                    <label class="block text-sm font-medium text-primary mb-1">Decision Reason</label>
                    <textarea name="decision_reason" rows="7" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm"></textarea>
                </div>
                <button class="w-full bg-white text-error border border-error/30 px-4 py-2 rounded-md hover:bg-error-bg font-medium">Deny Extension</button>
            </form>
        </div>
    <?php else: ?>
        <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 text-sm">
            <span class="text-text-muted block mb-1">Decision</span>
            <p class="text-primary"><?= e($extension['decision_reason'] ?? 'No decision note.') ?></p>
        </div>
    <?php endif; ?>
</div>
