<?php

namespace App\Http\Controllers\Candidate;

use App\Enums\JobRequisitionStatus;
use App\Http\Controllers\Controller;
use App\Models\JobRequisition;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class JobController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', JobRequisition::class);

        return view('candidate.jobs.index', [
            'title' => 'Open Jobs',
            'jobs' => JobRequisition::query()
                ->with('department')
                ->where('status', JobRequisitionStatus::OPEN)
                ->latest('opened_at')
                ->get(),
        ]);
    }

    public function show(JobRequisition $requisition): View
    {
        Gate::authorize('view', $requisition);

        return view('candidate.jobs.show', [
            'title' => $requisition->title,
            'job' => $requisition->load('department'),
        ]);
    }
}
