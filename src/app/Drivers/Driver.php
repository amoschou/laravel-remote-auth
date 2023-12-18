<?php

namespace AMoschou\RemoteAuth\App\Drivers;

abstract class Driver
{
    // yes
    abstract public function validate($username, $password): bool;

    // yes
    abstract protected function user($username, $password): array;

    // yes
    public function getUser($username, $password)
    {
        return $this->validate($username, $password)
            ? $this->user()
            : null;
    }

    // yes, used in Ldap.php
    public function dnsRecordExists(): bool
    {
        $domain = config("remote_auth.drivers.{$this->driver}.dns");

        return checkdnsrr($domain, 'A');
    }

    public static function select($driver = null)
    {
        return is_null($driver) ? self::choose() : self::as($driver);
    }

    public static function use()
    {
        $driver = self::choose();

        return self::as($driver);
    }

    private static function choose(): string|null
    {
        $list = self::getOrderedList();

        for ($i = 0; $i < count($list); $i++) {
            $driver = $list[$i];

            $dns = self::as($driver)->dnsRecordExists();

            if ($dns) {
                $i = count($list);
            }
        }

        return $dns ? $driver : null;
    }

    private static function as($driver)
    {
        $driverClass = config("remote_auth.drivers.{$driver}.class");

        $auth = new $driverClass;

        return $auth;
    }
}
