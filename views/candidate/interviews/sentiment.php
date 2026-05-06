<div class="max-w-3xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Post-Interview Feedback</h1>
            <p class="text-text-muted mt-2 text-lg">Share your interview experience.</p>
        </div>
        <a href="<?= e(url('candidate.interviews.show', [$interview['interview_id']])) ?>" class="text-info hover:underline text-sm font-medium">&larr; Back to Interview</a>
    </div>

    <?php if ($alreadySubmitted): ?>
        <div class="bg-success-bg border border-success text-success px-4 py-3 rounded-lg shadow-sm flex items-center">
            <span class="material-symbols-outlined mr-2">check_circle</span>
            You have already submitted your feedback for this interview. Thank you!
        </div>
    <?php else: ?>
        <div class="bg-card-surface border border-border-base rounded-xl shadow-sm p-8">
            <form method="POST" action="<?= e(url('candidate.interviews.sentiment.store', [$interview['interview_id']])) ?>">
                <?= csrf_field() ?>

                <div class="mb-6">
                    <label class="block text-primary font-semibold mb-3 text-lg">How would you rate your interview experience?</label>
                    <div class="flex gap-4 items-center">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="flex flex-col items-center cursor-pointer group">
                                <input type="radio" name="rating" value="<?= $i ?>" class="sr-only peer" required>
                                <span class="text-4xl peer-checked:text-yellow-400 text-gray-300 group-hover:text-yellow-300 transition-colors">★</span>
                                <span class="text-xs text-text-muted mt-1"><?= $i ?></span>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="comment" class="block text-primary font-semibold mb-2">Additional Comments <span class="text-text-muted font-normal">(optional)</span></label>
                    <textarea id="comment" name="comment" rows="4" class="w-full border border-border-base rounded-lg px-4 py-3 bg-bg-base text-primary focus:ring-2 focus:ring-info focus:border-info transition" placeholder="Share any additional thoughts about your experience..."></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-primary hover:bg-primary-light text-white px-6 py-2.5 rounded-lg font-medium transition-colors shadow-sm">
                        Submit Feedback
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>
