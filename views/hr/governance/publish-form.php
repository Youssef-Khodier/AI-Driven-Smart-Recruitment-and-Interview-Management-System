<?php $title = 'Publish to Job Boards'; ?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Publish to Job Boards</h1>
            <p class="text-text-muted mt-1">Create local sync log records for <?= e($requisition['title']) ?>.</p>
        </div>
        <a href="<?= e(url('hr.requisitions.show', [$requisition['job_id']])) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to Requisition
        </a>
    </div>

    <?php if ($flash = \App\Core\Session::flashed('error')): ?>
        <div class="bg-error/10 border border-error text-error p-4 rounded-lg text-sm">
            <?= e($flash) ?>
        </div>
    <?php endif; ?>

    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
        <div class="p-6 border-b border-border-base bg-surface-container-lowest">
            <h2 class="text-lg font-semibold text-primary">Local Job-Board Sync Manager</h2>
            <p class="text-sm text-text-muted mt-1">No external APIs are called. Publishing writes database sync records and marks them complete locally.</p>
        </div>

        <div class="p-6">
            <?php if ($requisition['status'] !== 'OPEN'): ?>
                <div class="bg-warning-bg border border-warning/30 text-warning-dark p-4 rounded-lg text-sm">
                    This requisition cannot be published because its status is not OPEN.
                </div>
            <?php else: ?>
                <form action="<?= e(url('hr.requisitions.publish.store', [$requisition['job_id']])) ?>" method="POST" class="space-y-6">
                    <?= csrf_field() ?>
                    <fieldset class="space-y-4">
                        <legend class="text-sm font-semibold text-primary">Select local platforms</legend>
                        <?php foreach ($activePlatforms as $platform): ?>
                            <?php $isPublished = in_array($platform['platform_id'], $publishedPlatforms); ?>
                            <label class="flex items-start gap-3 rounded-lg border border-border-base p-3 <?= $isPublished ? 'bg-surface-container-low text-text-muted' : 'bg-white' ?>">
                                <input
                                    name="platforms[]"
                                    value="<?= e($platform['platform_id']) ?>"
                                    type="checkbox"
                                    class="mt-1 rounded border-border-base text-secondary focus:ring-secondary"
                                    <?= $isPublished ? 'checked disabled' : '' ?>
                                >
                                <span>
                                    <span class="block font-medium text-primary"><?= e($platform['name']) ?></span>
                                    <span class="block text-xs text-text-muted"><?= $isPublished ? 'Already published in local sync history.' : 'A PUBLISHED sync record will be stored locally.' ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                        <?php if (empty($activePlatforms)): ?>
                            <p class="text-text-muted text-sm italic">No active job-board platforms are configured.</p>
                        <?php endif; ?>
                    </fieldset>

                    <div class="flex justify-end gap-3">
                        <a href="<?= e(url('hr.requisitions.sync-history', [$requisition['job_id']])) ?>" class="px-5 py-2.5 border border-outline-variant rounded-md shadow-sm text-sm font-medium text-primary bg-white hover:bg-surface-container-highest transition-colors">View Sync History</a>
                        <button type="submit" class="px-5 py-2.5 bg-secondary text-white rounded-md shadow-sm text-sm font-medium hover:bg-blue-800 transition-colors">Publish</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
