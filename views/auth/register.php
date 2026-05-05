<div class="max-w-2xl mx-auto bg-card-surface rounded-xl shadow-ambient p-8 border border-border-base mt-10">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-primary">Candidate Registration</h1>
        <p class="text-text-muted mt-2 text-sm">Create an account to start applying for open roles.</p>
    </div>
    <form method="POST" action="<?= e(url('register.store')) ?>" class="space-y-5">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Full Name *</label>
                <input name="name" value="<?= e(old('name')) ?>" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Email Address *</label>
                <input type="email" name="email" value="<?= e(old('email')) ?>" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Password *</label>
                <input type="password" name="password" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Phone *</label>
                <input name="phone" value="<?= e(old('phone')) ?>" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Current Title</label>
                <input name="current_title" value="<?= e(old('current_title')) ?>" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Years of Experience</label>
                <input type="number" name="years_experience" value="<?= e(old('years_experience', 0)) ?>" min="0" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-primary mb-1">Location</label>
                <input name="location" value="<?= e(old('location')) ?>" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-primary mb-1">Skill Keywords</label>
                <textarea name="skill_keywords" rows="3" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm"><?= e(old('skill_keywords')) ?></textarea>
                <p class="text-xs text-text-muted mt-1">Comma-separated list of your technical skills.</p>
            </div>
        </div>
        <div class="pt-4 border-t border-border-base mt-6">
            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-secondary-container hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary-container transition-colors">
                Create Account
            </button>
        </div>
        <div class="text-center text-sm text-text-muted mt-4">
            Already have an account? <a href="<?= e(url('login')) ?>" class="text-secondary hover:underline">Log in</a>
        </div>
    </form>
</div>
