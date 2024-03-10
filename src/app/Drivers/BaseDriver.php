<?php

namespace AMoschou\RemoteAuth\App\Drivers;

use AMoschou\RemoteAuth\App\Support\ReadsConfig;
use stdClass;

abstract class BaseDriver
{
    use ReadsConfig;

    public $isRemote = true;

    /**
     * The key that is used to identify the provider in the config file.
     */
    private string $key;

    /**
     * Create a new driver instance.
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * Determine whether the given username and password can authenticate using
     * this driver.
     */
    protected abstract function validate(string $username, string $password): bool;

    /**
     * Determine the profile for the given username and password. Remote
     * servers provide up-to-date information which then become synced,
     * but the DB driver retrieves only previously synced profiles.
     */
    // protected abstract function profile(string $username, ?string $password): ?array;
    
    /**
     * Pull the record of the user for the given username and password from the
     * remote server. This is an object that includes the username and email as
     * strings (and optionally a hashed password), and profile as an array. 
     */
    protected abstract function record(string $username, string $password): ?stdClass;

    public function getKey()
    {
        return $this->key;
    }

    public function getRecord($username, $password)
    {
        if (! $this->validate($username, $password)) {
            return null;
        }
        
        return $this->record($username, $password);
    }
}
