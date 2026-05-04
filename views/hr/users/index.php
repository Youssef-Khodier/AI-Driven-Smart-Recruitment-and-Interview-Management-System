<h1>Users</h1>
<p><a class="button" href="<?= e(url('hr.users.create')) ?>">Create user</a></p>
<table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Department</th><th></th></tr></thead><tbody>
<?php foreach ($users as $row): ?>
<tr><td><?= e($row['name']) ?></td><td><?= e($row['email']) ?></td><td><?= e($row['role']) ?></td><td><?= e($row['status']) ?></td><td><?= e($row['department_name'] ?? '-') ?></td><td><a href="<?= e(url('hr.users.access.edit', [$row['user_id']])) ?>">Access</a></td></tr>
<?php endforeach; ?>
</tbody></table>
