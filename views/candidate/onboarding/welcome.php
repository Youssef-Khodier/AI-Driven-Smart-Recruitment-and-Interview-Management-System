<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Welcome to <?= e($onboarding['department_name']) ?>!</h1>
            <p class="text-text-muted mt-2 text-lg">Your position: <strong><?= e($onboarding['job_title']) ?></strong></p>
        </div>
        <a href="<?= e(url('candidate.onboarding.index')) ?>" class="text-info hover:underline text-sm font-medium">&larr; Back</a>
    </div>

    <?php if (isset($_SESSION['flash_status'])): ?>
        <div class="bg-success-bg border border-success text-success px-4 py-3 rounded-lg shadow-sm flex items-center">
            <span class="material-symbols-outlined mr-2">check_circle</span><?= e($_SESSION['flash_status']) ?>
        </div>
    <?php endif; ?>

    <!-- Progress Bar -->
    <?php
        $completedCount = count(array_filter($tasks, fn($t) => $t['completed']));
        $totalCount = count($tasks);
        $progress = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;
    ?>
    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-3">
            <span class="text-primary font-semibold">Onboarding Progress</span>
            <span class="text-sm text-text-muted"><?= $completedCount ?> / <?= $totalCount ?> tasks</span>
        </div>
        <div class="w-full bg-bg-alt rounded-full h-4 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-info to-success transition-all duration-500" style="width: <?= $progress ?>%"></div>
        </div>
        <?php if ($progress === 100): ?>
            <p class="text-success font-medium mt-3 flex items-center">
                <span class="material-symbols-outlined mr-1">celebration</span>
                All tasks complete! You're ready for day one.
            </p>
        <?php endif; ?>
    </div>

    <!-- Offer Summary Card -->
    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-primary mb-4 flex items-center">
            <span class="material-symbols-outlined mr-2 text-info">work</span>Your Offer Summary
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-lg font-bold text-primary"><?= e($onboarding['offer_type'] ?? 'N/A') ?></div>
                <div class="text-xs text-text-muted">Employment Type</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-primary">$<?= number_format((float)($onboarding['ctc'] ?? 0), 0) ?></div>
                <div class="text-xs text-text-muted">Base Salary</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-primary">$<?= number_format((float)($onboarding['bonus'] ?? 0), 0) ?></div>
                <div class="text-xs text-text-muted">Bonus</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-primary"><?= e($onboarding['location'] ?? 'TBD') ?></div>
                <div class="text-xs text-text-muted">Location</div>
            </div>
        </div>
    </div>

    <!-- Task Checklist -->
    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-primary mb-4 flex items-center">
            <span class="material-symbols-outlined mr-2 text-warning">checklist</span>Day-One Document Checklist
        </h2>
        <div class="space-y-3">
            <?php foreach ($tasks as $task): ?>
                <div class="flex items-center justify-between border border-border-base rounded-lg p-4 <?= $task['completed'] ? 'bg-success-bg/20' : 'bg-bg-base' ?>">
                    <div class="flex items-center gap-3">
                        <?php if ($task['completed']): ?>
                            <span class="material-symbols-outlined text-success text-2xl">check_circle</span>
                        <?php else: ?>
                            <span class="material-symbols-outlined text-text-muted text-2xl">radio_button_unchecked</span>
                        <?php endif; ?>
                        <div>
                            <div class="font-medium text-primary <?= $task['completed'] ? 'line-through opacity-60' : '' ?>"><?= e($task['label']) ?></div>
                            <div class="text-xs text-text-muted"><?= e($task['description']) ?></div>
                        </div>
                    </div>
                    <?php if (!$task['completed']): ?>
                        <form method="POST" action="<?= e(url('candidate.onboarding.complete-task', [$onboarding['onboarding_id']])) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="task_key" value="<?= e($task['key']) ?>">
                            <button type="submit" class="bg-primary hover:bg-primary-light text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                                Mark Complete
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
