<?php

namespace AMoschou\RemoteAuth\App\Support;

use AMoschou\RemoteAuth\App\Drivers\Ldap as LdapDriver;

class Ldap
{
    private static $justthese = [
        'samaccountname',
        'sn',
        'givenname',
        'displayname',
        'telephonenumber',
        'mail',
        'memberof',
    ];

    private $connection;

    private $username;

    private $userPrincipalName;

    private $invalidCredentials;

    public $aboutUser;

    private function setUsername($username)
    {
        $this->username = $username;

        $driverKey = LdapDriver::key();

        $domain = config("remote_auth.settings.{$driverKey}.domain");

        $this->userPrincipalName = "{$username}@{$domain}";

        return $this;
    }

    private function getConnection()
    {
        if (is_null($this->connection)) {
            $driverKey = LdapDriver::key();

            $this->connection = ldap_connect(config("remote_auth.settings.{$driverKey}.connection"));

            $this->invalidCredentials = false;
        }

        return $this->connection;
    }

    private function getUserPrincipalName()
    {
        return $this->userPrincipalName;
    }

    private function getUserName()
    {
        return $this->username;
    }

    private function bind($password)
    {
        try {
            $this->bind = ldap_bind(
                $this->getConnection(),
                $this->getUserPrincipalName(),
                $password
            );

            $this->invalidCredentials = false;
        } catch (\Throwable $t) {
            $this->invalidCredentials = true;
        }

        return $this;
    }

    public function credentials($username, $password)
    {
        return $this->setUsername($username)->bind($password);
    }

    public function unbind()
    {
        ldap_unbind($this->getConnection());

        return $this;
    }

    public function hasInvalidCredentials()
    {
        return $this->invalidCredentials;
    }

    public function searchUsername()
    {
        $username = $this->getUsername();

        $filter = "(samaccountname={$username})";

        $driverKey = LdapDriver::key();

        $search = ldap_search(
            $this->getConnection(),
            config("remote_auth.settings.{$driverKey}.organisation"),
            $filter,
            self::$justthese
        );

        $entries = ldap_get_entries(
            $this->getConnection(),
            $search
        );

        $unparsedResult = array_filter(
            $entries[0],
            fn ($key) => in_array($key, self::$justthese),
            ARRAY_FILTER_USE_KEY
        );

        $easyResults = [];

        foreach ($unparsedResult as $key => $val) {
            if (is_array($val)) {
                if ($val['count'] === 1) {
                    $easyResults[$key] = $val[0];
                } else {
                    unset($val['count']);

                    $easyResults[$key] = $val;
                }
            } else {
                $easyResults[$key] = $val;
            }
        }

        $groups = [];

        foreach ($easyResults['memberof'] as $group) {
            $check = ldap_explode_dn($group, 1)[0];

            $groups[] = $check === false ? $group : $check;
        }

        $this->aboutUser = [
            'username' => $easyResults['samaccountname'],
            'last_name' => $easyResults['sn'],
            'first_name' => $easyResults['givenname'],
            'display_name' => $easyResults['displayname'],
            'id' => $easyResults['telephonenumber'] ?? null,
            'email' => $easyResults['mail'] ?? null,
            'groups' => $groups,
        ];

        return $this;
    }

    public function getAboutUser()
    {
        return $this->aboutUser;
    }
}
