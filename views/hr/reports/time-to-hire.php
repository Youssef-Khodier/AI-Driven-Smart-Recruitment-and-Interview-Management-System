<div class="space-y-8">
    <div>
        <h1 class="text-3xl font-bold text-primary">Time-to-Hire Summary</h1>
        <p class="text-text-muted mt-2">Average days from application submission to first HIRED transition.</p>
    </div>

    <section class="bg-card-surface rounded-xl border border-border-base shadow-ambient overflow-hidden">
        <div class="p-4 border-b border-border-base"><h2 class="font-semibold text-primary">By Requisition</h2></div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-surface-container-low text-text-muted uppercase text-xs"><tr><th class="text-left p-3">Requisition</th><th class="text-left p-3">Department</th><th class="text-right p-3">Hired</th><th class="text-right p-3">Avg Days</th></tr></thead>
                <tbody class="divide-y divide-border-base">
                    <?php if (empty($requisitions)): ?><tr><td colspan="4" class="p-6 text-center text-text-muted">No hired applications found.</td></tr><?php endif; ?>
                    <?php foreach ($requisitions as $row): ?>
                        <tr><td class="p-3 font-medium"><?= e($row['title']) ?></td><td class="p-3 text-text-muted"><?= e($row['department_name']) ?></td><td class="p-3 text-right"><?= e($row['hired_count']) ?></td><td class="p-3 text-right font-semibold"><?= e($row['average_days'] ?? 'N/A') ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="bg-card-surface rounded-xl border border-border-base shadow-ambient overflow-hidden">
        <div class="p-4 border-b border-border-base"><h2 class="font-semibold text-primary">By Department</h2></div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-surface-container-low text-text-muted uppercase text-xs"><tr><th class="text-left p-3">Department</th><th class="text-right p-3">Hired</th><th class="text-right p-3">Avg Days</th></tr></thead>
                <tbody class="divide-y divide-border-base">
                    <?php if (empty($departments)): ?><tr><td colspan="3" class="p-6 text-center text-text-muted">No hired applications found.</td></tr><?php endif; ?>
                    <?php foreach ($departments as $row): ?>
                        <tr><td class="p-3 font-medium"><?= e($row['department_name']) ?></td><td class="p-3 text-right"><?= e($row['hired_count']) ?></td><td class="p-3 text-right font-semibold"><?= e($row['average_days'] ?? 'N/A') ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
