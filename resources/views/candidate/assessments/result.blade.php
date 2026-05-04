@extends('layouts.app')

@section('content')
    <h1>Assessment Result</h1>
    <p><strong>Assessment:</strong> {{ $attempt->assessment->title }}</p>
    <p><strong>Status:</strong> {{ $attempt->status->value }}</p>
    <p><strong>Simulated score:</strong> {{ $attempt->score ?? 'Pending' }}</p>
    <p><strong>Simulation notice:</strong> This score and any proctoring data are simulated and advisory.</p>
    <p><strong>Started:</strong> {{ $attempt->start_time?->format('Y-m-d H:i') }}</p>
    <p><strong>Ended:</strong> {{ $attempt->end_time?->format('Y-m-d H:i') ?? 'N/A' }}</p>

    <h2>Your saved answers</h2>
    <table>
        <thead><tr><th>#</th><th>Question</th><th>Answer</th></tr></thead>
        <tbody>
            @foreach ($attempt->attemptQuestions as $snapshot)
                <tr>
                    <td>{{ $snapshot->display_order }}</td>
                    <td>{{ $snapshot->question_text }}</td>
                    <td>{{ $snapshot->submission?->answer_text ?? 'No answer saved' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Simulated focus-loss events:</strong> {{ $attempt->integrityEvents->where('event_type', 'FOCUS_LOST')->count() }}</p>
@endsection
