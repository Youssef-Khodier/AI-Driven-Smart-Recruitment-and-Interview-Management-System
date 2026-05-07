<div class="max-w-6xl mx-auto space-y-8">
    <div>
        <h1 class="text-3xl font-bold text-primary">HR Dashboard</h1>
        <p class="text-text-muted mt-2 text-lg">Welcome, <?= e($user['name']) ?>.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <?php if ($user['is_department_head'] ?? false): ?>
        <div class="bg-card-surface p-5 rounded-lg shadow-ambient border border-border-base flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <span class="material-symbols-outlined text-red-700 bg-red-100 p-2 rounded">fact_check</span>
                <?php if ($pendingApprovalsCount > 0): ?>
                    <span class="bg-error text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= e($pendingApprovalsCount) ?></span>
                <?php endif; ?>
            </div>
            <div>
                <p class="font-semibold text-primary text-lg">Recruitment Pipeline & Triage</p>
                <p class="text-sm text-text-muted mt-1">Review requisition approvals awaiting department-head action.</p>
            </div>
            <a href="<?= e(url('hr.approvals.index')) ?>" class="text-sm font-medium text-secondary hover:underline">Approval Queue</a>
        </div>
        <?php endif; ?>

        <div class="bg-card-surface p-5 rounded-lg shadow-ambient border border-border-base flex flex-col gap-4">
            <span class="material-symbols-outlined text-info bg-info-bg p-2 rounded w-fit">work</span>
            <div>
                <p class="font-semibold text-primary text-lg">Recruitment Pipeline & Triage</p>
                <p class="text-sm text-text-muted mt-1">Manage requisitions, applications, screening, shortlists, duplicates, publishing, and pipeline reports.</p>
            </div>
            <div class="flex flex-wrap gap-3 text-sm font-medium">
                <a href="<?= e(url('hr.requisitions.index')) ?>" class="text-secondary hover:underline">Requisitions</a>
                <a href="<?= e(url('hr.reports.pipeline')) ?>" class="text-secondary hover:underline">Pipeline Report</a>
                <a href="<?= e(url('hr.reports.time-to-hire')) ?>" class="text-secondary hover:underline">Time-to-Hire</a>
                <a href="<?= e(url('hr.reports.bottlenecks')) ?>" class="text-secondary hover:underline">Bottlenecks</a>
            </div>
        </div>

        <div class="bg-card-surface p-5 rounded-lg shadow-ambient border border-border-base flex flex-col gap-4">
            <span class="material-symbols-outlined text-secondary bg-blue-50 p-2 rounded w-fit">quiz</span>
            <div>
                <p class="font-semibold text-primary text-lg">Assessment & Proctored Simulation</p>
                <p class="text-sm text-text-muted mt-1">Open a requisition to manage assessments, question banks, attempts, results, and simulated integrity review.</p>
            </div>
            <a href="<?= e(url('hr.requisitions.index')) ?>" class="text-sm font-medium text-secondary hover:underline">Open Requisitions</a>
        </div>

        <div class="bg-card-surface p-5 rounded-lg shadow-ambient border border-border-base flex flex-col gap-4">
            <span class="material-symbols-outlined text-warning bg-warning-bg p-2 rounded w-fit">event</span>
            <div>
                <p class="font-semibold text-primary text-lg">Interview Coordination & Logistics</p>
                <p class="text-sm text-text-muted mt-1">Schedule interviews from eligible applications and review assigned panels, briefings, and interview status.</p>
            </div>
            <a href="<?= e(url('hr.interviews.index')) ?>" class="text-sm font-medium text-secondary hover:underline">Interview List</a>
        </div>

        <div class="bg-card-surface p-5 rounded-lg shadow-ambient border border-border-base flex flex-col gap-4">
            <span class="material-symbols-outlined text-purple-700 bg-purple-100 p-2 rounded w-fit">rate_review</span>
            <div>
                <p class="font-semibold text-primary text-lg">Feedback & Evaluation</p>
                <p class="text-sm text-text-muted mt-1">Review interviewer feedback, governance flags, normalization, sentiment, and final hiring recommendations.</p>
            </div>
            <a href="<?= e(url('hr.feedback-governance.index')) ?>" class="text-sm font-medium text-secondary hover:underline">Feedback Governance</a>
        </div>

        <div class="bg-card-surface p-5 rounded-lg shadow-ambient border border-border-base flex flex-col gap-4">
            <span class="material-symbols-outlined text-success bg-success-bg p-2 rounded w-fit">description</span>
            <div>
                <p class="font-semibold text-primary text-lg">Offers & Onboarding</p>
                <p class="text-sm text-text-muted mt-1">Manage offer packages, offer letters, referrals, background checks, and onboarding records.</p>
            </div>
            <div class="flex flex-wrap gap-3 text-sm font-medium">
                <a href="<?= e(url('hr.offers.index')) ?>" class="text-secondary hover:underline">Offers</a>
                <a href="<?= e(url('hr.onboarding.index')) ?>" class="text-secondary hover:underline">Onboarding</a>
                <a href="<?= e(url('hr.referrals.index')) ?>" class="text-secondary hover:underline">Referrals</a>
            </div>
        </div>

        <div class="bg-card-surface p-5 rounded-lg shadow-ambient border border-border-base flex flex-col gap-4">
            <span class="material-symbols-outlined text-primary bg-surface-container-high p-2 rounded w-fit">admin_panel_settings</span>
            <div>
                <p class="font-semibold text-primary text-lg">System Administration & Compliance</p>
                <p class="text-sm text-text-muted mt-1">Manage users, roles, notifications, audit logs, retention, compliance reports, template versions, and archive checks.</p>
            </div>
            <div class="flex flex-wrap gap-3 text-sm font-medium">
                <a href="<?= e(url('hr.users.index')) ?>" class="text-secondary hover:underline">Users</a>
                <a href="<?= e(url('hr.audit-log.index')) ?>" class="text-secondary hover:underline">Audit Log</a>
                <a href="<?= e(url('hr.data-retention.index')) ?>" class="text-secondary hover:underline">Retention</a>
                <a href="<?= e(url('hr.compliance.index')) ?>" class="text-secondary hover:underline">Compliance</a>
                <a href="<?= e(url('notifications.index')) ?>" class="text-secondary hover:underline">Notifications</a>
            </div>
        </div>
    </div>

    <div class="bg-card-surface border border-border-base rounded-lg p-6 shadow-ambient flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-primary">Compliance Notification Checks</h2>
            <p class="text-sm text-text-muted mt-1">Run missing-feedback reminders and offer-expiry alerts manually. Duplicate notifications are skipped.</p>
        </div>
        <form method="POST" action="<?= e(url('hr.checks.run')) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="bg-primary text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-primary-container">Run Checks</button>
        </form>
    </div>
</div>
