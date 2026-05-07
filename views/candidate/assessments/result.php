<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-3xl font-bold text-primary">Assessment Result</h1>
        <a href="<?= e(url('candidate.applications.index')) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back
        </a>
    </div>

    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden mb-8">
        <div class="p-6 bg-surface-container-low border-b border-border-base flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="text-text-muted text-sm block mb-1">Assessment</span>
                <span class="font-semibold text-primary text-lg"><?= e($attempt['assessment_title']) ?></span>
            </div>
            <div class="flex items-center gap-6">
                <div>
                    <span class="text-text-muted text-sm block mb-1">Status</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $attempt['status'] === 'SUBMITTED' ? 'bg-success-bg text-success' : 'bg-blue-100 text-blue-800' ?>">
                        <?= e($attempt['status']) ?>
                    </span>
                </div>
                <div>
                    <span class="text-text-muted text-sm block mb-1">Simulated score</span>
                    <span class="font-bold text-primary text-xl"><?= e($attempt['score'] ?? 0) ?>%</span>
                </div>
            </div>
        </div>
        <div class="bg-warning-bg border-b border-warning/20 p-3 px-6 flex items-center gap-2 text-warning text-sm">
            <span class="material-symbols-outlined text-[18px]">info</span>
            Scores and proctoring data are simulated and advisory for the academic SRIM demo.
        </div>
        
        <div class="p-6">
            <h2 class="text-xl font-semibold text-primary mb-4 border-b border-border-base pb-2">Answers</h2>
            <div class="overflow-x-auto rounded-lg border border-border-base mb-8">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-surface-container-lowest text-text-muted text-xs uppercase tracking-wider border-b border-border-base">
                        <tr>
                            <th class="px-4 py-3 font-medium w-12 text-center">#</th>
                            <th class="px-4 py-3 font-medium">Question</th>
                            <th class="px-4 py-3 font-medium">Answer</th>
                            <th class="px-4 py-3 font-medium text-right whitespace-nowrap">Points</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-base">
                    <?php foreach ($questions as $question): ?>
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="px-4 py-4 font-medium text-text-muted text-center align-top"><?= e($question['display_order']) ?></td>
                            <td class="px-4 py-4 text-primary text-sm align-top max-w-xs break-words"><?= e(str_limit($question['question_text'], 140)) ?></td>
                            <td class="px-4 py-4 text-text-muted text-sm align-top">
                                <div class="bg-surface-container-low p-3 rounded border border-border-base whitespace-pre-wrap max-h-32 overflow-y-auto"><?= nl2br(e($question['answer_text'] ?? '')) ?></div>
                                <?php if (($question['code_output'] ?? '') !== ''): ?>
                                    <div class="mt-2 text-xs text-text-muted">Simulated output match: <?= $question['output_matched'] === null ? 'not checked' : ($question['output_matched'] ? 'matched' : 'not matched') ?></div>
                                <?php endif; ?>
                                <?php if ($question['plagiarism_score'] !== null): ?>
                                    <div class="mt-1 text-xs text-text-muted">Simulated plagiarism similarity: <?= e($question['plagiarism_score']) ?>%</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-primary font-medium text-right align-top whitespace-nowrap">
                                <?= e($question['awarded_points'] ?? '-') ?> / <?= e($question['points']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($questions)): ?>
                        <tr><td colspan="4" class="px-4 py-6 text-center text-text-muted">No answers recorded.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h2 class="text-xl font-semibold text-primary mb-4 border-b border-border-base pb-2">Simulated Proctoring Events</h2>
            <?php if (empty($events)): ?>
                <div class="bg-surface-container-low p-4 rounded-lg text-text-muted text-sm flex items-center gap-2 border border-border-base">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span> No proctoring events recorded.
                </div>
            <?php else: ?>
                <ul class="space-y-3">
                    <?php foreach ($events as $event): ?>
                        <li class="flex items-start gap-3 bg-surface-container-lowest p-3 rounded-lg border border-border-base">
                            <span class="material-symbols-outlined text-warning shrink-0 mt-0.5">warning</span>
                            <div>
                                <span class="font-medium text-primary block"><?= e($event['event_type']) ?></span>
                                <span class="text-xs text-text-muted block mt-0.5">at <?= e($event['occurred_at']) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
