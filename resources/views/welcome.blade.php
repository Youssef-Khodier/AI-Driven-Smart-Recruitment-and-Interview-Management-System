<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SRIM') }}</title>
</head>
<body>
    <main>
        <h1>SRIM</h1>
        <p>Smart Recruitment and Interview Management foundation.</p>
        <p><a href="{{ route('register') }}">Candidate registration</a> · <a href="{{ route('login') }}">Login</a></p>
    </main>
</body>
</html>
