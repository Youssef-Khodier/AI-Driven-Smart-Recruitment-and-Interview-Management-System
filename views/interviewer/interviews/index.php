<div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
    <div class="px-6 py-5 border-b border-border-base flex justify-between items-center">
        <h1 class="text-2xl font-bold text-primary"><?= e($title) ?></h1>
    </div>

    <?php if (empty($interviews)): ?>
        <div class="p-8 text-center text-text-muted">
            <span class="material-symbols-outlined text-[48px] mb-2 opacity-50">event_busy</span>
            <p>You have no assigned interviews at this time.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-container-low text-text-muted text-xs uppercase tracking-wider border-b border-border-base">
                    <tr>
                        <th class="px-6 py-3 font-medium">Candidate</th>
                        <th class="px-6 py-3 font-medium">Job Title</th>
                        <th class="px-6 py-3 font-medium">Scheduled For</th>
                        <th class="px-6 py-3 font-medium">Duration</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium">Your Role</th>
                        <th class="px-6 py-3 font-medium">Feedback</th>
                        <th class="px-6 py-3 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-base">
                <?php foreach ($interviews as $interview): ?>
                    <tr class="hover:bg-surface-container-lowest transition-colors">
                        <td class="px-6 py-4 font-medium text-primary"><?= e($interview['candidate_name']) ?></td>
                        <td class="px-6 py-4 text-text-muted"><?= e($interview['job_title']) ?></td>
                        <td class="px-6 py-4 text-text-muted whitespace-nowrap"><?= e(date('M d, Y H:i', strtotime($interview['scheduled_at']))) ?></td>
                        <td class="px-6 py-4 text-text-muted"><?= e($interview['duration_minutes']) ?> min</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border <?= $interview['status'] === 'COMPLETED' ? 'border-success text-success bg-success-bg' : 'border-blue-200 text-blue-800 bg-blue-50' ?>">
                                <?= e($interview['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-text-muted"><?= e($interview['role_in_panel']) ?></td>
                        <td class="px-6 py-4">
                            <?php 
                            $alreadySubmitted = \App\Repositories\InterviewFeedbackRepository::alreadySubmitted($interview['interview_id'], $actor['user_id']);
                            if ($alreadySubmitted) {
                                echo '<span class="inline-flex items-center gap-1 text-success text-sm font-medium"><span class="material-symbols-outlined text-[16px]">check_circle</span> Submitted</span>';
                            } elseif ($interview['status'] === \App\Enums\InterviewStatus::COMPLETED->value && in_array($interview['role_in_panel'], \App\Enums\InterviewAssignmentRole::officialScorerValues())) {
                                echo '<span class="inline-flex items-center gap-1 text-warning text-sm font-medium"><span class="material-symbols-outlined text-[16px]">pending</span> Pending</span>';
                            } elseif ($interview['role_in_panel'] === \App\Enums\InterviewAssignmentRole::OBSERVER->value) {
                                echo '<span class="text-text-muted text-sm">N/A (Observer)</span>';
                            } else {
                                echo '<span class="text-text-muted text-sm">Not ready</span>';
                            }
                            ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a class="inline-flex items-center justify-center px-3 py-1.5 border border-outline-variant rounded-md shadow-sm text-xs font-medium text-primary bg-white hover:bg-surface-container-low transition-colors whitespace-nowrap" href="<?= e(url('interviewer.interviews.show', [$interview['interview_id']])) ?>">
                                View Briefing
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
