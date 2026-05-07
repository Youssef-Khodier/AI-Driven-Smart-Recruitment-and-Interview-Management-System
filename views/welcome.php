<section class="relative overflow-hidden rounded-lg border border-border-base bg-white shadow-ambient">
    <div class="absolute inset-0 pointer-events-none">
        <div class="h-full w-full bg-[linear-gradient(135deg,#ffffff_0%,#f5f7fb_45%,#eef6ff_100%)]"></div>
        <div class="absolute inset-x-0 bottom-0 h-28 bg-[linear-gradient(0deg,rgba(255,255,255,0.96),rgba(255,255,255,0))]"></div>
    </div>

    <div class="relative min-h-[620px] px-5 py-8 sm:px-8 md:px-12 md:py-12 lg:px-14">
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 rounded-full border border-secondary-fixed-dim bg-white/85 px-3 py-1 text-xs font-semibold uppercase text-on-secondary-fixed-variant shadow-sm">
                <span class="material-symbols-outlined text-[18px]">auto_awesome</span>
                Smart recruitment workspace
            </div>

            <h1 class="mt-6 max-w-3xl text-4xl font-black leading-tight text-primary sm:text-5xl md:text-6xl">
                SRIM
            </h1>
            <p class="mt-4 max-w-2xl text-xl font-semibold leading-snug text-text-body sm:text-2xl">
                AI-driven hiring, interviews, evaluations, offers, and onboarding in one focused system.
            </p>
            <p class="mt-4 max-w-2xl text-base leading-7 text-text-muted sm:text-lg">
                Give HR teams a calmer command center, candidates a clearer path, and interviewers the context they need before every decision.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-primary px-6 py-3 text-sm font-semibold text-white shadow-md transition-colors hover:bg-primary-container" href="<?= e(url('login')) ?>">
                    <span class="material-symbols-outlined text-[20px]">login</span>
                    Login
                </a>
                <a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-secondary-container px-6 py-3 text-sm font-semibold text-white shadow-md transition-colors hover:bg-blue-700" href="<?= e(url('register')) ?>">
                    <span class="material-symbols-outlined text-[20px]">person_add</span>
                    Candidate Registration
                </a>
            </div>
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-border-base bg-white/90 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined rounded bg-info-bg p-2 text-[22px] text-info">manage_search</span>
                    <div>
                        <p class="text-sm font-bold text-primary">Screen smarter</p>
                        <p class="text-xs text-text-muted">Shortlists, scoring, duplicates</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg border border-border-base bg-white/90 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined rounded bg-warning-bg p-2 text-[22px] text-warning">event_available</span>
                    <div>
                        <p class="text-sm font-bold text-primary">Coordinate panels</p>
                        <p class="text-xs text-text-muted">Briefings, status, feedback</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg border border-border-base bg-white/90 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined rounded bg-success-bg p-2 text-[22px] text-success">verified</span>
                    <div>
                        <p class="text-sm font-bold text-primary">Close the loop</p>
                        <p class="text-xs text-text-muted">Offers, checks, onboarding</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10 rounded-lg border border-border-base bg-primary p-4 text-white shadow-ambient md:p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-inverse-primary">Live pipeline preview</p>
                    <h2 class="mt-1 text-2xl font-bold">From requisition approval to first day, every step stays visible.</h2>
                </div>
                <div class="grid grid-cols-3 gap-3 text-center sm:min-w-[360px]">
                    <div class="rounded border border-white/15 bg-white/10 px-3 py-3">
                        <p class="text-2xl font-black">24</p>
                        <p class="text-xs text-inverse-on-surface">Active candidates</p>
                    </div>
                    <div class="rounded border border-white/15 bg-white/10 px-3 py-3">
                        <p class="text-2xl font-black">8</p>
                        <p class="text-xs text-inverse-on-surface">Interviews</p>
                    </div>
                    <div class="rounded border border-white/15 bg-white/10 px-3 py-3">
                        <p class="text-2xl font-black">3</p>
                        <p class="text-xs text-inverse-on-surface">Offers</p>
                    </div>
                </div>
            </div>

            <div class="mt-5 grid gap-3 md:grid-cols-4">
                <div class="rounded bg-white p-3 text-primary">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase text-text-muted">Open</span>
                        <span class="material-symbols-outlined text-[20px] text-info">work</span>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-info-bg">
                        <div class="h-2 w-4/5 rounded-full bg-info"></div>
                    </div>
                    <p class="mt-2 text-sm font-bold">Requisition ready</p>
                </div>
                <div class="rounded bg-white p-3 text-primary">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase text-text-muted">Screen</span>
                        <span class="material-symbols-outlined text-[20px] text-secondary">filter_alt</span>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-secondary-fixed">
                        <div class="h-2 w-3/5 rounded-full bg-secondary-container"></div>
                    </div>
                    <p class="mt-2 text-sm font-bold">Shortlist synced</p>
                </div>
                <div class="rounded bg-white p-3 text-primary">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase text-text-muted">Meet</span>
                        <span class="material-symbols-outlined text-[20px] text-warning">groups</span>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-warning-bg">
                        <div class="h-2 w-1/2 rounded-full bg-warning"></div>
                    </div>
                    <p class="mt-2 text-sm font-bold">Panel scheduled</p>
                </div>
                <div class="rounded bg-white p-3 text-primary">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase text-text-muted">Hire</span>
                        <span class="material-symbols-outlined text-[20px] text-success">handshake</span>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-success-bg">
                        <div class="h-2 w-2/5 rounded-full bg-success"></div>
                    </div>
                    <p class="mt-2 text-sm font-bold">Offer in review</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mt-8 grid gap-4 md:grid-cols-3">
    <a href="<?= e(url('register')) ?>" class="group rounded-lg border border-border-base bg-white p-5 shadow-ambient transition hover:-translate-y-0.5 hover:shadow-lg">
        <span class="material-symbols-outlined rounded bg-secondary-fixed p-2 text-[22px] text-on-secondary-fixed-variant">badge</span>
        <h2 class="mt-4 text-lg font-bold text-primary">For candidates</h2>
        <p class="mt-2 text-sm leading-6 text-text-muted">Create a profile, browse jobs, track applications, complete assessments, and follow offer or onboarding steps.</p>
        <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-secondary">
            Start profile
            <span class="material-symbols-outlined text-[18px] transition-transform group-hover:translate-x-1">arrow_forward</span>
        </span>
    </a>

    <a href="<?= e(url('login')) ?>" class="group rounded-lg border border-border-base bg-white p-5 shadow-ambient transition hover:-translate-y-0.5 hover:shadow-lg">
        <span class="material-symbols-outlined rounded bg-info-bg p-2 text-[22px] text-info">admin_panel_settings</span>
        <h2 class="mt-4 text-lg font-bold text-primary">For HR teams</h2>
        <p class="mt-2 text-sm leading-6 text-text-muted">Manage requisitions, screening rules, approvals, interview coordination, compliance checks, reports, and users.</p>
        <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-secondary">
            Open workspace
            <span class="material-symbols-outlined text-[18px] transition-transform group-hover:translate-x-1">arrow_forward</span>
        </span>
    </a>

    <a href="<?= e(url('login')) ?>" class="group rounded-lg border border-border-base bg-white p-5 shadow-ambient transition hover:-translate-y-0.5 hover:shadow-lg">
        <span class="material-symbols-outlined rounded bg-warning-bg p-2 text-[22px] text-warning">rate_review</span>
        <h2 class="mt-4 text-lg font-bold text-primary">For interviewers</h2>
        <p class="mt-2 text-sm leading-6 text-text-muted">Review assigned interviews, use candidate briefings, enter feedback, and request schedule extensions when needed.</p>
        <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-secondary">
            Review interviews
            <span class="material-symbols-outlined text-[18px] transition-transform group-hover:translate-x-1">arrow_forward</span>
        </span>
    </a>
</section>
