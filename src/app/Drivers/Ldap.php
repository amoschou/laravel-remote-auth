<?php

namespace AMoschou\RemoteAuth\App\Drivers;

use AMoschou\RemoteAuth\App\Support\Ldap as LdapSupport;

class Ldap extends Driver
{
    public $driver = 'ldap';

    public function validate($username, $password): bool
    {
        if (! $this->dnsRecordExists()) {
            return false;
        }

        $ldapAuth = new LdapSupport;

        $ldapAuth->credentials($username, $password)->unbind();

        return ! $ldapAuth->hasInvalidCredentials();
    }

    public function getUser($username, $password): array
    {
        $ldapAuth = new LdapSupport;
    
        $aboutUser = $ldapAuth->credentials($username, $password)
            ->searchUsername()->unbind()
            ->getAboutUser();
    
        return $aboutUser;
    }
}
