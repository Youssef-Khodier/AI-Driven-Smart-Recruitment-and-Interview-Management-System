<div class="min-h-[50vh] flex flex-col items-center justify-center text-center px-4">
    <div class="bg-warning-bg/50 text-warning p-6 rounded-full mb-6">
        <span class="material-symbols-outlined text-[64px]">timer</span>
    </div>
    <h1 class="text-4xl font-bold text-primary mb-4">Session Expired</h1>
    <p class="text-lg text-text-muted"><?= e($message ?? 'Please try again.') ?></p>
    <a href="<?= e(url('login')) ?>" class="mt-8 bg-secondary-container text-white px-6 py-2.5 rounded-md hover:bg-blue-700 transition-colors font-medium">Return to Login</a>
</div>
