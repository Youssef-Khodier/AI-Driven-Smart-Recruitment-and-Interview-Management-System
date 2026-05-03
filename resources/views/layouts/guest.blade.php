<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title.' - ' : '' }}{{ config('app.name', 'SRIM') }}</title>
    <style>
        body { min-height: 100vh; margin: 0; display: grid; place-items: center; font-family: Arial, sans-serif; background: linear-gradient(135deg, #172033, #1f5eff); color: #172033; }
        main { width: min(92vw, 430px); background: #fff; border-radius: 1rem; box-shadow: 0 20px 60px rgba(0,0,0,.22); padding: 2rem; }
        input { display: block; width: 100%; box-sizing: border-box; padding: .75rem; margin: .25rem 0 1rem; border: 1px solid #cbd5e1; border-radius: .5rem; }
        button, .button { display: inline-block; background: #1f5eff; color: #fff; border: 0; border-radius: .5rem; padding: .75rem 1rem; text-decoration: none; cursor: pointer; }
        .alert { background: #fdecec; color: #8f1f1f; border-radius: .5rem; padding: .75rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <main>
        @if ($errors->any())
            <div class="alert">
                <strong>Please correct the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
