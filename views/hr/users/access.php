<h1>Update Access</h1>
<p><?= e($target['name']) ?>, <?= e($target['email']) ?></p>
<form method="POST" action="<?= e(url('hr.users.access.update', [$target['user_id']])) ?>">
    <?= csrf_field() ?><?= method_field('PUT') ?>
    <label>Role<select name="role"><?php foreach (['HR_ADMIN', 'INTERVIEWER', 'CANDIDATE'] as $role): ?><option value="<?= e($role) ?>"<?= selected($target['role'], $role) ?>><?= e($role) ?></option><?php endforeach; ?></select></label>
    <label>Status<select name="status"><?php foreach (['ACTIVE', 'INACTIVE'] as $status): ?><option value="<?= e($status) ?>"<?= selected($target['status'], $status) ?>><?= e($status) ?></option><?php endforeach; ?></select></label>
    <button type="submit">Update access</button>
</form>
