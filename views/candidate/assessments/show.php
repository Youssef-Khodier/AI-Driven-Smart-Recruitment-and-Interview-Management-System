<h1><?= e($attempt['assessment_title']) ?></h1>
<p><strong>Job:</strong> <?= e($attempt['job_title']) ?> | <strong>Expires:</strong> <?= e($attempt['expires_at']) ?></p>
<form method="POST" action="<?= e(url('candidate.assessments.focus-events.store', [$attempt['ca_id']])) ?>">
    <?= csrf_field() ?><input type="hidden" name="event_type" value="FOCUS_LOST"><input type="hidden" name="visible_state" value="manual-demo"><button type="submit">Record focus loss event</button>
</form>
<?php foreach ($questions as $question): ?>
    <form method="POST" action="<?= e(url('candidate.assessments.answers.update', [$attempt['ca_id'], $question['attempt_question_id']])) ?>" style="margin-top:1rem;padding-top:1rem;border-top:1px solid #e2e8f0;">
        <?= csrf_field() ?><?= method_field('PUT') ?>
        <h2>Question <?= e($question['display_order']) ?> (<?= e($question['question_type']) ?>, <?= e($question['points']) ?> pts)</h2>
        <p><?= nl2br(e($question['question_text'])) ?></p>
        <?php if ($question['options']): ?><pre><?= e($question['options']) ?></pre><?php endif; ?>
        <label>Answer<textarea name="answer_text"><?= e($question['answer_text'] ?? '') ?></textarea></label>
        <button type="submit">Save answer</button>
    </form>
<?php endforeach; ?>
<form method="POST" action="<?= e(url('candidate.assessments.submit', [$attempt['ca_id']])) ?>" style="margin-top:1.5rem;">
    <?= csrf_field() ?><button type="submit">Submit final assessment</button>
</form>
