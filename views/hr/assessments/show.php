<div class="max-w-5xl mx-auto space-y-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary flex items-center gap-3">
                <?= e($assessment['title']) ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold tracking-wide <?= $assessment['is_active'] ? 'bg-success-bg text-success border border-success/20' : 'bg-gray-100 text-gray-600 border border-gray-200' ?>">
                    <?= $assessment['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
            </h1>
            <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-sm text-text-muted">
                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px]">work</span> <?= e($assessment['job_title']) ?></span>
                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px]">category</span> <?= e($assessment['type']) ?></span>
                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px]">timer</span> <?= e($assessment['duration_minutes']) ?> min</span>
            </div>
        </div>
        <div class="flex gap-2">
            <?php if ($assessment['is_active']): ?>
                <form class="inline m-0" method="POST" action="<?= e(url('hr.assessments.deactivate', [$assessment['assessment_id']])) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="bg-white border border-outline-variant text-primary px-3 py-1.5 rounded-md hover:bg-surface-container-highest transition-colors text-xs font-medium shadow-sm flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">pause_circle</span> Deactivate
                    </button>
                </form>
            <?php endif; ?>
            <a href="<?= e(url('hr.assessments.index', [$assessment['job_id']])) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1 pl-2">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back
            </a>
        </div>
    </div>

    <!-- Management Actions -->
    <div class="bg-surface-container-low border border-border-base rounded-lg p-4 flex flex-wrap items-center gap-3">
        <a class="bg-white border border-outline-variant text-primary px-4 py-2 rounded-md hover:bg-surface-container-highest transition-colors font-medium flex items-center gap-2 text-sm shadow-sm" href="<?= e(url('hr.assessments.edit', [$assessment['assessment_id']])) ?>">
            <span class="material-symbols-outlined text-[18px]">edit</span> Edit assessment
        </a>
        <a class="bg-secondary-container text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors font-medium flex items-center gap-2 text-sm shadow-sm" href="<?= e(url('hr.assessment-questions.create', [$assessment['assessment_id']])) ?>">
            <span class="material-symbols-outlined text-[18px]">add_circle</span> Add question
        </a>
        <a class="bg-white border border-outline-variant text-primary px-4 py-2 rounded-md hover:bg-surface-container-highest transition-colors font-medium flex items-center gap-2 text-sm shadow-sm" href="<?= e(url('hr.assessment-results.index', [$assessment['job_id']])) ?>">
            <span class="material-symbols-outlined text-[18px]">analytics</span> Review results
        </a>
    </div>

    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-5 grid gap-4 md:grid-cols-2">
        <div>
            <h2 class="text-lg font-bold text-primary mb-2">Question-Bank Rules</h2>
            <div class="flex flex-wrap gap-2 text-sm">
                <?php foreach (['EASY', 'MEDIUM', 'HARD'] as $level): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-surface-container-low border border-border-base text-primary">
                        <?= e($level) ?>: <?= e($rules[$level] ?? 0) ?> questions
                    </span>
                <?php endforeach; ?>
            </div>
            <p class="text-xs text-text-muted mt-3">Retake cool-down: <?= e($assessment['cooldown_months'] ?? 6) ?> month(s).</p>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
            <h2 class="text-sm font-semibold text-blue-900 mb-1">Dynamic Difficulty Suggestion</h2>
            <p class="text-sm text-blue-800"><?= e($difficultySuggestion) ?></p>
        </div>
    </div>

    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
        <div class="px-6 py-4 border-b border-border-base flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary">format_list_numbered</span>
            <h2 class="text-xl font-bold text-primary m-0">Questions</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-container-lowest text-text-muted text-xs uppercase tracking-wider border-b border-border-base">
                    <tr>
                        <th class="px-6 py-3 font-medium">Type</th>
                        <th class="px-6 py-3 font-medium">Difficulty</th>
                        <th class="px-6 py-3 font-medium">Question</th>
                        <th class="px-6 py-3 font-medium text-center">Points</th>
                        <th class="px-6 py-3 font-medium text-center">Active</th>
                        <th class="px-6 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-base text-sm">
                <?php foreach ($questions as $question): ?>
                    <tr class="hover:bg-surface-container-lowest transition-colors">
                        <td class="px-6 py-3 text-text-muted"><?= e($question['type']) ?></td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium border <?= $question['difficulty_level'] === 'HARD' ? 'bg-error-bg text-error border-error/20' : ($question['difficulty_level'] === 'MEDIUM' ? 'bg-warning-bg text-warning border-warning/20' : 'bg-success-bg text-success border-success/20') ?>">
                                <?= e($question['difficulty_level']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-3 text-primary max-w-md truncate"><?= e(str_limit($question['question_text'], 140)) ?></td>
                        <td class="px-6 py-3 text-primary font-medium text-center"><?= e($question['points']) ?></td>
                        <td class="px-6 py-3 text-center">
                            <span class="material-symbols-outlined text-[18px] <?= $question['is_active'] ? 'text-success' : 'text-gray-400' ?>"><?= $question['is_active'] ? 'check_circle' : 'cancel' ?></span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?= e(url('hr.assessment-questions.edit', [$question['question_id']])) ?>" class="text-secondary hover:text-blue-800 flex items-center p-1" title="Edit">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </a>
                                <?php if ($question['is_active']): ?>
                                    <form class="inline m-0" method="POST" action="<?= e(url('hr.assessment-questions.deactivate', [$question['question_id']])) ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="text-text-muted hover:text-error flex items-center p-1" title="Deactivate">
                                            <span class="material-symbols-outlined text-[18px]">power_settings_new</span>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($questions)): ?>
                    <tr><td colspan="6" class="px-6 py-8 text-center text-text-muted">No questions added yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
        <div class="px-6 py-4 border-b border-border-base flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary">how_to_reg</span>
            <h2 class="text-xl font-bold text-primary m-0">Candidate Attempts</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-container-lowest text-text-muted text-xs uppercase tracking-wider border-b border-border-base">
                    <tr>
                        <th class="px-6 py-3 font-medium">Candidate</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium text-center">Score</th>
                        <th class="px-6 py-3 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-base text-sm">
                <?php foreach ($attempts as $attempt): ?>
                    <tr class="hover:bg-surface-container-lowest transition-colors">
                        <td class="px-6 py-3">
                            <div class="font-medium text-primary"><?= e($attempt['name']) ?></div>
                            <div class="text-xs text-text-muted"><?= e($attempt['email']) ?></div>
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium <?= $attempt['status'] === 'SUBMITTED' ? 'bg-success-bg text-success border border-success/20' : 'bg-blue-50 text-blue-800 border border-blue-200' ?>">
                                <?= e($attempt['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-3 text-primary font-bold text-center text-base"><?= e($attempt['score'] ?? '-') ?></td>
                        <td class="px-6 py-3 text-right">
                            <a class="inline-flex items-center justify-center px-3 py-1 border border-outline-variant rounded shadow-sm text-xs font-medium text-primary bg-white hover:bg-surface-container-low transition-colors" href="<?= e(url('hr.candidate-assessments.show', [$attempt['ca_id']])) ?>">
                                Review
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($attempts)): ?>
                    <tr><td colspan="4" class="px-6 py-8 text-center text-text-muted">No candidate attempts recorded yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
