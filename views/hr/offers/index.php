<h1>Offers</h1>
<table>
    <thead>
        <tr>
            <th>Candidate</th>
            <th>Job</th>
            <th>Seq</th>
            <th>Status</th>
            <th>Expiry</th>
            <th>Sent At</th>
            <th>Response At</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($offers as $offer): ?>
            <tr>
                <td><?= e($offer['candidate_name']) ?></td>
                <td><?= e($offer['job_title']) ?></td>
                <td><?= e($offer['offer_sequence']) ?></td>
                <td><?= e($offer['status']) ?></td>
                <td><?= e($offer['expiry_date']) ?></td>
                <td><?= e($offer['sent_at'] ?? 'N/A') ?></td>
                <td><?= e($offer['accepted_at'] ?? $offer['rejected_at'] ?? 'N/A') ?></td>
                <td><a href="<?= e(url('hr.offers.show', [$offer['offer_id']])) ?>">View</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
