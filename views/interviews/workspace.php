<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary"><?= e($title) ?></h1>
            <p class="text-sm text-text-muted mt-1">
                <?= e($interview['candidate_name'] ?? 'Candidate') ?> - <?= e($interview['job_title'] ?? 'Interview') ?>
            </p>
        </div>
        <a href="<?= e($backRoute) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back
        </a>
    </div>

    <?php if (!$canSave): ?>
        <div class="bg-info-bg border border-info/20 text-info px-4 py-3 rounded-lg flex items-center gap-3">
            <span class="material-symbols-outlined">visibility</span>
            <p class="text-sm font-medium">Observer access is view-only. Shadowing changes are not saved and do not affect final evaluation.</p>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= e($saveRoute) ?>" class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 space-y-5">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm font-medium text-primary mb-1">Prompt</label>
            <textarea name="prompt_text" rows="4" <?= $canSave ? '' : 'readonly' ?> class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary font-mono text-sm"><?= e(old('prompt_text', $workspace['prompt_text'] ?? '')) ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-primary mb-1">Code Snapshot</label>
            <textarea name="code_text" rows="14" <?= $canSave ? '' : 'readonly' ?> class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary font-mono text-sm"><?= e(old('code_text', $workspace['code_text'] ?? '')) ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Candidate Run Notes</label>
                <textarea name="candidate_run_notes" rows="5" <?= $canSave ? '' : 'readonly' ?> class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary text-sm"><?= e(old('candidate_run_notes', $workspace['candidate_run_notes'] ?? '')) ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Interviewer Notes</label>
                <textarea name="interviewer_notes" rows="5" <?= $canSave ? '' : 'readonly' ?> class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary text-sm"><?= e(old('interviewer_notes', $workspace['interviewer_notes'] ?? '')) ?></textarea>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between gap-3 border-t border-border-base pt-4 text-sm text-text-muted">
            <span>Version <?= e($workspace['version_number'] ?? 0) ?><?= !empty($workspace['last_saved_by_name']) ? ' saved by ' . e($workspace['last_saved_by_name']) : '' ?></span>
            <?php if ($canSave): ?>
                <button type="submit" class="bg-secondary-container text-white px-5 py-2 rounded-md hover:bg-blue-700 transition-colors font-medium flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">save</span> Save Snapshot
                </button>
            <?php endif; ?>
        </div>
    </form>

    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
        <div class="px-6 py-4 border-b border-border-base">
            <h2 class="text-lg font-semibold text-primary">Workspace History</h2>
        </div>
        <div class="divide-y divide-border-base">
            <?php foreach ($history as $record): ?>
                <div class="px-6 py-3 text-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                    <span class="text-primary font-medium">v<?= e($record['new_version_number']) ?> by <?= e($record['actor_name']) ?></span>
                    <span class="text-text-muted"><?= e($record['changed_section']) ?> - <?= e(date('M d, H:i', strtotime($record['created_at']))) ?></span>
                </div>
            <?php endforeach; ?>
            <?php if (empty($history)): ?>
                <div class="px-6 py-6 text-center text-text-muted text-sm italic">No snapshots saved yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
