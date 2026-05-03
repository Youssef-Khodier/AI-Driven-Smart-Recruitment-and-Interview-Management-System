@extends('layouts.guest')

@section('content')
    <h1>Login</h1>
    <p>Use your SRIM account credentials.</p>

    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <p>New candidate? <a href="{{ route('register') }}">Register here</a>.</p>
@endsection
