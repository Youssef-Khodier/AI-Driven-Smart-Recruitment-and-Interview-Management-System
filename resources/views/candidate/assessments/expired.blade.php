@extends('layouts.app')

@section('content')
    <h1>Assessment Expired</h1>
    <p>This assessment is expired and is now read-only.</p>
    <p><strong>Simulated score:</strong> {{ $attempt->score ?? 'Pending' }}</p>
    <p>Only answers saved before the deadline were scored.</p>
    <p><a class="button" href="{{ route('candidate.assessments.result', $attempt) }}">View result</a></p>
@endsection
