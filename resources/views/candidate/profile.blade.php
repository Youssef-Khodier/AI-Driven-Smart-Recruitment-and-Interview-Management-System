@extends('layouts.app')

@section('content')
    <h1>My Candidate Profile</h1>
    <dl>
        <dt>Name</dt>
        <dd>{{ $user->name }}</dd>
        <dt>Email</dt>
        <dd>{{ $user->email }}</dd>
        <dt>Phone</dt>
        <dd>{{ $candidate?->phone }}</dd>
    </dl>
@endsection
