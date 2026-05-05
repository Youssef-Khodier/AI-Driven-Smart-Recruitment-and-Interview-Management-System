<div class="min-h-[50vh] flex flex-col items-center justify-center text-center px-4">
    <div class="text-text-muted/30 mb-6">
        <span class="material-symbols-outlined text-[80px]">travel_explore</span>
    </div>
    <h1 class="text-4xl font-bold text-primary mb-4">Page Not Found</h1>
    <p class="text-lg text-text-muted"><?= e($message ?? 'The requested page was not found.') ?></p>
    <a href="<?= e(url('dashboard')) ?>" class="mt-8 bg-secondary-container text-white px-6 py-2.5 rounded-md hover:bg-blue-700 transition-colors font-medium">Return to Dashboard</a>
</div>
