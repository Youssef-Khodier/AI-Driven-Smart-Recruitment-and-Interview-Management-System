<?php $editing = (bool) $requisition; ?>
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-primary"><?= $editing ? 'Edit' : 'Create' ?> Requisition</h1>
        <a href="<?= e($editing ? url('hr.requisitions.show', [$requisition['job_id']]) : url('hr.requisitions.index')) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Cancel
        </a>
    </div>

    <form method="POST" action="<?= e($editing ? url('hr.requisitions.update', [$requisition['job_id']]) : url('hr.requisitions.store')) ?>" class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 md:p-8 space-y-6">
        <?= csrf_field() ?><?php if ($editing): ?><?= method_field('PUT') ?><?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Department</label>
                <select name="department_id" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= e($department['department_id']) ?>"<?= selected($requisition['department_id'] ?? old('department_id'), $department['department_id']) ?>><?= e($department['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Title</label>
                <input name="title" value="<?= e($requisition['title'] ?? old('title')) ?>" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-primary mb-1">Location</label>
                <input name="location" value="<?= e($requisition['location'] ?? old('location')) ?>" placeholder="e.g. Remote, New York, NY" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-primary mb-1">Description</label>
            <textarea name="description" rows="5" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm"><?= e($requisition['description'] ?? old('description')) ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-primary mb-1">Requirements</label>
            <textarea name="requirements" rows="5" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm"><?= e($requisition['requirements'] ?? old('requirements')) ?></textarea>
        </div>

        <div class="pt-6 border-t border-border-base flex justify-end">
            <button type="submit" class="bg-secondary-container text-white px-6 py-2.5 rounded-md hover:bg-blue-700 transition-colors font-medium shadow-sm flex items-center justify-center gap-2 w-full sm:w-auto">
                <span class="material-symbols-outlined text-[18px]">save</span> Save requisition
            </button>
        </div>
    </form>
</div>
