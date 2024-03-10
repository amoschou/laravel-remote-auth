<?php

namespace AMoschou\RemoteAuth;

class Settings
{
    public static $registersLogInOutRoutes = true;

    public static function ignoreLogInOutRoutes()
    {
        static::$registersLogInOutRoutes = false;

        return new static;
    }
}