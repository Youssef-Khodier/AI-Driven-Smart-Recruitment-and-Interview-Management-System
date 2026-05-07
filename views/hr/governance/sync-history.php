<?php $title = 'Job-Board Sync History'; ?>
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Job-Board Sync History</h1>
            <p class="text-text-muted mt-1">Local database sync records for <?= e($requisition['title']) ?>.</p>
        </div>
        <a href="<?= e(url('hr.requisitions.show', [$requisition['job_id']])) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to Requisition
        </a>
    </div>

    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-surface-container-lowest text-text-muted tracking-wider border-b border-border-base">
                <tr>
                    <th class="px-6 py-3 font-medium">Platform</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Queued</th>
                    <th class="px-6 py-3 font-medium">Completed</th>
                    <th class="px-6 py-3 font-medium">Created By</th>
                    <th class="px-6 py-3 font-medium">Payload Summary</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
                <?php foreach ($syncRecords as $record): ?>
                    <?php $payload = json_decode($record['payload_summary'] ?? 'null', true) ?: []; ?>
                    <tr class="hover:bg-surface-container-lowest transition-colors">
                        <td class="px-6 py-4 font-medium text-primary"><?= e($record['platform_name']) ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs font-bold <?= $record['status'] === 'PUBLISHED' ? 'bg-success-bg text-success' : ($record['status'] === 'UNPUBLISHED' ? 'bg-gray-100 text-gray-800' : 'bg-warning-bg text-warning-dark') ?>">
                                <?= e($record['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-text-muted whitespace-nowrap"><?= e($record['queued_at']) ?></td>
                        <td class="px-6 py-4 text-text-muted whitespace-nowrap"><?= e($record['completed_at'] ?? '-') ?></td>
                        <td class="px-6 py-4 text-primary"><?= e($record['creator_name']) ?></td>
                        <td class="px-6 py-4 text-text-muted">
                            <div class="max-w-xs">
                                <div class="font-medium text-primary"><?= e($payload['title'] ?? '-') ?></div>
                                <div class="text-xs"><?= e($payload['department'] ?? '-') ?></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($syncRecords)): ?>
                    <tr><td colspan="6" class="px-6 py-8 text-center text-text-muted italic">No sync records found for this requisition.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
