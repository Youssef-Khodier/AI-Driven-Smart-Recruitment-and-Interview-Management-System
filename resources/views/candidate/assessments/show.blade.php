@extends('layouts.app')

@section('content')
    <h1>{{ $attempt->assessment->title }}</h1>
    <p><strong>Job:</strong> {{ $attempt->assessment->jobRequisition->title }}</p>
    <p><strong>Status:</strong> {{ $attempt->status->value }}</p>
    <p><strong>Time remaining until:</strong> {{ $attempt->expires_at?->format('Y-m-d H:i:s') }}</p>
    <p><strong>Simulation notice:</strong> Your score is simulated and advisory.</p>

    <form method="POST" action="{{ route('candidate.assessments.focus-events.store', $attempt) }}" style="margin-bottom:1rem;">
        @csrf
        <input type="hidden" name="event_type" value="FOCUS_LOST">
        <input type="hidden" name="visible_state" value="manual-demo">
        <button type="submit">Record simulated focus-loss event</button>
    </form>

    @foreach ($attempt->attemptQuestions as $snapshot)
        <section style="border:1px solid #e2e8f0;border-radius:.5rem;padding:1rem;margin-bottom:1rem;">
            <h2>Question {{ $snapshot->display_order }} ({{ $snapshot->question_type->value }})</h2>
            <p>{{ $snapshot->question_text }}</p>
            <form method="POST" action="{{ route('candidate.assessments.answers.update', [$attempt, $snapshot]) }}">
                @csrf
                @method('PUT')
                @if ($snapshot->question_type === \App\Enums\AssessmentQuestionType::MCQ && is_array($snapshot->options))
                    <label for="answer_{{ $snapshot->attempt_question_id }}">Answer</label>
                    <select id="answer_{{ $snapshot->attempt_question_id }}" name="answer_text">
                        <option value="">Select an answer</option>
                        @foreach ($snapshot->options as $option)
                            <option value="{{ $option }}" @selected(($snapshot->submission?->answer_text ?? '') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                @else
                    <label for="answer_{{ $snapshot->attempt_question_id }}">Answer</label>
                    <textarea id="answer_{{ $snapshot->attempt_question_id }}" name="answer_text">{{ $snapshot->submission?->answer_text }}</textarea>
                @endif
                <button type="submit">Save answer</button>
            </form>
        </section>
    @endforeach

    <form method="POST" action="{{ route('candidate.assessments.submit', $attempt) }}">
        @csrf
        <button type="submit">Submit final answers</button>
    </form>
@endsection
