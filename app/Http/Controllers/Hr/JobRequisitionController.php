<?php

namespace App\Http\Controllers\Hr;

use App\Enums\JobRequisitionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreJobRequisitionRequest;
use App\Http\Requests\Hr\UpdateJobRequisitionRequest;
use App\Models\Department;
use App\Models\JobRequisition;
use App\Models\JobRequisitionStatusHistory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class JobRequisitionController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', JobRequisition::class);

        $status = $request->string('status')->toString();
        $query = JobRequisition::query()->with(['department', 'creator'])->latest('updated_at');

        if ($status !== '' && JobRequisitionStatus::tryFrom($status)) {
            $query->where('status', $status);
        }

        return view('hr.requisitions.index', [
            'title' => 'Job Requisitions',
            'requisitions' => $query->get(),
            'statuses' => JobRequisitionStatus::cases(),
            'selectedStatus' => $status,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', JobRequisition::class);

        return view('hr.requisitions.create', [
            'title' => 'Create Requisition',
            'departments' => Department::orderBy('name')->get(),
            'requisition' => new JobRequisition(['status' => JobRequisitionStatus::DRAFT]),
        ]);
    }

    public function store(StoreJobRequisitionRequest $request): RedirectResponse
    {
        Gate::authorize('create', JobRequisition::class);

        $requisition = JobRequisition::create($request->validated() + [
            'status' => JobRequisitionStatus::DRAFT,
            'created_by' => $request->user()->user_id,
        ]);

        return redirect()->route('hr.requisitions.show', $requisition)
            ->with('status', 'Job requisition draft created.');
    }

    public function show(JobRequisition $requisition): View
    {
        Gate::authorize('view', $requisition);

        $requisition->load(['department', 'creator', 'approver', 'assessments', 'statusHistories.actor']);

        return view('hr.requisitions.show', [
            'title' => $requisition->title,
            'requisition' => $requisition,
        ]);
    }

    public function edit(JobRequisition $requisition): View
    {
        Gate::authorize('update', $requisition);

        return view('hr.requisitions.edit', [
            'title' => 'Edit Requisition',
            'departments' => Department::orderBy('name')->get(),
            'requisition' => $requisition,
        ]);
    }

    public function update(UpdateJobRequisitionRequest $request, JobRequisition $requisition): RedirectResponse
    {
        Gate::authorize('update', $requisition);

        if ($requisition->updated_at?->toIso8601String() !== $request->validated('last_seen_updated_at')) {
            throw ValidationException::withMessages([
                'last_seen_updated_at' => 'This requisition changed after you opened it. Reload before saving.',
            ]);
        }

        $oldStatus = $requisition->status;
        $requisition->fill($request->safe()->except('last_seen_updated_at'));

        if (in_array($oldStatus, [JobRequisitionStatus::PENDING_APPROVAL, JobRequisitionStatus::APPROVED], true)) {
            $requisition->status = JobRequisitionStatus::DRAFT;
        }

        $requisition->save();

        if ($oldStatus !== $requisition->status) {
            $this->recordStatus($requisition, $request->user()->user_id, $oldStatus, $requisition->status, 'Material edits returned requisition to Draft.');
        }

        return redirect()->route('hr.requisitions.show', $requisition)
            ->with('status', 'Job requisition updated.');
    }

    public function submit(Request $request, JobRequisition $requisition): RedirectResponse
    {
        Gate::authorize('submit', $requisition);

        $oldStatus = $requisition->status;
        $requisition->update(['status' => JobRequisitionStatus::PENDING_APPROVAL]);
        $this->recordStatus($requisition, $request->user()->user_id, $oldStatus, JobRequisitionStatus::PENDING_APPROVAL);

        return redirect()->route('hr.requisitions.show', $requisition)
            ->with('status', 'Job requisition submitted for approval.');
    }

    public function approve(Request $request, JobRequisition $requisition): RedirectResponse
    {
        if ($requisition->created_by === $request->user()->user_id) {
            return back()->withErrors(['approval' => 'A different active HR Admin must approve this requisition.']);
        }

        Gate::authorize('approve', $requisition);

        $oldStatus = $requisition->status;
        $requisition->update([
            'status' => JobRequisitionStatus::APPROVED,
            'approved_by' => $request->user()->user_id,
            'approved_at' => now(),
        ]);
        $this->recordStatus($requisition, $request->user()->user_id, $oldStatus, JobRequisitionStatus::APPROVED);

        return redirect()->route('hr.requisitions.show', $requisition)
            ->with('status', 'Job requisition approved.');
    }

    public function open(Request $request, JobRequisition $requisition): RedirectResponse
    {
        Gate::authorize('open', $requisition);

        $oldStatus = $requisition->status;
        $requisition->update([
            'status' => JobRequisitionStatus::OPEN,
            'opened_at' => now(),
        ]);
        $this->recordStatus($requisition, $request->user()->user_id, $oldStatus, JobRequisitionStatus::OPEN);

        return redirect()->route('hr.requisitions.show', $requisition)
            ->with('status', 'Job requisition opened to candidates.');
    }

    public function close(Request $request, JobRequisition $requisition): RedirectResponse
    {
        Gate::authorize('close', $requisition);

        $oldStatus = $requisition->status;
        $requisition->update([
            'status' => JobRequisitionStatus::CLOSED,
            'closed_at' => now(),
        ]);
        $this->recordStatus($requisition, $request->user()->user_id, $oldStatus, JobRequisitionStatus::CLOSED);

        return redirect()->route('hr.requisitions.show', $requisition)
            ->with('status', 'Job requisition closed.');
    }

    private function recordStatus(JobRequisition $requisition, int $actorUserId, ?JobRequisitionStatus $oldStatus, JobRequisitionStatus $newStatus, ?string $reason = null): void
    {
        JobRequisitionStatusHistory::create([
            'job_id' => $requisition->job_id,
            'actor_user_id' => $actorUserId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
        ]);
    }
}
