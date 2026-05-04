<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\UpdateApplicationStatusRequest;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\JobRequisition;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ApplicationController extends Controller
{
    public function index(JobRequisition $requisition): View
    {
        Gate::authorize('viewAny', Application::class);

        return view('hr.applications.index', [
            'title' => 'Applicants',
            'requisition' => $requisition->load('department'),
            'applications' => Application::query()
                ->with(['candidate.user'])
                ->where('job_id', $requisition->job_id)
                ->orderByDesc('match_score')
                ->orderBy('applied_at')
                ->get(),
        ]);
    }

    public function update(UpdateApplicationStatusRequest $request, Application $application): RedirectResponse
    {
        Gate::authorize('update', $application);

        $oldStatus = $application->status;
        $newStatus = $request->enum('status', \App\Enums\ApplicationStatus::class);
        $application->update(['status' => $newStatus]);

        ApplicationStatusHistory::create([
            'application_id' => $application->application_id,
            'actor_user_id' => $request->user()->user_id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $request->validated('reason'),
        ]);

        return redirect()->route('hr.applications.index', $application->job_id)
            ->with('status', 'Application status updated.');
    }
}
