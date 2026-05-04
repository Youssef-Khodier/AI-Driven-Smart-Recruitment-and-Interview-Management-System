@extends('layouts.app')

@section('content')
    <h1>{{ $assessment->title }}</h1>
    <p><strong>Job:</strong> {{ $assessment->jobRequisition->title }}</p>
    <p><strong>Type:</strong> {{ $assessment->type->value }}</p>
    <p><strong>Duration:</strong> {{ $assessment->duration_minutes }} minutes</p>
    <p><strong>Status:</strong> {{ $assessment->is_active ? 'Active' : 'Inactive' }}</p>
    <p><strong>Simulation notice:</strong> Scores and proctoring events are simulated and advisory.</p>

    <p>
        <a class="button" href="{{ route('hr.assessments.edit', $assessment) }}">Edit assessment</a>
        <a class="button" href="{{ route('hr.assessment-questions.create', $assessment) }}">Add question</a>
        <a class="button" href="{{ route('hr.assessment-results.index', $assessment->jobRequisition) }}">Review results</a>
    </p>

    @if ($assessment->is_active)
        <form method="POST" action="{{ route('hr.assessments.deactivate', $assessment) }}" style="margin-bottom:1rem;">
            @csrf
            <button type="submit">Deactivate for new attempts</button>
        </form>
    @endif

    <h2>Questions</h2>
    @if ($assessment->questions->isEmpty())
        <p>No questions have been added yet.</p>
    @else
        <table>
            <thead><tr><th>Order</th><th>Type</th><th>Question</th><th>Points</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($assessment->questions as $question)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $question->type->value }}</td>
                        <td>{{ \\Illuminate\\Support\\Str::limit($question->question_text, 80) }}</td>
                        <td>{{ $question->points }}</td>
                        <td>{{ $question->is_active ? 'Active' : 'Inactive' }}</td>
                        <td>
                            <a href="{{ route('hr.assessment-questions.edit', $question) }}">Edit</a>
                            @if ($question->is_active)
                                <form method="POST" action="{{ route('hr.assessment-questions.deactivate', $question) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit">Deactivate</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @isset($reviewAttempt)
        <h2>Attempt Detail</h2>
        <p><strong>Candidate:</strong> {{ $reviewAttempt->candidate->user->name }}</p>
        <p><strong>Status:</strong> {{ $reviewAttempt->status->value }}</p>
        <p><strong>Simulated score:</strong> {{ $reviewAttempt->score ?? 'Pending' }}</p>
        <p><strong>Started:</strong> {{ $reviewAttempt->start_time?->format('Y-m-d H:i') }}</p>
        <p><strong>Ended:</strong> {{ $reviewAttempt->end_time?->format('Y-m-d H:i') ?? 'N/A' }}</p>

        <h3>Saved Answer Evidence</h3>
        <table>
            <thead><tr><th>#</th><th>Question Snapshot</th><th>Answer</th><th>Awarded</th></tr></thead>
            <tbody>
                @foreach ($reviewAttempt->attemptQuestions as $snapshot)
                    <tr>
                        <td>{{ $snapshot->display_order }}</td>
                        <td>{{ $snapshot->question_text }}</td>
                        <td>{{ $snapshot->submission?->answer_text ?? 'No answer saved' }}</td>
                        <td>{{ $snapshot->submission?->awarded_points ?? 'N/A' }} / {{ $snapshot->points }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h3>Simulated Proctoring Events</h3>
        @if ($reviewAttempt->integrityEvents->isEmpty())
            <p>No simulated focus-loss events recorded.</p>
        @else
            <table>
                <thead><tr><th>When</th><th>Event</th></tr></thead>
                <tbody>
                    @foreach ($reviewAttempt->integrityEvents as $event)
                        <tr><td>{{ $event->occurred_at->format('Y-m-d H:i:s') }}</td><td>{{ $event->event_type }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endisset
@endsection
