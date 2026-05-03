<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about-srim', function (): void {
    $this->info('SRIM Laravel RBAC foundation');
});
