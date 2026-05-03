@extends('layouts.app')

@section('content')
    <h1>HR Dashboard</h1>
    <p>Manage phase-one user access and audit-relevant account changes.</p>
    <p>Total user accounts: {{ $userCount }}</p>
    <a class="button" href="{{ route('hr.users.index') }}">Open user administration</a>
@endsection
