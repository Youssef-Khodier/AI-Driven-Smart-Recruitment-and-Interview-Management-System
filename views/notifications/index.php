<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary">Notifications</h1>
            <p class="text-text-muted mt-2">Review in-app updates and mark them as read.</p>
        </div>
        <?php if (! empty($notifications)): ?>
            <form method="POST" action="<?= e(url('notifications.read-all')) ?>">
                <?= csrf_field() ?>
                <button class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-primary-container" type="submit">Mark all as read</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="bg-card-surface border border-border-base rounded-xl p-10 text-center shadow-ambient">
            <span class="material-symbols-outlined text-5xl text-text-muted">notifications_off</span>
            <h2 class="text-xl font-semibold text-primary mt-4">No notifications</h2>
            <p class="text-text-muted mt-2">You do not have any notifications yet.</p>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($notifications as $notification): ?>
                <article class="bg-card-surface border <?= $notification['is_read'] ? 'border-border-base' : 'border-info/40' ?> rounded-xl p-5 shadow-ambient">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <?php if (! $notification['is_read']): ?>
                                    <span class="w-2.5 h-2.5 rounded-full bg-info"></span>
                                <?php endif; ?>
                                <h2 class="font-semibold text-primary"><?= e($notification['title']) ?></h2>
                                <span class="text-xs uppercase tracking-wide bg-surface-container-low text-text-muted rounded-full px-2 py-0.5"><?= e($notification['type']) ?></span>
                            </div>
                            <p class="text-sm text-text-muted"><?= e($notification['message']) ?></p>
                            <p class="text-xs text-text-muted">
                                Created <?= e(date('Y-m-d H:i', strtotime($notification['created_at']))) ?>
                                <?php if ($notification['read_at']): ?>
                                    <span class="mx-1">&middot;</span> Read <?= e(date('Y-m-d H:i', strtotime($notification['read_at']))) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php if (! $notification['is_read']): ?>
                            <form method="POST" action="<?= e(url('notifications.read', [$notification['notification_id']])) ?>">
                                <?= csrf_field() ?>
                                <button class="border border-border-base px-3 py-1.5 rounded-lg text-sm text-primary hover:bg-surface-container-low" type="submit">Mark as read</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php $totalPages = (int) ceil($total / $perPage); ?>
        <?php if ($totalPages > 1): ?>
            <div class="flex items-center justify-between pt-4">
                <span class="text-sm text-text-muted">Page <?= e($page) ?> of <?= e($totalPages) ?></span>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a class="px-3 py-1.5 rounded border border-border-base text-sm" href="<?= e(url('/notifications?page=' . ($page - 1))) ?>">Previous</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a class="px-3 py-1.5 rounded border border-border-base text-sm" href="<?= e(url('/notifications?page=' . ($page + 1))) ?>">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
