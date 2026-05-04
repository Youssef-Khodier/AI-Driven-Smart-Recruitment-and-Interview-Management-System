@extends('layouts.app')

@section('content')
    <h1>{{ $job->title }}</h1>
    <p><strong>Department:</strong> {{ $job->department->name }}</p>
    <p><strong>Description:</strong></p>
    <p>{{ $job->description }}</p>
    <p><strong>Requirements:</strong></p>
    <p>{{ $job->requirements }}</p>
    <p><strong>Score notice:</strong> Any match score shown after applying is simulated, advisory, and does not determine hiring decisions.</p>

    <form method="POST" action="{{ route('candidate.applications.store', $job) }}">
        @csrf
        <button type="submit">Apply once to this job</button>
    </form>
@endsection
