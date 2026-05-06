<?php require base_path('views/layouts/header.php'); ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <a href="/hr/requisitions/<?= htmlspecialchars($requisition['job_id']) ?>" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to Requisition</a>
            <h1 class="text-2xl font-semibold text-gray-900 mt-2">Sync History</h1>
            <p class="text-gray-600 mt-1">Requisition: <?= htmlspecialchars($requisition['title']) ?></p>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <ul class="divide-y divide-gray-200">
            <?php if (empty($syncRecords)): ?>
                <li class="px-6 py-4 text-gray-500 text-sm">No sync records found for this requisition.</li>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platform</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamps</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payload Summary</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($syncRecords as $record): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($record['platform_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php if ($record['status'] === 'PUBLISHED'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">PUBLISHED</span>
                                        <?php elseif ($record['status'] === 'UNPUBLISHED'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">UNPUBLISHED</span>
                                        <?php elseif ($record['status'] === 'QUEUED'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">QUEUED</span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"><?= htmlspecialchars($record['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div><strong>Queued:</strong> <?= htmlspecialchars($record['queued_at']) ?></div>
                                        <?php if ($record['completed_at']): ?>
                                            <div><strong>Completed:</strong> <?= htmlspecialchars($record['completed_at']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($record['creator_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <pre class="text-xs bg-gray-50 p-2 rounded max-w-xs overflow-auto whitespace-pre-wrap"><?= htmlspecialchars(json_encode(json_decode($record['payload_summary']), JSON_PRETTY_PRINT)) ?></pre>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php require base_path('views/layouts/footer.php'); ?>
