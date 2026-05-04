<h1><?= e($title) ?></h1>

<div>
    <p><strong>Candidate:</strong> <?= e($application['candidate_name']) ?></p>
    <p><strong>Job:</strong> <?= e($application['job_title']) ?></p>
    <p><strong>Application Status:</strong> <?= e($application['status']) ?></p>
</div>

<form method="POST" action="<?= e(url('hr.interviews.store', [$application['application_id']])) ?>">
    <?= csrf_field() ?>

    <label>Interview Type
        <select name="interview_type" required>
            <option value="TECHNICAL" <?= selected(old('interview_type'), 'TECHNICAL') ?>>Technical</option>
            <option value="HR" <?= selected(old('interview_type'), 'HR') ?>>HR</option>
            <option value="PANEL" <?= selected(old('interview_type'), 'PANEL') ?>>Panel</option>
        </select>
    </label>

    <label>Scheduled Date & Time
        <input type="datetime-local" name="scheduled_at" value="<?= e(old('scheduled_at')) ?>" required>
    </label>
    <?php if ($error = error('scheduled_at')): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

    <label>Duration (minutes)
        <input type="number" name="duration_minutes" value="<?= e(old('duration_minutes', '60')) ?>" min="1" required>
    </label>
    <?php if ($error = error('duration_minutes')): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

    <h3>Panel Assignments</h3>
    <p>Assign at least one official scorer (PANEL_LEAD or INTERVIEWER). Select a blank row to ignore it.</p>
    <?php if ($error = error('panel_members')): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

    <div id="panel-members">
        <?php for ($i = 0; $i < 5; $i++): ?>
            <div style="margin-bottom: 10px;">
                <select name="panel_members[<?= $i ?>][user_id]">
                    <option value="">-- Select Staff --</option>
                    <?php foreach ($panelUsers as $staff): ?>
                        <option value="<?= e($staff['user_id']) ?>" <?= selected(old("panel_members.{$i}.user_id"), $staff['user_id']) ?>>
                            <?= e($staff['name']) ?> (<?= e($staff['role']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="panel_members[<?= $i ?>][role_in_panel]">
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= e($role) ?>" <?= selected(old("panel_members.{$i}.role_in_panel"), $role) ?>><?= e($role) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endfor; ?>
    </div>

    <button type="submit">Schedule Interview</button>
</form>
