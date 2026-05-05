<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-primary">Create HR or Interviewer User</h1>
        <a href="<?= e(url('hr.users.index')) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Cancel
        </a>
    </div>

    <form method="POST" action="<?= e(url('hr.users.store')) ?>" class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 md:p-8 space-y-6">
        <?= csrf_field() ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Name</label>
                <input name="name" value="<?= e(old('name')) ?>" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Email</label>
                <input type="email" name="email" value="<?= e(old('email')) ?>" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-primary mb-1">Password</label>
                <input type="password" name="password" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Role</label>
                <select name="role" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                    <option value="HR_ADMIN">HR_ADMIN</option>
                    <option value="INTERVIEWER">INTERVIEWER</option>
                    <option value="JUNIOR_STAFF">JUNIOR_STAFF</option>
                </select>
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-primary mb-1">Department</label>
                <select name="department_id" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                    <option value="">None</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= e($department['department_id']) ?>"><?= e($department['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="pt-6 border-t border-border-base flex justify-end">
            <button type="submit" class="bg-secondary-container text-white px-6 py-2.5 rounded-md hover:bg-blue-700 transition-colors font-medium shadow-sm flex items-center justify-center gap-2 w-full sm:w-auto">
                <span class="material-symbols-outlined text-[18px]">person_add</span> Create user
            </button>
        </div>
    </form>
</div>
