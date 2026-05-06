<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">My Onboarding</h1>
            <p class="text-text-muted mt-2 text-lg">Welcome! Complete your pre-onboarding tasks below.</p>
        </div>
        <a href="<?= e(url('candidate.dashboard')) ?>" class="text-info hover:underline text-sm font-medium">&larr; Back to Dashboard</a>
    </div>

    <?php if (empty($onboardings)): ?>
        <div class="bg-card-surface border border-border-base rounded-xl shadow-sm p-8 text-center">
            <span class="material-symbols-outlined text-6xl text-text-muted mb-4">assignment</span>
            <p class="text-text-muted text-lg">No onboarding tasks available yet.</p>
            <p class="text-text-muted text-sm mt-2">Once you accept an offer, your onboarding tasks will appear here.</p>
        </div>
    <?php else: ?>
        <?php foreach ($onboardings as $ob): ?>
            <a href="<?= e(url('candidate.onboarding.show', [$ob['onboarding_id']])) ?>" class="block bg-card-surface border border-border-base rounded-xl shadow-sm p-6 hover:border-info transition-colors">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-xl font-semibold text-primary"><?= e($ob['job_title']) ?></h2>
                        <p class="text-text-muted text-sm mt-1"><?= e($ob['department_name']) ?></p>
                        <p class="text-sm text-text-muted mt-2">Status: <span class="font-medium text-primary"><?= e($ob['status'] ?? 'PENDING') ?></span></p>
                    </div>
                    <span class="material-symbols-outlined text-info text-3xl">arrow_forward</span>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
