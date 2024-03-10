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
            <p>Hello {{ $username }},</p>
            <p>We are emailing you a link.</p>
            <p>To continue logging in, please check your email for further instructions.</p>
            <p>This link expires in {{ $minutes }} minutes. Do not share it with anyone else.</p>
            <p>Regards,<br>{{ config('app.name') }}</p>
        </div>
    </body>
</html>
