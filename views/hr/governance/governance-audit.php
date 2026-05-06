<div class="mb-6">
    <a href="<?= e(url('hr.requisitions.show', [$requisition['job_id']])) ?>" class="text-sm font-medium text-primary hover:underline flex items-center gap-1 mb-2">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to Requisition
    </a>
    <h1 class="text-2xl font-bold text-primary">Governance Audit: <?= e($requisition['title']) ?> (ID: <?= e($requisition['job_id']) ?>)</h1>
    <p class="text-text-muted mt-1 text-sm">Complete audit trail of all governance actions for this requisition.</p>
</div>

<div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-border-base bg-surface-container-lowest">
        <h2 class="text-lg font-semibold text-primary">Filters</h2>
    </div>
    <div class="p-6">
        <form method="GET" action="<?= e(url('hr.requisitions.governance-audit', [$requisition['job_id']])) ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label for="action" class="block text-sm font-medium text-primary mb-1">Action Type</label>
                <select name="action" id="action" class="w-full rounded-md border border-outline-variant bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary-container">
                    <option value="">All Actions</option>
                    <?php foreach (\App\Enums\GovernanceAuditAction::cases() as $action): ?>
                        <option value="<?= e($action->value) ?>" <?= (isset($_GET['action']) && $_GET['action'] === $action->value) ? 'selected' : '' ?>><?= e($action->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="date_from" class="block text-sm font-medium text-primary mb-1">Date From</label>
                <input type="date" name="date_from" id="date_from" value="<?= e($_GET['date_from'] ?? '') ?>" class="w-full rounded-md border border-outline-variant bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary-container">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-primary mb-1">Date To</label>
                <input type="date" name="date_to" id="date_to" value="<?= e($_GET['date_to'] ?? '') ?>" class="w-full rounded-md border border-outline-variant bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary-container">
            </div>
            <div>
                <label for="actor" class="block text-sm font-medium text-primary mb-1">Actor (Name)</label>
                <input type="text" name="actor" id="actor" value="<?= e($_GET['actor'] ?? '') ?>" placeholder="e.g. John Doe" class="w-full rounded-md border border-outline-variant bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary-container">
            </div>
            <div class="md:col-span-4 flex justify-end gap-2 mt-2">
                <a href="<?= e(url('hr.requisitions.governance-audit', [$requisition['job_id']])) ?>" class="px-4 py-2 text-sm font-medium text-primary bg-white border border-outline-variant rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary-container transition-colors">Clear Filters</a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-secondary-container rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary-container shadow-sm transition-colors">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-lowest text-text-muted text-xs uppercase tracking-wider border-b border-border-base">
                <tr>
                    <th class="px-6 py-3 font-medium">Date/Time</th>
                    <th class="px-6 py-3 font-medium">Actor</th>
                    <th class="px-6 py-3 font-medium">Action</th>
                    <th class="px-6 py-3 font-medium w-1/4">Old Value</th>
                    <th class="px-6 py-3 font-medium w-1/4">New Value</th>
                    <th class="px-6 py-3 font-medium">Comments</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
                <?php foreach ($logs['rows'] as $log): ?>
                    <tr class="hover:bg-surface-container-lowest transition-colors">
                        <td class="px-6 py-4 text-sm text-text-muted whitespace-nowrap"><?= e(date('M d, Y H:i', strtotime($log['created_at']))) ?></td>
                        <td class="px-6 py-4 text-sm font-medium text-primary"><?= e($log['actor_name']) ?></td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?= e($log['action']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-text-muted">
                            <?php if ($log['old_values']): ?>
                                <pre class="text-xs overflow-x-auto max-w-xs whitespace-pre-wrap bg-gray-50 p-2 rounded border border-gray-100"><?= e($log['old_values']) ?></pre>
                            <?php else: ?>
                                <span class="text-gray-400 italic">None</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-text-muted">
                            <?php if ($log['new_values']): ?>
                                <pre class="text-xs overflow-x-auto max-w-xs whitespace-pre-wrap bg-gray-50 p-2 rounded border border-gray-100"><?= e($log['new_values']) ?></pre>
                            <?php else: ?>
                                <span class="text-gray-400 italic">None</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-text-muted">
                            <?= e($log['comments'] ?? '-') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($logs['rows'])): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-text-muted">No governance audit logs found matching the criteria.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($logs['total'] > 25): ?>
        <div class="px-6 py-4 border-t border-border-base flex items-center justify-between bg-surface-container-lowest">
            <?php 
                $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                $totalPages = ceil($logs['total'] / 25);
                
                $queryParams = $_GET;
            ?>
            <span class="text-sm text-text-muted">
                Showing <?= min(($currentPage - 1) * 25 + 1, $logs['total']) ?> to <?= min($currentPage * 25, $logs['total']) ?> of <?= $logs['total'] ?> results
            </span>
            <div class="flex gap-2">
                <?php if ($currentPage > 1): ?>
                    <?php $queryParams['page'] = $currentPage - 1; ?>
                    <a href="?<?= http_build_query($queryParams) ?>" class="px-3 py-1 text-sm bg-white border border-outline-variant rounded hover:bg-gray-50 text-primary">Previous</a>
                <?php else: ?>
                    <button disabled class="px-3 py-1 text-sm bg-gray-100 border border-outline-variant rounded text-gray-400 cursor-not-allowed">Previous</button>
                <?php endif; ?>
                
                <?php if ($currentPage < $totalPages): ?>
                    <?php $queryParams['page'] = $currentPage + 1; ?>
                    <a href="?<?= http_build_query($queryParams) ?>" class="px-3 py-1 text-sm bg-white border border-outline-variant rounded hover:bg-gray-50 text-primary">Next</a>
                <?php else: ?>
                    <button disabled class="px-3 py-1 text-sm bg-gray-100 border border-outline-variant rounded text-gray-400 cursor-not-allowed">Next</button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>