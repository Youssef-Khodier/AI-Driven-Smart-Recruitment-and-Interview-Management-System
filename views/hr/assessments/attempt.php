<h1>Assessment Attempt</h1>
<p><strong>Candidate:</strong> <?= e($attempt['name']) ?> | <strong>Assessment:</strong> <?= e($attempt['assessment_title']) ?> | <strong>Status:</strong> <?= e($attempt['status']) ?> | <strong>Score:</strong> <?= e($attempt['score'] ?? '-') ?></p>
<h2>Answers</h2>
<table><thead><tr><th>#</th><th>Question</th><th>Answer</th><th>Correct</th><th>Points</th></tr></thead><tbody>
<?php foreach ($questions as $question): ?><tr><td><?= e($question['display_order']) ?></td><td><?= nl2br(e($question['question_text'])) ?></td><td><?= nl2br(e($question['answer_text'] ?? '')) ?></td><td><?= $question['is_correct'] === null ? '-' : ($question['is_correct'] ? 'Yes' : 'No') ?></td><td><?= e($question['awarded_points'] ?? '-') ?> / <?= e($question['points']) ?></td></tr><?php endforeach; ?>
</tbody></table>
<h2>Simulated Proctoring Events</h2>
<table><tr><th>Type</th><th>When</th><th>Metadata</th></tr><?php foreach ($events as $event): ?><tr><td><?= e($event['event_type']) ?></td><td><?= e($event['occurred_at']) ?></td><td><?= e($event['metadata'] ?? '') ?></td></tr><?php endforeach; ?></table>
