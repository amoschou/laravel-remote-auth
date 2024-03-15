<x-mail::message>
# {{ $app }}

Hello {{ $name }},

To continue logging in, please click this link:

<x-mail::button :url="$link">Log in</x-mail::button>

If clicking link does not work, copy and paste the following:

**{{ $link }}**

This link expires in {{ $expire }} minutes. Do not share it with anyone else.

Regards,\
{{ $app }}
</x-mail::message>
