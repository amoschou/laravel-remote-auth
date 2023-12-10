<?php

use AMoschou\RemoteAuth\App\Drivers\{
    Db,
    Ldap,
};
use App\Providers\RouteServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Authentication Driver and Preferences
    |--------------------------------------------------------------------------
    |
    | In the first instance, authentication is attempted using the default
    | driver. In case this fails, each of the backup drivers as listed in
    | the preferences will be tried in order until one is successful.
    |
    */

    'default' => 'ldap',

    'preferences' => [
        'db',
    ],

    'drivers' => [

        'db' => [
            'class' => Db::class,
        ],

        'ldap' => [
            'connection' => env('REMOTE_AUTH_LDAP', 'ldaps://ad.subdomain.example.com'),
            'domain' => env('REMOTE_AUTH_LDAP_DOMAIN', 'subdomain.example.com'),
            'organisation' => env('REMOTE_AUTH_LDAP_ORGANISATION', 'OU=Organisation,DC=subdomain,DC=example,DC=com'),
            'dns' => env('REMOTE_AUTH_LDAP_DNS', 'subdomain.example.com'),
            'class' => Ldap::class,
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
