<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register | Task Manager</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Instrument Sans", ui-sans-serif, system-ui, sans-serif;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1rem;
            background: radial-gradient(circle at 80% 0%, #e6f4f1 0%, #eef4f2 38%, #f4f4f6 100%);
            color: #1f2937;
        }
        .card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(17, 24, 39, 0.08);
        }
        h1 {
            margin: 0 0 0.35rem;
            font-size: 1.6rem;
        }
        p {
            margin: 0 0 1.25rem;
            color: #4b5563;
        }
        .field { margin-bottom: 1rem; }
        label {
            display: block;
            margin-bottom: 0.35rem;
            font-weight: 600;
            font-size: 0.9rem;
        }
        input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0.7rem 0.75rem;
            font-size: 1rem;
        }
        input:focus {
            outline: 2px solid #111827;
            outline-offset: 1px;
            border-color: #111827;
        }
        button {
            width: 100%;
            border: none;
            border-radius: 8px;
            background: #111827;
            color: #fff;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover { background: #000; }
        .error-box {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #be123c;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .error-list { margin: 0; padding-left: 1rem; }
        .link {
            margin-top: 1rem;
            text-align: center;
            font-size: 0.92rem;
        }
        a { color: #0f172a; font-weight: 600; }
    </style>
</head>
<body>
    <section class="card">
        <h1>Create account</h1>
        <p>Register to manage tasks securely.</p>

        @if ($errors->any())
            <div class="error-box">
                <ul class="error-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <div class="field">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required>
            </div>

            <button type="submit">Register</button>
        </form>

        <p class="link">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
    </section>
</body>
</html>
