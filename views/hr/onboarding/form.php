<?php if (!isset($record) || !$record): ?>
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-primary flex items-center gap-2">
            <span class="material-symbols-outlined text-purple-700 text-[32px]">person_add</span>
            Create Onboarding
        </h1>
        <a href="<?= e(url('hr.offers.index')) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Cancel
        </a>
    </div>
    
    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 md:p-8">
<?php endif; ?>

<form method="POST" action="<?= $record ? e(url('hr.onboarding.update', [$record['onboarding_id']])) : e(url('hr.onboarding.store', [$offerId])) ?>" class="space-y-6">
    <?= csrf_field() ?>
    <?php if ($record): ?>
        <?= method_field('PUT') ?>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-primary mb-1">Start Date:</label>
            <input type="date" name="start_date" value="<?= e($record['start_date'] ?? '') ?>" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-primary mb-1">Status:</label>
            <select name="status" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm font-medium">
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= e($status) ?>" <?= selected($record['status'] ?? 'PENDING', $status) ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <div class="bg-surface-container-lowest p-4 rounded-lg border border-border-base mt-4">
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="documents_completed" value="1" <?= ($record['documents_completed'] ?? false) ? 'checked' : '' ?> class="h-5 w-5 text-secondary focus:ring-secondary border-border-base rounded">
            <span class="text-sm font-medium text-primary">Documents Completed</span>
        </label>
        <p class="text-xs text-text-muted mt-1 ml-8">Mark when the candidate has signed all required paperwork.</p>
    </div>
    
    <div class="pt-6 <?= $record ? '' : 'border-t border-border-base' ?> flex justify-end">
        <button type="submit" class="bg-secondary-container text-white px-6 py-2.5 rounded-md hover:bg-blue-700 transition-colors font-medium shadow-sm flex items-center justify-center gap-2 w-full sm:w-auto">
            <span class="material-symbols-outlined text-[18px]">save</span> <?= $record ? 'Update Onboarding' : 'Create Onboarding' ?>
        </button>
    </div>
</form>

<?php if (!isset($record) || !$record): ?>
    </div>
</div>
<?php endif; ?>
