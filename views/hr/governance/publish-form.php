<?php require base_path('views/layouts/header.php'); ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <a href="/hr/requisitions/<?= htmlspecialchars($requisition['job_id']) ?>" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to Requisition</a>
            <h1 class="text-2xl font-semibold text-gray-900 mt-2">Publish to Job Boards</h1>
            <p class="text-gray-600 mt-1">Requisition: <?= htmlspecialchars($requisition['title']) ?> (Status: <?= htmlspecialchars($requisition['status']) ?>)</p>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
            <div class="flex">
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="p-6">
            <?php if ($requisition['status'] !== 'OPEN'): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                This requisition cannot be published because its status is not OPEN.
                            </p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <form action="/hr/requisitions/<?= htmlspecialchars($requisition['job_id']) ?>/publish" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::getToken()) ?>">
                    
                    <fieldset>
                        <legend class="text-base font-medium text-gray-900">Select Platforms</legend>
                        <div class="mt-4 space-y-4">
                            <?php foreach ($activePlatforms as $platform): ?>
                                <?php 
                                    $isPublished = in_array($platform['platform_id'], $publishedPlatforms);
                                ?>
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="platform_<?= $platform['platform_id'] ?>" 
                                               name="platforms[]" 
                                               value="<?= $platform['platform_id'] ?>" 
                                               type="checkbox" 
                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                               <?= $isPublished ? 'checked disabled' : '' ?>>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="platform_<?= $platform['platform_id'] ?>" class="font-medium text-gray-700">
                                            <?= htmlspecialchars($platform['name']) ?>
                                        </label>
                                        <?php if ($isPublished): ?>
                                            <p class="text-gray-500">Already published</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>

                    <div class="mt-6">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Publish
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require base_path('views/layouts/footer.php'); ?>
