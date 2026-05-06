<div class="max-w-3xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Add Referral</h1>
            <p class="text-text-muted mt-2 text-lg"><?= e($application['candidate_name']) ?> — <?= e($application['job_title']) ?></p>
        </div>
        <a href="<?= e(url('hr.referrals.index')) ?>" class="text-info hover:underline text-sm font-medium">&larr; Back to Referrals</a>
    </div>

    <?php if ($existingReferral): ?>
        <div class="bg-warning-bg border border-warning text-warning px-4 py-3 rounded-lg shadow-sm">
            A referral already exists for this application (Source: <?= e($existingReferral['referral_source']) ?>).
        </div>
    <?php else: ?>
        <div class="bg-card-surface border border-border-base rounded-xl shadow-sm p-8">
            <form method="POST" action="<?= e(url('hr.referrals.store', [$application['application_id']])) ?>">
                <?= csrf_field() ?>

                <div class="mb-5">
                    <label class="block text-primary font-semibold mb-2">Referral Source *</label>
                    <select name="referral_source" class="w-full border border-border-base rounded-lg px-4 py-3 bg-bg-base text-primary" required>
                        <option value="INTERNAL">Internal Employee</option>
                        <option value="EXTERNAL">External Contact</option>
                        <option value="JOB_BOARD">Job Board</option>
                        <option value="SOCIAL_MEDIA">Social Media</option>
                        <option value="UNIVERSITY">University / Campus</option>
                        <option value="OTHER">Other</option>
                    </select>
                </div>

                <div class="mb-5">
                    <label class="block text-primary font-semibold mb-2">Internal Referrer (optional)</label>
                    <select name="referrer_user_id" class="w-full border border-border-base rounded-lg px-4 py-3 bg-bg-base text-primary">
                        <option value="">— Select employee —</option>
                        <?php foreach ($internalUsers as $u): ?>
                            <option value="<?= e($u['user_id']) ?>"><?= e($u['name']) ?> (<?= e($u['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-primary font-semibold mb-2">External Referrer Name</label>
                        <input name="referrer_name" class="w-full border border-border-base rounded-lg px-4 py-3 bg-bg-base text-primary" placeholder="John Doe">
                    </div>
                    <div>
                        <label class="block text-primary font-semibold mb-2">External Referrer Email</label>
                        <input type="email" name="referrer_email" class="w-full border border-border-base rounded-lg px-4 py-3 bg-bg-base text-primary" placeholder="john@example.com">
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-primary font-semibold mb-2">Reward Amount ($)</label>
                    <input type="number" name="reward_amount" step="0.01" min="0" class="w-full border border-border-base rounded-lg px-4 py-3 bg-bg-base text-primary" placeholder="0.00">
                    <p class="text-xs text-text-muted mt-1">Leave empty if no monetary reward applies.</p>
                </div>

                <div class="mb-6">
                    <label class="block text-primary font-semibold mb-2">Notes</label>
                    <textarea name="notes" rows="3" class="w-full border border-border-base rounded-lg px-4 py-3 bg-bg-base text-primary" placeholder="Any additional notes..."></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-primary hover:bg-primary-light text-white px-6 py-2.5 rounded-lg font-medium transition-colors shadow-sm">Record Referral</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>
