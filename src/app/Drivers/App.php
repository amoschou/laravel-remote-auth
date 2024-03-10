<?php

namespace AMoschou\RemoteAuth\App\Drivers;

use AMoschou\RemoteAuth\App\Models\User;
use Illuminate\Support\Facades\Auth;
use stdClass;

class App extends BaseDriver
{
    public $isRemote = false;

    /**
     * Determine whether the given username and password can authenticate using
     * this driver.
     */
    public function validate(string $username, string $password): bool
    {
        return Auth::validate([
            'username' => $username,
            'password' => $password,
        ]);
    }

    /**
     * Retrieve the profile that was previously saved into the app’s database.
     */
    protected function record(string $username, string $password): ?stdClass
    {
        return User::find($username)->record();
    }
}
