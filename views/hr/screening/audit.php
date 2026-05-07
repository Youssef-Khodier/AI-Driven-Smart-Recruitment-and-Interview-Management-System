<?php $title = 'Screening Audit Log'; ?>
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Screening Audit Log</h1>
            <p class="text-text-muted mt-1">Audit trail of all screening configuration, scoring, triage, and duplicate actions for <?= e($requisition['title']) ?>.</p>
        </div>
        <a href="<?= e(url('hr.requisitions.show', [$requisition['job_id']])) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to Requisition
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-surface-container-low border border-border-base rounded-lg p-4">
        <form method="GET" action="<?= e(url('hr.screening.audit', [$requisition['job_id']])) ?>" class="flex flex-wrap gap-4 items-end">
            <div class="w-48">
                <label class="block text-xs font-medium text-text-muted mb-1">Action Type</label>
                <select name="action_type" class="w-full rounded border border-border-base px-3 py-2 text-sm focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow bg-white">
                    <option value="">All Actions</option>
                    <option value="CONFIG_CREATED" <?= ($filters['action_type'] ?? '') === 'CONFIG_CREATED' ? 'selected' : '' ?>>CONFIG_CREATED</option>
                    <option value="CONFIG_UPDATED" <?= ($filters['action_type'] ?? '') === 'CONFIG_UPDATED' ? 'selected' : '' ?>>CONFIG_UPDATED</option>
                    <option value="SCORES_RECALCULATED" <?= ($filters['action_type'] ?? '') === 'SCORES_RECALCULATED' ? 'selected' : '' ?>>SCORES_RECALCULATED</option>
                    <option value="TRIAGE_STATUS_CHANGE" <?= ($filters['action_type'] ?? '') === 'TRIAGE_STATUS_CHANGE' ? 'selected' : '' ?>>TRIAGE_STATUS_CHANGE</option>
                    <option value="TRIAGE_EXECUTED" <?= ($filters['action_type'] ?? '') === 'TRIAGE_EXECUTED' ? 'selected' : '' ?>>TRIAGE_EXECUTED</option>
                    <option value="DUPLICATE_CHECK_RUN" <?= ($filters['action_type'] ?? '') === 'DUPLICATE_CHECK_RUN' ? 'selected' : '' ?>>DUPLICATE_CHECK_RUN</option>
                    <option value="DUPLICATE_DECISION" <?= ($filters['action_type'] ?? '') === 'DUPLICATE_DECISION' ? 'selected' : '' ?>>DUPLICATE_DECISION</option>
                </select>
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-text-muted mb-1">From Date</label>
                <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" class="w-full rounded border border-border-base px-3 py-2 text-sm focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow bg-white">
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-text-muted mb-1">To Date</label>
                <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" class="w-full rounded border border-border-base px-3 py-2 text-sm focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow bg-white">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-secondary text-white rounded shadow-sm text-sm font-medium hover:bg-blue-800 transition-colors">Filter</button>
                <a href="<?= e(url('hr.screening.audit', [$requisition['job_id']])) ?>" class="px-4 py-2 border border-outline-variant text-primary bg-white rounded shadow-sm text-sm font-medium hover:bg-surface-container-highest transition-colors">Clear</a>
            </div>
        </form>
    </div>

    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-surface-container-lowest text-text-muted tracking-wider border-b border-border-base">
                <tr>
                    <th class="px-6 py-3 font-medium">Date / Time</th>
                    <th class="px-6 py-3 font-medium">Actor</th>
                    <th class="px-6 py-3 font-medium">Action</th>
                    <th class="px-6 py-3 font-medium">Entity / Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
                <?php foreach ($records as $record): ?>
                    <tr class="hover:bg-surface-container-lowest transition-colors">
                        <td class="px-6 py-4 text-text-muted whitespace-nowrap">
                            <?= e(date('M d, Y H:i:s', strtotime($record['created_at']))) ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-primary"><?= e($record['actor_name'] ?? 'System') ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs font-bold bg-surface-container text-text-muted border border-border-base">
                                <?= e($record['action']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($record['entity_type']): ?>
                                <div class="text-xs text-text-muted mb-1"><?= e($record['entity_type']) ?> #<?= e($record['entity_id'] ?? 'N/A') ?></div>
                            <?php endif; ?>
                            <div class="text-sm font-mono bg-gray-50 text-gray-800 p-2 rounded border border-gray-200 overflow-x-auto max-w-lg">
                                <?php
                                    $details = [];
                                    if ($record['old_values']) $details['old'] = json_decode($record['old_values'], true);
                                    if ($record['new_values']) $details['new'] = json_decode($record['new_values'], true);
                                    echo e(json_encode($details, JSON_PRETTY_PRINT));
                                ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($records)): ?>
                    <tr><td colspan="4" class="px-6 py-8 text-center text-text-muted italic">No audit records found matching criteria.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if ($pagination['total'] > 1): ?>
            <div class="px-6 py-4 border-t border-border-base bg-surface-container-lowest flex items-center justify-between">
                <span class="text-sm text-text-muted">Showing page <?= $pagination['current'] ?> of <?= $pagination['total'] ?> (<?= $pagination['total_records'] ?> total)</span>
                <div class="flex gap-2">
                    <?php if ($pagination['current'] > 1): ?>
                        <a href="?page=<?= $pagination['current'] - 1 ?>" class="px-3 py-1 border border-border-base rounded text-sm text-primary hover:bg-surface-container-highest transition-colors">Previous</a>
                    <?php endif; ?>
                    <?php if ($pagination['current'] < $pagination['total']): ?>
                        <a href="?page=<?= $pagination['current'] + 1 ?>" class="px-3 py-1 border border-border-base rounded text-sm text-primary hover:bg-surface-container-highest transition-colors">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
