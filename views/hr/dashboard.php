<h1>HR Dashboard</h1>
<p>Welcome, <?= e($user['name']) ?>.</p>
<p>
    <a class="button" href="<?= e(url('hr.users.index')) ?>">Open user administration</a> 
    <a class="button" href="<?= e(url('hr.requisitions.index')) ?>">Manage job requisitions</a>
    <a class="button" href="<?= e(url('hr.offers.index')) ?>">Manage offers</a>
    <a class="button" href="<?= e(url('hr.onboarding.index')) ?>">Manage onboarding</a>
</p>
