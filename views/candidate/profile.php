<h1>My Profile</h1>
<form method="POST" action="<?= e(url('candidate.profile.update')) ?>">
    <?= csrf_field() ?><?= method_field('PUT') ?>
    <label>Phone<input name="phone" value="<?= e($candidate['phone'] ?? '') ?>" required></label>
    <label>Current title<input name="current_title" value="<?= e($candidate['current_title'] ?? '') ?>"></label>
    <label>Years experience<input type="number" name="years_experience" min="0" value="<?= e($candidate['years_experience'] ?? 0) ?>"></label>
    <label>Location<input name="location" value="<?= e($candidate['location'] ?? '') ?>"></label>
    <label>Resume URL<input name="resume_url" value="<?= e($candidate['resume_url'] ?? '') ?>"></label>
    <label>Skill keywords<textarea name="skill_keywords"><?= e($candidate['skill_keywords'] ?? '') ?></textarea></label>
    <button type="submit">Save profile</button>
</form>
