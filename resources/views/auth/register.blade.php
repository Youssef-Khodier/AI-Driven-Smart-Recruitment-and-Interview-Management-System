@extends('layouts.guest')

@section('content')
    <h1>Candidate Registration</h1>
    <p>Create your candidate account for the SRIM recruitment portal.</p>

    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <label for="name">Name</label>
        <input id="name" name="name" value="{{ old('name') }}" required autofocus>

        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>

        <label for="phone">Phone</label>
        <input id="phone" name="phone" value="{{ old('phone') }}" required>

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>

        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required>

        <button type="submit">Register</button>
    </form>

    <p>Already registered? <a href="{{ route('login') }}">Log in</a>.</p>
@endsection
