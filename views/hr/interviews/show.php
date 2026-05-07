<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-3xl font-bold text-primary flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[32px]">event_note</span>
            <?= e($title) ?>
        </h1>
        <a href="<?= e(url('hr.interviews.index')) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1 shrink-0">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to Interviews
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6">
            <h2 class="text-lg font-semibold text-primary mb-4 border-b border-border-base pb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary">schedule</span> Schedule Details
            </h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-text-muted">Status</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium border <?= $interview['status'] === 'COMPLETED' ? 'bg-success-bg text-success border-success/20' : 'bg-blue-50 text-blue-800 border-blue-200' ?>"><?= e($interview['status']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-text-muted">Type</span>
                    <span class="font-medium text-primary"><?= e($interview['interview_type']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-text-muted">Scheduled</span>
                    <span class="font-medium text-primary"><?= e(date('M d, Y H:i', strtotime($interview['scheduled_at']))) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-text-muted">Duration</span>
                    <span class="font-medium text-primary"><?= e((int)$interview['duration_minutes'] + (int)($interview['extended_duration_minutes'] ?? 0)) ?> minutes</span>
                </div>
                <?php if (!empty($interview['extended_duration_minutes'])): ?>
                    <div class="flex justify-between">
                        <span class="text-text-muted">Extension</span>
                        <span class="font-medium text-primary">+<?= e($interview['extended_duration_minutes']) ?> minutes</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6">
            <h2 class="text-lg font-semibold text-primary mb-4 border-b border-border-base pb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary">person</span> Candidate & Job
            </h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-text-muted">Candidate</span>
                    <span class="font-medium text-primary"><?= e($interview['candidate_name']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-text-muted">Email</span>
                    <span class="font-medium text-primary"><?= e($interview['candidate_email']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-text-muted">Job</span>
                    <span class="font-medium text-primary"><?= e($interview['job_title']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6">
            <h2 class="text-lg font-semibold text-primary mb-4 border-b border-border-base pb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary">group</span> Panel Assignments
            </h2>
            <ul class="space-y-2">
                <?php foreach ($interview['assignments'] as $assignment): ?>
                    <li class="flex items-center justify-between bg-surface-container-low p-2 rounded border border-border-base text-sm">
                        <span class="font-medium text-primary"><?= e($assignment['interviewer_name']) ?></span>
                        <span class="text-xs font-semibold text-text-muted uppercase tracking-wider">
                            <?= e($assignment['role_in_panel']) ?><?= !empty($assignment['is_shadowing']) ? ' - SHADOW' : '' ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6">
            <div class="flex items-center justify-between mb-4 border-b border-border-base pb-2">
                <h2 class="text-lg font-semibold text-primary flex items-center gap-2 m-0">
                    <span class="material-symbols-outlined text-secondary">rate_review</span> Feedback
                </h2>
                <span class="text-xs font-bold uppercase tracking-wider <?= $interview['completion_state'] === 'COMPLETE' ? 'text-success' : 'text-warning' ?>">State: <?= e($interview['completion_state']) ?></span>
            </div>
            
            <?php if ($interview['status'] === \App\Enums\InterviewStatus::SCHEDULED->value): ?>
                <form method="POST" action="<?= e(url('hr.interviews.complete', [$interview['interview_id']])) ?>" class="mb-4">
                    <?= csrf_field() ?>
                    <button type="submit" class="w-full bg-secondary hover:bg-blue-800 text-white px-4 py-2 rounded-md transition-colors text-sm font-medium shadow-sm flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">check_circle</span> Mark Completed
                    </button>
                </form>
            <?php endif; ?>
            
            <ul class="space-y-4">
                <?php foreach ($interview['feedback'] as $f): ?>
                    <li class="bg-surface-container-lowest p-3 rounded-lg border border-border-base text-sm">
                        <div class="flex justify-between items-start mb-2">
                            <strong class="text-primary text-base"><?= e($f['interviewer_name']) ?></strong>
                            <span class="text-xs text-text-muted"><?= e(date('M d, Y', strtotime($f['submitted_at']))) ?></span>
                        </div>
                        <div class="flex flex-wrap gap-2 mb-2 text-xs">
                            <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded">Tech: <strong class="text-blue-900"><?= e($f['technical_score']) ?></strong></span>
                            <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded">Comm: <strong class="text-blue-900"><?= e($f['communication_score']) ?></strong></span>
                            <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded">Culture: <strong class="text-blue-900"><?= e($f['culture_fit_score']) ?></strong></span>
                            <span class="bg-secondary-container/10 text-secondary px-2 py-1 rounded">Overall: <strong class="text-secondary-container"><?= e($f['overall_score']) ?></strong></span>
                        </div>
                        <div class="text-text-muted italic bg-surface-container-low p-2 rounded">
                            "<?= nl2br(e($f['comments'])) ?>"
                        </div>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($interview['feedback'])): ?>
                    <li class="text-text-muted text-sm text-center py-4 italic">No feedback submitted yet.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6">
            <div class="flex items-center justify-between gap-3 mb-4 border-b border-border-base pb-2">
                <h2 class="text-lg font-semibold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary">article</span> Briefing Snapshot
                </h2>
                <form method="POST" action="<?= e(url('hr.interviews.briefing.refresh', [$interview['interview_id']])) ?>">
                    <?= csrf_field() ?>
                    <button class="text-xs bg-surface-container-low hover:bg-surface-container-highest text-primary border border-border-base px-3 py-1.5 rounded-md">Refresh</button>
                </form>
            </div>
            <?php if (empty($interview['briefing_snapshot'])): ?>
                <p class="text-text-muted text-sm italic">No briefing snapshot saved yet.</p>
            <?php else: ?>
                <div class="space-y-3 text-sm">
                    <div><span class="text-text-muted block mb-1">Candidate</span><p class="text-primary"><?= e($interview['briefing_snapshot']['candidate_summary']) ?></p></div>
                    <div><span class="text-text-muted block mb-1">Assessment</span><p class="text-primary"><?= e($interview['briefing_snapshot']['assessment_summary']) ?></p></div>
                    <div><span class="text-text-muted block mb-1">Job Requirements</span><p class="text-primary whitespace-pre-wrap"><?= e($interview['briefing_snapshot']['job_requirements_summary']) ?></p></div>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6">
            <h2 class="text-lg font-semibold text-primary mb-4 border-b border-border-base pb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary">more_time</span> Extensions & Workspace
            </h2>
            <a href="<?= e(url('hr.interviews.workspace', [$interview['interview_id']])) ?>" class="inline-flex items-center gap-2 bg-secondary-container text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium mb-4">
                <span class="material-symbols-outlined text-[18px]">code</span> Open Workspace
            </a>
            <div class="space-y-3">
                <?php foreach ($interview['extension_requests'] as $extension): ?>
                    <div class="border border-border-base rounded-lg p-3 text-sm bg-surface-container-lowest">
                        <div class="flex justify-between gap-2">
                            <span class="font-medium text-primary"><?= e($extension['requested_by_name']) ?> requested <?= e($extension['requested_minutes']) ?> min</span>
                            <a class="text-secondary hover:underline" href="<?= e(url('hr.interviews.extensions.show', [$interview['interview_id'], $extension['extension_request_id']])) ?>"><?= e($extension['status']) ?></a>
                        </div>
                        <p class="text-text-muted mt-1"><?= e($extension['request_reason']) ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($interview['extension_requests'])): ?>
                    <p class="text-text-muted text-sm italic">No extension requests.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
        <div class="px-6 py-4 border-b border-border-base">
            <h2 class="text-lg font-semibold text-primary m-0 flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary">history</span> Audit History
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-surface-container-lowest text-text-muted uppercase tracking-wider border-b border-border-base text-xs">
                    <tr>
                        <th class="px-6 py-3 font-medium">Time</th>
                        <th class="px-6 py-3 font-medium">Actor</th>
                        <th class="px-6 py-3 font-medium">Action</th>
                        <th class="px-6 py-3 font-medium">Changes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-base">
                    <?php foreach ($interview['audit_records'] as $record): ?>
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="px-6 py-3 text-text-muted whitespace-nowrap"><?= e(date('M d, H:i', strtotime($record['created_at']))) ?></td>
                            <td class="px-6 py-3 text-primary font-medium"><?= e($record['actor_name']) ?></td>
                            <td class="px-6 py-3 text-primary">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700">
                                    <?= e($record['action']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <pre class="text-xs text-text-muted bg-surface-container-low p-2 rounded border border-border-base max-w-xs overflow-x-auto font-mono"><?= e($record['changed_fields'] ?? '[]') ?></pre>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($interview['audit_records'])): ?>
                        <tr><td colspan="4" class="px-6 py-4 text-center text-text-muted italic">No audit records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
