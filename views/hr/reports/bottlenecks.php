<div class="max-w-5xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Pipeline Bottleneck Analysis</h1>
            <p class="text-text-muted mt-2 text-lg">Identify slow stages and conversion drop-offs in your recruitment pipeline.</p>
        </div>
        <a href="<?= e(url('hr.dashboard')) ?>" class="text-info hover:underline text-sm font-medium">&larr; Back to Dashboard</a>
    </div>

    <!-- Stage Conversion Funnel -->
    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-primary mb-4 flex items-center">
            <span class="material-symbols-outlined mr-2 text-info">filter_alt</span>Stage Conversion Rates
        </h2>
        <div class="space-y-4">
            <?php foreach ($conversionRates as $rate): ?>
                <div class="flex items-center gap-4">
                    <span class="w-28 text-sm font-medium text-primary text-right"><?= e($rate['from_stage']) ?></span>
                    <span class="text-text-muted">→</span>
                    <span class="w-28 text-sm font-medium text-primary"><?= e($rate['to_stage']) ?></span>
                    <div class="flex-1 bg-bg-alt rounded-full h-5 relative overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-info to-success transition-all" style="width: <?= max(3, $rate['conversion_rate']) ?>%"></div>
                    </div>
                    <span class="w-16 text-right font-bold text-primary"><?= e($rate['conversion_rate']) ?>%</span>
                    <span class="w-24 text-xs text-text-muted text-right"><?= e($rate['to_count']) ?>/<?= e($rate['from_count']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bottleneck Alerts -->
    <?php if (!empty($bottlenecks)): ?>
    <div class="bg-error-bg/30 border border-error rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-error mb-4 flex items-center">
            <span class="material-symbols-outlined mr-2">warning</span>Bottleneck Alerts
        </h2>
        <?php foreach ($bottlenecks as $bn): ?>
            <div class="border border-error/30 rounded-lg p-4 mb-3 bg-card-surface">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="font-bold text-primary text-lg"><?= e($bn['stage']) ?></span>
                        <span class="ml-2 px-2 py-1 rounded text-xs font-bold <?= $bn['severity'] === 'HIGH' ? 'bg-error text-white' : 'bg-warning text-white' ?>"><?= e($bn['severity']) ?></span>
                    </div>
                    <span class="text-2xl font-bold text-error"><?= e($bn['avg_days']) ?> days avg</span>
                </div>
                <p class="text-text-muted text-sm mt-2"><?= e($bn['recommendation']) ?></p>
                <p class="text-xs text-text-muted mt-1">Max: <?= e($bn['max_days']) ?> days | Threshold: <?= e($bn['threshold_days']) ?> days | Transitions: <?= e($bn['transition_count']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Average Stage Durations -->
    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border-base bg-bg-alt">
            <h2 class="text-xl font-semibold text-primary flex items-center">
                <span class="material-symbols-outlined mr-2 text-warning">schedule</span>Average Stage Durations
            </h2>
        </div>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-bg-alt border-b border-border-base text-text-muted text-sm uppercase tracking-wider">
                    <th class="px-6 py-3 font-semibold">Stage</th>
                    <th class="px-6 py-3 font-semibold">Avg Days</th>
                    <th class="px-6 py-3 font-semibold">Max Days</th>
                    <th class="px-6 py-3 font-semibold">Transitions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
                <?php foreach ($stageDurations as $sd): ?>
                    <tr class="hover:bg-bg-alt transition-colors">
                        <td class="px-6 py-4 text-primary font-medium"><?= e($sd['stage']) ?></td>
                        <td class="px-6 py-4 font-bold <?= (float)$sd['avg_days'] > 7 ? 'text-error' : 'text-primary' ?>"><?= e($sd['avg_days']) ?></td>
                        <td class="px-6 py-4 text-text-muted"><?= e($sd['max_days']) ?></td>
                        <td class="px-6 py-4 text-text-muted"><?= e($sd['transition_count']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($stageDurations)): ?>
                    <tr><td colspan="4" class="px-6 py-8 text-center text-text-muted">No stage duration data available yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
