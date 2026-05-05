<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-primary">Update Access</h1>
        <a href="<?= e(url('hr.users.index')) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Cancel
        </a>
    </div>

    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 md:p-8 space-y-6">
        <div class="bg-surface-container-low p-4 rounded-lg border border-border-base flex items-center gap-4">
            <div class="h-12 w-12 rounded-full bg-primary-fixed text-primary flex items-center justify-center font-bold text-lg">
                <?= e(strtoupper(substr($target['name'], 0, 1))) ?>
            </div>
            <div>
                <h2 class="font-bold text-primary text-lg"><?= e($target['name']) ?></h2>
                <p class="text-sm text-text-muted"><?= e($target['email']) ?></p>
            </div>
        </div>

        <form method="POST" action="<?= e(url('hr.users.access.update', [$target['user_id']])) ?>" class="space-y-6">
            <?= csrf_field() ?><?= method_field('PUT') ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-primary mb-1">Role</label>
                    <select name="role" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                        <?php foreach (\App\Enums\UserRole::values() as $role): ?>
                            <option value="<?= e($role) ?>"<?= selected($target['role'], $role) ?>><?= e($role) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-primary mb-1">Status</label>
                    <select name="status" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                        <?php foreach (['ACTIVE', 'INACTIVE'] as $status): ?>
                            <option value="<?= e($status) ?>"<?= selected($target['status'], $status) ?>><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="pt-6 border-t border-border-base flex justify-end">
                <button type="submit" class="bg-secondary-container text-white px-6 py-2.5 rounded-md hover:bg-blue-700 transition-colors font-medium shadow-sm flex items-center justify-center gap-2 w-full sm:w-auto">
                    <span class="material-symbols-outlined text-[18px]">shield_person</span> Update access
                </button>
            </div>
        </form>
    </div>
</div>
