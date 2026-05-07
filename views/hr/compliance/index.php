<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary">Compliance Checks</h1>
            <p class="text-text-muted mt-2">Manual HR controls for diversity aggregates, integrity archiving, compliance evidence, and notification escalation.</p>
        </div>
        <a href="<?= e(url('hr.compliance.diversity')) ?>" class="inline-flex items-center justify-center gap-2 bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold">
            <span class="material-symbols-outlined text-[18px]">diversity_3</span> Diversity Report
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-card-surface border border-border-base rounded-lg p-5 shadow-ambient">
            <div class="text-sm text-text-muted">Consented demographic profiles</div>
            <div class="text-3xl font-bold text-primary mt-2"><?= e($diversitySummary['total_consented_candidates']) ?></div>
        </div>
        <div class="bg-card-surface border border-border-base rounded-lg p-5 shadow-ambient">
            <div class="text-sm text-text-muted">Closed requisitions ready to archive</div>
            <div class="text-3xl font-bold text-primary mt-2"><?= e($archiveSummary['closed_requisitions']) ?></div>
        </div>
        <div class="bg-card-surface border border-border-base rounded-lg p-5 shadow-ambient">
            <div class="text-sm text-text-muted">Rejected candidate applications ready to archive</div>
            <div class="text-3xl font-bold text-primary mt-2"><?= e($archiveSummary['rejected_applications']) ?></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <section class="bg-card-surface border border-border-base rounded-lg p-6 shadow-ambient">
            <h2 class="text-xl font-semibold text-primary">Notification Escalator</h2>
            <p class="text-sm text-text-muted mt-1">Creates in-app notification records for overdue feedback and pending offer actions. Duplicate notifications are skipped.</p>
            <form method="POST" action="<?= e(url('hr.checks.run')) ?>" class="mt-4">
                <?= csrf_field() ?>
                <button type="submit" class="bg-primary text-white px-5 py-2.5 rounded-lg font-semibold">Run Notification Checks</button>
            </form>
        </section>

        <section class="bg-card-surface border border-border-base rounded-lg p-6 shadow-ambient">
            <h2 class="text-xl font-semibold text-primary">Database Integrity Manager</h2>
            <p class="text-sm text-text-muted mt-1">Archives closed requisitions and rejected candidate applications using existing archive fields and archive action logs.</p>
            <form method="POST" action="<?= e(url('hr.compliance.archive')) ?>" class="mt-4">
                <?= csrf_field() ?>
                <button type="submit" class="bg-secondary text-white px-5 py-2.5 rounded-lg font-semibold">Archive Eligible Records</button>
            </form>
        </section>
    </div>

    <section class="bg-card-surface border border-border-base rounded-lg shadow-ambient overflow-hidden">
        <div class="p-5 border-b border-border-base">
            <h2 class="text-xl font-semibold text-primary">Recent Compliance Runs</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-surface-container-low text-text-muted uppercase text-xs tracking-wide">
                    <tr><th class="text-left p-3">Started</th><th class="text-left p-3">Type</th><th class="text-left p-3">Actor</th><th class="text-left p-3">Findings</th><th class="text-left p-3">Summary</th></tr>
                </thead>
                <tbody class="divide-y divide-border-base">
                    <?php if (empty($recentBatches)): ?>
                        <tr><td colspan="5" class="p-6 text-center text-text-muted">No compliance checks have been run yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recentBatches as $batch): ?>
                        <tr>
                            <td class="p-3 whitespace-nowrap"><?= e(date('Y-m-d H:i', strtotime($batch['started_at']))) ?></td>
                            <td class="p-3"><?= e($batch['check_type']) ?></td>
                            <td class="p-3"><?= e($batch['actor_name']) ?></td>
                            <td class="p-3"><?= e($batch['total_findings']) ?></td>
                            <td class="p-3 text-text-muted"><?= e($batch['summary_message'] ?? $batch['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="bg-card-surface border border-border-base rounded-lg shadow-ambient overflow-hidden">
        <div class="p-5 border-b border-border-base">
            <h2 class="text-xl font-semibold text-primary">Recent Archive Evidence</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-surface-container-low text-text-muted uppercase text-xs tracking-wide">
                    <tr><th class="text-left p-3">Time</th><th class="text-left p-3">Entity</th><th class="text-left p-3">Status</th><th class="text-left p-3">Actor</th><th class="text-left p-3">Reason</th></tr>
                </thead>
                <tbody class="divide-y divide-border-base">
                    <?php if (empty($recentArchiveActions)): ?>
                        <tr><td colspan="5" class="p-6 text-center text-text-muted">No archive actions recorded yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recentArchiveActions as $action): ?>
                        <tr>
                            <td class="p-3 whitespace-nowrap"><?= e(date('Y-m-d H:i', strtotime($action['action_timestamp']))) ?></td>
                            <td class="p-3"><?= e($action['entity_type']) ?> #<?= e($action['entity_id']) ?></td>
                            <td class="p-3"><?= e($action['action_status']) ?></td>
                            <td class="p-3"><?= e($action['actor_name']) ?></td>
                            <td class="p-3 text-text-muted"><?= e($action['reason']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
