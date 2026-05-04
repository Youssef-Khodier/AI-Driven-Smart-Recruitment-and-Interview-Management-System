@extends('layouts.app')

@section('content')
    <h1>Candidate Dashboard</h1>
    <p>Welcome, {{ $user->name }}. Complete your profile and browse open requisitions to apply.</p>
    <a class="button" href="{{ route('candidate.profile') }}">View my profile</a>
    <a class="button" href="{{ route('candidate.jobs.index') }}">Browse open jobs</a>
    <a class="button" href="{{ route('candidate.applications.index') }}">Track my applications</a>
@endsection
