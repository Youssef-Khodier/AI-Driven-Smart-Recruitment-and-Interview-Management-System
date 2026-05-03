@extends('layouts.app')

@section('content')
    <h1>Create User</h1>
    <form method="POST" action="{{ route('hr.users.store') }}">
        @csrf
        <label for="name">Name</label>
        <input id="name" name="name" value="{{ old('name') }}" required>

        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>

        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required>

        <label for="role">Role</label>
        <select id="role" name="role" required>
            @foreach ($roles as $role)
                <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ $role->value }}</option>
            @endforeach
        </select>

        <label for="status">Status</label>
        <select id="status" name="status" required>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', 'ACTIVE') === $status->value)>{{ $status->value }}</option>
            @endforeach
        </select>

        <label for="department_id">Department</label>
        <select id="department_id" name="department_id">
            <option value="">None</option>
            @foreach ($departments as $department)
                <option value="{{ $department->department_id }}" @selected((string) old('department_id') === (string) $department->department_id)>{{ $department->name }}</option>
            @endforeach
        </select>

        <label for="phone">Candidate phone</label>
        <input id="phone" name="phone" value="{{ old('phone') }}" placeholder="Required for candidate accounts">

        <button type="submit">Create user</button>
    </form>
@endsection
