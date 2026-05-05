<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-primary">Pipeline Report</h1>
        <p class="text-text-muted mt-2">Application counts by status for open requisitions.</p>
    </div>

    <div class="bg-card-surface rounded-xl border border-border-base shadow-ambient overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-surface-container-low text-text-muted uppercase text-xs tracking-wide">
                <tr>
                    <th class="text-left p-3">Requisition</th>
                    <th class="text-left p-3">Department</th>
                    <?php foreach ($statuses as $status): ?>
                        <th class="text-right p-3"><?= e($status) ?></th>
                    <?php endforeach; ?>
                    <th class="text-right p-3">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
                <?php if (empty($rows)): ?>
                    <tr><td colspan="<?= e(count($statuses) + 3) ?>" class="p-6 text-center text-text-muted">No open requisitions found.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td class="p-3 font-medium text-primary"><?= e($row['title']) ?></td>
                        <td class="p-3 text-text-muted"><?= e($row['department_name']) ?></td>
                        <?php foreach ($statuses as $status): ?>
                            <td class="p-3 text-right"><?= e($row['counts'][$status] ?? 0) ?></td>
                        <?php endforeach; ?>
                        <td class="p-3 text-right font-semibold"><?= e($row['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-surface-container-low font-semibold">
                <tr>
                    <td class="p-3" colspan="2">Totals</td>
                    <?php foreach ($statuses as $status): ?>
                        <td class="p-3 text-right"><?= e($totals[$status] ?? 0) ?></td>
                    <?php endforeach; ?>
                    <td class="p-3 text-right"><?= e($grand_total) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
