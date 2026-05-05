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
                <label class="block text-sm font-medium text-primary mb-1">CTC (Annual):</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-text-muted sm:text-sm">$</span>
                    </div>
                    <input type="number" step="0.01" min="0" name="ctc" required class="w-full pl-7 border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Bonus:</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-text-muted sm:text-sm">$</span>
                    </div>
                    <input type="number" step="0.01" min="0" name="bonus" value="0" class="w-full pl-7 border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Stock Options:</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-text-muted sm:text-sm">$</span>
                    </div>
                    <input type="number" step="0.01" min="0" name="stock_options" value="0" class="w-full pl-7 border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                </div>
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
