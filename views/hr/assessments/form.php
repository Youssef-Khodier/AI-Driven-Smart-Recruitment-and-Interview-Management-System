<?php $editing = (bool) $assessment; ?>
<h1><?= $editing ? 'Edit' : 'Create' ?> Assessment</h1>
<p class="muted">Job: <?= e($requisition['title']) ?></p>
<form method="POST" action="<?= e($editing ? url('hr.assessments.update', [$assessment['assessment_id']]) : url('hr.assessments.store', [$requisition['job_id']])) ?>">
    <?= csrf_field() ?><?php if ($editing): ?><?= method_field('PUT') ?><?php endif; ?>
    <label>Title<input name="title" value="<?= e($assessment['title'] ?? old('title')) ?>" required></label>
    <label>Description<textarea name="description"><?= e($assessment['description'] ?? old('description')) ?></textarea></label>
    <label>Type<select name="type"><?php foreach (['TECHNICAL','APTITUDE','CODING','THEORY','OTHER'] as $type): ?><option value="<?= e($type) ?>"<?= selected($assessment['type'] ?? 'TECHNICAL', $type) ?>><?= e($type) ?></option><?php endforeach; ?></select></label>
    <label>Duration minutes<input type="number" min="1" name="duration_minutes" value="<?= e($assessment['duration_minutes'] ?? 60) ?>" required></label>
    <label><input type="checkbox" name="is_active" value="1"<?= checked($assessment['is_active'] ?? true) ?>> Active</label>
    <button type="submit">Save assessment</button>
</form>
