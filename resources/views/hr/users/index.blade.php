@extends('layouts.app')

@section('content')
    <h1>User Administration</h1>
    <p><a class="button" href="{{ route('hr.users.create') }}">Create user</a></p>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Department</th>
                <th>Access</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role->value }}</td>
                    <td>{{ $user->status->value }}</td>
                    <td>{{ $user->department?->name ?? 'N/A' }}</td>
                    <td><a href="{{ route('hr.users.access.edit', $user) }}">Edit access</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
