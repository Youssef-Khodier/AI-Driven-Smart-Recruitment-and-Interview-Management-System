<?php $editing = (bool) $assessment; ?>
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-primary"><?= $editing ? 'Edit' : 'Create' ?> Assessment</h1>
        <a href="<?= e(url('hr.assessments.index', [$requisition['job_id']])) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Cancel
        </a>
    </div>
    
    <div class="text-sm text-text-muted flex items-center gap-2 mb-2 bg-surface-container-low p-3 rounded-lg border border-border-base w-fit">
        <span class="material-symbols-outlined text-[18px]">work</span> <strong>Job:</strong> <?= e($requisition['title']) ?>
    </div>

    <form method="POST" action="<?= e($editing ? url('hr.assessments.update', [$assessment['assessment_id']]) : url('hr.assessments.store', [$requisition['job_id']])) ?>" class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 md:p-8 space-y-6">
        <?= csrf_field() ?><?php if ($editing): ?><?= method_field('PUT') ?><?php endif; ?>
        
        <div>
            <label class="block text-sm font-medium text-primary mb-1">Title</label>
            <input name="title" value="<?= e($assessment['title'] ?? old('title')) ?>" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-primary mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm"><?= e($assessment['description'] ?? old('description')) ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Type</label>
                <select name="type" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                    <?php foreach (['TECHNICAL','APTITUDE','CODING','THEORY','OTHER'] as $type): ?>
                        <option value="<?= e($type) ?>"<?= selected($assessment['type'] ?? 'TECHNICAL', $type) ?>><?= e($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Duration minutes</label>
                <input type="number" min="1" name="duration_minutes" value="<?= e($assessment['duration_minutes'] ?? 60) ?>" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>
        </div>

        <div class="flex items-center gap-2 pt-2 border-t border-border-base mt-2">
            <input type="checkbox" id="is_active" name="is_active" value="1"<?= checked($assessment['is_active'] ?? true) ?> class="h-4 w-4 text-secondary focus:ring-secondary border-border-base rounded">
            <label for="is_active" class="text-sm font-medium text-primary cursor-pointer select-none">Active (available to candidates)</label>
        </div>

        <div class="pt-6 border-t border-border-base flex justify-end mt-4">
            <button type="submit" class="bg-secondary-container text-white px-6 py-2.5 rounded-md hover:bg-blue-700 transition-colors font-medium shadow-sm flex items-center justify-center gap-2 w-full sm:w-auto">
                <span class="material-symbols-outlined text-[18px]">save</span> Save assessment
            </button>
        </div>
    </form>
</div>
