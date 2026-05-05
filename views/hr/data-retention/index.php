<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-primary">Candidate Data Retention</h1>
        <p class="text-text-muted mt-2">Eligible candidates have no active applications and their latest application is older than <?= e($retentionDays) ?> days.</p>
    </div>

    <div class="bg-warning-bg border border-warning/20 rounded-xl p-4 text-warning text-sm">
        Retention actions are irreversible. The server rechecks eligibility after submission and writes an audit record for every successful action.
    </div>

    <div class="bg-card-surface rounded-xl border border-border-base shadow-ambient overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-surface-container-low text-text-muted uppercase text-xs tracking-wide">
                <tr><th class="text-left p-3">Candidate</th><th class="text-left p-3">Last Application</th><th class="text-left p-3">Status</th><th class="text-left p-3">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-border-base">
                <?php if (empty($candidates)): ?>
                    <tr><td colspan="4" class="p-6 text-center text-text-muted">No candidates are currently eligible for retention actions.</td></tr>
                <?php endif; ?>
                <?php foreach ($candidates as $candidate): ?>
                    <tr>
                        <td class="p-3"><div class="font-medium text-primary"><?= e($candidate['name']) ?></div><div class="text-xs text-text-muted"><?= e($candidate['email']) ?></div></td>
                        <td class="p-3"><div><?= e(date('Y-m-d', strtotime($candidate['last_applied_at']))) ?></div><div class="text-xs text-text-muted"><?= e($candidate['job_title']) ?></div></td>
                        <td class="p-3"><span class="bg-surface-container-low rounded px-2 py-1 text-xs"><?= e($candidate['status']) ?> / <?= e($candidate['job_status']) ?></span></td>
                        <td class="p-3">
                            <div class="flex flex-col md:flex-row gap-2">
                                <form method="POST" action="<?= e(url('hr.data-retention.anonymize', [$candidate['user_id']])) ?>" class="flex gap-2">
                                    <?= csrf_field() ?>
                                    <input class="w-32 rounded border-border-base text-xs" name="confirm" placeholder="ANONYMIZE">
                                    <button class="px-3 py-1.5 rounded bg-warning text-white text-xs font-semibold" type="submit">Anonymize</button>
                                </form>
                                <form method="POST" action="<?= e(url('hr.data-retention.delete', [$candidate['user_id']])) ?>" class="flex gap-2">
                                    <?= csrf_field() ?>
                                    <input class="w-24 rounded border-border-base text-xs" name="confirm" placeholder="DELETE">
                                    <button class="px-3 py-1.5 rounded bg-error text-white text-xs font-semibold" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
