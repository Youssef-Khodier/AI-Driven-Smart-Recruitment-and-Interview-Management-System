<h1>Final Evaluation: <?= e($application['candidate_name']) ?> for <?= e($application['job_title']) ?></h1>

<h2>Assessment Evidence</h2>
<?php if (empty($evidence['assessments'])): ?>
<p>No assessment evidence available.</p>
<?php else: ?>
<ul>
    <?php foreach ($evidence['assessments'] as $a): ?>
        <li><?= e($a['title']) ?> (<?= e($a['type']) ?>): <?= e($a['score']) ?></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>

<h2>Interview Evidence</h2>
<?php if (empty($evidence['interviews'])): ?>
<p>No interview evidence available.</p>
<?php else: ?>
<ul>
    <?php foreach ($evidence['interviews'] as $i): ?>
        <li><?= e($i['interview_type']) ?> by <?= e($i['interviewer_name']) ?>: Overall Score <?= e($i['overall_score']) ?>/5</li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>

<?php if ($scoreData['has_partial_evidence']): ?>
<div style="color: orange; font-weight: bold; margin-bottom: 1rem;">Warning: Partial Evidence (Missing Assessment or Interview)</div>
<?php endif; ?>

<h3>Aggregate Score: <?= $scoreData['score'] !== null ? e($scoreData['score']) : 'N/A' ?></h3>

<?php if ($evaluation): ?>
    <h2>Saved Decision</h2>
    <p><strong>Recommendation:</strong> <?= e($evaluation['recommendation']) ?></p>
    <p><strong>Notes:</strong> <?= nl2br(e($evaluation['decision_notes'])) ?></p>
    <p><strong>Status:</strong> <?= e($evaluation['status']) ?></p>
    <?php if ($canCreateOffer): ?>
        <p><a class="button" href="<?= e(url('hr.offers.create', [$application['application_id']])) ?>">Create Offer</a></p>
    <?php endif; ?>
<?php else: ?>
    <form method="POST" action="<?= e(url('hr.evaluations.store', [$application['application_id']])) ?>">
        <?= csrf_field() ?>
        
        <div>
            <label>Recommendation:</label>
            <select name="recommendation" required>
                <option value="">Select recommendation...</option>
                <?php foreach ($recommendations as $rec): ?>
                    <option value="<?= e($rec) ?>"><?= e($rec) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label>Decision Notes:</label>
            <textarea name="decision_notes" required rows="4" cols="50"></textarea>
        </div>
        
        <?php if ($scoreData['has_partial_evidence']): ?>
            <div>
                <label>
                    <input type="checkbox" name="partial_evidence_acknowledged" value="1" required>
                    I acknowledge the partial evidence and wish to proceed.
                </label>
            </div>
        <?php endif; ?>
        
        <div>
            <button type="submit">Save Final Evaluation</button>
        </div>
    </form>
<?php endif; ?>
