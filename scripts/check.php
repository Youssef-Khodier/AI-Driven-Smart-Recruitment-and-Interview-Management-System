<?php

$root = dirname(__DIR__);
$paths = [$root . '/app', $root . '/bootstrap', $root . '/public', $root . '/routes', $root . '/scripts', $root . '/views'];
$failed = false;

foreach ($paths as $path) {
    if (! is_dir($path)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $command = 'php -l ' . escapeshellarg($file->getPathname());
        exec($command, $output, $code);
        if ($code !== 0) {
            $failed = true;
            print implode("\n", $output) . "\n";
        }
        $output = [];
    }
}

if ($failed) {
    exit(1);
}

print "PHP syntax check passed.\n";
