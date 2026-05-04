<h1>Create HR or Interviewer User</h1>
<form method="POST" action="<?= e(url('hr.users.store')) ?>">
    <?= csrf_field() ?>
    <label>Name<input name="name" value="<?= e(old('name')) ?>" required></label>
    <label>Email<input type="email" name="email" value="<?= e(old('email')) ?>" required></label>
    <label>Password<input type="password" name="password" required></label>
    <label>Role<select name="role"><option value="HR_ADMIN">HR_ADMIN</option><option value="INTERVIEWER">INTERVIEWER</option></select></label>
    <label>Department<select name="department_id"><option value="">None</option><?php foreach ($departments as $department): ?><option value="<?= e($department['department_id']) ?>"><?= e($department['name']) ?></option><?php endforeach; ?></select></label>
    <button type="submit">Create user</button>
</form>
