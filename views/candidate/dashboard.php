<div class="max-w-4xl mx-auto">
    <div class="bg-card-surface rounded-xl shadow-ambient p-8 border border-border-base">
        <h1 class="text-3xl font-bold text-primary mb-2">Candidate Dashboard</h1>
        <p class="text-text-muted mb-8 text-lg">Welcome, <?= e($user['name']) ?>.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a class="bg-secondary-container hover:bg-blue-700 text-white p-6 rounded-xl transition-colors flex flex-col items-center justify-center gap-3 text-center group" href="<?= e(url('candidate.jobs.index')) ?>">
                <span class="material-symbols-outlined text-[48px] group-hover:scale-110 transition-transform">work</span>
                <span class="text-xl font-medium">Browse open jobs</span>
            </a>
            <a class="bg-card-surface hover:bg-surface-container-low border border-outline-variant text-primary p-6 rounded-xl transition-colors flex flex-col items-center justify-center gap-3 text-center group" href="<?= e(url('candidate.applications.index')) ?>">
                <span class="material-symbols-outlined text-[48px] text-secondary group-hover:scale-110 transition-transform">assignment</span>
                <span class="text-xl font-medium">Track applications & offers</span>
            </a>
        </div>
    </div>
</div>
