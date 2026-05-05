<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-3xl font-bold text-primary flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[32px]">assignment_ind</span>
            Assessment Attempt
        </h1>
        <a href="javascript:history.back()" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back
        </a>
    </div>

    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden mb-8">
        <div class="grid grid-cols-2 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-border-base bg-surface-container-lowest">
            <div class="p-4 md:p-6">
                <span class="text-text-muted text-xs uppercase tracking-wider block mb-1">Candidate</span>
                <span class="font-medium text-primary block"><?= e($attempt['name']) ?></span>
            </div>
            <div class="p-4 md:p-6">
                <span class="text-text-muted text-xs uppercase tracking-wider block mb-1">Assessment</span>
                <span class="font-medium text-primary block"><?= e($attempt['assessment_title']) ?></span>
            </div>
            <div class="p-4 md:p-6">
                <span class="text-text-muted text-xs uppercase tracking-wider block mb-1">Status</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium border <?= $attempt['status'] === 'COMPLETED' ? 'bg-success-bg text-success border-success/20' : 'bg-blue-50 text-blue-800 border-blue-200' ?>">
                    <?= e($attempt['status']) ?>
                </span>
            </div>
            <div class="p-4 md:p-6">
                <span class="text-text-muted text-xs uppercase tracking-wider block mb-1">Score</span>
                <span class="font-bold text-primary text-xl"><?= e($attempt['score'] ?? '-') ?></span>
            </div>
        </div>
        
        <div class="p-6">
            <h2 class="text-xl font-semibold text-primary mb-4 border-b border-border-base pb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary">list_alt</span> Answers
            </h2>
            <div class="overflow-x-auto rounded-lg border border-border-base mb-8">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-surface-container-lowest text-text-muted text-xs uppercase tracking-wider border-b border-border-base">
                        <tr>
                            <th class="px-4 py-3 font-medium w-12 text-center">#</th>
                            <th class="px-4 py-3 font-medium">Question</th>
                            <th class="px-4 py-3 font-medium">Answer</th>
                            <th class="px-4 py-3 font-medium text-center">Correct</th>
                            <th class="px-4 py-3 font-medium text-right whitespace-nowrap">Points</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-base text-sm">
                    <?php foreach ($questions as $question): ?>
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="px-4 py-4 font-medium text-text-muted text-center align-top"><?= e($question['display_order']) ?></td>
                            <td class="px-4 py-4 text-primary align-top max-w-xs break-words"><?= nl2br(e($question['question_text'])) ?></td>
                            <td class="px-4 py-4 text-text-muted align-top">
                                <div class="bg-surface-container-low p-3 rounded border border-border-base whitespace-pre-wrap max-h-48 overflow-y-auto"><?= nl2br(e($question['answer_text'] ?? '')) ?></div>
                            </td>
                            <td class="px-4 py-4 text-center align-top">
                                <?php if ($question['is_correct'] === null): ?>
                                    <span class="text-text-muted">-</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center justify-center p-1 rounded-full <?= $question['is_correct'] ? 'bg-success-bg text-success' : 'bg-error-bg text-error' ?>">
                                        <span class="material-symbols-outlined text-[18px]"><?= $question['is_correct'] ? 'check' : 'close' ?></span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-primary font-medium text-right align-top whitespace-nowrap">
                                <?= e($question['awarded_points'] ?? '-') ?> / <?= e($question['points']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($questions)): ?>
                        <tr><td colspan="5" class="px-4 py-6 text-center text-text-muted">No answers recorded.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h2 class="text-xl font-semibold text-primary mb-4 border-b border-border-base pb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-warning">warning</span> Simulated Proctoring Events
            </h2>
            <div class="overflow-x-auto rounded-lg border border-border-base">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-surface-container-lowest text-text-muted text-xs uppercase tracking-wider border-b border-border-base">
                        <tr>
                            <th class="px-6 py-3 font-medium">Type</th>
                            <th class="px-6 py-3 font-medium">When</th>
                            <th class="px-6 py-3 font-medium">Metadata</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-base text-sm">
                    <?php foreach ($events as $event): ?>
                        <tr class="hover:bg-warning-bg/30 transition-colors">
                            <td class="px-6 py-3 font-medium text-warning flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">priority_high</span>
                                <?= e($event['event_type']) ?>
                            </td>
                            <td class="px-6 py-3 text-text-muted"><?= e(date('M d, Y H:i:s', strtotime($event['occurred_at']))) ?></td>
                            <td class="px-6 py-3 text-text-muted font-mono text-xs"><?= e($event['metadata'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($events)): ?>
                        <tr><td colspan="3" class="px-6 py-6 text-center text-text-muted flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">check_circle</span> No proctoring events recorded.
                        </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
