@extends('layouts.app')

@section('content')
    <h1>HR Dashboard</h1>
    <p>Manage user access, job requisitions, and audit-relevant recruitment changes.</p>
    <p>Total user accounts: {{ $userCount }}</p>
    <a class="button" href="{{ route('hr.users.index') }}">Open user administration</a>
    <a class="button" href="{{ route('hr.requisitions.index') }}">Manage job requisitions</a>
@endsection
