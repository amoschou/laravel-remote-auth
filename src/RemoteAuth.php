<?php

namespace AMoschou\RemoteAuth;

use AMoschou\RemoteAuth\App\Models\User;

class RemoteAuth
{
    private $driver;

    private $data = [];

    public function __construct($key)
    {
        $class = config("remote_auth.providers.{$key}");

        $this->driver = new $class($key);
    }

    public static function useProvider($key): RemoteAuth
    {
        return new RemoteAuth($key);
    }

    public static function findProvider($username, $password, $returnAsKey = false): ?RemoteAuth
    {
        $providers = config('remote_auth.providers', []);

        $successfulKey = null;

        $successfulProvider = null;

        $provider = null;

        foreach ($providers as $key => $driver) {
            if (is_null($successfulKey)) {
                $provider = RemoteAuth::useProvider($key)->validate($username, $password);

                if ($provider->hasValidCredentials()) {
                    $successfulKey = $key;

                    $successfulProvider = $provider;
                }
            }
        }

        return $returnAsKey ? $successfulKey : $successfulProvider;
    }

    public function setUsername($username)
    {
        if (
            is_null($this->data['username'] ?? null)
            || ($this->data['username'] !== $username)
        ) {
            $this->data['username'] = $username;

            $this->validateAs(false);
        }

        return $this;
    }

    public function tryPassword($password)
    {
        $validateResult = false;

        if (
            ! is_null($this->data['username'] ?? null)
            && ! is_null($password)
        ) {
            $validateResult = $this->driver->validate($this->data['username'], $password);
        }

        return $this->validateAs($validateResult);
    }

    private function validateAs($bool)
    {
        $this->data['validCredentials'] = ($bool === true) ? true : false;

        return $this;
    }

    public function validate($username, $password)
    {
        return $this->setUsername($username)->tryPassword($password);
    }

    private function hasValidCredentials()
    {
        return $this->data['validCredentials'] ?? false;
    }

    public function getDriver()
    {
        return $this->driver;
    }
}
