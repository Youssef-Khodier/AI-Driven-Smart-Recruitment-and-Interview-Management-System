@extends('layouts.app')

@section('content')
    <h1>{{ $requisition->title }}</h1>
    <p><strong>Status:</strong> {{ $requisition->status->value }}</p>
    <p><strong>Department:</strong> {{ $requisition->department->name }}</p>
    <p><strong>Creator:</strong> {{ $requisition->creator->name }}</p>
    <p><strong>Description:</strong></p>
    <p>{{ $requisition->description }}</p>
    <p><strong>Requirements:</strong></p>
    <p>{{ $requisition->requirements }}</p>

    <p>
        @can('update', $requisition)
            <a class="button" href="{{ route('hr.requisitions.edit', $requisition) }}">Edit</a>
        @endcan
        <a class="button" href="{{ route('hr.applications.index', $requisition) }}">Review applicants</a>
    </p>

    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        @can('submit', $requisition)
            <form method="POST" action="{{ route('hr.requisitions.submit', $requisition) }}">@csrf<button type="submit">Submit for approval</button></form>
        @endcan
        @if ($requisition->status === \App\Enums\JobRequisitionStatus::PENDING_APPROVAL)
            <form method="POST" action="{{ route('hr.requisitions.approve', $requisition) }}">@csrf<button type="submit">Approve</button></form>
        @endif
        @can('open', $requisition)
            <form method="POST" action="{{ route('hr.requisitions.open', $requisition) }}">@csrf<button type="submit">Open to candidates</button></form>
        @endcan
        @can('close', $requisition)
            <form method="POST" action="{{ route('hr.requisitions.close', $requisition) }}">@csrf<button type="submit">Close</button></form>
        @endcan
    </div>

    <h2>Status History</h2>
    @if ($requisition->statusHistories->isEmpty())
        <p>No status changes recorded yet.</p>
    @else
        <table>
            <thead><tr><th>When</th><th>Actor</th><th>Change</th><th>Reason</th></tr></thead>
            <tbody>
                @foreach ($requisition->statusHistories as $history)
                    <tr>
                        <td>{{ $history->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $history->actor->name }}</td>
                        <td>{{ $history->old_status?->value ?? 'None' }} -> {{ $history->new_status->value }}</td>
                        <td>{{ $history->reason ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
