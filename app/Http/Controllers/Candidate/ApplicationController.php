<?php

namespace App\Http\Controllers\Candidate;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\StoreApplicationRequest;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\JobRequisition;
use App\Support\SimulatedMatchScorer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ApplicationController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Application::class);

        return view('candidate.applications.index', [
            'title' => 'My Applications',
            'applications' => Application::query()
                ->with(['jobRequisition.department'])
                ->where('candidate_id', request()->user()->candidate?->candidate_id)
                ->latest('applied_at')
                ->get(),
        ]);
    }

    public function show(Application $application): View
    {
        Gate::authorize('view', $application);

        return view('candidate.applications.show', [
            'title' => 'Application Status',
            'application' => $application->load(['jobRequisition.department', 'jobRequisition.assessments', 'candidateAssessments.assessment', 'statusHistories.actor']),
        ]);
    }

    public function store(StoreApplicationRequest $request, JobRequisition $requisition, SimulatedMatchScorer $scorer): RedirectResponse
    {
        $candidate = $request->user()->candidate;
        $existing = Application::where('candidate_id', $candidate->candidate_id)
            ->where('job_id', $requisition->job_id)
            ->first();

        if ($existing) {
            return redirect()->route('candidate.applications.show', $existing)
                ->withErrors(['duplicate' => 'You have already applied to this job.']);
        }

        $application = Application::create([
            'candidate_id' => $candidate->candidate_id,
            'job_id' => $requisition->job_id,
            'status' => ApplicationStatus::APPLIED,
            'match_score' => $scorer->score($requisition->requirements, $candidate->skill_keywords, $candidate->current_title, $candidate->years_experience),
            'applied_at' => now(),
        ]);

        ApplicationStatusHistory::create([
            'application_id' => $application->application_id,
            'actor_user_id' => $request->user()->user_id,
            'old_status' => null,
            'new_status' => ApplicationStatus::APPLIED,
            'reason' => 'Candidate submitted application.',
        ]);

        return redirect()->route('candidate.applications.show', $application)
            ->with('status', 'Application submitted with a simulated advisory match score.');
    }
}
