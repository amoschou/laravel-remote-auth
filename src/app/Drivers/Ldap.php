<?php

namespace AMoschou\RemoteAuth\App\Drivers;

use AMoschou\RemoteAuth\App\Support\Ldap as LdapSupport;

class Ldap extends Driver
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
        if (! $this->dnsRecordExists()) {
            return false;
        }

        return ! (new LdapSupport)
            ->credentials($username, $password)
            ->unbind()
            ->hasInvalidCredentials();
    }

    /**
     * Get a newly synced set of details about the user for the given username
     * and password.
     * 
     * @param  string  $username
     * @param  string  $password
     * 
     * @return array<string, string>
     */
    protected function user($username, $password): array
    {
        return (new LdapSupport)
            ->credentials($username, $password)
            ->searchUsername()
            ->unbind()
            ->getAboutUser();
    }
}
