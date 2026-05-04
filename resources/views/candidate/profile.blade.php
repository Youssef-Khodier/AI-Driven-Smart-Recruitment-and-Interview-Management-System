@extends('layouts.app')

@section('content')
    <h1>My Candidate Profile</h1>
    <dl>
        <dt>Name</dt>
        <dd>{{ $user->name }}</dd>
        <dt>Email</dt>
        <dd>{{ $user->email }}</dd>
    </dl>

    <form method="POST" action="{{ route('candidate.profile.update') }}">
        @csrf
        @method('PUT')

        <label for="phone">Phone</label>
        <input id="phone" name="phone" value="{{ old('phone', $candidate?->phone) }}" required>

        <label for="current_title">Current title</label>
        <input id="current_title" name="current_title" value="{{ old('current_title', $candidate?->current_title) }}" required>

        <label for="years_experience">Years experience</label>
        <input id="years_experience" name="years_experience" type="number" min="0" value="{{ old('years_experience', $candidate?->years_experience ?? 0) }}" required>

        <label for="location">Location</label>
        <input id="location" name="location" value="{{ old('location', $candidate?->location) }}" required>

        <label for="resume_url">Resume URL or reference</label>
        <input id="resume_url" name="resume_url" value="{{ old('resume_url', $candidate?->resume_url) }}" required>

        <label for="skill_keywords">Skill keywords</label>
        <input id="skill_keywords" name="skill_keywords" value="{{ old('skill_keywords', $candidate?->skill_keywords) }}" placeholder="Laravel, PHP, MySQL" required>

        <button type="submit">Save profile</button>
    </form>
@endsection
