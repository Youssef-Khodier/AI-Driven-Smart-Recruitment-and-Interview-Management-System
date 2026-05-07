<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-8">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-3xl font-bold text-primary mb-2"><?= e($application['title']) ?></h1>
                <div class="flex items-center gap-4 text-sm">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-medium bg-blue-100 text-blue-800">
                        <?= e($application['status']) ?>
                    </span>
                    <span class="text-text-muted flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">psychology</span> 
                        Simulated match: <span class="font-semibold text-primary"><?= e($application['match_score']) ?>%</span>
                    </span>
                </div>
            </div>
            <a href="<?= e(url('candidate.applications.index')) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back
            </a>
        </div>

        <h2 class="text-xl font-semibold text-primary mb-4 border-b border-border-base pb-2">Assessments</h2>
        <?php if ($application['status'] !== 'ASSESSMENT'): ?>
            <div class="bg-surface-container-low text-text-muted p-4 rounded-lg flex items-center gap-3">
                <span class="material-symbols-outlined">info</span>
                <p>Assessments become available when HR moves this application to ASSESSMENT status.</p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($assessments)): ?>
            <ul class="space-y-4 mt-4">
            <?php foreach ($assessments as $assessment): ?>
                <li class="bg-white border border-border-base rounded-lg p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm hover:shadow-md transition-shadow">
                    <div>
                        <div class="font-medium text-primary text-lg"><?= e($assessment['title']) ?></div>
                        <div class="text-text-muted text-sm mt-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">schedule</span> <?= e($assessment['duration_minutes']) ?> min
                        </div>
                    </div>
                    <?php if (isset($attemptMap[(int) $assessment['assessment_id']])): ?>
                        <?php
                        $attempt = $attemptMap[(int) $assessment['assessment_id']];
                        $cooldownEnds = strtotime(sprintf('%s +%d months', $attempt['end_time'] ?: $attempt['updated_at'], (int) ($assessment['cooldown_months'] ?? 0)));
                        $canRetake = $application['status'] === 'ASSESSMENT' && $attempt['status'] !== 'IN_PROGRESS' && $cooldownEnds <= time();
                        ?>
                        <div class="flex flex-col sm:items-end gap-2">
                            <a class="inline-flex items-center justify-center px-4 py-2 border border-outline-variant rounded-md shadow-sm text-sm font-medium text-primary bg-card-surface hover:bg-surface-container-low transition-colors whitespace-nowrap"
                               href="<?= e($attempt['status'] === 'IN_PROGRESS' ? url('candidate.assessments.show', [$attempt['ca_id']]) : url('candidate.assessments.result', [$attempt['ca_id']])) ?>">
                                View attempt
                            </a>
                            <?php if ($canRetake): ?>
                                <form class="inline m-0" method="POST" action="<?= e(url('candidate.assessments.start', [$application['application_id'], $assessment['assessment_id']])) ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-secondary-container hover:bg-blue-700 transition-colors whitespace-nowrap">
                                        Retake assessment
                                    </button>
                                </form>
                            <?php elseif ($attempt['status'] !== 'IN_PROGRESS'): ?>
                                <span class="text-xs text-text-muted">Retake available after <?= e(date('Y-m-d', $cooldownEnds)) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($application['status'] === 'ASSESSMENT'): ?>
                        <form class="inline m-0" method="POST" action="<?= e(url('candidate.assessments.start', [$application['application_id'], $assessment['assessment_id']])) ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-secondary-container hover:bg-blue-700 transition-colors whitespace-nowrap">
                                Start Assessment
                            </button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (!empty($interviews)): ?>
            <div class="mt-8 pt-6 border-t border-border-base">
                <h2 class="text-xl font-semibold text-primary mb-4">Interviews</h2>
                <div class="space-y-3">
                    <?php foreach ($interviews as $interview): ?>
                        <div class="bg-white border border-border-base rounded-lg p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
                            <div>
                                <div class="font-medium text-primary"><?= e($interview['interview_type']) ?> Interview</div>
                                <div class="text-text-muted text-sm mt-1"><?= e(date('M d, Y H:i', strtotime($interview['scheduled_at']))) ?> - <?= e((int)$interview['duration_minutes'] + (int)($interview['extended_duration_minutes'] ?? 0)) ?> min</div>
                            </div>
                            <a href="<?= e(url('candidate.interviews.show', [$interview['interview_id']])) ?>" class="inline-flex items-center justify-center px-4 py-2 border border-outline-variant rounded-md shadow-sm text-sm font-medium text-primary bg-card-surface hover:bg-surface-container-low transition-colors whitespace-nowrap">
                                View Interview
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php
        $candidateOffers = \App\Repositories\OfferRepository::findByApplicationId($application['application_id']);
        $visibleOffers = array_filter($candidateOffers, fn($o) => $o['status'] !== 'DRAFT');
        if (!empty($visibleOffers)):
            $latestOffer = end($visibleOffers);
        ?>
        <div class="mt-8 pt-6 border-t border-border-base">
            <h2 class="text-xl font-semibold text-primary mb-4">Offer Status</h2>
            <div class="bg-success-bg border border-success/20 p-5 rounded-lg">
                <div class="flex items-center gap-3 mb-3 text-success">
                    <span class="material-symbols-outlined text-2xl">celebration</span>
                    <h3 class="font-medium text-lg">You have an offer!</h3>
                </div>
                <p class="text-success text-sm mb-4">An offer has been extended for this application. Please review the details.</p>
                <a class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-success hover:bg-green-700 transition-colors" href="<?= e(url('candidate.offers.show', [$latestOffer['offer_id']])) ?>">
                    View Offer
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
