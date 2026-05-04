<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title.' - ' : '' }}{{ config('app.name', 'SRIM') }}</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f5f7fb; color: #172033; }
        header { background: #172033; color: #fff; padding: 1rem; }
        nav { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; }
        nav a, nav button { color: #fff; background: transparent; border: 0; cursor: pointer; font: inherit; text-decoration: none; }
        main { max-width: 980px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: #fff; border-radius: .75rem; box-shadow: 0 10px 30px rgba(23, 32, 51, .08); padding: 1.5rem; }
        .alert { padding: .75rem 1rem; border-radius: .5rem; margin-bottom: 1rem; }
        .alert-success { background: #e8f7ef; color: #116033; }
        .alert-error { background: #fdecec; color: #8f1f1f; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: .75rem; border-bottom: 1px solid #e2e8f0; text-align: left; }
        input, select { display: block; width: 100%; max-width: 32rem; padding: .65rem; margin: .25rem 0 1rem; border: 1px solid #cbd5e1; border-radius: .4rem; }
        button, .button { display: inline-block; background: #1f5eff; color: #fff; border: 0; border-radius: .4rem; padding: .65rem 1rem; text-decoration: none; cursor: pointer; }
    </style>
</head>
<body>
    <header>
        <nav aria-label="Primary navigation">
            <strong>{{ config('app.name', 'SRIM') }}</strong>
            <a href="{{ route('dashboard') }}">Dashboard</a>
            @auth
                @if(auth()->user()->hasRole(\App\Enums\UserRole::HR_ADMIN))
                    <a href="{{ route('hr.users.index') }}">Users</a>
                    <a href="{{ route('hr.requisitions.index') }}">Requisitions</a>
                @endif
                @if(auth()->user()->hasRole(\App\Enums\UserRole::CANDIDATE))
                    <a href="{{ route('candidate.profile') }}">My Profile</a>
                    <a href="{{ route('candidate.jobs.index') }}">Open Jobs</a>
                    <a href="{{ route('candidate.applications.index') }}">My Applications</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" style="margin-left:auto;">
                    @csrf
                    <button type="submit">Logout {{ auth()->user()->name }}</button>
                </form>
            @endauth
        </nav>
    </header>

    <main>
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <strong>Please correct the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="card">
            @yield('content')
        </section>
    </main>
</body>
</html>
