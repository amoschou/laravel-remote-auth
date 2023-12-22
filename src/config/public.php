<?php

use AMoschou\RemoteAuth\App\Drivers\Db;
use AMoschou\RemoteAuth\App\Drivers\Ldap;
use App\Providers\RouteServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Drivers and Settings
    |--------------------------------------------------------------------------
    |
    | Authentication is first attempted using the first driver in the list. In
    | case this fails, the next is tried and so on until one is successful.
    | Any setting that a driver may require can be configured as well.
    |
    */

    'drivers' => [
        'ldap' => Ldap::class,
        'db' => Db::class,
    ],

    'settings' => [

        'ldap' => [
            'connection' => env('REMOTE_AUTH_LDAP', 'ldaps://ad.subdomain.example.com'),
            'domain' => env('REMOTE_AUTH_LDAP_DOMAIN', 'subdomain.example.com'),
            'organisation' => env('REMOTE_AUTH_LDAP_ORGANISATION', 'OU=Organisation,DC=subdomain,DC=example,DC=com'),
            'dns' => env('REMOTE_AUTH_LDAP_DNS', 'subdomain.example.com'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Redirection Routes
    |--------------------------------------------------------------------------
    |
    | Here, you can identify the fallback route, which will be navigated to if
    | the intended destination before being intercepted to log in is not
    | available, and the redirection route after logging out.
    |
    */

    'redirect' => [
        'after_login_fallback' => RouteServiceProvider::HOME,
        'after_logout' => '/',
    ],

];
