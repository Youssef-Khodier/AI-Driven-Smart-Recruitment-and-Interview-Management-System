<div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
    <div class="px-6 py-5 border-b border-border-base flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-primary">Users</h1>
        </div>
        <a class="bg-secondary-container text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors font-medium flex items-center gap-2 text-sm shadow-sm" href="<?= e(url('hr.users.create')) ?>">
            <span class="material-symbols-outlined text-[18px]">person_add</span> Create user
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low text-text-muted text-xs uppercase tracking-wider border-b border-border-base">
                <tr>
                    <th class="px-6 py-3 font-medium">Name</th>
                    <th class="px-6 py-3 font-medium">Email</th>
                    <th class="px-6 py-3 font-medium">Role</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Department</th>
                    <th class="px-6 py-3 font-medium text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
            <?php foreach ($users as $row): ?>
                <tr class="hover:bg-surface-container-lowest transition-colors">
                    <td class="px-6 py-4 font-medium text-primary flex items-center gap-3">
                        <div class="h-8 w-8 rounded-full bg-primary-fixed text-primary flex items-center justify-center font-bold text-xs">
                            <?= e(strtoupper(substr($row['name'], 0, 1))) ?>
                        </div>
                        <?= e($row['name']) ?>
                    </td>
                    <td class="px-6 py-4 text-text-muted"><?= e($row['email']) ?></td>
                    <td class="px-6 py-4 text-text-muted">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border border-border-base bg-white text-primary">
                            <?= e($row['role']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 text-sm <?= $row['status'] === 'ACTIVE' ? 'text-success' : 'text-text-muted' ?>">
                            <span class="h-2 w-2 rounded-full <?= $row['status'] === 'ACTIVE' ? 'bg-success' : 'bg-gray-400' ?>"></span>
                            <?= e($row['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-text-muted"><?= e($row['department_name'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-right">
                        <a class="inline-flex items-center justify-center px-3 py-1.5 border border-outline-variant rounded-md shadow-sm text-xs font-medium text-primary bg-white hover:bg-surface-container-low transition-colors" href="<?= e(url('hr.users.access.edit', [$row['user_id']])) ?>">
                            Access
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-text-muted">No users found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
