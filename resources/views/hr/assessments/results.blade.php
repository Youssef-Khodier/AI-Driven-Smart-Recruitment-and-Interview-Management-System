@extends('layouts.app')

@section('content')
    <h1>Assessment Results for {{ $requisition->title }}</h1>
    <p><strong>Simulation notice:</strong> Scores and focus-loss events are simulated review data and do not automatically reject candidates.</p>

    @if ($attempts->isEmpty())
        <p>No candidate assessment attempts are available for this job.</p>
    @else
        <table>
            <thead><tr><th>Candidate</th><th>Assessment</th><th>Status</th><th>Simulated Score</th><th>Focus Events</th><th>Timing</th><th>Action</th></tr></thead>
            <tbody>
                @foreach ($attempts as $attempt)
                    <tr>
                        <td>{{ $attempt->candidate->user->name }}</td>
                        <td>{{ $attempt->assessment->title }}</td>
                        <td>{{ $attempt->status->value }}</td>
                        <td>{{ $attempt->score ?? 'Pending' }}</td>
                        <td>{{ $attempt->integrityEvents->where('event_type', 'FOCUS_LOST')->count() }}</td>
                        <td>{{ $attempt->start_time?->format('Y-m-d H:i') }} - {{ $attempt->end_time?->format('Y-m-d H:i') ?? 'In progress' }}</td>
                        <td><a href="{{ route('hr.candidate-assessments.show', $attempt) }}">Review</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
