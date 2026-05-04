<h1>Assessment Result</h1>
<p><strong>Assessment:</strong> <?= e($attempt['assessment_title']) ?> | <strong>Status:</strong> <?= e($attempt['status']) ?> | <strong>Simulated score:</strong> <?= e($attempt['score'] ?? 0) ?>%</p>
<p class="muted">Scores and proctoring data are simulated and advisory for the academic SRIM demo.</p>
<h2>Answers</h2>
<table><thead><tr><th>#</th><th>Question</th><th>Answer</th><th>Points</th></tr></thead><tbody>
<?php foreach ($questions as $question): ?><tr><td><?= e($question['display_order']) ?></td><td><?= e(str_limit($question['question_text'], 140)) ?></td><td><?= nl2br(e($question['answer_text'] ?? '')) ?></td><td><?= e($question['awarded_points'] ?? '-') ?> / <?= e($question['points']) ?></td></tr><?php endforeach; ?>
</tbody></table>
<h2>Simulated Proctoring Events</h2>
<ul><?php foreach ($events as $event): ?><li><?= e($event['event_type']) ?> at <?= e($event['occurred_at']) ?></li><?php endforeach; ?></ul>
