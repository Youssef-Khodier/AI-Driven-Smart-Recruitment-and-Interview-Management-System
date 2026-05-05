<div class="max-w-md mx-auto bg-card-surface rounded-xl shadow-ambient p-8 border border-border-base mt-10">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-primary">Login</h1>
        <p class="text-text-muted mt-2 text-sm">Welcome back to SRIM. Please enter your details.</p>
    </div>
    <form method="POST" action="<?= e(url('login.store')) ?>" class="space-y-5">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm font-medium text-primary mb-1">Email</label>
            <input type="email" name="email" value="<?= e(old('email')) ?>" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-primary mb-1">Password</label>
            <input type="password" name="password" required class="w-full border-border-base rounded-md shadow-sm focus:ring-secondary focus:border-secondary sm:text-sm">
        </div>
        <div class="pt-2">
            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-secondary-container hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary-container transition-colors">
                Sign in
            </button>
        </div>
        <div class="text-center text-sm text-text-muted mt-4">
            Don't have an account? <a href="<?= e(url('register')) ?>" class="text-secondary hover:underline">Register</a>
        </div>
    </form>
</div>
