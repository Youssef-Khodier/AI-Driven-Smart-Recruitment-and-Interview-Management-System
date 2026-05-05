<?php $editing = (bool) $question; ?>
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-primary"><?= $editing ? 'Edit' : 'Create' ?> Question</h1>
        <a href="<?= e(url('hr.assessments.show', [$assessment['assessment_id']])) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Cancel
        </a>
    </div>

    <div class="text-sm text-text-muted flex items-center gap-2 mb-2 bg-surface-container-low p-3 rounded-lg border border-border-base w-fit">
        <span class="material-symbols-outlined text-[18px]">quiz</span> <strong>Assessment:</strong> <?= e($assessment['title']) ?>
    </div>

    <form method="POST" action="<?= e($editing ? url('hr.assessment-questions.update', [$question['question_id']]) : url('hr.assessment-questions.store', [$assessment['assessment_id']])) ?>" class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 md:p-8 space-y-6">
        <?= csrf_field() ?><?php if ($editing): ?><?= method_field('PUT') ?><?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Type</label>
                <select name="type" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                    <?php foreach (['MCQ','CODING','THEORY','OTHER'] as $type): ?>
                        <option value="<?= e($type) ?>"<?= selected($question['type'] ?? 'MCQ', $type) ?>><?= e($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Difficulty</label>
                <select name="difficulty_level" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                    <?php foreach (['EASY','MEDIUM','HARD'] as $level): ?>
                        <option value="<?= e($level) ?>"<?= selected($question['difficulty_level'] ?? 'MEDIUM', $level) ?>><?= e($level) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-primary mb-1">Points</label>
                <input type="number" step="0.01" min="0.01" name="points" value="<?= e($question['points'] ?? 1) ?>" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-primary mb-1">Question text</label>
            <textarea name="question_text" rows="4" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm"><?= e($question['question_text'] ?? old('question_text')) ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-primary mb-1">Options JSON or text (Optional)</label>
            <textarea name="options" rows="3" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm font-mono text-xs"><?= e($question['options'] ?? old('options')) ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-primary mb-1">Correct/reference answer (Optional)</label>
            <textarea name="correct_answer" rows="3" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm font-mono text-xs"><?= e($question['correct_answer'] ?? old('correct_answer')) ?></textarea>
        </div>

        <div class="flex items-center gap-2 pt-2 border-t border-border-base mt-2">
            <input type="checkbox" id="is_active" name="is_active" value="1"<?= checked($question['is_active'] ?? true) ?> class="h-4 w-4 text-secondary focus:ring-secondary border-border-base rounded">
            <label for="is_active" class="text-sm font-medium text-primary cursor-pointer select-none">Active (included in assessment)</label>
        </div>

        <div class="pt-6 border-t border-border-base flex justify-end">
            <button type="submit" class="bg-secondary-container text-white px-6 py-2.5 rounded-md hover:bg-blue-700 transition-colors font-medium shadow-sm flex items-center justify-center gap-2 w-full sm:w-auto">
                <span class="material-symbols-outlined text-[18px]">save</span> Save question
            </button>
        </div>
    </form>
</div>
