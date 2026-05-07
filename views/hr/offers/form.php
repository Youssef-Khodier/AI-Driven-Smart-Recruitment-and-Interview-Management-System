<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-primary flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[32px]">post_add</span>
            <?= isset($replacesOfferId) && $replacesOfferId ? 'Create Replacement Offer' : 'Create Offer' ?>
        </h1>
        <a href="<?= e(url('hr.applications.index', [$applicationId])) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1 shrink-0">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Cancel
        </a>
    </div>

    <form method="POST" action="<?= e(url('hr.offers.store', [$applicationId])) ?>" class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 md:p-8 space-y-6">
        <?= csrf_field() ?>
        <?php if (!empty($application)): ?>
            <div class="bg-surface-container-low border border-border-base rounded-lg p-4 text-sm text-text-muted">
                <strong class="text-primary"><?= e($application['job_title']) ?></strong>
                <span class="mx-2">|</span>
                Candidate experience: <?= e($application['years_experience']) ?> years
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Offer Type:</label>
                <select name="offer_type" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                    <?php foreach ($offerTypes as $type): ?>
                        <option value="<?= e($type) ?>"><?= e($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-primary mb-1">Calculator Rule:</label>
                <select name="package_level" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                    <option value="CANDIDATE_EXPERIENCE">Use Candidate Experience</option>
                    <option value="ENTRY">Entry</option>
                    <option value="MID">Mid</option>
                    <option value="SENIOR">Senior</option>
                    <option value="LEAD">Lead</option>
                </select>
                <p class="mt-1 text-xs text-text-muted">Leave CTC empty to use this level rule.</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-primary mb-1">CTC (Annual):</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-text-muted sm:text-sm">$</span>
                    </div>
                    <input type="number" step="0.01" min="0" name="ctc" class="w-full pl-7 border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                </div>
                <p class="mt-1 text-xs text-text-muted">Enter a salary manually, or leave empty for a calculated package.</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Bonus:</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-text-muted sm:text-sm">$</span>
                    </div>
                    <input type="number" step="0.01" min="0" name="bonus" class="w-full pl-7 border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                </div>
                <p class="mt-1 text-xs text-text-muted">Leave empty to calculate from offer type.</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Stock Options:</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-text-muted sm:text-sm">$</span>
                    </div>
                    <input type="number" step="0.01" min="0" name="stock_options" class="w-full pl-7 border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                </div>
                <p class="mt-1 text-xs text-text-muted">Leave empty to calculate from offer type.</p>
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-primary mb-1">Expiry Date & Time:</label>
                <input type="datetime-local" name="expiry_date" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                <p class="mt-1 text-xs text-text-muted flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">info</span> Set the deadline for candidate response.</p>
            </div>
        </div>
        
        <div class="pt-6 border-t border-border-base flex justify-end">
            <button type="submit" class="bg-secondary-container text-white px-6 py-2.5 rounded-md hover:bg-blue-700 transition-colors font-medium shadow-sm flex items-center justify-center gap-2 w-full sm:w-auto">
                <span class="material-symbols-outlined text-[18px]">save</span> Create Draft Offer
            </button>
        </div>
    </form>
</div>
