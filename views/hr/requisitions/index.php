<div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
    <div class="px-6 py-5 border-b border-border-base flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-primary">Job Requisitions</h1>
        </div>
        <a class="bg-secondary-container text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors font-medium flex items-center gap-2 text-sm shadow-sm" href="<?= e(url('hr.requisitions.create')) ?>">
            <span class="material-symbols-outlined text-[18px]">add</span> Create requisition
        </a>
    </div>
    
    <div class="bg-surface-container-low px-6 py-3 border-b border-border-base flex flex-wrap gap-2 text-sm">
        <a href="<?= e(url('hr.requisitions.index')) ?>" class="px-3 py-1 rounded-full <?= !isset($_GET['status']) ? 'bg-primary text-white' : 'bg-white text-text-muted border border-border-base hover:bg-gray-50' ?>">All</a>
        <?php foreach (['DRAFT','PENDING','APPROVED','OPEN','CLOSED'] as $s): ?>
            <a href="<?= e(url('hr.requisitions.index')) ?>?status=<?= e($s) ?>" class="px-3 py-1 rounded-full <?= (isset($_GET['status']) && $_GET['status'] === $s) ? 'bg-primary text-white' : 'bg-white text-text-muted border border-border-base hover:bg-gray-50' ?>"><?= e($s) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-lowest text-text-muted text-xs uppercase tracking-wider border-b border-border-base">
                <tr>
                    <th class="px-6 py-3 font-medium">Title</th>
                    <th class="px-6 py-3 font-medium">Department</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Created By</th>
                    <th class="px-6 py-3 font-medium text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
            <?php foreach ($requisitions as $row): ?>
                <tr class="hover:bg-surface-container-lowest transition-colors">
                    <td class="px-6 py-4 font-medium text-primary"><?= e($row['title']) ?></td>
                    <td class="px-6 py-4 text-text-muted"><?= e($row['department_name']) ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $row['status'] === 'OPEN' ? 'bg-success-bg text-success' : ($row['status'] === 'DRAFT' ? 'bg-gray-100 text-gray-800' : ($row['status'] === 'REJECTED' ? 'bg-error text-white' : 'bg-blue-100 text-blue-800')) ?>">
                            <?= e($row['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-text-muted"><?= e($row['creator_name']) ?></td>
                    <td class="px-6 py-4 text-right">
                        <a class="inline-flex items-center justify-center px-3 py-1.5 border border-outline-variant rounded-md shadow-sm text-xs font-medium text-primary bg-white hover:bg-surface-container-low transition-colors" href="<?= e(url('hr.requisitions.show', [$row['job_id']])) ?>">
                            View
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($requisitions)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-text-muted">No job requisitions found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
