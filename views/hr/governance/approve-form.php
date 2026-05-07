<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Review Requisition</h1>
            <p class="text-text-muted mt-2 text-lg">Approve or reject this requisition.</p>
        </div>
        <a href="<?= e(url('hr.approvals.index')) ?>" class="text-info hover:underline text-sm font-medium">&larr; Back to Queue</a>
    </div>

    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold mb-4 border-b pb-2"><?= e($requisition['title']) ?></h2>
        
        <div class="grid grid-cols-2 gap-4 mb-6 text-sm text-text-muted">
            <div>
                <strong>Department:</strong> <?= e($requisition['department_name']) ?>
            </div>
            <div>
                <strong>Creator:</strong> <?= e($requisition['creator_name']) ?>
            </div>
        </div>

        <div class="mb-6">
            <h3 class="text-lg font-medium mb-2">Description</h3>
            <div class="prose max-w-none p-4 bg-bg-alt rounded-lg border">
                <?= nl2br(e($requisition['description'])) ?>
            </div>
        </div>

        <div class="mb-6">
            <h3 class="text-lg font-medium mb-2">Requirements</h3>
            <div class="prose max-w-none p-4 bg-bg-alt rounded-lg border">
                <?= nl2br(e($requisition['requirements'])) ?>
            </div>
        </div>

        <form method="POST" action="<?= e(url('hr.requisitions.approve', ['id' => $requisition['job_id']])) ?>" class="space-y-4 border-t pt-4">
            <?= csrf_field() ?>
            
            <div>
                <label for="comments" class="block text-sm font-medium text-text-primary mb-1">Comments (Optional)</label>
                <textarea id="comments" name="comments" rows="3" class="w-full border-border-base rounded-lg shadow-sm focus:ring-primary focus:border-primary"></textarea>
            </div>

            <div class="flex items-center space-x-4">
                <button type="submit" class="bg-success text-white px-6 py-2 rounded-lg font-medium hover:bg-green-700 transition">
                    Approve
                </button>
                <button type="submit" formaction="<?= e(url('hr.requisitions.reject', ['id' => $requisition['job_id']])) ?>" class="bg-error text-white px-6 py-2 rounded-lg font-medium hover:bg-red-700 transition">
                    Reject
                </button>
            </div>
        </form>
    </div>
</div>
