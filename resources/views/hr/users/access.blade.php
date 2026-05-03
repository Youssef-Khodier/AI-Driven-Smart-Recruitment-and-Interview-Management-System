@extends('layouts.app')

@section('content')
    <h1>Edit Access</h1>
    <p>{{ $target->name }} · {{ $target->email }}</p>

    <form method="POST" action="{{ route('hr.users.access.update', $target) }}">
        @csrf
        @method('PUT')

        <label for="role">Role</label>
        <select id="role" name="role" required>
            @foreach ($roles as $role)
                <option value="{{ $role->value }}" @selected(old('role', $target->role->value) === $role->value)>{{ $role->value }}</option>
            @endforeach
        </select>

        <label for="status">Status</label>
        <select id="status" name="status" required>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $target->status->value) === $status->value)>{{ $status->value }}</option>
            @endforeach
        </select>

        <button type="submit">Update access</button>
    </form>
@endsection
