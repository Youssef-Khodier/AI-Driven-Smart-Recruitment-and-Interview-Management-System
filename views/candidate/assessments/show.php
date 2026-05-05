<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 border-b border-border-base pb-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-primary mb-2"><?= e($attempt['assessment_title']) ?></h1>
                <div class="flex flex-wrap items-center gap-4 text-sm text-text-muted">
                    <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">work</span> <strong>Job:</strong> <?= e($attempt['job_title']) ?></span>
                    <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">timer</span> <strong>Expires:</strong> <?= e($attempt['expires_at']) ?></span>
                </div>
            </div>
            
            <form method="POST" action="<?= e(url('candidate.assessments.focus-events.store', [$attempt['ca_id']])) ?>" class="shrink-0">
                <?= csrf_field() ?>
                <input type="hidden" name="event_type" value="FOCUS_LOST">
                <input type="hidden" name="visible_state" value="manual-demo">
                <button type="submit" class="bg-warning-bg text-warning border border-warning/20 px-4 py-2 rounded-md hover:bg-warning/20 transition-colors text-sm font-medium flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">visibility_off</span> Record focus loss event
                </button>
            </form>
        </div>

        <div class="space-y-8">
            <?php foreach ($questions as $question): ?>
                <div class="bg-surface-container-low rounded-lg border border-border-base p-6">
                    <form method="POST" action="<?= e(url('candidate.assessments.answers.update', [$attempt['ca_id'], $question['attempt_question_id']])) ?>">
                        <?= csrf_field() ?><?= method_field('PUT') ?>
                        
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-border-base/50">
                            <h2 class="text-lg font-semibold text-primary">Question <?= e($question['display_order']) ?></h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?= e($question['question_type']) ?> &bull; <?= e($question['points']) ?> pts
                            </span>
                        </div>
                        
                        <div class="prose max-w-none text-primary mb-6">
                            <p class="whitespace-pre-wrap"><?= nl2br(e($question['question_text'])) ?></p>
                        </div>
                        
                        <?php if ($question['options']): ?>
                            <pre class="bg-white p-4 rounded-md border border-border-base text-sm font-mono text-text-muted mb-6 overflow-x-auto"><?= e($question['options']) ?></pre>
                        <?php endif; ?>
                        
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-primary">Your Answer</label>
                            <textarea name="answer_text" rows="5" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm"><?= e($question['answer_text'] ?? '') ?></textarea>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-white border border-outline-variant text-primary px-4 py-2 rounded-md hover:bg-surface-container-highest transition-colors text-sm font-medium flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]">save</span> Save answer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-8 pt-6 border-t border-border-base flex justify-end">
            <form method="POST" action="<?= e(url('candidate.assessments.submit', [$attempt['ca_id']])) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="bg-secondary-container text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-md flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">done_all</span> Submit final assessment
                </button>
            </form>
        </div>
    </div>
</div>
