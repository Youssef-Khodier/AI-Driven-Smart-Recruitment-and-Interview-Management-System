<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Assessment;
use App\Models\CandidateAssessment;
use App\Models\JobRequisition;
use App\Policies\ApplicationPolicy;
use App\Policies\AssessmentPolicy;
use App\Policies\CandidateAssessmentPolicy;
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
        Gate::policy(Assessment::class, AssessmentPolicy::class);
        Gate::policy(CandidateAssessment::class, CandidateAssessmentPolicy::class);
    }
}
