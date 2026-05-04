@extends('layouts.app')

@section('content')
    <h1>{{ $application->jobRequisition->title }}</h1>
    <p><strong>Department:</strong> {{ $application->jobRequisition->department->name }}</p>
    <p><strong>Status:</strong> {{ $application->status->value }}</p>
    <p><strong>Simulated advisory match score:</strong> {{ $application->match_score }}</p>
    <p><strong>Applied:</strong> {{ $application->applied_at->format('Y-m-d H:i') }}</p>

    <h2>Technical Assessments</h2>
    @if ($application->jobRequisition->assessments->isEmpty())
        <p>No assessment is available for this job yet.</p>
    @elseif ($application->status !== \App\Enums\ApplicationStatus::ASSESSMENT)
        <p>Assessments become available when your application reaches the Assessment stage.</p>
    @else
        <table>
            <thead><tr><th>Assessment</th><th>Duration</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                @foreach ($application->jobRequisition->assessments as $assessment)
                    @php($attempt = $application->candidateAssessments->firstWhere('assessment_id', $assessment->assessment_id))
                    <tr>
                        <td>{{ $assessment->title }}</td>
                        <td>{{ $assessment->duration_minutes }} minutes</td>
                        <td>{{ $attempt?->status->value ?? ($assessment->is_active ? 'Available' : 'Unavailable') }}</td>
                        <td>
                            @if ($attempt)
                                <a href="{{ $attempt->status === \App\Enums\AssessmentAttemptStatus::IN_PROGRESS ? route('candidate.assessments.show', $attempt) : route('candidate.assessments.result', $attempt) }}">View attempt</a>
                            @elseif ($assessment->is_active)
                                <form method="POST" action="{{ route('candidate.assessments.start', [$application, $assessment]) }}">
                                    @csrf
                                    <button type="submit">Start assessment</button>
                                </form>
                            @else
                                Not available
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

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
