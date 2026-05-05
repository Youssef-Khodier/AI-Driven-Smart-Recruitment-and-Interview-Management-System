<div class="max-w-3xl mx-auto">
    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
        <div class="px-6 py-5 border-b border-border-base bg-surface-container-lowest flex justify-between items-center">
            <h1 class="text-2xl font-bold text-primary flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-3xl">description</span>
                Your Offer for <?= e($offer['job_title']) ?>
            </h1>
        </div>
        
        <div class="p-6 md:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-surface-container-low p-4 rounded-lg border border-border-base">
                    <span class="text-text-muted text-sm uppercase tracking-wider block mb-1">Status</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $offer['status'] === 'ACCEPTED' ? 'bg-success-bg text-success' : ($offer['status'] === 'REJECTED' ? 'bg-error-bg text-error' : 'bg-blue-100 text-blue-800') ?>">
                        <?= e($offer['status']) ?>
                    </span>
                </div>
                <div class="bg-surface-container-low p-4 rounded-lg border border-border-base">
                    <span class="text-text-muted text-sm uppercase tracking-wider block mb-1">Offer Type</span>
                    <span class="font-medium text-primary text-lg"><?= e($offer['offer_type']) ?></span>
                </div>
                <div class="bg-surface-container-low p-4 rounded-lg border border-border-base">
                    <span class="text-text-muted text-sm uppercase tracking-wider block mb-1">CTC</span>
                    <span class="font-bold text-primary text-xl">$<?= number_format($offer['ctc'], 2) ?></span>
                </div>
                <div class="bg-surface-container-low p-4 rounded-lg border border-border-base">
                    <span class="text-text-muted text-sm uppercase tracking-wider block mb-1 flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">event</span> Please respond by</span>
                    <span class="font-medium <?= strtotime($offer['expiry_date']) < time() ? 'text-error' : 'text-primary' ?> text-lg"><?= e($offer['expiry_date']) ?></span>
                </div>
                
                <?php if ($offer['bonus'] > 0): ?>
                    <div class="bg-surface-container-low p-4 rounded-lg border border-border-base">
                        <span class="text-text-muted text-sm uppercase tracking-wider block mb-1">Bonus</span>
                        <span class="font-medium text-primary text-lg">$<?= number_format($offer['bonus'], 2) ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($offer['stock_options'] > 0): ?>
                    <div class="bg-surface-container-low p-4 rounded-lg border border-border-base">
                        <span class="text-text-muted text-sm uppercase tracking-wider block mb-1">Stock Options</span>
                        <span class="font-medium text-primary text-lg">$<?= number_format($offer['stock_options'], 2) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($offer['status'] === 'SENT'): ?>
                <div class="border-t border-border-base pt-6 mt-2">
                    <h2 class="text-lg font-semibold text-primary mb-4 text-center">Action Required</h2>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <form method="POST" action="<?= e(url('candidate.offers.accept', [$offer['offer_id']])) ?>" class="flex-1 max-w-xs">
                            <?= csrf_field() ?>
                            <button type="submit" class="w-full bg-success hover:bg-green-700 text-white px-6 py-3 rounded-lg transition-colors font-medium shadow-md flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined">thumb_up</span> Accept Offer
                            </button>
                        </form>
                        <form method="POST" action="<?= e(url('candidate.offers.reject', [$offer['offer_id']])) ?>" onsubmit="return confirm('Are you sure you want to reject this offer?');" class="flex-1 max-w-xs">
                            <?= csrf_field() ?>
                            <button type="submit" class="w-full bg-white border border-error text-error hover:bg-error hover:text-white px-6 py-3 rounded-lg transition-colors font-medium shadow-sm flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined">thumb_down</span> Reject Offer
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
