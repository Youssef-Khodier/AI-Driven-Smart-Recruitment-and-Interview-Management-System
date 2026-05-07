<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-primary flex flex-col md:flex-row md:items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[32px] hidden md:block">gavel</span>
            <span>Final Evaluation: <span class="text-secondary"><?= e($application['candidate_name']) ?></span> for <?= e($application['job_title']) ?></span>
        </h1>
        <a href="<?= e(url('hr.applications.index', [$application['job_id']])) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1 shrink-0">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Evidence Column -->
        <div class="space-y-6">
            <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6">
                <h2 class="text-lg font-semibold text-primary mb-4 border-b border-border-base pb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary">quiz</span> Assessment Evidence
                </h2>
                <?php if (empty($evidence['assessments'])): ?>
                    <p class="text-text-muted text-sm italic bg-surface-container-lowest p-3 rounded border border-dashed border-border-base text-center">No assessment evidence available.</p>
                <?php else: ?>
                    <ul class="space-y-3">
                        <?php foreach ($evidence['assessments'] as $a): ?>
                            <li class="bg-surface-container-low p-3 rounded-lg border border-border-base text-sm flex justify-between items-center">
                                <div>
                                    <span class="font-medium text-primary block"><?= e($a['title']) ?></span>
                                    <span class="text-xs text-text-muted uppercase tracking-wide"><?= e($a['type']) ?></span>
                                </div>
                                <span class="font-bold text-primary text-base bg-white px-2 py-1 rounded border border-border-base"><?= e($a['score']) ?>%</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6">
                <h2 class="text-lg font-semibold text-primary mb-4 border-b border-border-base pb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary">record_voice_over</span> Interview Evidence
                </h2>
                <?php if (empty($evidence['interviews'])): ?>
                    <p class="text-text-muted text-sm italic bg-surface-container-lowest p-3 rounded border border-dashed border-border-base text-center">No interview evidence available.</p>
                <?php else: ?>
                    <ul class="space-y-3">
                        <?php foreach ($evidence['interviews'] as $i): ?>
                            <li class="bg-surface-container-low p-3 rounded-lg border border-border-base text-sm">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-medium text-primary"><?= e($i['interview_type']) ?></span>
                                    <span class="font-bold text-primary bg-white px-2 py-0.5 rounded border border-border-base"><?= e($i['overall_score']) ?>/10</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2 mb-2 text-xs">
                                    <span class="text-text-muted">Tech <?= e($i['technical_score']) ?>/10</span>
                                    <span class="text-text-muted">Comm <?= e($i['communication_score']) ?>/10</span>
                                    <span class="text-text-muted">Culture <?= e($i['culture_fit_score']) ?>/10</span>
                                </div>
                                <span class="text-xs text-text-muted flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">person</span> <?= e($i['interviewer_name']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Evaluation Form Column -->
        <div class="space-y-6">
            <?php if ($scoreData['has_partial_evidence']): ?>
                <div class="bg-warning-bg border border-warning/20 text-warning p-4 rounded-lg flex gap-3 shadow-sm">
                    <span class="material-symbols-outlined shrink-0 mt-0.5">warning</span>
                    <div>
                        <strong class="block mb-1">Warning: Partial Evidence</strong>
                        <span class="text-sm">Missing Assessment or Interview data. The aggregate score may not be accurate.</span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 text-center">
                <h3 class="text-text-muted uppercase tracking-wider text-xs font-semibold mb-2">Aggregate Score</h3>
                <div class="text-4xl font-black <?= $scoreData['score'] !== null ? 'text-primary' : 'text-text-muted' ?>">
                    <?= $scoreData['score'] !== null ? e($scoreData['score']) : 'N/A' ?>
                </div>
            </div>

            <?php if (!empty($latestSnapshot)): ?>
                <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6">
                    <h2 class="text-lg font-semibold text-primary mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-info">analytics</span> Normalized Feedback
                    </h2>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-text-muted">Normalized aggregate</span>
                        <span class="font-bold text-primary"><?= e(number_format((float)$latestSnapshot['aggregate_score'], 1)) ?>/100</span>
                    </div>
                    <div class="flex items-center justify-between text-sm mt-2">
                        <span class="text-text-muted">Recommendation state</span>
                        <span class="font-semibold text-primary"><?= e($latestSnapshot['recommendation']) ?></span>
                    </div>
                    <div class="flex items-center justify-between text-sm mt-2">
                        <span class="text-text-muted">Consensus readiness</span>
                        <span class="font-semibold <?= (int)$latestSnapshot['missing_feedback_count'] === 0 ? 'text-success' : 'text-warning' ?>">
                            <?= (int)$latestSnapshot['missing_feedback_count'] === 0 ? 'READY' : 'WAITING' ?>
                        </span>
                    </div>
                    <a href="<?= e(url('hr.governance.show', [$application['application_id']])) ?>" class="inline-flex mt-4 text-secondary hover:underline text-sm font-medium">View governance details</a>
                </div>
            <?php endif; ?>

            <?php if ($evaluation): ?>
                <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6">
                    <h2 class="text-xl font-semibold text-primary mb-4 border-b border-border-base pb-2 flex items-center gap-2">
                        <span class="material-symbols-outlined text-success">check_circle</span> Saved Decision
                    </h2>
                    <div class="space-y-4 text-sm">
                        <div>
                            <span class="text-text-muted block text-xs uppercase tracking-wider mb-1">Recommendation</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border <?= $evaluation['recommendation'] === 'HIRE' ? 'bg-success-bg text-success border-success/20' : ($evaluation['recommendation'] === 'REJECT' ? 'bg-error-bg text-error border-error/20' : 'bg-blue-50 text-blue-800 border-blue-200') ?>">
                                <?= e($evaluation['recommendation']) ?>
                            </span>
                        </div>
                        <div>
                            <span class="text-text-muted block text-xs uppercase tracking-wider mb-1">Status</span>
                            <span class="font-medium text-primary"><?= e($evaluation['status']) ?></span>
                        </div>
                        <div>
                            <span class="text-text-muted block text-xs uppercase tracking-wider mb-1">Notes</span>
                            <div class="bg-surface-container-low p-3 rounded-lg border border-border-base text-primary whitespace-pre-wrap"><?= nl2br(e($evaluation['decision_notes'])) ?></div>
                        </div>
                        
                        <?php if ($canCreateOffer): ?>
                            <div class="pt-4 mt-2 border-t border-border-base">
                                <a href="<?= e(url('hr.offers.create', [$application['application_id']])) ?>" class="w-full bg-secondary-container text-white px-6 py-2.5 rounded-md hover:bg-blue-700 transition-colors font-medium shadow-sm flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]">post_add</span> Create Offer
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST" action="<?= e(url('hr.evaluations.store', [$application['application_id']])) ?>" class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 space-y-5">
                    <?= csrf_field() ?>
                    
                    <div>
                        <label class="block text-sm font-medium text-primary mb-1">Recommendation:</label>
                        <select name="recommendation" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm font-medium">
                            <option value="">Select recommendation...</option>
                            <?php foreach ($recommendations as $rec): ?>
                                <option value="<?= e($rec) ?>"><?= e($rec) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-primary mb-1">Decision Notes:</label>
                        <textarea name="decision_notes" required rows="4" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm"></textarea>
                    </div>
                    
                    <?php if ($scoreData['has_partial_evidence']): ?>
                        <div class="bg-warning-bg/30 border border-warning/20 p-3 rounded-lg">
                            <label class="flex items-start gap-2 cursor-pointer">
                                <input type="checkbox" name="partial_evidence_acknowledged" value="1" required class="mt-1 h-4 w-4 text-warning focus:ring-warning border-warning rounded">
                                <span class="text-sm font-medium text-warning-dark">I acknowledge the partial evidence and wish to proceed.</span>
                            </label>
                        </div>
                    <?php endif; ?>
                    
                    <div class="pt-4 border-t border-border-base">
                        <button type="submit" class="w-full bg-secondary-container text-white px-6 py-2.5 rounded-md hover:bg-blue-700 transition-colors font-medium shadow-sm flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">save</span> Save Final Evaluation
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
