<div class="max-w-5xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Department Heads Management</h1>
            <p class="text-text-muted mt-2 text-lg">Assign and manage department head roles.</p>
        </div>
        <a href="<?= e(url('hr.dashboard')) ?>" class="text-info hover:underline text-sm font-medium">&larr; Back to Dashboard</a>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="bg-success-bg border border-success text-success px-4 py-3 rounded-lg mb-6 shadow-sm flex items-center">
            <span class="material-symbols-outlined mr-2">check_circle</span>
            <?= e($_SESSION['flash_success']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="bg-error-bg border border-error text-error px-4 py-3 rounded-lg mb-6 shadow-sm flex items-center">
            <span class="material-symbols-outlined mr-2">error</span>
            <?= e($_SESSION['flash_error']) ?>
        </div>
    <?php endif; ?>

    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-bg-alt border-b border-border-base text-text-muted text-sm uppercase tracking-wider">
                    <th class="px-6 py-4 font-semibold">Department</th>
                    <th class="px-6 py-4 font-semibold">Current Head</th>
                    <th class="px-6 py-4 font-semibold text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
                <?php foreach ($departments as $dept): ?>
                    <?php 
                    $currentHead = null;
                    foreach ($heads as $h) {
                        if ($h['department_id'] == $dept['department_id']) {
                            $currentHead = $h;
                            break;
                        }
                    }
                    ?>
                    <tr class="hover:bg-bg-alt transition-colors">
                        <td class="px-6 py-4 text-primary font-medium">
                            <?= e($dept['name']) ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($currentHead): ?>
                                <div class="flex items-center">
                                    <span class="material-symbols-outlined text-success mr-2 text-xl">person</span>
                                    <span class="text-primary"><?= e($currentHead['user_name'] ?? $currentHead['name']) ?></span>
                                </div>
                            <?php else: ?>
                                <span class="text-text-muted italic">None assigned</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <?php if ($currentHead): ?>
                                <form method="POST" action="<?= e(url('hr.governance.department-heads.remove', ['id' => $currentHead['user_id']])) ?>" class="inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" onclick="return confirm('Remove department head?')" class="text-error hover:text-red-700 font-medium text-sm flex items-center justify-end">
                                        <span class="material-symbols-outlined text-sm mr-1">person_remove</span> Remove
                                    </button>
                                </form>
                            <?php else: ?>
                                <?php 
                                $availableAdmins = array_filter($hrAdmins, function($a) use ($dept) {
                                    return $a['department_id'] == $dept['department_id'];
                                });
                                ?>
                                <?php if (!empty($availableAdmins)): ?>
                                    <form method="POST" action="<?= e(url('hr.governance.department-heads.assign')) ?>" class="inline-flex items-center">
                                        <?= csrf_field() ?>
                                        <select name="user_id" required class="bg-bg-alt border border-border-base rounded-lg px-3 py-1 text-sm text-primary focus:outline-none focus:ring-2 focus:ring-info mr-2">
                                            <option value="">Select HR Admin...</option>
                                            <?php foreach ($availableAdmins as $admin): ?>
                                                <option value="<?= e($admin['user_id']) ?>"><?= e($admin['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="bg-info hover:bg-blue-700 text-white px-3 py-1 rounded-lg text-sm font-medium transition-colors shadow-sm">
                                            Assign
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-text-muted text-sm">No eligible HR Admins</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($departments)): ?>
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-text-muted">No departments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
