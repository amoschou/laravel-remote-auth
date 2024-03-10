# laravel-remote-auth
Authentication provided by remote servers for Laravel apps.
# Laravel remote auth

* Edit `config/auth` and/or update App\Models\User class
* Delete unneeded migration files

## Installation

### Composer and Artisan

Install the package into a newish Laravel app by doing:
```sh
composer require amoschou/laravel-remote-auth
```

Optionally publish the config and view files by doing:
```sh
php artisan vendor:publish --tag=remote-auth-config
php artisan vendor:publish --tag=remote-auth-views
```
T
### Configuration

You will amost always want to publish the config file. It will be made available at `config/remote-auth.php`. 

For details about this file, see *Configuration* below.

### Views

The package makes available the following views:
* A simple login form
* Several views related to emails

If published, they will be made available in `resources/views/vendor/remote-auth`. They can be customised from here.

### User model

The package provides a new user class. You will need to reference it in one of two ways:

Either clear out `app\Models\User.php` and replace it with something like:
```php
<?php

namespace App\Models;

use AMoschou\RemoteAuth\App\Models\User as BaseUser;

class User extends BaseUser {}
```

Or update `config/auth.php` so that the key `providers.users.model` references `AMoschou\RemoteAuth\App\Models\User.php`.

### Migrations

This package provides new migrations to handle users. If you have not yet run migrations in your app, delete the following migration files:
* `2014_10_12_000000_create_users_table.php`
* `2014_10_12_100000_create_password_reset_tokens_table.php`

Then do:
```sh
php artisan migrate
```

### Emails

This package sends emails using the `Mail` facade. Please configure your app for emails.

### Routes

This package provides the following routes:
* `GET /auth/login`
* `POST /auth/login`
* `GET /auth/logout`
* `GET /auth/sign/{username}`

The first three can be disabled by including `Settings::ignoreLogInOutRoutes();` in the `register()` method of a service provider. This gives you a chance to customise the login and logout routes.

The fourth route is the signed URL that is sent to users via email when multi factor authentication is called for.

## Drivers

This package provides three drivers for remote authentication:
* LDAP
* CSV
* App

Traditional username/password authentication is assumed. The LDAP driver valiates credentials using an external LDAP server. The CSV driver validates credentials using a local CSV file. The App driver validates credentials using the app’s database. The CSV and App drivers are not strictly *remote* but they are useful in many cases. The App driver is also used as a backup method in case the remote server is unavailable.

## Configuration

The configuration file, when published, is found at `config/remote_auth.php`.

It contains a list of providers, where each provider is powered by a driver. Customise this according to the requirements for your app:
```php
'providers' => [
    'ldap-1' => Ldap::class,
    'ldap-2' => Ldap::class,
    'csv-1' => Csv::class,
    'app' => App::class,
],
```

The order of providers in this list is important. When a user inputs their username and password into the app’s login form, these credentials are first checked against the first provider. If the username and password are valid, the user is logged in and authenticated to the app. If the first provider can not validate the credentials, then the next provider is tried, and so on, until one is successful. When the credentials are successfully validated, the user details (username, email, hashed password and profile) are stored in the app’s database.

The `App` driver would normally be listed last. This gives a chance for a successful login attempt in case the remote server is unavailable. There is a security concern as well, which is why the app driver will compulsorily ask for multi-factor authentication. The app will send an email to the user’s stored email address with a link.

Settings can be configured for the drivers of each provider, except the `App` driver.

```php
'settings' => [
    'ldap-1' => [
        'connection' => ...,
        'options' => ...,
        'domain' => ...,
        'search' => ...,
        'profile_map' => [
            'username' => 'uid',
            'last_name' => 'sn',
            'display_name' => 'cn',
            'phone' => 'telephonenumber',
            'email' => 'mail',
            'groups' => 'memberof',
        ],
    ],
    'csv-1' => [
        'connection' => ...,
        'header_offset' => ...,
        'header' => ...,
    ],
```

For LDAP settings: The `connection` is an LDAP URL of the form `ldap://hostname:port` or `ldaps://hostname:port`. `options` is an array of options and values (See the PHP function `ldap_set_option` for details). `domain` is the directory where users are found, together with the `username` it is used to build the DN of the user. `search` is the base DN for the directory that will be searched when collecting user profile values. For LDAP, the `profile_map` is an array that maps the keys of the user profile according to the app’s requirements to the correspondind keys of the LDAP query.

For CSV settings: The `connection` is the absolute path to the CSV file. `header_offset` is an integer representing the header offset in the CSV file, the default is `null` if it is not set. `header` is an array of the CSV column headers. This setting is used regardless of the presence of any headers in the CSV file.
