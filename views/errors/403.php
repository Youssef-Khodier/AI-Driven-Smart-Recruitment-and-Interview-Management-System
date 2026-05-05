<div class="min-h-[50vh] flex flex-col items-center justify-center text-center px-4">
    <div class="bg-error-bg/30 text-error p-6 rounded-full mb-6">
        <span class="material-symbols-outlined text-[64px]">lock</span>
    </div>
    <h1 class="text-4xl font-bold text-primary mb-4">Forbidden</h1>
    <p class="text-lg text-text-muted"><?= e($message ?? 'You are not authorized to access this page.') ?></p>
    <a href="<?= e(url('dashboard')) ?>" class="mt-8 bg-secondary-container text-white px-6 py-2.5 rounded-md hover:bg-blue-700 transition-colors font-medium">Return to Dashboard</a>
</div>
