<h1><?= $record ? 'Update Onboarding' : 'Create Onboarding' ?></h1>
<form method="POST" action="<?= $record ? e(url('hr.onboarding.update', [$record['onboarding_id']])) : e(url('hr.onboarding.store', [$offerId])) ?>">
    <?= csrf_field() ?>
    <?php if ($record): ?>
        <?= method_field('PUT') ?>
    <?php endif; ?>
    
    <div>
        <label>Start Date:</label>
        <input type="date" name="start_date" value="<?= e($record['start_date'] ?? '') ?>">
    </div>
    
    <div>
        <label>Status:</label>
        <select name="status" required>
            <?php foreach ($statuses as $status): ?>
                <option value="<?= e($status) ?>" <?= selected($record['status'] ?? 'PENDING', $status) ?>><?= e($status) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div>
        <label>
            <input type="checkbox" name="documents_completed" value="1" <?= ($record['documents_completed'] ?? false) ? 'checked' : '' ?>>
            Documents Completed
        </label>
    </div>
    
    <div>
        <button type="submit"><?= $record ? 'Update' : 'Create' ?></button>
    </div>
</form>
