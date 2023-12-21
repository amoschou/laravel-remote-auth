<?php

namespace AMoschou\RemoteAuth\App\Drivers;

use AMoschou\RemoteAuth\App\Support\Ldap as LdapSupport;

class Ldap extends Driver
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
        if (! $this->dnsRecordExists()) {
            return false;
        }

        return ! (new LdapSupport)
            ->credentials($username, $password)
            ->unbind()
            ->hasInvalidCredentials();
    }

    /**
     * Find the user with the given username and password.
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
