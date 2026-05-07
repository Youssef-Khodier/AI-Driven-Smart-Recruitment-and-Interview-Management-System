<div class="max-w-5xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Feedback Governance</h1>
            <p class="text-text-muted mt-2 text-lg"><?= e($application['candidate_name']) ?> — <?= e($application['job_title']) ?></p>
        </div>
        <a href="<?= e(url('hr.feedback-governance.index')) ?>" class="text-info hover:underline text-sm font-medium">&larr; Back to Governance</a>
    </div>

    <!-- Concern Flags -->
    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-primary mb-4 flex items-center">
            <span class="material-symbols-outlined mr-2 text-error">flag</span>Concern Flags (<?= count($flags) ?>)
        </h2>
        <?php foreach ($flags as $flag): ?>
            <div class="border border-border-base rounded-lg p-4 mb-3 <?= $flag['status'] === 'OPEN' ? 'bg-error-bg/30' : 'bg-bg-alt' ?>">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="font-medium text-primary"><?= e($flag['category']) ?></span>
                        <span class="text-sm text-<?= $flag['severity'] === 'HIGH' ? 'error' : 'warning' ?> ml-2 font-medium">[<?= e($flag['severity']) ?>]</span>
                        <p class="text-text-muted text-sm mt-1"><?= e($flag['explanation'] ?? '') ?></p>
                        <p class="text-xs text-text-muted mt-1">By <?= e($flag['created_by_name']) ?> on <?= e(date('M j, Y', strtotime($flag['created_at']))) ?></p>
                    </div>
                    <span class="px-2 py-1 rounded text-xs font-medium <?= $flag['status'] === 'OPEN' ? 'bg-error-bg text-error' : 'bg-success-bg text-success' ?>"><?= e($flag['status']) ?></span>
                </div>
                <?php if ($flag['status'] === 'OPEN'): ?>
                    <form method="POST" action="<?= e(url('hr.flags.resolve', [$flag['flag_id']])) ?>" class="mt-4 border-t border-border-base pt-4">
                        <?= csrf_field() ?>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-primary mb-1">Resolution</label>
                                <select name="resolution_status" class="w-full border border-border-base rounded-lg px-3 py-2 bg-bg-base text-primary text-sm" required>
                                    <?php foreach ($resolutionStatuses as $s): if ($s === 'OPEN') continue; ?>
                                        <option value="<?= e($s) ?>"><?= e($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-primary mb-1">Rationale</label>
                                <input name="resolution_rationale" class="w-full border border-border-base rounded-lg px-3 py-2 bg-bg-base text-primary text-sm" required>
                            </div>
                        </div>
                        <button type="submit" class="mt-3 bg-primary hover:bg-primary-light text-white px-4 py-2 rounded-lg text-sm font-medium">Resolve Flag</button>
                    </form>
                <?php elseif ($flag['resolved_by_name']): ?>
                    <p class="text-xs text-text-muted mt-2">Resolved by <?= e($flag['resolved_by_name']) ?> — <?= e($flag['resolution_rationale'] ?? '') ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if (empty($flags)): ?>
            <p class="text-text-muted">No concern flags for this application.</p>
        <?php endif; ?>
    </div>

    <!-- Normalized Snapshots -->
    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-primary mb-4 flex items-center">
            <span class="material-symbols-outlined mr-2 text-info">analytics</span>Normalization Snapshots
        </h2>
        <?php foreach ($snapshots as $snap): ?>
            <div class="border border-border-base rounded-lg p-4 mb-3">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="text-2xl font-bold text-primary"><?= e(number_format($snap['aggregate_score'], 1)) ?></span>
                        <span class="text-text-muted text-sm ml-2">/100</span>
                        <span class="ml-3 px-2 py-1 rounded text-xs font-medium <?= $snap['normalization_status'] === 'APPLIED' ? 'bg-success-bg text-success' : 'bg-warning-bg text-warning' ?>"><?= e($snap['normalization_status']) ?></span>
                        <span class="ml-2 px-2 py-1 rounded text-xs font-medium <?= (int)$snap['missing_feedback_count'] === 0 ? 'bg-success-bg text-success' : 'bg-warning-bg text-warning' ?>">
                            <?= (int)$snap['missing_feedback_count'] === 0 ? 'CONSENSUS READY' : 'WAITING FOR FEEDBACK' ?>
                        </span>
                    </div>
                    <span class="text-text-muted text-sm"><?= e(date('M j, Y g:i a', strtotime($snap['created_at']))) ?></span>
                </div>
                <p class="text-sm text-text-muted mt-1">Included: <?= e($snap['included_feedback_count'] ?? 0) ?> | Missing: <?= e($snap['missing_feedback_count'] ?? 0) ?> | Recommendation: <?= e($snap['recommendation']) ?></p>
                <?php $normalized = json_decode($snap['normalized_score_summary'] ?? '{}', true); $averages = $normalized['averages'] ?? []; ?>
                <?php if (!empty($averages)): ?>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mt-3 text-xs">
                        <span class="bg-surface-container-low rounded px-2 py-1 text-text-muted">Technical <?= e($averages['technical_score'] ?? '0') ?>/10</span>
                        <span class="bg-surface-container-low rounded px-2 py-1 text-text-muted">Communication <?= e($averages['communication_score'] ?? '0') ?>/10</span>
                        <span class="bg-surface-container-low rounded px-2 py-1 text-text-muted">Culture <?= e($averages['culture_fit_score'] ?? '0') ?>/10</span>
                        <span class="bg-surface-container-low rounded px-2 py-1 text-text-muted">Overall <?= e($averages['overall_score'] ?? '0') ?>/10</span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if (empty($snapshots)): ?>
            <p class="text-text-muted">No normalization snapshots yet.</p>
        <?php endif; ?>
    </div>

    <!-- Competency Gap Analysis -->
    <?php if (!empty($gapSnapshots)): ?>
    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-primary mb-4 flex items-center">
            <span class="material-symbols-outlined mr-2 text-warning">bar_chart</span>Competency Gap Analysis
        </h2>
        <div class="space-y-3">
            <?php foreach ($gapSnapshots as $gap): ?>
                <div class="flex items-center gap-4">
                    <span class="w-32 text-sm font-medium text-primary"><?= e($gap['competency']) ?></span>
                    <div class="flex-1 bg-bg-alt rounded-full h-6 relative overflow-hidden">
                        <div class="h-full rounded-full <?= $gap['severity'] === 'MEETS' ? 'bg-success' : ($gap['severity'] === 'MINOR_GAP' ? 'bg-info' : ($gap['severity'] === 'MODERATE_GAP' ? 'bg-warning' : 'bg-error')) ?>" style="width: <?= min(100, max(5, ($gap['candidate_score'] / max(1, $gap['benchmark_score'])) * 100)) ?>%"></div>
                    </div>
                    <span class="w-20 text-sm text-right text-text-muted"><?= e(number_format($gap['candidate_score'], 1)) ?>/<?= e(number_format($gap['benchmark_score'], 1)) ?></span>
                    <span class="w-28 text-xs font-medium <?= $gap['severity'] === 'MEETS' ? 'text-success' : ($gap['severity'] === 'CRITICAL_GAP' ? 'text-error' : 'text-warning') ?>"><?= e($gap['severity']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Candidate Sentiment -->
    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-primary mb-4 flex items-center">
            <span class="material-symbols-outlined mr-2 text-info">sentiment_satisfied</span>Candidate Sentiment
        </h2>
        <?php foreach ($sentiments as $s): ?>
            <div class="border border-border-base rounded-lg p-4 mb-3">
                <div class="flex items-center gap-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="text-xl <?= $i <= $s['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>">★</span>
                    <?php endfor; ?>
                    <span class="text-sm text-text-muted ml-2"><?= e(date('M j, Y', strtotime($s['submitted_at']))) ?></span>
                </div>
                <?php if ($s['comment']): ?>
                    <p class="text-text-muted text-sm mt-2">"<?= e($s['comment']) ?>"</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if (empty($sentiments)): ?>
            <p class="text-text-muted">No sentiment submitted yet.</p>
        <?php endif; ?>
    </div>

    <!-- Debriefs -->
    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-primary mb-4 flex items-center">
            <span class="material-symbols-outlined mr-2 text-primary">groups</span>Debrief Records
        </h2>
        <?php foreach ($debriefs as $d): ?>
            <div class="border border-border-base rounded-lg p-4 mb-3">
                <div class="flex justify-between items-center">
                    <span class="px-2 py-1 rounded text-xs font-medium bg-info/10 text-info"><?= e($d['status']) ?></span>
                    <span class="text-text-muted text-sm"><?= e(date('M j, Y', strtotime($d['created_at']))) ?></span>
                </div>
                <?php if ($d['status'] === 'PENDING'): ?>
                    <p class="text-sm text-success mt-2">Consensus record created because all required feedback is submitted.</p>
                <?php endif; ?>
                <?php if ($d['rationale']): ?>
                    <p class="text-sm text-text-muted mt-2"><?= e($d['rationale']) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if (empty($debriefs)): ?>
            <p class="text-text-muted">No debrief records.</p>
        <?php endif; ?>
    </div>
</div>
