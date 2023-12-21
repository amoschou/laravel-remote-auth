<?php

namespace AMoschou\RemoteAuth\App\Drivers;

use AMoschou\RemoteAuth\App\Models\User;
use Illuminate\Support\Facades\Auth;

final class Db extends Driver
{
    /**
     * Decide whether the username and password are valid.
     * 
     * @param  string  $username
     * @param  string  $password
     * 
     * @return bool
     */
    public function validate($username, $password): bool
    {
        return Auth::attempt([
            'username' => $username,
            'password' => $password,
        ]);
    }

    /**
     * Find the user with the given username and password.
     * 
     * @param  string  $username
     * @param  string|null  $password
     * 
     * @return array<string, mixed>
     */
    protected function user($username, $password = null): array
    {
        return User::find($username)->getAboutUser();
    }
}
