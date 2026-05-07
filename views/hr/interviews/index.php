<div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
    <div class="px-6 py-5 border-b border-border-base flex justify-between items-center bg-surface-container-low">
        <h1 class="text-2xl font-bold text-primary flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary">event</span>
            <?= e($title) ?>
        </h1>
    </div>

    <?php if (empty($interviews)): ?>
        <div class="p-8 text-center text-text-muted">
            <span class="material-symbols-outlined text-[48px] mb-2 opacity-50">event_busy</span>
            <p>No interviews scheduled yet.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-container-lowest text-text-muted text-xs uppercase tracking-wider border-b border-border-base">
                    <tr>
                        <th class="px-6 py-3 font-medium">ID</th>
                        <th class="px-6 py-3 font-medium">Candidate</th>
                        <th class="px-6 py-3 font-medium">Job Title</th>
                        <th class="px-6 py-3 font-medium">Scheduled For</th>
                        <th class="px-6 py-3 font-medium">Duration</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium">Panel</th>
                        <th class="px-6 py-3 font-medium">Feedback</th>
                        <th class="px-6 py-3 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-base text-sm">
                    <?php foreach ($interviews as $interview): ?>
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="px-6 py-4 text-text-muted">#<?= e($interview['interview_id']) ?></td>
                            <td class="px-6 py-4 font-medium text-primary"><?= e($interview['candidate_name']) ?></td>
                            <td class="px-6 py-4 text-text-muted"><?= e($interview['job_title']) ?></td>
                            <td class="px-6 py-4 text-text-muted whitespace-nowrap"><?= e(date('M d, Y H:i', strtotime($interview['scheduled_at']))) ?></td>
                            <td class="px-6 py-4 text-text-muted whitespace-nowrap"><?= e($interview['duration_minutes']) ?> min</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium border <?= $interview['status'] === 'COMPLETED' ? 'bg-success-bg text-success border-success/20' : 'bg-blue-50 text-blue-800 border-blue-200' ?>">
                                    <?= e($interview['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-text-muted">
                                <?php foreach ($interview['assignments'] as $assignment): ?>
                                    <div class="truncate max-w-[150px]" title="<?= e($assignment['interviewer_name']) ?> (<?= e($assignment['role_in_panel']) ?>)">
                                        <?= e($assignment['interviewer_name']) ?> <span class="text-xs opacity-75">(<?= e($assignment['role_in_panel']) ?>)</span>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php 
                                $state = \App\Models\InterviewFeedbackModel::completionState($interview['interview_id']);
                                $stateClass = $state === 'COMPLETE' ? 'text-success' : ($state === 'PARTIAL' ? 'text-warning' : 'text-text-muted');
                                ?>
                                <span class="font-medium text-xs <?= $stateClass ?>"><?= e($state) ?></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a class="inline-flex items-center justify-center px-3 py-1.5 border border-outline-variant rounded-md shadow-sm text-xs font-medium text-primary bg-white hover:bg-surface-container-low transition-colors" href="<?= e(url('hr.interviews.show', [$interview['interview_id']])) ?>">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
