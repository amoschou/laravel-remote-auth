<?php

namespace AMoschou\RemoteAuth\App\Drivers;

abstract class Driver
{
    public $driver;

    abstract public function validate($username, $password): bool;

    abstract public function getUser($username, $password): array;

    public $usesHash = false;

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

    public static function getOrderedList(): array
    {
        $default = [config('remote_auth.default')];

        $preferences = config('remote_auth.preferences');

        return array_merge($default, $preferences);
    }
}
