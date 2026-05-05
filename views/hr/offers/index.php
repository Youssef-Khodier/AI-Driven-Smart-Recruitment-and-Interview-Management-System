<div class="bg-card-surface rounded-xl shadow-ambient border border-border-base overflow-hidden">
    <div class="px-6 py-5 border-b border-border-base flex justify-between items-center bg-surface-container-low">
        <h1 class="text-2xl font-bold text-primary flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[28px]">description</span>
            Offers
        </h1>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-lowest text-text-muted text-xs uppercase tracking-wider border-b border-border-base">
                <tr>
                    <th class="px-6 py-3 font-medium">Candidate</th>
                    <th class="px-6 py-3 font-medium">Job</th>
                    <th class="px-6 py-3 font-medium text-center">Seq</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Expiry</th>
                    <th class="px-6 py-3 font-medium">Sent At</th>
                    <th class="px-6 py-3 font-medium">Response At</th>
                    <th class="px-6 py-3 font-medium text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base text-sm">
                <?php foreach ($offers as $offer): ?>
                    <tr class="hover:bg-surface-container-lowest transition-colors">
                        <td class="px-6 py-4 font-medium text-primary"><?= e($offer['candidate_name']) ?></td>
                        <td class="px-6 py-4 text-text-muted"><?= e($offer['job_title']) ?></td>
                        <td class="px-6 py-4 text-text-muted text-center"><?= e($offer['offer_sequence']) ?></td>
                        <td class="px-6 py-4">
                            <?php
                            $statusClass = 'bg-gray-100 text-gray-800 border-gray-200';
                            if ($offer['status'] === 'ACCEPTED') $statusClass = 'bg-success-bg text-success border-success/20';
                            elseif ($offer['status'] === 'REJECTED' || $offer['status'] === 'WITHDRAWN') $statusClass = 'bg-error-bg text-error border-error/20';
                            elseif ($offer['status'] === 'SENT') $statusClass = 'bg-blue-50 text-blue-800 border-blue-200';
                            ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold tracking-wide border <?= $statusClass ?>">
                                <?= e($offer['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-text-muted whitespace-nowrap">
                            <?= e(date('M d, Y', strtotime($offer['expiry_date']))) ?>
                        </td>
                        <td class="px-6 py-4 text-text-muted whitespace-nowrap">
                            <?= $offer['sent_at'] ? e(date('M d, Y H:i', strtotime($offer['sent_at']))) : '<span class="italic text-gray-400">N/A</span>' ?>
                        </td>
                        <td class="px-6 py-4 text-text-muted whitespace-nowrap">
                            <?php 
                            $response = $offer['accepted_at'] ?? $offer['rejected_at'] ?? null;
                            echo $response ? e(date('M d, Y H:i', strtotime($response))) : '<span class="italic text-gray-400">N/A</span>';
                            ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a class="inline-flex items-center justify-center px-3 py-1.5 border border-outline-variant rounded-md shadow-sm text-xs font-medium text-primary bg-white hover:bg-surface-container-low transition-colors" href="<?= e(url('hr.offers.show', [$offer['offer_id']])) ?>">
                                View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($offers)): ?>
                    <tr><td colspan="8" class="px-6 py-8 text-center text-text-muted">No offers generated yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
