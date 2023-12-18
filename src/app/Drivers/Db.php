<?php

namespace AMoschou\RemoteAuth\App\Drivers;

use AMoschou\RemoteAuth\App\Models\User;
use Illuminate\Support\Facades\Auth;

final class Db extends Driver
{
    public function validate($username, $password): bool
    {
        return Auth::attempt([
            'username' => $username,
            'password' => $password,
        ]);
    }

    protected function user($username, $password = null): array
    {
        return User::find($username)->getAboutUser();
    }
}
