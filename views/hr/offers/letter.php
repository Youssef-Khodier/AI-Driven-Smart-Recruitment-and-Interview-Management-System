<div class="max-w-3xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Offer Letter</h1>
            <p class="text-text-muted mt-2 text-lg">Generated letter for Offer #<?= e($offerId) ?></p>
        </div>
        <div class="flex gap-3">
            <a href="<?= e(url('hr.offers.show', [$offerId])) ?>" class="text-info hover:underline text-sm font-medium">&larr; Back to Offer</a>
            <button onclick="window.print()" class="bg-primary hover:bg-primary-light text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center">
                <span class="material-symbols-outlined mr-1 text-lg">print</span>Print
            </button>
        </div>
    </div>

    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm p-2">
        <div class="bg-white rounded-lg p-6" id="offer-letter-content">
            <?= $letter['letter_html'] ?? '' ?>
        </div>
    </div>

    <div class="text-center text-text-muted text-sm">
        Template: <?= e($letter['template_version'] ?? 'unknown') ?> |
        Generated: <?= e($letter['generated_at'] ?? 'N/A') ?>
    </div>
</div>
