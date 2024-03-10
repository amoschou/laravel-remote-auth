<?php

namespace AMoschou\RemoteAuth\App\Drivers;

use AMoschou\RemoteAuth\App\Support\Ldap as Support;
use stdClass;

class Ldap extends BaseDriver
{
    /**
     * Determine whether the given username and password can authenticate using
     * this driver.
     */
    public function validate(string $username, string $password): bool
    {
        return $this->support()->validate($username, $password);
    }

    /**
     * Determine the profile for the given username and password, provided by
     * the remote server.
     */
    // public function profile(string $username, ?string $password): ?array
    // {
    //     return $this->support()->profile($username, $password);
    // }

    protected function record(string $username, string $password): ?stdClass
    {
        return $this->support()->record($username, $password);
    }

    /**
     * Helper functions.
     */
    private function support()
    {
        return Support::for($this->getKey());
    }
}
