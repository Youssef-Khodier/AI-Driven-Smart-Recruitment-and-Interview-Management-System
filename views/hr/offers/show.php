<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h1 class="text-3xl font-bold text-primary flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[32px]">description</span>
            Offer for <span class="text-secondary"><?= e($offer['candidate_name']) ?></span>
        </h1>
        <a href="<?= e(url('hr.offers.index')) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1 shrink-0">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to Offers
        </a>
    </div>
    
    <div class="text-sm text-text-muted flex items-center gap-2 mb-2 bg-surface-container-low p-3 rounded-lg border border-border-base w-fit">
        <span class="material-symbols-outlined text-[18px]">work</span> <strong>Job:</strong> <?= e($offer['job_title']) ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 space-y-4">
            <h2 class="text-lg font-semibold text-primary border-b border-border-base pb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary">info</span> Offer Details
            </h2>
            
            <div class="flex justify-between items-center py-1">
                <span class="text-text-muted text-sm">Status</span>
                <?php
                $statusClass = 'bg-gray-100 text-gray-800 border-gray-200';
                if ($offer['status'] === 'ACCEPTED') $statusClass = 'bg-success-bg text-success border-success/20';
                elseif ($offer['status'] === 'REJECTED' || $offer['status'] === 'WITHDRAWN') $statusClass = 'bg-error-bg text-error border-error/20';
                elseif ($offer['status'] === 'SENT') $statusClass = 'bg-blue-50 text-blue-800 border-blue-200';
                ?>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold tracking-wide border <?= $statusClass ?>">
                    <?= e($offer['status']) ?>
                </span>
            </div>
            
            <div class="flex justify-between items-center py-1 border-t border-border-base/50 pt-3">
                <span class="text-text-muted text-sm">Sequence</span>
                <span class="font-medium text-primary bg-surface-container-low px-2 py-0.5 rounded text-sm"><?= e($offer['offer_sequence']) ?></span>
            </div>
            
            <div class="flex justify-between items-center py-1 border-t border-border-base/50 pt-3">
                <span class="text-text-muted text-sm">Type</span>
                <span class="font-medium text-primary text-sm"><?= e($offer['offer_type']) ?></span>
            </div>
            
            <div class="flex justify-between items-center py-1 border-t border-border-base/50 pt-3">
                <span class="text-text-muted text-sm">CTC</span>
                <span class="font-bold text-primary">$<?= number_format($offer['ctc'], 2) ?></span>
            </div>
            
            <div class="flex justify-between items-center py-1 border-t border-border-base/50 pt-3">
                <span class="text-text-muted text-sm">Bonus</span>
                <span class="font-medium text-primary text-sm">$<?= number_format($offer['bonus'], 2) ?></span>
            </div>
            
            <div class="flex justify-between items-center py-1 border-t border-border-base/50 pt-3">
                <span class="text-text-muted text-sm">Stock Options</span>
                <span class="font-medium text-primary text-sm">$<?= number_format($offer['stock_options'], 2) ?></span>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 space-y-4">
                <h2 class="text-lg font-semibold text-primary border-b border-border-base pb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary">schedule</span> Timeline
                </h2>
                
                <div class="flex justify-between items-center py-1">
                    <span class="text-text-muted text-sm">Expiry</span>
                    <span class="font-medium <?= strtotime($offer['expiry_date']) < time() ? 'text-error' : 'text-primary' ?> text-sm"><?= e(date('M d, Y H:i', strtotime($offer['expiry_date']))) ?></span>
                </div>
                
                <div class="flex justify-between items-center py-1 border-t border-border-base/50 pt-3">
                    <span class="text-text-muted text-sm">Created At</span>
                    <span class="font-medium text-primary text-sm"><?= e(date('M d, Y H:i', strtotime($offer['created_at']))) ?></span>
                </div>
                
                <div class="flex justify-between items-center py-1 border-t border-border-base/50 pt-3">
                    <span class="text-text-muted text-sm">Sent At</span>
                    <span class="<?= $offer['sent_at'] ? 'font-medium text-primary' : 'italic text-text-muted opacity-75' ?> text-sm">
                        <?= $offer['sent_at'] ? e(date('M d, Y H:i', strtotime($offer['sent_at']))) : 'N/A' ?>
                    </span>
                </div>
                
                <div class="flex justify-between items-center py-1 border-t border-border-base/50 pt-3">
                    <span class="text-text-muted text-sm">Accepted At</span>
                    <span class="<?= $offer['accepted_at'] ? 'font-medium text-success' : 'italic text-text-muted opacity-75' ?> text-sm">
                        <?= $offer['accepted_at'] ? e(date('M d, Y H:i', strtotime($offer['accepted_at']))) : 'N/A' ?>
                    </span>
                </div>
                
                <div class="flex justify-between items-center py-1 border-t border-border-base/50 pt-3">
                    <span class="text-text-muted text-sm">Rejected At</span>
                    <span class="<?= $offer['rejected_at'] ? 'font-medium text-error' : 'italic text-text-muted opacity-75' ?> text-sm">
                        <?= $offer['rejected_at'] ? e(date('M d, Y H:i', strtotime($offer['rejected_at']))) : 'N/A' ?>
                    </span>
                </div>
            </div>

            <!-- Action Panels -->
            <?php if ($offer['status'] === 'DRAFT'): ?>
                <div class="bg-card-surface rounded-xl shadow-ambient border border-secondary/30 p-6">
                    <h2 class="text-lg font-semibold text-primary mb-2">Draft Actions</h2>
                    <p class="text-sm text-text-muted mb-4">This offer is currently a draft. Send it to the candidate to allow them to respond.</p>
                    <form method="POST" action="<?= e(url('hr.offers.send', [$offer['offer_id']])) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="w-full bg-secondary-container hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg transition-colors font-medium shadow-sm flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">send</span> Send Offer
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($offer['status'] === 'ACCEPTED'): ?>
                <div class="bg-card-surface rounded-xl shadow-ambient border border-success/30 p-6">
                    <h2 class="text-lg font-semibold text-success flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined">check_circle</span> Offer Accepted
                    </h2>
                    <p class="text-sm text-text-muted mb-4">The candidate has accepted the offer. Proceed with the onboarding workflow.</p>
                    
                    <?php if ($onboarding): ?>
                        <a href="<?= e(url('hr.onboarding.show', [$onboarding['onboarding_id']])) ?>" class="w-full bg-white border border-outline-variant text-primary px-4 py-2.5 rounded-lg hover:bg-surface-container-low transition-colors font-medium shadow-sm flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">how_to_reg</span> Manage Onboarding
                        </a>
                    <?php else: ?>
                        <a href="<?= e(url('hr.onboarding.create', [$offer['offer_id']])) ?>" class="w-full bg-secondary-container hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg transition-colors font-medium shadow-sm flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">person_add</span> Create Onboarding
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
