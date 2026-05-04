<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\JobRequisition;
use App\Policies\ApplicationPolicy;
use App\Policies\JobRequisitionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(JobRequisition::class, JobRequisitionPolicy::class);
        Gate::policy(Application::class, ApplicationPolicy::class);
    }
}
