<?php

namespace AMoschou\RemoteAuth\App\Drivers;

use AMoschou\RemoteAuth\App\Support\Ldap as LdapSupport;

class Ldap extends Driver
{
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

    protected function user($username, $password): array
    {
        return (new LdapSupport)
            ->credentials($username, $password)
            ->searchUsername()
            ->unbind()
            ->getAboutUser();
    }
}
