<?php $title = 'Resolve Duplicate Candidate'; ?>
<?php ob_start(); ?>
<?php
    $cA = $suggestion['candidate_a'];
    $cB = $suggestion['candidate_b'];
?>
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Resolve Duplicate Match</h1>
            <p class="text-text-muted mt-1">Review the profiles side-by-side to make a merge decision.</p>
        </div>
        <a href="<?= e(url('hr.screening.duplicates', [$requisition['job_id']])) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to List
        </a>
    </div>

    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="bg-error/10 border border-error text-error p-4 rounded-lg text-sm">
            <strong>Please correct the following errors:</strong>
            <ul class="list-disc pl-5 mt-2 space-y-1">
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Candidate A -->
        <div class="bg-card-surface border border-border-base rounded-xl p-6 shadow-sm">
            <h2 class="text-lg font-bold text-primary border-b border-border-base pb-3 mb-4">Candidate A</h2>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="font-medium text-text-muted text-xs">Name</dt>
                    <dd class="text-primary mt-0.5"><?= e($cA['name']) ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-text-muted text-xs">Email</dt>
                    <dd class="text-primary mt-0.5"><?= e($cA['email']) ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-text-muted text-xs">Phone</dt>
                    <dd class="text-primary mt-0.5"><?= e($cA['phone']) ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-text-muted text-xs">Current Title</dt>
                    <dd class="text-primary mt-0.5"><?= e($cA['current_title'] ?? 'N/A') ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-text-muted text-xs">Experience</dt>
                    <dd class="text-primary mt-0.5"><?= e($cA['years_experience']) ?> years</dd>
                </div>
            </dl>
        </div>

        <!-- Candidate B -->
        <div class="bg-card-surface border border-border-base rounded-xl p-6 shadow-sm">
            <h2 class="text-lg font-bold text-primary border-b border-border-base pb-3 mb-4">Candidate B</h2>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="font-medium text-text-muted text-xs">Name</dt>
                    <dd class="text-primary mt-0.5"><?= e($cB['name']) ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-text-muted text-xs">Email</dt>
                    <dd class="text-primary mt-0.5"><?= e($cB['email']) ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-text-muted text-xs">Phone</dt>
                    <dd class="text-primary mt-0.5"><?= e($cB['phone']) ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-text-muted text-xs">Current Title</dt>
                    <dd class="text-primary mt-0.5"><?= e($cB['current_title'] ?? 'N/A') ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-text-muted text-xs">Experience</dt>
                    <dd class="text-primary mt-0.5"><?= e($cB['years_experience']) ?> years</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Resolution Form -->
    <div class="bg-surface-container-lowest border border-border-base rounded-xl p-6 mt-6 shadow-ambient">
        <h2 class="text-lg font-semibold text-primary border-b border-border-base pb-3 mb-4">Record Decision</h2>
        <form method="POST" action="<?= e(url('hr.screening.duplicates.resolve', [$requisition['job_id']])) ?>" class="space-y-6">
            <?= csrf_field() ?>
            <input type="hidden" name="candidate_a" value="<?= $cA['candidate_id'] ?>">
            <input type="hidden" name="candidate_b" value="<?= $cB['candidate_id'] ?>">
            <input type="hidden" name="confidence" value="<?= e($suggestion['confidence']) ?>">

            <div class="space-y-3">
                <label class="block text-sm font-medium text-primary">Decision Type</label>
                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="decision_type" value="MERGE" class="text-secondary focus:ring-secondary" checked onchange="togglePrimary(true)">
                        <span class="text-sm">Merge Profiles</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="decision_type" value="IGNORE" class="text-secondary focus:ring-secondary" onchange="togglePrimary(false)">
                        <span class="text-sm">Ignore (Not duplicates)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="decision_type" value="DEFER" class="text-secondary focus:ring-secondary" onchange="togglePrimary(false)">
                        <span class="text-sm">Defer (Decide later)</span>
                    </label>
                </div>
            </div>

            <div id="primary-selection" class="space-y-3 bg-blue-50 p-4 rounded border border-blue-100">
                <label class="block text-sm font-medium text-primary">Select Primary Candidate (to keep)</label>
                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="primary_candidate_id" value="<?= $cA['candidate_id'] ?>" class="text-secondary focus:ring-secondary" checked>
                        <span class="text-sm">Candidate A (<?= e($cA['name']) ?>)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="primary_candidate_id" value="<?= $cB['candidate_id'] ?>" class="text-secondary focus:ring-secondary">
                        <span class="text-sm">Candidate B (<?= e($cB['name']) ?>)</span>
                    </label>
                </div>
                <p class="text-xs text-text-muted mt-2">The non-primary candidate's data will be superseded or removed per system rules.</p>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-primary">Reason / Notes <span class="text-error">*</span></label>
                <textarea name="notes" rows="3" class="w-full rounded border border-border-base px-3 py-2 text-sm focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow" placeholder="Explain your decision..." required></textarea>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 bg-secondary text-white rounded-md shadow-sm text-sm font-medium hover:bg-blue-800 transition-colors">
                    Save Decision
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePrimary(show) {
        const el = document.getElementById('primary-selection');
        if (show) {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    }
</script>
<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../../layouts/app.php'; ?>
