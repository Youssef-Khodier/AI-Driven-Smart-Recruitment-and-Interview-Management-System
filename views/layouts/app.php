<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? '') ? $title . ' - SRIM' : 'SRIM') ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "info": "#2563eb",
                        "on-primary-container": "#7f879f",
                        "surface-container-high": "#eae7e9",
                        "surface-dim": "#dcd9db",
                        "surface-container-lowest": "#ffffff",
                        "on-error": "#ffffff",
                        "error-bg": "#fdecec",
                        "card-surface": "#ffffff",
                        "outline-variant": "#c6c6cd",
                        "primary-fixed": "#d9e2fc",
                        "status-closed-bg": "#1e293b",
                        "on-tertiary-fixed-variant": "#564426",
                        "tertiary": "#0f0800",
                        "on-primary-fixed": "#121b2e",
                        "inverse-primary": "#bdc6e0",
                        "surface-container-low": "#f6f3f5",
                        "inverse-on-surface": "#f3f0f2",
                        "status-approved-text": "#1e40af",
                        "background": "#fbf8fa",
                        "status-closed-text": "#ffffff",
                        "text-muted": "#64748b",
                        "on-primary-fixed-variant": "#3e475b",
                        "error": "#dc2626",
                        "status-active-bg": "#22c55e",
                        "secondary": "#0046d2",
                        "status-approved-bg": "#3b82f6",
                        "secondary-fixed-dim": "#b6c4ff",
                        "role-admin": "#7c3aed",
                        "surface-container": "#f0edef",
                        "inverse-surface": "#303032",
                        "bg-main": "#f5f7fb",
                        "secondary-container": "#1d5dfe",
                        "tertiary-fixed-dim": "#ddc39b",
                        "warning": "#d97706",
                        "on-secondary-fixed-variant": "#003ab2",
                        "on-tertiary-container": "#9b8561",
                        "secondary-fixed": "#dce1ff",
                        "primary-container": "#172033",
                        "surface-container-highest": "#e4e2e3",
                        "on-secondary-container": "#eeefff",
                        "warning-bg": "#fef3c7",
                        "status-draft-bg": "#94a3b8",
                        "tertiary-fixed": "#fadfb5",
                        "status-active-text": "#166534",
                        "on-primary": "#ffffff",
                        "on-background": "#1b1b1d",
                        "success-bg": "#e8f7ef",
                        "outline": "#76777d",
                        "on-secondary-fixed": "#001550",
                        "surface-variant": "#e4e2e3",
                        "surface": "#fbf8fa",
                        "on-error-container": "#93000a",
                        "primary-fixed-dim": "#bdc6e0",
                        "error-container": "#ffdad6",
                        "on-tertiary": "#ffffff",
                        "info-bg": "#eff6ff",
                        "primary": "#01081a",
                        "text-body": "#172033",
                        "on-surface": "#1b1b1d",
                        "status-draft-text": "#475569",
                        "surface-bright": "#fbf8fa",
                        "success": "#16a34a",
                        "border-base": "#e2e8f0",
                        "status-pending-bg": "#f59e0b",
                        "on-surface-variant": "#45474c",
                        "tertiary-container": "#2c1e04",
                        "on-tertiary-fixed": "#271902",
                        "surface-tint": "#555e74",
                        "status-pending-text": "#92400e",
                        "on-secondary": "#ffffff"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "xl": "2rem",
                        "sm": "0.5rem",
                        "xs": "0.25rem",
                        "lg": "1.5rem",
                        "md": "1rem"
                    },
                    "fontFamily": {
                        "h2": ["Inter"],
                        "body": ["Inter"],
                        "mono": ["monospace"],
                        "label": ["Inter"],
                        "h3": ["Inter"],
                        "h1": ["Inter"]
                    },
                    "fontSize": {
                        "h2": ["1.25rem", { "lineHeight": "1.3", "fontWeight": "600" }],
                        "body": ["0.95rem", { "lineHeight": "1.5", "fontWeight": "400" }],
                        "mono": ["0.85rem", { "fontWeight": "400" }],
                        "label": ["0.8rem", { "letterSpacing": "0.05em", "fontWeight": "500" }],
                        "h3": ["1.1rem", { "lineHeight": "1.4", "fontWeight": "600" }],
                        "h1": ["1.75rem", { "lineHeight": "1.2", "fontWeight": "700" }]
                    }
                }
            }
        }
    </script>
    <style>
        .shadow-ambient {
            box-shadow: 0 10px 30px rgba(23, 32, 51, 0.08);
        }
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(23, 32, 51, 0.12);
        }
        .app-shell {
            box-sizing: border-box;
            max-width: 1100px;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .app-content,
        .app-content > * {
            min-width: 0;
        }
        .app-content .overflow-x-auto {
            max-width: 100%;
        }
        @media (min-width: 768px) {
            .app-shell {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }
        @media (min-width: 1024px) {
            .app-content .overflow-x-auto {
                overflow-x: visible;
            }
            .app-content .overflow-x-auto > table {
                table-layout: fixed;
                width: 100%;
                min-width: 0;
            }
            .app-content .overflow-x-auto th,
            .app-content .overflow-x-auto td {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
                white-space: normal !important;
                overflow-wrap: break-word;
                vertical-align: top;
            }
            .app-content .overflow-x-auto a,
            .app-content .overflow-x-auto button,
            .app-content .overflow-x-auto .inline-flex {
                overflow-wrap: normal;
            }
            .app-content .overflow-x-auto .whitespace-nowrap,
            .app-content .overflow-x-auto .truncate {
                white-space: normal !important;
            }
            .app-content .overflow-x-auto .truncate {
                max-width: none !important;
                overflow: visible !important;
                text-overflow: clip !important;
            }
        }
    </style>
</head>
<body class="bg-bg-main min-h-screen font-body text-body flex flex-col">

<header class="sticky top-0 w-full z-50 shadow-sm font-['Inter'] antialiased text-sm font-medium tracking-tight border-b flex items-center justify-between px-4 md:px-8 py-3 bg-white border-border-base">
    <div class="flex items-center gap-8">
        <div class="text-xl font-black tracking-tighter text-primary">SRIM</div>
        <nav class="hidden md:flex items-center gap-6">
            <a class="transition-colors rounded-md transition-all duration-200 active:scale-95 duration-150 px-2 py-1 text-text-muted hover:bg-surface-container-low hover:text-primary" href="<?= e(url('dashboard')) ?>">Dashboard</a>
            <?php if ($user = auth_user()): ?>
                <?php if ($user['role'] === 'HR_ADMIN'): ?>
                    <a class="transition-colors rounded-md transition-all duration-200 active:scale-95 duration-150 px-2 py-1 text-text-muted hover:bg-surface-container-low hover:text-primary" href="<?= e(url('hr.requisitions.index')) ?>">Recruitment</a>
                    <a class="transition-colors rounded-md transition-all duration-200 active:scale-95 duration-150 px-2 py-1 text-text-muted hover:bg-surface-container-low hover:text-primary" href="<?= e(url('hr.interviews.index')) ?>">Interviews</a>
                    <a class="transition-colors rounded-md transition-all duration-200 active:scale-95 duration-150 px-2 py-1 text-text-muted hover:bg-surface-container-low hover:text-primary" href="<?= e(url('hr.feedback-governance.index')) ?>">Feedback</a>
                    <a class="transition-colors rounded-md transition-all duration-200 active:scale-95 duration-150 px-2 py-1 text-text-muted hover:bg-surface-container-low hover:text-primary" href="<?= e(url('hr.offers.index')) ?>">Offers</a>
                    <a class="transition-colors rounded-md transition-all duration-200 active:scale-95 duration-150 px-2 py-1 text-text-muted hover:bg-surface-container-low hover:text-primary" href="<?= e(url('hr.compliance.index')) ?>">Compliance</a>
                    <a class="transition-colors rounded-md transition-all duration-200 active:scale-95 duration-150 px-2 py-1 text-text-muted hover:bg-surface-container-low hover:text-primary" href="<?= e(url('hr.users.index')) ?>">Administration</a>
                <?php endif; ?>
                <?php if ($user['role'] === 'CANDIDATE'): ?>
                    <a class="transition-colors rounded-md transition-all duration-200 active:scale-95 duration-150 px-2 py-1 text-text-muted hover:bg-surface-container-low hover:text-primary" href="<?= e(url('candidate.profile')) ?>">My Profile</a>
                    <a class="transition-colors rounded-md transition-all duration-200 active:scale-95 duration-150 px-2 py-1 text-text-muted hover:bg-surface-container-low hover:text-primary" href="<?= e(url('candidate.jobs.index')) ?>">Open Jobs</a>
                    <a class="transition-colors rounded-md transition-all duration-200 active:scale-95 duration-150 px-2 py-1 text-text-muted hover:bg-surface-container-low hover:text-primary" href="<?= e(url('candidate.applications.index')) ?>">My Applications</a>
                    <a class="transition-colors rounded-md transition-all duration-200 active:scale-95 duration-150 px-2 py-1 text-text-muted hover:bg-surface-container-low hover:text-primary" href="<?= e(url('candidate.onboarding.index')) ?>">Onboarding</a>
                <?php endif; ?>
                <?php if ($user['role'] === 'INTERVIEWER' || $user['role'] === 'JUNIOR_STAFF'): ?>
                    <a class="transition-colors rounded-md transition-all duration-200 active:scale-95 duration-150 px-2 py-1 text-text-muted hover:bg-surface-container-low hover:text-primary" href="<?= e(url('interviewer.interviews.index')) ?>">My Interviews</a>
            <?php endif; ?>
            <?php endif; ?>
        </nav>
    </div>
    
    <div class="flex items-center gap-4">
        <?php if ($user = auth_user()): ?>
            <?php $unreadCount = \App\Models\NotificationModel::unreadCount((int) $user['user_id']); ?>
            <a href="<?= e(url('notifications.index')) ?>" class="relative inline-flex items-center justify-center w-9 h-9 rounded-full bg-surface-container-low text-primary hover:bg-surface-container-high transition-colors" title="Notifications">
                <span class="material-symbols-outlined text-[20px]">notifications</span>
                <?php if ($unreadCount > 0): ?>
                    <span class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-error text-white text-[11px] leading-5 text-center font-semibold"><?= e(min($unreadCount, 99)) ?></span>
                <?php endif; ?>
            </a>
            <span class="hidden md:inline text-sm text-text-muted mr-2"><?= e($user['name']) ?></span>
            <form method="POST" action="<?= e(url('logout')) ?>" class="inline m-0">
                <?= csrf_field() ?>
                <button type="submit" class="bg-surface-container-low text-primary px-3 py-1.5 rounded text-sm hover:bg-surface-container-high transition-colors">Logout</button>
            </form>
        <?php else: ?>
            <a class="transition-colors rounded-md px-3 py-1.5 text-text-muted hover:bg-surface-container-low hover:text-primary" href="<?= e(url('login')) ?>">Login</a>
            <a class="bg-secondary-container text-white px-4 py-1.5 rounded-md hover:bg-blue-700 transition-colors" href="<?= e(url('register')) ?>">Register</a>
        <?php endif; ?>
    </div>
</header>

<main class="app-shell flex-grow w-full mx-auto py-6 md:py-xl">
    <?php if ($status = flash('status')): ?>
        <div class="bg-success-bg border border-success/20 text-success px-4 py-3 rounded-lg mb-6 flex items-center gap-3">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <p class="text-sm font-medium"><?= e($status) ?></p>
        </div>
    <?php endif; ?>

    <?php if ($errors = error_list()): ?>
        <div class="bg-error-bg border border-error/20 text-error px-4 py-4 rounded-lg mb-6">
            <div class="flex items-center gap-2 mb-2 font-medium">
                <span class="material-symbols-outlined text-[20px]">error</span>
                <strong>Please correct the following:</strong>
            </div>
            <ul class="list-disc list-inside ml-8 text-sm space-y-1">
            <?php foreach ($errors as $messages): foreach ((array) $messages as $message): ?>
                <li><?= e($message) ?></li>
            <?php endforeach; endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="app-content w-full">
        <?= $content ?>
    </div>
</main>

</body>
</html>
