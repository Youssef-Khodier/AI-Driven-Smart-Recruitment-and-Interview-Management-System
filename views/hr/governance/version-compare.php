<div class="max-w-5xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Compare Versions</h1>
            <p class="text-text-muted mt-2 text-lg">Comparing v<?= e($v1['version_number']) ?> &harr; v<?= e($v2['version_number']) ?></p>
        </div>
        <a href="<?= e(url('hr.requisitions.versions.index', [$requisition['job_id']])) ?>" class="text-info hover:underline text-sm font-medium">&larr; Back to Version History</a>
    </div>

    <div class="bg-card-surface border border-border-base rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-border-base">
            <h2 class="text-xl font-bold text-primary mb-4">Description Diff</h2>
            <div class="font-mono text-sm space-y-1 bg-gray-50 p-4 rounded border border-gray-200 overflow-x-auto">
                <?php foreach ($diffDescription as $chunk): ?>
                    <?php if ($chunk['type'] === 'added'): ?>
                        <div class="bg-green-100 text-green-800 px-2 py-0.5"><span class="select-none text-green-500 mr-2">+</span><?= e($chunk['content']) ?: '&nbsp;' ?></div>
                    <?php elseif ($chunk['type'] === 'removed'): ?>
                        <div class="bg-red-100 text-red-800 px-2 py-0.5"><span class="select-none text-red-500 mr-2">-</span><?= e($chunk['content']) ?: '&nbsp;' ?></div>
                    <?php else: ?>
                        <div class="text-gray-600 px-2 py-0.5"><span class="select-none text-gray-300 mr-2">&nbsp;</span><?= e($chunk['content']) ?: '&nbsp;' ?></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="p-6 border-b border-border-base">
            <h2 class="text-xl font-bold text-primary mb-4">Requirements Diff</h2>
            <div class="font-mono text-sm space-y-1 bg-gray-50 p-4 rounded border border-gray-200 overflow-x-auto">
                <?php foreach ($diffRequirements as $chunk): ?>
                    <?php if ($chunk['type'] === 'added'): ?>
                        <div class="bg-green-100 text-green-800 px-2 py-0.5"><span class="select-none text-green-500 mr-2">+</span><?= e($chunk['content']) ?: '&nbsp;' ?></div>
                    <?php elseif ($chunk['type'] === 'removed'): ?>
                        <div class="bg-red-100 text-red-800 px-2 py-0.5"><span class="select-none text-red-500 mr-2">-</span><?= e($chunk['content']) ?: '&nbsp;' ?></div>
                    <?php else: ?>
                        <div class="text-gray-600 px-2 py-0.5"><span class="select-none text-gray-300 mr-2">&nbsp;</span><?= e($chunk['content']) ?: '&nbsp;' ?></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="p-4 bg-gray-50 text-sm text-text-muted flex justify-between">
            <div>
                <span class="inline-block w-3 h-3 bg-green-200 mr-1 rounded"></span> Added in v<?= e($v2['version_number']) ?>
            </div>
            <div>
                <span class="inline-block w-3 h-3 bg-red-200 mr-1 rounded"></span> Removed from v<?= e($v1['version_number']) ?>
            </div>
        </div>
    </div>
</div>
