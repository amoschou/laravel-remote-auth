<?php

namespace AMoschou\RemoteAuth\App\Drivers;

use AMoschou\RemoteAuth\App\Models\User;

class Db extends Driver
{
    public $driver = 'db';

    public function validate($username, $password): bool
    {
        return Auth::attempt([
            'username' => $username,
            'password' => $password,
        ]);
    }

    public function getUser($username, $password = null): array
    {
        return User::find($username)->getAboutUser();
    }
}
