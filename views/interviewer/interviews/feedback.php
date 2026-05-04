<h1><?= e($title) ?></h1>

<form method="POST" action="<?= e(url('interviewer.interviews.feedback.store', [$briefing['interview_id']])) ?>">
    <?= csrf_field() ?>

    <?php foreach (['technical_score', 'communication_score', 'culture_fit_score', 'overall_score'] as $field): ?>
        <label><?= e(ucwords(str_replace('_', ' ', $field))) ?> (0-10)
            <input type="number" name="<?= e($field) ?>" value="<?= e(old($field)) ?>" step="0.01" min="0" max="10" required>
        </label>
        <?php if ($error = error($field)): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <?php endforeach; ?>

    <label>Comments
        <textarea name="comments" required><?= e(old('comments')) ?></textarea>
    </label>
    <?php if ($error = error('comments')): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

    <button type="submit">Submit official feedback</button>
</form>

<p><a href="<?= e(url('interviewer.interviews.show', [$briefing['interview_id']])) ?>">Cancel</a></p>
