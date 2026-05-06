<?php $title = 'Configure Screening Rules'; ?>
<?php ob_start(); ?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Simulated Screening Configuration</h1>
            <p class="text-text-muted mt-1">Configure skills and triage thresholds for <?= e($requisition['title']) ?>.</p>
        </div>
        <a href="<?= e(url('hr.requisitions.show', [$requisition['job_id']])) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to Requisition
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

    <?php if ($flash = \App\Core\Session::getFlash('success')): ?>
        <div class="bg-success-bg border border-success/30 text-success-dark p-4 rounded-lg text-sm mb-4">
            <?= e($flash) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= e(url('hr.screening.config.store', [$requisition['job_id']])) ?>" class="space-y-8">
        <?= csrf_field() ?>
        
        <!-- Skills Section -->
        <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
            <div class="p-6 border-b border-border-base bg-surface-container-lowest">
                <h2 class="text-lg font-semibold text-primary">Required Skills & Weights</h2>
                <p class="text-sm text-text-muted">Define the skills required for this job and their relative importance. Total weight must equal 100%.</p>
            </div>
            <div class="p-6 space-y-4" id="skills-container">
                <?php 
                $oldInput = \App\Core\Session::getFlash('old_input');
                $oldSkills = $oldInput['skills'] ?? $skills;
                if (empty($oldSkills)) {
                    $oldSkills = [['skill_name' => '', 'weight' => '', 'evidence_field' => 'skill_keywords']];
                }
                foreach ($oldSkills as $i => $skill): 
                ?>
                <div class="flex gap-4 items-start skill-row">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-text-muted mb-1">Skill Name</label>
                        <input type="text" name="skills[<?= $i ?>][skill_name]" value="<?= e($skill['skill_name'] ?? '') ?>" class="w-full rounded border border-border-base px-3 py-2 text-sm focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow" placeholder="e.g. PHP">
                    </div>
                    <div class="w-24">
                        <label class="block text-xs font-medium text-text-muted mb-1">Weight (%)</label>
                        <input type="number" name="skills[<?= $i ?>][weight]" value="<?= e($skill['weight'] ?? '') ?>" class="w-full rounded border border-border-base px-3 py-2 text-sm focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow weight-input" min="1" max="100" step="0.01">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-text-muted mb-1">Evidence Field</label>
                        <select name="skills[<?= $i ?>][evidence_field]" class="w-full rounded border border-border-base px-3 py-2 text-sm focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow">
                            <option value="skill_keywords" <?= ($skill['evidence_field'] ?? '') === 'skill_keywords' ? 'selected' : '' ?>>Anywhere (Title, Keywords, Resume)</option>
                            <option value="current_title" <?= ($skill['evidence_field'] ?? '') === 'current_title' ? 'selected' : '' ?>>Current Title Only</option>
                        </select>
                    </div>
                    <div class="pt-6">
                        <button type="button" class="text-error hover:bg-error/10 p-1.5 rounded transition-colors remove-row" title="Remove">
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="p-4 border-t border-border-base bg-surface-container-lowest flex justify-between items-center">
                <button type="button" id="add-skill" class="text-secondary text-sm font-medium hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">add</span> Add Skill
                </button>
                <div class="text-sm font-medium text-primary">
                    Total Weight: <span id="total-weight">0</span>%
                </div>
            </div>
        </div>

        <!-- Thresholds Section -->
        <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
            <div class="p-6 border-b border-border-base bg-surface-container-lowest">
                <h2 class="text-lg font-semibold text-primary">Automated Triage Thresholds</h2>
                <p class="text-sm text-text-muted">Define score bands and their target application status. Bands must be contiguous from 0 to 100.</p>
            </div>
            <div class="p-6 space-y-4" id="thresholds-container">
                <?php 
                $oldThresholds = $oldInput['thresholds'] ?? $thresholds;
                if (empty($oldThresholds)) {
                    $oldThresholds = [
                        ['min_score' => 0, 'max_score' => 39, 'target_status' => 'REJECTED'],
                        ['min_score' => 40, 'max_score' => 59, 'target_status' => 'SCREENING'],
                        ['min_score' => 60, 'max_score' => 79, 'target_status' => 'ASSESSMENT'],
                        ['min_score' => 80, 'max_score' => 100, 'target_status' => 'INTERVIEW']
                    ];
                }
                foreach ($oldThresholds as $i => $threshold): 
                ?>
                <div class="flex gap-4 items-start threshold-row">
                    <div class="w-32">
                        <label class="block text-xs font-medium text-text-muted mb-1">Min Score</label>
                        <input type="number" name="thresholds[<?= $i ?>][min_score]" value="<?= e($threshold['min_score'] ?? '') ?>" class="w-full rounded border border-border-base px-3 py-2 text-sm focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow min-score" min="0" max="100">
                    </div>
                    <div class="w-32">
                        <label class="block text-xs font-medium text-text-muted mb-1">Max Score</label>
                        <input type="number" name="thresholds[<?= $i ?>][max_score]" value="<?= e($threshold['max_score'] ?? '') ?>" class="w-full rounded border border-border-base px-3 py-2 text-sm focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow max-score" min="0" max="100">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-text-muted mb-1">Target Status</label>
                        <select name="thresholds[<?= $i ?>][target_status]" class="w-full rounded border border-border-base px-3 py-2 text-sm focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow">
                            <option value="REJECTED" <?= ($threshold['target_status'] ?? '') === 'REJECTED' ? 'selected' : '' ?>>REJECTED</option>
                            <option value="SCREENING" <?= ($threshold['target_status'] ?? '') === 'SCREENING' ? 'selected' : '' ?>>SCREENING</option>
                            <option value="ASSESSMENT" <?= ($threshold['target_status'] ?? '') === 'ASSESSMENT' ? 'selected' : '' ?>>ASSESSMENT</option>
                            <option value="INTERVIEW" <?= ($threshold['target_status'] ?? '') === 'INTERVIEW' ? 'selected' : '' ?>>INTERVIEW</option>
                        </select>
                    </div>
                    <div class="pt-6">
                        <button type="button" class="text-error hover:bg-error/10 p-1.5 rounded transition-colors remove-row" title="Remove">
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="p-4 border-t border-border-base bg-surface-container-lowest">
                <button type="button" id="add-threshold" class="text-secondary text-sm font-medium hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">add</span> Add Threshold Band
                </button>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="<?= e(url('hr.requisitions.show', [$requisition['job_id']])) ?>" class="px-5 py-2.5 border border-outline-variant rounded-md shadow-sm text-sm font-medium text-primary bg-white hover:bg-surface-container-highest transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-secondary text-white rounded-md shadow-sm text-sm font-medium hover:bg-blue-800 transition-colors">Save Configuration</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Dynamic weight total calculation
    const updateWeightTotal = () => {
        let total = 0;
        document.querySelectorAll('.weight-input').forEach(input => {
            total += parseFloat(input.value || 0);
        });
        const totalSpan = document.getElementById('total-weight');
        totalSpan.textContent = total.toFixed(2);
        totalSpan.className = total === 100 ? 'text-success' : 'text-error';
    };

    document.getElementById('skills-container').addEventListener('input', (e) => {
        if (e.target.classList.contains('weight-input')) updateWeightTotal();
    });

    document.querySelectorAll('.remove-row').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.target.closest('.skill-row, .threshold-row').remove();
            updateWeightTotal();
        });
    });

    updateWeightTotal();

    // Add new skill row
    let skillIndex = document.querySelectorAll('.skill-row').length;
    document.getElementById('add-skill').addEventListener('click', () => {
        const tpl = `
            <div class="flex gap-4 items-start skill-row">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-text-muted mb-1">Skill Name</label>
                    <input type="text" name="skills[${skillIndex}][skill_name]" class="w-full rounded border border-border-base px-3 py-2 text-sm focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow" placeholder="e.g. PHP">
                </div>
                <div class="w-24">
                    <label class="block text-xs font-medium text-text-muted mb-1">Weight (%)</label>
                    <input type="number" name="skills[${skillIndex}][weight]" class="w-full rounded border border-border-base px-3 py-2 text-sm focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow weight-input" min="1" max="100" step="0.01">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-text-muted mb-1">Evidence Field</label>
                    <select name="skills[${skillIndex}][evidence_field]" class="w-full rounded border border-border-base px-3 py-2 text-sm focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow">
                        <option value="skill_keywords">Anywhere (Title, Keywords, Resume)</option>
                        <option value="current_title">Current Title Only</option>
                    </select>
                </div>
                <div class="pt-6">
                    <button type="button" class="text-error hover:bg-error/10 p-1.5 rounded transition-colors remove-row" title="Remove" onclick="this.closest('.skill-row').remove(); updateWeightTotal();">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
                </div>
            </div>
        `;
        document.getElementById('skills-container').insertAdjacentHTML('beforeend', tpl);
        skillIndex++;
    });

    // Add new threshold row
    let thresholdIndex = document.querySelectorAll('.threshold-row').length;
    document.getElementById('add-threshold').addEventListener('click', () => {
        const tpl = `
            <div class="flex gap-4 items-start threshold-row">
                <div class="w-32">
                    <label class="block text-xs font-medium text-text-muted mb-1">Min Score</label>
                    <input type="number" name="thresholds[${thresholdIndex}][min_score]" class="w-full rounded border border-border-base px-3 py-2 text-sm focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow min-score" min="0" max="100">
                </div>
                <div class="w-32">
                    <label class="block text-xs font-medium text-text-muted mb-1">Max Score</label>
                    <input type="number" name="thresholds[${thresholdIndex}][max_score]" class="w-full rounded border border-border-base px-3 py-2 text-sm focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow max-score" min="0" max="100">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-text-muted mb-1">Target Status</label>
                    <select name="thresholds[${thresholdIndex}][target_status]" class="w-full rounded border border-border-base px-3 py-2 text-sm focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow">
                        <option value="REJECTED">REJECTED</option>
                        <option value="SCREENING">SCREENING</option>
                        <option value="ASSESSMENT">ASSESSMENT</option>
                        <option value="INTERVIEW">INTERVIEW</option>
                    </select>
                </div>
                <div class="pt-6">
                    <button type="button" class="text-error hover:bg-error/10 p-1.5 rounded transition-colors remove-row" title="Remove" onclick="this.closest('.threshold-row').remove();">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
                </div>
            </div>
        `;
        document.getElementById('thresholds-container').insertAdjacentHTML('beforeend', tpl);
        thresholdIndex++;
    });
});
</script>
<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../../layouts/app.php'; ?>
