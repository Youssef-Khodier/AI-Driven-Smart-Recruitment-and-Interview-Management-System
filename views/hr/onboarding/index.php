<h1>Onboarding</h1>
<table>
    <thead>
        <tr>
            <th>Candidate</th>
            <th>Job</th>
            <th>Offer Status</th>
            <th>Onboarding Status</th>
            <th>Start Date</th>
            <th>Docs Completed</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($records as $record): ?>
            <tr>
                <td><?= e($record['candidate_name']) ?></td>
                <td><?= e($record['job_title']) ?></td>
                <td><?= e($record['offer_type']) ?></td>
                <td><?= e($record['status']) ?></td>
                <td><?= e($record['start_date'] ?? 'TBD') ?></td>
                <td><?= $record['documents_completed'] ? 'Yes' : 'No' ?></td>
                <td><a href="<?= e(url('hr.onboarding.show', [$record['onboarding_id']])) ?>">Manage</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
