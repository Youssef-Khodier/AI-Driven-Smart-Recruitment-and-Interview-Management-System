<div class="max-w-4xl mx-auto space-y-8">
    <div>
        <h1 class="text-3xl font-bold text-primary">Interviewer Dashboard</h1>
        <p class="text-text-muted mt-2 text-lg">Welcome, <?= e($user['name']) ?>.</p>
    </div>

    <div class="grid grid-cols-1 gap-6">
        <a href="<?= e(url('interviewer.interviews.index')) ?>" class="bg-card-surface p-6 rounded-xl shadow-ambient hover-lift border border-border-base flex flex-col justify-between group h-full">
            <div class="p-3 bg-secondary-container/10 rounded-lg text-secondary w-fit mb-4 group-hover:bg-secondary-container group-hover:text-white transition-colors">
                <span class="material-symbols-outlined text-[32px]">assignment_ind</span>
            </div>
            <div>
                <p class="font-semibold text-primary text-xl group-hover:text-secondary transition-colors">Interview Coordination & Logistics</p>
                <p class="text-sm text-text-muted mt-2">View candidate briefings and submit feedback</p>
            </div>
        </a>
    </div>
</div>
