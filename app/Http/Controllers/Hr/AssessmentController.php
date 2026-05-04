<?php

namespace App\Http\Controllers\Hr;

use App\Enums\AssessmentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreAssessmentRequest;
use App\Http\Requests\Hr\UpdateAssessmentRequest;
use App\Models\Assessment;
use App\Models\CandidateAssessment;
use App\Models\JobRequisition;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class AssessmentController extends Controller
{
    public function index(JobRequisition $requisition): View
    {
        Gate::authorize('viewAny', Assessment::class);

        return view('hr.assessments.index', [
            'title' => 'Assessments',
            'requisition' => $requisition->load('department'),
            'assessments' => $requisition->assessments()->withCount(['questions', 'candidateAssessments'])->latest()->get(),
        ]);
    }

    public function create(JobRequisition $requisition): View
    {
        Gate::authorize('create', Assessment::class);

        return view('hr.assessments.create', [
            'title' => 'Create Assessment',
            'requisition' => $requisition,
            'assessment' => new Assessment(['type' => AssessmentType::TECHNICAL, 'duration_minutes' => 60, 'is_active' => true]),
            'types' => AssessmentType::cases(),
        ]);
    }

    public function store(StoreAssessmentRequest $request, JobRequisition $requisition): RedirectResponse
    {
        Gate::authorize('create', Assessment::class);

        $assessment = $requisition->assessments()->create($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('hr.assessments.show', $assessment)
            ->with('status', 'Assessment created.');
    }

    public function show(Assessment $assessment): View
    {
        Gate::authorize('view', $assessment);

        return view('hr.assessments.show', [
            'title' => $assessment->title,
            'assessment' => $assessment->load(['jobRequisition', 'questions', 'candidateAssessments.candidate.user', 'candidateAssessments.integrityEvents']),
        ]);
    }

    public function edit(Assessment $assessment): View
    {
        Gate::authorize('update', $assessment);

        return view('hr.assessments.edit', [
            'title' => 'Edit Assessment',
            'requisition' => $assessment->jobRequisition,
            'assessment' => $assessment,
            'types' => AssessmentType::cases(),
        ]);
    }

    public function update(UpdateAssessmentRequest $request, Assessment $assessment): RedirectResponse
    {
        Gate::authorize('update', $assessment);

        $assessment->update($request->validated() + [
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('hr.assessments.show', $assessment)
            ->with('status', 'Assessment updated. Existing attempts keep their original snapshots.');
    }

    public function deactivate(Assessment $assessment): RedirectResponse
    {
        Gate::authorize('deactivate', $assessment);
        $assessment->update(['is_active' => false]);

        return redirect()->route('hr.assessments.show', $assessment)
            ->with('status', 'Assessment deactivated for new attempts.');
    }

    public function results(JobRequisition $requisition): View
    {
        Gate::authorize('viewAny', Assessment::class);

        return view('hr.assessments.results', [
            'title' => 'Assessment Results',
            'requisition' => $requisition->load('department'),
            'attempts' => CandidateAssessment::query()
                ->with(['candidate.user', 'application', 'assessment', 'integrityEvents'])
                ->whereHas('assessment', fn ($query) => $query->where('job_id', $requisition->job_id))
                ->latest('updated_at')
                ->get(),
        ]);
    }

    public function attempt(CandidateAssessment $attempt): View
    {
        Gate::authorize('review', $attempt);

        return view('hr.assessments.show', [
            'title' => 'Assessment Attempt',
            'assessment' => $attempt->assessment->load(['jobRequisition', 'questions']),
            'reviewAttempt' => $attempt->load(['candidate.user', 'application', 'attemptQuestions.submission', 'integrityEvents']),
        ]);
    }
}
