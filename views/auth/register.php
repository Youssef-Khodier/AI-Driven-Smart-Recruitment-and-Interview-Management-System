<h1>Candidate Registration</h1>
<form method="POST" action="<?= e(url('register.store')) ?>">
    <?= csrf_field() ?>
    <label>Name<input name="name" value="<?= e(old('name')) ?>" required></label>
    <label>Email<input type="email" name="email" value="<?= e(old('email')) ?>" required></label>
    <label>Password<input type="password" name="password" required></label>
    <label>Phone<input name="phone" value="<?= e(old('phone')) ?>" required></label>
    <label>Current title<input name="current_title" value="<?= e(old('current_title')) ?>"></label>
    <label>Years experience<input type="number" name="years_experience" value="<?= e(old('years_experience', 0)) ?>" min="0"></label>
    <label>Location<input name="location" value="<?= e(old('location')) ?>"></label>
    <label>Skill keywords<textarea name="skill_keywords"><?= e(old('skill_keywords')) ?></textarea></label>
    <button type="submit">Register</button>
</form>
