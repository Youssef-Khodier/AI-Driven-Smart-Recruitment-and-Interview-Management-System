<h1>Login</h1>
<form method="POST" action="<?= e(url('login.store')) ?>">
    <?= csrf_field() ?>
    <label>Email<input type="email" name="email" value="<?= e(old('email')) ?>" required></label>
    <label>Password<input type="password" name="password" required></label>
    <button type="submit">Login</button>
</form>
