<?php $title = 'Duplicate Candidates'; ?>
<?php ob_start(); ?>
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Duplicate Candidates</h1>
            <p class="text-text-muted mt-1">Review AI-suggested duplicate applicant profiles for <?= e($requisition['title']) ?>.</p>
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

    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
        <div class="p-6 border-b border-border-base bg-surface-container-lowest">
            <h2 class="text-lg font-semibold text-primary">Suggested Matches (<?= count($suggestions) ?>)</h2>
        </div>
        
        <?php if (empty($suggestions)): ?>
            <div class="p-8 text-center text-text-muted">
                <span class="material-symbols-outlined text-[48px] opacity-20 mb-2">task_alt</span>
                <p>No new duplicate candidates found in this applicant pool.</p>
            </div>
        <?php else: ?>
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-surface-container-lowest text-text-muted tracking-wider border-b border-border-base">
                    <tr>
                        <th class="px-6 py-3 font-medium">Confidence</th>
                        <th class="px-6 py-3 font-medium">Candidate A</th>
                        <th class="px-6 py-3 font-medium">Candidate B</th>
                        <th class="px-6 py-3 font-medium">Matching Evidence</th>
                        <th class="px-6 py-3 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-base">
                    <?php foreach ($suggestions as $suggestion): ?>
                        <?php 
                            $confClass = [
                                'HIGH' => 'bg-error/10 text-error',
                                'MEDIUM' => 'bg-warning-bg text-warning-dark',
                                'LOW' => 'bg-blue-100 text-blue-800'
                            ][$suggestion['confidence']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded text-xs font-bold <?= $confClass ?>">
                                    <?= e($suggestion['confidence']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-primary"><?= e($suggestion['candidate_a']['name']) ?></div>
                                <div class="text-xs text-text-muted"><?= e($suggestion['candidate_a']['email']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-primary"><?= e($suggestion['candidate_b']['name']) ?></div>
                                <div class="text-xs text-text-muted"><?= e($suggestion['candidate_b']['email']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <?php foreach ($suggestion['evidence'] as $key => $val): ?>
                                        <span class="inline-block bg-surface-container text-text-muted text-[10px] px-1.5 py-0.5 rounded border border-border-base">
                                            <?= e(str_replace('_', ' ', $key)) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php 
                                    $url = url('hr.screening.duplicates.resolve.form', [$requisition['job_id']]) . 
                                           '?candidate_a=' . $suggestion['candidate_a']['candidate_id'] . 
                                           '&candidate_b=' . $suggestion['candidate_b']['candidate_id'] . 
                                           '&confidence=' . $suggestion['confidence'];
                                ?>
                                <a href="<?= e($url) ?>" class="inline-flex items-center justify-center px-3 py-1.5 border border-outline-variant rounded text-xs font-medium text-primary bg-white hover:bg-surface-container-highest transition-colors shadow-sm">
                                    Review
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../../layouts/app.php'; ?>
