<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? '') ? $title . ' - SRIM' : 'SRIM') ?></title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f5f7fb; color: #172033; }
        header { background: #172033; color: #fff; padding: 1rem; }
        nav { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; }
        nav a, nav button { color: #fff; background: transparent; border: 0; cursor: pointer; font: inherit; text-decoration: none; }
        main { max-width: 1080px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: #fff; border-radius: .75rem; box-shadow: 0 10px 30px rgba(23, 32, 51, .08); padding: 1.5rem; overflow-x: auto; }
        .alert { padding: .75rem 1rem; border-radius: .5rem; margin-bottom: 1rem; }
        .alert-success { background: #e8f7ef; color: #116033; }
        .alert-error { background: #fdecec; color: #8f1f1f; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: .75rem; border-bottom: 1px solid #e2e8f0; text-align: left; vertical-align: top; }
        input, select, textarea { display: block; width: 100%; max-width: 36rem; padding: .65rem; margin: .25rem 0 1rem; border: 1px solid #cbd5e1; border-radius: .4rem; }
        textarea { min-height: 7rem; }
        button, .button { display: inline-block; background: #1f5eff; color: #fff; border: 0; border-radius: .4rem; padding: .65rem 1rem; text-decoration: none; cursor: pointer; margin: .2rem .2rem .2rem 0; }
        .muted { color: #64748b; }
        .actions { display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; }
        form.inline { display: inline; }
    </style>
</head>
<body>
<header>
    <nav aria-label="Primary navigation">
        <strong>SRIM</strong>
        <a href="<?= e(url('dashboard')) ?>">Dashboard</a>
        <?php if ($user = auth_user()): ?>
            <?php if ($user['role'] === 'HR_ADMIN'): ?>
                <a href="<?= e(url('hr.users.index')) ?>">Users</a>
                <a href="<?= e(url('hr.requisitions.index')) ?>">Requisitions</a>
            <?php endif; ?>
            <?php if ($user['role'] === 'CANDIDATE'): ?>
                <a href="<?= e(url('candidate.profile')) ?>">My Profile</a>
                <a href="<?= e(url('candidate.jobs.index')) ?>">Open Jobs</a>
                <a href="<?= e(url('candidate.applications.index')) ?>">My Applications</a>
            <?php endif; ?>
            <form method="POST" action="<?= e(url('logout')) ?>" style="margin-left:auto;">
                <?= csrf_field() ?>
                <button type="submit">Logout <?= e($user['name']) ?></button>
            </form>
        <?php else: ?>
            <a href="<?= e(url('login')) ?>">Login</a>
            <a href="<?= e(url('register')) ?>">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main>
    <?php if ($status = flash('status')): ?><div class="alert alert-success"><?= e($status) ?></div><?php endif; ?>
    <?php if ($errors = error_list()): ?>
        <div class="alert alert-error"><strong>Please correct the following:</strong><ul>
        <?php foreach ($errors as $messages): foreach ((array) $messages as $message): ?><li><?= e($message) ?></li><?php endforeach; endforeach; ?>
        </ul></div>
    <?php endif; ?>
    <section class="card"><?= $content ?></section>
</main>
</body>
</html>
