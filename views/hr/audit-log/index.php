<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-primary">Audit Log</h1>
        <p class="text-text-muted mt-2">Read-only consolidated audit history across account, interview, offer, application, and requisition changes.</p>
    </div>

    <form method="GET" action="<?= e(url('hr.audit-log.index')) ?>" class="bg-card-surface border border-border-base rounded-xl p-4 shadow-ambient grid grid-cols-1 md:grid-cols-6 gap-3">
        <input class="rounded border-border-base" type="date" name="from" value="<?= e($filters['from']) ?>" aria-label="From date">
        <input class="rounded border-border-base" type="date" name="to" value="<?= e($filters['to']) ?>" aria-label="To date">
        <input class="rounded border-border-base" type="text" name="actor" value="<?= e($filters['actor']) ?>" placeholder="Actor name/email">
        <input class="rounded border-border-base" type="text" name="action" value="<?= e($filters['action']) ?>" placeholder="Action">
        <select class="rounded border-border-base" name="entity">
            <option value="">All entities</option>
            <?php foreach ($entities as $entity): ?>
                <option value="<?= e($entity) ?>"<?= selected($filters['entity'], $entity) ?>><?= e($entity) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="bg-primary text-white px-4 py-2 rounded font-semibold" type="submit">Filter</button>
    </form>

    <div class="bg-card-surface rounded-xl border border-border-base shadow-ambient overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-surface-container-low text-text-muted uppercase text-xs tracking-wide">
                <tr><th class="text-left p-3">Time</th><th class="text-left p-3">Actor</th><th class="text-left p-3">Entity</th><th class="text-left p-3">Action</th><th class="text-left p-3">Summary</th></tr>
            </thead>
            <tbody class="divide-y divide-border-base">
                <?php if (empty($rows)): ?>
                    <tr><td colspan="5" class="p-6 text-center text-text-muted">No audit entries match the filters.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td class="p-3 whitespace-nowrap"><?= e(date('Y-m-d H:i', strtotime($row['occurred_at']))) ?></td>
                        <td class="p-3"><div class="font-medium"><?= e($row['actor_name']) ?></div><div class="text-xs text-text-muted"><?= e($row['actor_email']) ?></div></td>
                        <td class="p-3"><span class="bg-surface-container-low px-2 py-1 rounded text-xs"><?= e($row['entity_type']) ?> #<?= e($row['entity_id']) ?></span></td>
                        <td class="p-3 font-medium"><?= e($row['action']) ?></td>
                        <td class="p-3 text-text-muted"><?= e($row['summary']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php $totalPages = (int) ceil($total / $per_page); ?>
    <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-between">
            <span class="text-sm text-text-muted">Page <?= e($page) ?> of <?= e($totalPages) ?></span>
            <div class="flex gap-2">
                <?php $query = $_GET; ?>
                <?php if ($page > 1): $query['page'] = $page - 1; ?>
                    <a class="px-3 py-1.5 rounded border border-border-base text-sm" href="<?= e(url('/hr/audit-log?' . http_build_query($query))) ?>">Previous</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): $query['page'] = $page + 1; ?>
                    <a class="px-3 py-1.5 rounded border border-border-base text-sm" href="<?= e(url('/hr/audit-log?' . http_build_query($query))) ?>">Next</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
