<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-primary flex flex-col sm:flex-row sm:items-center gap-2">
            <span class="material-symbols-outlined text-purple-700 text-[32px] hidden sm:block">how_to_reg</span>
            <span>Onboarding: <span class="text-secondary"><?= e($record['candidate_name']) ?></span> for <?= e($record['job_title']) ?></span>
        </h1>
        <a href="<?= e(url('hr.onboarding.index')) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1 shrink-0">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-surface-container-low p-4 rounded-lg border border-border-base">
            <span class="text-text-muted text-xs uppercase tracking-wider block mb-1">Candidate</span>
            <span class="font-medium text-primary block"><?= e($record['candidate_name']) ?></span>
        </div>
        <div class="bg-surface-container-low p-4 rounded-lg border border-border-base">
            <span class="text-text-muted text-xs uppercase tracking-wider block mb-1">Job</span>
            <span class="font-medium text-primary block"><?= e($record['job_title']) ?></span>
        </div>
        <div class="bg-surface-container-low p-4 rounded-lg border border-border-base">
            <span class="text-text-muted text-xs uppercase tracking-wider block mb-1">Offer Type</span>
            <span class="font-medium text-primary block"><?= e($record['offer_type']) ?></span>
        </div>
    </div>

    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 md:p-8 mt-6">
        <h2 class="text-xl font-semibold text-primary mb-6 border-b border-border-base pb-2 flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary">manage_accounts</span> Manage Onboarding
        </h2>
        
        <?php include __DIR__ . '/form.php'; ?>
    </div>
</div>
