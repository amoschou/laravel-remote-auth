<?php

use AMoschou\RemoteAuth\App\Drivers\App;
use AMoschou\RemoteAuth\App\Drivers\Csv;
use AMoschou\RemoteAuth\App\Drivers\Ldap;
use AMoschou\RemoteAuth\App\Providers\ServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Providers and Settings
    |--------------------------------------------------------------------------
    |
    | Authentication is first attempted using the first provider in the list.
    | If this fails, the next is tried and so on until one is successful.
    | Any setting that may be required can be configured below as well.
    |
    | The 'profile_map' setting is required by all providers except 'app'. It is
    | of the form 'appkey' => 'providerkey' and shows how providers construct
    | user profiles. Customise this as needed, only 'username' and 'email' are required.
    |
    | Settings for LDAP drivers:
    |   connection:   A full LDAP URI of the form ldap://hostname:port or
    |                 ldaps://hostname:port for SSL encryption
    |   options:      An array of the form option => value (See a list of
    |                 available options at
                      https://www.php.net/manual/en/function.ldap-set-option.php)
    |   domain:       This could be the domain name part of a UPN, in which
    |                 case usernames will be bound as 'username@domain'.
    |                 Alternatively, this could be the RDN where users are
    |                 located, in which case usernames will be bound as
    |                 'uid=username,ou=...'.
    |   organisation: The search directory DN.
    |
    */

    'providers' => [
        'ldap' => Ldap::class,
        'csv' => Csv::class,
        'app' => App::class,
    ],

    'settings' => [

        'ldap' => [
            'connection' => env('REMOTE_AUTH_LDAP', 'ldap://ldap.forumsys.com'),
            'options' => [
                LDAP_OPT_PROTOCOL_VERSION => 3,
            ],
            'domain' => env('REMOTE_AUTH_LDAP_DOMAIN', 'DC=example,DC=com'),
            'search' => env('REMOTE_AUTH_LDAP_SEARCH', 'DC=example,DC=com'),
            // 'dns' => env('REMOTE_AUTH_LDAP_DNS', 'subdomain.example.com'),
            'profile_map' => [
                'username' => 'uid',
                'last_name' => 'sn',
                'display_name' => 'cn',
                'phone' => 'telephonenumber',
                'email' => 'mail',
                'groups' => 'memberof',
            ],
        ],

        'csv' => [
            'connection' => env('REMOTE_AUTH_CSV', '/path/to/csv'),
            'header_offset' => 0,
            'header' => ['username', 'email', 'password', 'profile'],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Redirection Routes
    |--------------------------------------------------------------------------
    |
    | Here, you can identify the fallback route, which will be navigated to
    | when the intended destination before being intercepted during login
    | is not available, and the redirection route after logging out.
    |
    */

    'redirect' => [
        'after_login_fallback' => ServiceProvider::HOME,
        'after_logout' => '/',
    ],

];
