<?php

namespace AMoschou\RemoteAuth\App\Drivers;

abstract class Driver
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
    abstract public function attempt($username, $password): bool;

    /**
     * Get a newly synced set of details about the user for the given username
     * and password.
     * 
     * @param  string  $username
     * @param  string|null  $password
     * 
     * @return array<string, mixed>
     */
    abstract protected function user($username, $password): array;

    private function key()
    {
        return array_flip(config('remote_auth.drivers'))[static::class];
    }

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
        $domain = config("remote_auth.settings.{$this->key()}.dns");

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
        $driverClass = config("remote_auth.settings.{$this->key()}.class");

        $auth = new $driverClass;

        return $auth;
    }
}
