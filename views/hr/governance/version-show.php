<div class="max-w-5xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Version <?= e($version['version_number']) ?></h1>
            <p class="text-text-muted mt-2 text-lg">Created by <?= e($version['creator_name']) ?> on <?= e(date('M j, Y, g:i a', strtotime($version['created_at']))) ?></p>
        </div>
        <a href="<?= e(url('hr.versions.history', ['id' => $requisition['job_id']])) ?>" class="text-info hover:underline text-sm font-medium">&larr; Back to Version History</a>
    </div>

    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-border-base">
            <h2 class="text-xl font-bold text-primary mb-4">Description</h2>
            <div class="prose max-w-none text-text-body">
                <?= nl2br(e($version['description_body'])) ?>
            </div>
        </div>
        <div class="p-6">
            <h2 class="text-xl font-bold text-primary mb-4">Requirements</h2>
            <div class="prose max-w-none text-text-body">
                <?= nl2br(e($version['requirements_body'])) ?>
            </div>
        </div>
    </div>
</div>
