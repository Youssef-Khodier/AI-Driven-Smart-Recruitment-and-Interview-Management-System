<?php $title = 'Screening Layout Check'; ?>
<?php ob_start(); ?>
<div class="p-6">
    <h1 class="text-2xl font-bold">Screening Module Load OK</h1>
    <p>This verifies the view directory structure and layout compatibility.</p>
</div>
<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../../layouts/app.php'; ?>
