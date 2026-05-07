<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-primary"><?= e($title) ?></h1>
    </div>

    <form method="POST" action="<?= e(url('interviewer.interviews.feedback.store', [$briefing['interview_id']])) ?>" class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 md:p-8 space-y-6">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach (['technical_score', 'communication_score', 'culture_fit_score', 'overall_score'] as $field): ?>
                <div>
                    <label class="block text-sm font-medium text-primary mb-1">
                        <?= e(ucwords(str_replace('_', ' ', $field))) ?> (0-10)
                    </label>
                    <input type="number" name="<?= e($field) ?>" value="<?= e(old($field)) ?>" step="0.01" min="0" max="10" required 
                           class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm <?= error($field) ? 'border-error text-error focus:ring-error focus:border-error' : '' ?>">
                    <?php if ($error = error($field)): ?>
                        <p class="mt-1 text-sm text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> <?= e($error) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="pt-2">
            <label class="block text-sm font-medium text-primary mb-1">Comments</label>
            <textarea name="comments" rows="5" required 
                      class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm <?= error('comments') ? 'border-error text-error focus:ring-error focus:border-error' : '' ?>"><?= e(old('comments')) ?></textarea>
            <?php if ($error = error('comments')): ?>
                <p class="mt-1 text-sm text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> <?= e($error) ?></p>
            <?php endif; ?>
        </div>

        <div class="border border-border-base rounded-lg p-4 bg-surface-container-lowest space-y-4">
            <label class="flex items-start gap-3">
                <input type="checkbox" name="has_red_flag" value="1" class="mt-1 h-4 w-4 text-error focus:ring-error border-border-base rounded" <?= old('has_red_flag') ? 'checked' : '' ?>>
                <span>
                    <span class="block text-sm font-semibold text-primary">Escalate a candidate red flag to HR</span>
                    <span class="block text-xs text-text-muted mt-1">Use only for concerns HR should review before consensus or final recommendation.</span>
                </span>
            </label>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-primary mb-1">Red flag category</label>
                    <input name="red_flag_category" value="<?= e(old('red_flag_category')) ?>" placeholder="Example: conduct, risk, evidence gap" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm <?= error('red_flag_category') ? 'border-error text-error focus:ring-error focus:border-error' : '' ?>">
                    <?php if ($error = error('red_flag_category')): ?>
                        <p class="mt-1 text-sm text-error"><?= e($error) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-primary mb-1">Severity</label>
                    <select name="red_flag_severity" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm <?= error('red_flag_severity') ? 'border-error text-error focus:ring-error focus:border-error' : '' ?>">
                        <?php foreach (['LOW', 'MEDIUM', 'HIGH'] as $severity): ?>
                            <option value="<?= e($severity) ?>" <?= old('red_flag_severity', 'MEDIUM') === $severity ? 'selected' : '' ?>><?= e($severity) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($error = error('red_flag_severity')): ?>
                        <p class="mt-1 text-sm text-error"><?= e($error) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-primary mb-1">Red flag explanation</label>
                <textarea name="red_flag_explanation" rows="3" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm <?= error('red_flag_explanation') ? 'border-error text-error focus:ring-error focus:border-error' : '' ?>"><?= e(old('red_flag_explanation')) ?></textarea>
                <?php if ($error = error('red_flag_explanation')): ?>
                    <p class="mt-1 text-sm text-error"><?= e($error) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="pt-6 border-t border-border-base flex flex-col sm:flex-row items-center justify-between gap-4">
            <a href="<?= e(url('interviewer.interviews.show', [$briefing['interview_id']])) ?>" class="text-text-muted hover:text-primary font-medium text-sm transition-colors order-2 sm:order-1">
                Cancel
            </a>
            <button type="submit" class="w-full sm:w-auto bg-secondary-container text-white px-6 py-2.5 rounded-md hover:bg-blue-700 transition-colors font-medium shadow-sm flex items-center justify-center gap-2 order-1 sm:order-2">
                <span class="material-symbols-outlined text-[18px]">done</span> Submit official feedback
            </button>
        </div>
    </form>
</div>
