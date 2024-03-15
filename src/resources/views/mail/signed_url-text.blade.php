{{ strtoupper(config('app.name')) }}

Hello {{ $name }},

To continue logging in, please visit this link:

{{ $link }}

This link expires in {{ $expire }} minutes. Do not share it with anyone else.

Regards,\
{{ config('app.name') }}
