<div class="max-w-5xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Version History: <?= e($requisition['title']) ?></h1>
            <p class="text-text-muted mt-2 text-lg">Select two versions to compare their contents.</p>
        </div>
        <a href="<?= e(url('hr.requisitions.show', ['id' => $requisition['job_id']])) ?>" class="text-info hover:underline text-sm font-medium">&larr; Back to Requisition</a>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="bg-success-bg border border-success text-success px-4 py-3 rounded-lg mb-6 shadow-sm flex items-center">
            <span class="material-symbols-outlined mr-2">check_circle</span>
            <?= e($_SESSION['flash_success']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="bg-error-bg border border-error text-error px-4 py-3 rounded-lg mb-6 shadow-sm flex items-center">
            <span class="material-symbols-outlined mr-2">error</span>
            <?= e($_SESSION['flash_error']) ?>
        </div>
    <?php endif; ?>

    <form action="<?= e(url('hr.versions.compare', ['id' => $requisition['job_id']])) ?>" method="GET" class="space-y-4" id="compare-form">
        <div class="flex justify-end">
            <button type="submit" class="bg-primary hover:bg-primary-light text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm disabled:opacity-50" id="compare-btn" disabled>
                Compare Selected
            </button>
        </div>

        <div class="bg-card-surface border border-border-base rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-bg-alt border-b border-border-base text-text-muted text-sm uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold w-12 text-center">Compare</th>
                        <th class="px-6 py-4 font-semibold">Version</th>
                        <th class="px-6 py-4 font-semibold">Created By</th>
                        <th class="px-6 py-4 font-semibold">Date</th>
                        <th class="px-6 py-4 font-semibold">Preview</th>
                        <th class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-base">
                    <?php foreach ($versions as $version): ?>
                        <tr class="hover:bg-bg-alt transition-colors">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" name="versions[]" value="<?= e($version['version_id']) ?>" class="version-checkbox w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary">
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">v<?= e($version['version_number']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-primary text-sm">
                                <?= e($version['creator_name']) ?>
                            </td>
                            <td class="px-6 py-4 text-text-muted text-sm">
                                <?= e(date('M j, Y, g:i a', strtotime($version['created_at']))) ?>
                            </td>
                            <td class="px-6 py-4 text-text-muted text-sm max-w-xs truncate">
                                <?= e(substr(strip_tags($version['description_body']), 0, 100)) ?><?= strlen(strip_tags($version['description_body'])) > 100 ? '...' : '' ?>
                            </td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <a href="<?= e(url('hr.versions.show', ['id' => $requisition['job_id'], 'versionId' => $version['version_id']])) ?>" class="text-info hover:text-blue-700 font-medium text-sm transition-colors">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($versions)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-text-muted">No versions found for this requisition.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.version-checkbox');
    const compareBtn = document.getElementById('compare-btn');
    const compareForm = document.getElementById('compare-form');

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('.version-checkbox:checked').length;
            
            if (checkedCount >= 2) {
                checkboxes.forEach(box => {
                    if (!box.checked) box.disabled = true;
                });
                compareBtn.disabled = false;
            } else {
                checkboxes.forEach(box => box.disabled = false);
                compareBtn.disabled = true;
            }
        });
    });

    compareForm.addEventListener('submit', function(e) {
        const checked = document.querySelectorAll('.version-checkbox:checked');
        if (checked.length !== 2) {
            e.preventDefault();
            alert('Please select exactly two versions to compare.');
        } else {
            // Transform array brackets to v1 and v2 for clean URL params
            e.preventDefault();
            const v1 = checked[0].value;
            const v2 = checked[1].value;
            window.location.href = `${this.action}?v1=${v1}&v2=${v2}`;
        }
    });
});
</script>
