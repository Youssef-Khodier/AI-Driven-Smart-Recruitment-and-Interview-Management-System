<?php $title = 'Simulated AI-Ranked Shortlist'; ?>
<?php ob_start(); ?>
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Simulated AI-Ranked Shortlist</h1>
            <p class="text-text-muted mt-1">Rankings are simulated and reviewable — not an external or final hiring decision for <?= e($requisition['title']) ?>.</p>
        </div>
        <a href="<?= e(url('hr.requisitions.show', [$requisition['job_id']])) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to Requisition
        </a>
    </div>

    <?php if ($flash = \App\Core\Session::getFlash('success')): ?>
        <div class="bg-success-bg border border-success/30 text-success-dark p-4 rounded-lg text-sm">
            <?= e($flash) ?>
        </div>
    <?php endif; ?>
    <?php if ($flash = \App\Core\Session::getFlash('error')): ?>
        <div class="bg-error/10 border border-error text-error p-4 rounded-lg text-sm">
            <?= e($flash) ?>
        </div>
    <?php endif; ?>

    <div class="bg-surface-container-low border border-border-base rounded-lg p-4 flex gap-4">
        <form method="POST" action="<?= e(url('hr.screening.recalculate', [$requisition['job_id']])) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="px-4 py-2 bg-secondary text-white rounded-md shadow-sm text-sm font-medium hover:bg-blue-800 transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">calculate</span> Recalculate Scores
            </button>
        </form>
        <a href="<?= e(url('hr.screening.triage', [$requisition['job_id']])) ?>" class="px-4 py-2 border border-outline-variant text-primary bg-white rounded-md shadow-sm text-sm font-medium hover:bg-surface-container-highest transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">alt_route</span> Run Triage
        </a>
    </div>

    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-surface-container-lowest text-text-muted tracking-wider border-b border-border-base">
                <tr>
                    <th class="px-6 py-3 font-medium">Rank</th>
                    <th class="px-6 py-3 font-medium">Candidate</th>
                    <th class="px-6 py-3 font-medium text-center">Match Score</th>
                    <th class="px-6 py-3 font-medium">Experience</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Applied Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
                <?php foreach ($applications as $index => $app): ?>
                    <?php 
                        $breakdown = json_decode($app['match_score_breakdown'] ?? 'null', true);
                        $missingFlags = [];
                        if ($breakdown && isset($breakdown['skills'])) {
                            foreach ($breakdown['skills'] as $skill) {
                                if (!$skill['found']) {
                                    $missingFlags[] = $skill['name'];
                                }
                            }
                        }
                    ?>
                    <tr class="hover:bg-surface-container-lowest transition-colors">
                        <td class="px-6 py-4 font-bold text-primary">#<?= $index + 1 ?></td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-primary"><?= e($app['name']) ?></div>
                            <div class="text-xs text-text-muted"><?= e($app['email']) ?></div>
                            <?php if (!empty($missingFlags)): ?>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <?php foreach ($missingFlags as $flag): ?>
                                        <span class="inline-block bg-error/10 text-error text-[10px] px-1.5 py-0.5 rounded">Missing: <?= e($flag) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="inline-flex items-center justify-center w-10 h-10 rounded-full <?= (int)$app['match_score'] >= 80 ? 'bg-success-bg text-success font-bold' : ((int)$app['match_score'] >= 50 ? 'bg-yellow-100 text-yellow-800 font-bold' : 'bg-gray-100 text-gray-600') ?>">
                                <?= (int)$app['match_score'] ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-primary"><?= e($app['years_experience']) ?> years</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-800">
                                <?= e($app['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-text-muted whitespace-nowrap"><?= e(date('M d, Y', strtotime($app['applied_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($applications)): ?>
                    <tr><td colspan="6" class="px-6 py-8 text-center text-text-muted italic">No applications found for this requisition.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../../layouts/app.php'; ?>
