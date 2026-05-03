@extends('layouts.app')

@section('content')
    <h1>Candidate Dashboard</h1>
    <p>Welcome, {{ $user->name }}. Your applications and assessments will appear here in later SRIM phases.</p>
    <a class="button" href="{{ route('candidate.profile') }}">View my profile</a>
@endsection
