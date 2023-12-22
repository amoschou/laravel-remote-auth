<?php

namespace AMoschou\RemoteAuth\App\Drivers;

use AMoschou\RemoteAuth\App\Models\User;
use Illuminate\Support\Facades\Auth;

final class Db extends Driver
{
    /**
     * Determine whether the username and password can authenticate against
     * this driver.
     * 
     * @param  string  $username
     * @param  string  $password
     * 
     * @return bool
     */
    public function attempt($username, $password): bool
    {
        return Auth::attempt([
            'username' => $username,
            'password' => $password,
        ]);
    }

    /**
     * Get a newly synced set of details about the user for the given username
     * and password.
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
