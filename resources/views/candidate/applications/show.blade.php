@extends('layouts.app')

@section('content')
    <h1>{{ $application->jobRequisition->title }}</h1>
    <p><strong>Department:</strong> {{ $application->jobRequisition->department->name }}</p>
    <p><strong>Status:</strong> {{ $application->status->value }}</p>
    <p><strong>Simulated advisory match score:</strong> {{ $application->match_score }}</p>
    <p><strong>Applied:</strong> {{ $application->applied_at->format('Y-m-d H:i') }}</p>

    <h2>Status History</h2>
    @if ($application->statusHistories->isEmpty())
        <p>No status history is available yet.</p>
    @else
        <table>
            <thead><tr><th>When</th><th>Status</th><th>Reason</th></tr></thead>
            <tbody>
                @foreach ($application->statusHistories as $history)
                    <tr>
                        <td>{{ $history->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $history->new_status->value }}</td>
                        <td>{{ $history->reason ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
