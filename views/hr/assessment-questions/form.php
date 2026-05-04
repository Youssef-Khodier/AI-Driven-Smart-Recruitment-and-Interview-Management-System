<?php $editing = (bool) $question; ?>
<h1><?= $editing ? 'Edit' : 'Create' ?> Question</h1>
<p class="muted">Assessment: <?= e($assessment['title']) ?></p>
<form method="POST" action="<?= e($editing ? url('hr.assessment-questions.update', [$question['question_id']]) : url('hr.assessment-questions.store', [$assessment['assessment_id']])) ?>">
    <?= csrf_field() ?><?php if ($editing): ?><?= method_field('PUT') ?><?php endif; ?>
    <label>Type<select name="type"><?php foreach (['MCQ','CODING','THEORY','OTHER'] as $type): ?><option value="<?= e($type) ?>"<?= selected($question['type'] ?? 'MCQ', $type) ?>><?= e($type) ?></option><?php endforeach; ?></select></label>
    <label>Difficulty<select name="difficulty_level"><?php foreach (['EASY','MEDIUM','HARD'] as $level): ?><option value="<?= e($level) ?>"<?= selected($question['difficulty_level'] ?? 'MEDIUM', $level) ?>><?= e($level) ?></option><?php endforeach; ?></select></label>
    <label>Question text<textarea name="question_text" required><?= e($question['question_text'] ?? old('question_text')) ?></textarea></label>
    <label>Options JSON or text<textarea name="options"><?= e($question['options'] ?? old('options')) ?></textarea></label>
    <label>Correct/reference answer<textarea name="correct_answer"><?= e($question['correct_answer'] ?? old('correct_answer')) ?></textarea></label>
    <label>Points<input type="number" step="0.01" min="0.01" name="points" value="<?= e($question['points'] ?? 1) ?>" required></label>
    <label><input type="checkbox" name="is_active" value="1"<?= checked($question['is_active'] ?? true) ?>> Active</label>
    <button type="submit">Save question</button>
</form>
