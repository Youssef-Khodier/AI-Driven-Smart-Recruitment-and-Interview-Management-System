<h1>Interviewer Dashboard</h1>
<p>Welcome, <?= e($user['name']) ?>.</p>
<p><a href="<?= e(url('interviewer.interviews.index')) ?>">Assigned interviews</a></p>
<p class="muted">Additional interview scheduling and feedback workflows are reserved for the next SRIM phase.</p>
