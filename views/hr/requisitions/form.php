<?php $editing = (bool) $requisition; ?>
<h1><?= $editing ? 'Edit' : 'Create' ?> Requisition</h1>
<form method="POST" action="<?= e($editing ? url('hr.requisitions.update', [$requisition['job_id']]) : url('hr.requisitions.store')) ?>">
    <?= csrf_field() ?><?php if ($editing): ?><?= method_field('PUT') ?><?php endif; ?>
    <label>Department<select name="department_id" required><?php foreach ($departments as $department): ?><option value="<?= e($department['department_id']) ?>"<?= selected($requisition['department_id'] ?? old('department_id'), $department['department_id']) ?>><?= e($department['name']) ?></option><?php endforeach; ?></select></label>
    <label>Title<input name="title" value="<?= e($requisition['title'] ?? old('title')) ?>" required></label>
    <label>Description<textarea name="description" required><?= e($requisition['description'] ?? old('description')) ?></textarea></label>
    <label>Requirements<textarea name="requirements" required><?= e($requisition['requirements'] ?? old('requirements')) ?></textarea></label>
    <button type="submit">Save requisition</button>
</form>
