<div class="max-w-5xl mx-auto space-y-8">
    <div>
        <h1 class="text-3xl font-bold text-primary">HR Dashboard</h1>
        <p class="text-text-muted mt-2 text-lg">Welcome, <?= e($user['name']) ?>.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Dashboard Nav Cards -->
        <a href="<?= e(url('hr.requisitions.index')) ?>" class="bg-card-surface p-6 rounded-xl shadow-ambient hover-lift border border-border-base flex flex-col justify-between group h-full">
            <div class="p-3 bg-info-bg rounded-lg text-info w-fit mb-4 group-hover:bg-blue-200 transition-colors">
                <span class="material-symbols-outlined">folder_open</span>
            </div>
            <div>
                <p class="font-semibold text-primary text-lg group-hover:text-info transition-colors">Manage Requisitions</p>
                <p class="text-sm text-text-muted mt-1">Create and track job postings</p>
            </div>
        </a>

        <a href="<?= e(url('hr.users.index')) ?>" class="bg-card-surface p-6 rounded-xl shadow-ambient hover-lift border border-border-base flex flex-col justify-between group h-full">
            <div class="p-3 bg-success-bg rounded-lg text-success w-fit mb-4 group-hover:bg-green-200 transition-colors">
                <span class="material-symbols-outlined">group</span>
            </div>
            <div>
                <p class="font-semibold text-primary text-lg group-hover:text-success transition-colors">User Administration</p>
                <p class="text-sm text-text-muted mt-1">Manage system access & roles</p>
            </div>
        </a>

        <a href="<?= e(url('hr.offers.index')) ?>" class="bg-card-surface p-6 rounded-xl shadow-ambient hover-lift border border-border-base flex flex-col justify-between group h-full">
            <div class="p-3 bg-warning-bg rounded-lg text-warning w-fit mb-4 group-hover:bg-yellow-200 transition-colors">
                <span class="material-symbols-outlined">description</span>
            </div>
            <div>
                <p class="font-semibold text-primary text-lg group-hover:text-warning transition-colors">Manage Offers</p>
                <p class="text-sm text-text-muted mt-1">Review and issue job offers</p>
            </div>
        </a>

        <a href="<?= e(url('hr.onboarding.index')) ?>" class="bg-card-surface p-6 rounded-xl shadow-ambient hover-lift border border-border-base flex flex-col justify-between group h-full">
            <div class="p-3 bg-purple-100 rounded-lg text-purple-700 w-fit mb-4 group-hover:bg-purple-200 transition-colors">
                <span class="material-symbols-outlined">how_to_reg</span>
            </div>
            <div>
                <p class="font-semibold text-primary text-lg group-hover:text-purple-700 transition-colors">Manage Onboarding</p>
                <p class="text-sm text-text-muted mt-1">Track candidate onboarding</p>
            </div>
        </a>
    </div>
</div>
