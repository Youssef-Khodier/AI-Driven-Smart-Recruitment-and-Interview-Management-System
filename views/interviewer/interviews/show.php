<h1><?= e($title) ?></h1>

<?php if ($briefing['assignment']['role_in_panel'] === \App\Enums\InterviewAssignmentRole::OBSERVER->value): ?>
    <p class="notice">Observer access - training only</p>
<?php endif; ?>

<div>
    <h2>Candidate Summary</h2>
    <p><strong>Name:</strong> <?= e($briefing['candidate_name']) ?></p>
    <p><strong>Title:</strong> <?= e($briefing['current_title'] ?? 'N/A') ?></p>
    <p><strong>Experience:</strong> <?= e($briefing['years_experience']) ?> years</p>
    <p><strong>Location:</strong> <?= e($briefing['location'] ?? 'N/A') ?></p>
    <?php if (empty($briefing['resume_url'])): ?>
        <p class="muted">Resume is missing.</p>
    <?php else: ?>
        <p><a href="<?= e($briefing['resume_url']) ?>" target="_blank">View Resume</a></p>
    <?php endif; ?>
</div>

<div>
    <h2>Job Requirements</h2>
    <p><strong>Title:</strong> <?= e($briefing['job_title']) ?></p>
    <p><?= nl2br(e($briefing['requirements'])) ?></p>
</div>

<div>
    <h2>Application Status</h2>
    <p><strong>Status:</strong> <?= e($briefing['application_status']) ?></p>
    <p><strong>Match Score:</strong> <?= e($briefing['match_score']) ?>%</p>
</div>

<div>
    <h2>Assessment Summary</h2>
    <?php if (empty($briefing['assessment_attempt'])): ?>
        <p class="muted">No completed assessment found for this candidate and job.</p>
    <?php else: ?>
        <p><strong>Assessment:</strong> <?= e($briefing['assessment_attempt']['assessment_title']) ?></p>
        <p><strong>Attempt Status:</strong> <?= e($briefing['assessment_attempt']['status']) ?></p>
        <p><strong>Score:</strong> <?= e($briefing['assessment_attempt']['score'] ?? 'Pending') ?></p>
        
        <?php if (empty($briefing['submissions_summary'])): ?>
            <p class="muted">No submitted answers.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($briefing['submissions_summary'] as $sub): ?>
                    <li>
                        <strong><?= e($sub['question_text']) ?></strong><br>
                        Answer: <pre><?= e($sub['answer_text'] ?? 'N/A') ?></pre>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php 
$alreadySubmitted = \App\Repositories\InterviewFeedbackRepository::alreadySubmitted($briefing['interview_id'], $actor['user_id']);
if ((new \App\Policies\InterviewFeedbackPolicy())->create($actor, $briefing, $briefing['assignment'], $alreadySubmitted)): 
?>
    <p><a href="<?= e(url('interviewer.interviews.feedback.create', [$briefing['interview_id']])) ?>">Submit official feedback</a></p>
<?php endif; ?>

<p><a href="<?= e(url('interviewer.interviews.index')) ?>">Back to Assigned Interviews</a></p>
