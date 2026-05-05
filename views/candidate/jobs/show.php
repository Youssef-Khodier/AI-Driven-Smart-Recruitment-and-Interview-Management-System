<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
        <div class="px-6 py-5 border-b border-border-base flex justify-between items-center bg-surface-container-low">
            <h1 class="text-3xl font-bold text-primary"><?= e($job['title']) ?></h1>
            <a href="<?= e(url('candidate.jobs.index')) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back
            </a>
        </div>
        <div class="p-6">
            <div class="mb-6 flex items-center gap-2 text-sm text-text-muted bg-surface-container-lowest border border-border-base rounded px-3 py-2 w-fit">
                <span class="material-symbols-outlined text-[18px]">business</span>
                <strong>Department:</strong> <span class="font-medium text-primary"><?= e($job['department_name']) ?></span>
            </div>
            
            <div class="mb-8">
                <div class="text-primary leading-relaxed whitespace-pre-wrap"><?= nl2br(e($job['description'])) ?></div>
            </div>
            
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-primary mb-3 border-b border-border-base pb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary text-[20px]">list_alt</span> Requirements
                </h2>
                <div class="text-text-muted leading-relaxed whitespace-pre-wrap bg-surface-container-low p-4 rounded-lg border border-border-base"><?= nl2br(e($job['requirements'])) ?></div>
            </div>
            
            <div class="pt-6 border-t border-border-base">
                <?php if ($application): ?>
                    <div class="bg-success-bg border border-success/20 text-success px-4 py-4 rounded-lg flex items-center gap-3">
                        <span class="material-symbols-outlined text-2xl">check_circle</span>
                        <div>
                            <p class="font-semibold text-lg">You already applied.</p>
                            <p class="text-sm mt-1">Status: <span class="font-bold underline decoration-success/30 underline-offset-2"><?= e($application['status']) ?></span></p>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?= e(url('candidate.applications.store', [$job['job_id']])) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="bg-secondary-container text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-md flex items-center gap-2 justify-center w-full sm:w-auto">
                            <span class="material-symbols-outlined text-[20px]">send</span> Apply for this job
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
