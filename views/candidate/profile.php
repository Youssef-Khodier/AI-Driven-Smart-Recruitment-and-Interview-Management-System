<div class="max-w-3xl mx-auto bg-card-surface rounded-xl shadow-ambient p-8 border border-border-base">
    <h1 class="text-2xl font-bold text-primary mb-6">My Profile</h1>
    <form method="POST" action="<?= e(url('candidate.profile.update')) ?>" class="space-y-6">
        <?= csrf_field() ?><?= method_field('PUT') ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Phone</label>
                <input name="phone" value="<?= e($candidate['phone'] ?? '') ?>" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Current title</label>
                <input name="current_title" value="<?= e($candidate['current_title'] ?? '') ?>" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Years experience</label>
                <input type="number" name="years_experience" min="0" value="<?= e($candidate['years_experience'] ?? 0) ?>" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-1">Location</label>
                <input name="location" value="<?= e($candidate['location'] ?? '') ?>" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-primary mb-1">Resume URL</label>
                <input name="resume_url" value="<?= e($candidate['resume_url'] ?? '') ?>" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-primary mb-1">Skill keywords</label>
                <textarea name="skill_keywords" rows="3" class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm"><?= e($candidate['skill_keywords'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="pt-4 border-t border-border-base flex justify-end">
            <button type="submit" class="bg-secondary-container text-white px-6 py-2.5 rounded-md hover:bg-blue-700 transition-colors font-medium shadow-sm">Save profile</button>
        </div>
    </form>
</div>
