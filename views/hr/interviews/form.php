<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-primary"><?= e($title) ?></h1>
        <a href="<?= e(url('hr.applications.index', [$application['job_id']])) ?>" class="text-secondary hover:underline text-sm font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Cancel
        </a>
    </div>

    <div class="bg-surface-container-low p-4 rounded-lg border border-border-base grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
        <div>
            <span class="text-text-muted block text-xs uppercase tracking-wider mb-1">Candidate</span>
            <span class="font-medium text-primary block"><?= e($application['candidate_name']) ?></span>
        </div>
        <div>
            <span class="text-text-muted block text-xs uppercase tracking-wider mb-1">Job</span>
            <span class="font-medium text-primary block"><?= e($application['job_title']) ?></span>
        </div>
        <div>
            <span class="text-text-muted block text-xs uppercase tracking-wider mb-1">Application Status</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-50 text-blue-800 border border-blue-200"><?= e($application['status']) ?></span>
        </div>
    </div>

    <form method="POST" action="<?= e(url('hr.interviews.store', [$application['application_id']])) ?>" class="bg-card-surface rounded-xl shadow-ambient border border-border-base p-6 md:p-8 space-y-6">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Interview Type</label>
                <select name="interview_type" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                    <option value="TECHNICAL" <?= selected(old('interview_type'), 'TECHNICAL') ?>>Technical</option>
                    <option value="HR" <?= selected(old('interview_type'), 'HR') ?>>HR</option>
                    <option value="PANEL" <?= selected(old('interview_type'), 'PANEL') ?>>Panel</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-primary mb-1">Scheduled Date & Time</label>
                <input type="datetime-local" name="scheduled_at" value="<?= e(old('scheduled_at')) ?>" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm <?= error('scheduled_at') ? 'border-error text-error focus:ring-error' : '' ?>">
                <?php if ($error = error('scheduled_at')): ?>
                    <p class="mt-1 text-sm text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> <?= e($error) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-primary mb-1">Duration (minutes)</label>
                <input type="number" name="duration_minutes" value="<?= e(old('duration_minutes', '60')) ?>" min="1" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm <?= error('duration_minutes') ? 'border-error text-error focus:ring-error' : '' ?>">
                <?php if ($error = error('duration_minutes')): ?>
                    <p class="mt-1 text-sm text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> <?= e($error) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="pt-4 border-t border-border-base">
            <h3 class="text-lg font-semibold text-primary mb-1">Panel Assignments</h3>
            <p class="text-sm text-text-muted mb-4">Assign at least one official scorer (PANEL_LEAD or INTERVIEWER). Select a blank row to ignore it.</p>
            
            <?php if ($error = error('panel_members')): ?>
                <div class="bg-error-bg border border-error/20 text-error px-4 py-2 rounded-md mb-4 text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">error</span> <?= e($error) ?>
                </div>
            <?php endif; ?>

            <div id="panel-members" class="space-y-3">
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <div class="flex flex-col sm:flex-row gap-3 items-center bg-surface-container-lowest p-3 rounded-lg border border-border-base">
                        <span class="text-text-muted font-medium w-6 text-center shrink-0"><?= $i + 1 ?>.</span>
                        <div class="flex-1 w-full">
                            <select name="panel_members[<?= $i ?>][user_id]" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                                <option value="">-- Select Staff --</option>
                                <?php foreach ($panelUsers as $staff): ?>
                                    <option value="<?= e($staff['user_id']) ?>" <?= selected(old("panel_members.{$i}.user_id"), $staff['user_id']) ?>>
                                        <?= e($staff['name']) ?> (<?= e($staff['role']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="w-full sm:w-48 shrink-0">
                            <select name="panel_members[<?= $i ?>][role_in_panel]" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= e($role) ?>" <?= selected(old("panel_members.{$i}.role_in_panel"), $role) ?>><?= e($role) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <div class="pt-6 border-t border-border-base flex justify-end mt-4">
            <button type="submit" class="bg-secondary-container text-white px-6 py-2.5 rounded-md hover:bg-blue-700 transition-colors font-medium shadow-sm flex items-center justify-center gap-2 w-full sm:w-auto">
                <span class="material-symbols-outlined text-[18px]">event_available</span> Schedule Interview
            </button>
        </div>
    </form>
</div>
