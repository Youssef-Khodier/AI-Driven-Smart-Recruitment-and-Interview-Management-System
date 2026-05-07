<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary">Diversity & Inclusion Audit</h1>
            <p class="text-text-muted mt-2">Aggregate counts from stored candidate demographic fields with consent. No individual demographic profiles are shown.</p>
        </div>
        <a href="<?= e(url('hr.compliance.index')) ?>" class="text-secondary hover:underline text-sm font-medium">Back to Compliance</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-card-surface border border-border-base rounded-lg p-5 shadow-ambient">
            <div class="text-sm text-text-muted">Consented candidates</div>
            <div class="text-3xl font-bold text-primary mt-2"><?= e($summary['total_consented_candidates']) ?></div>
        </div>
        <div class="bg-card-surface border border-border-base rounded-lg p-5 shadow-ambient">
            <div class="text-sm text-text-muted">Applications with consented demographics</div>
            <div class="text-3xl font-bold text-primary mt-2"><?= e($summary['total_applications']) ?></div>
        </div>
    </div>

    <?php
        $groups = [
            'Gender' => $summary['gender'],
            'Ethnicity' => $summary['ethnicity'],
            'Disability' => $summary['disability'],
            'Veteran Status' => $summary['veteran'],
        ];
    ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <?php foreach ($groups as $label => $rows): ?>
            <section class="bg-card-surface border border-border-base rounded-lg shadow-ambient overflow-hidden">
                <div class="p-5 border-b border-border-base">
                    <h2 class="text-xl font-semibold text-primary"><?= e($label) ?></h2>
                </div>
                <table class="min-w-full text-sm">
                    <thead class="bg-surface-container-low text-text-muted uppercase text-xs tracking-wide">
                        <tr><th class="text-left p-3">Category</th><th class="text-right p-3">Count</th></tr>
                    </thead>
                    <tbody class="divide-y divide-border-base">
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="2" class="p-5 text-center text-text-muted">No consented data available.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td class="p-3"><?= e($row['category']) ?></td>
                                <td class="p-3 text-right font-semibold"><?= e($row['count']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endforeach; ?>
    </div>

    <section class="bg-card-surface border border-border-base rounded-lg shadow-ambient overflow-hidden">
        <div class="p-5 border-b border-border-base">
            <h2 class="text-xl font-semibold text-primary">Pipeline by Gender Category</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-surface-container-low text-text-muted uppercase text-xs tracking-wide">
                    <tr><th class="text-left p-3">Application Status</th><th class="text-left p-3">Category</th><th class="text-right p-3">Count</th></tr>
                </thead>
                <tbody class="divide-y divide-border-base">
                    <?php if (empty($summary['pipeline'])): ?>
                        <tr><td colspan="3" class="p-6 text-center text-text-muted">No application demographic aggregates available.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($summary['pipeline'] as $row): ?>
                        <tr>
                            <td class="p-3"><?= e($row['status']) ?></td>
                            <td class="p-3"><?= e($row['category']) ?></td>
                            <td class="p-3 text-right font-semibold"><?= e($row['count']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
