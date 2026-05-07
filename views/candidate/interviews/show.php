<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6">
        <h1 class="text-3xl font-bold text-primary mb-4"><?= e($title) ?></h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-text-muted block mb-1">Job</span>
                <span class="font-medium text-primary"><?= e($interview['job_title']) ?></span>
            </div>
            <div>
                <span class="text-text-muted block mb-1">Scheduled</span>
                <span class="font-medium text-primary"><?= e(date('M d, Y H:i', strtotime($interview['scheduled_at']))) ?></span>
            </div>
            <div>
                <span class="text-text-muted block mb-1">Duration</span>
                <span class="font-medium text-primary"><?= e((int)$interview['duration_minutes'] + (int)($interview['extended_duration_minutes'] ?? 0)) ?> minutes</span>
            </div>
            <div>
                <span class="text-text-muted block mb-1">Status</span>
                <span class="font-medium text-primary"><?= e($interview['status']) ?></span>
            </div>
        </div>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="<?= e(url('candidate.interviews.workspace', [$interview['interview_id']])) ?>" class="bg-secondary-container text-white px-5 py-2 rounded-md hover:bg-blue-700 font-medium flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">code</span> Open Coding Workspace
            </a>
            <a href="<?= e(url('candidate.interviews.sentiment.create', [$interview['interview_id']])) ?>" class="bg-surface-container-low hover:bg-surface-container-highest text-primary border border-border-base px-5 py-2 rounded-md font-medium flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">sentiment_satisfied</span> Interview Sentiment
            </a>
        </div>
    </div>
</div>
