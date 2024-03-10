<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>
    </head>

    <body>
        <h1>{{ config('app.name') }}</h1>
        <h2>Log in</h2>

        <div>
            <form method="post" action="{{ route('login.post') }}">
                @csrf

                @error('_remote_auth_rule')
                    <p>{{ $errors->first('_remote_auth_rule') }}</p>
                @enderror

                <p>
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" />
                    @error('username') {{ $errors->first('username') }} @enderror
                </p>

                <p>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" />
                    @error('password') {{ $errors->first('password') }} @enderror
                </p>

                <p>
                    <input type="checkbox" id="remember_me" name="remember_me">
                    <label for="remember_me">Remember me</label>
                </p>

                <p>
                    <button type="submit">Submit</button>
                </p>
            </form>
        </div>
    </body>
</html>
