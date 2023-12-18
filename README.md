# README

Authentication provided by a remote server for Laravel apps.

## User registration and processes

This package assumes that the only users who are authorised to use the app are already registered in the remote server. This is true if your organisation already uses LDAP, for example. There is no need for a separate user registration process. Likewise, there are no ‘reset password’ or ‘change username/name/email’ processes. To achieve these types of processes, users need to use your organisation’s existing procedures.

## Drivers

This package provides two drivers:
* LDAP (This requires the LDAP extension in PHP)
* DB

The LDAP driver is appropriate if your organisation already uses an LDAP server. Users submit their username and password, and these are used to attempt authentication to the LDAP server. The app receives the result of this attempt as success or fail. If successful, a second request is also made to receive information about the user which includes their name, email address and ID number, and this information is stored in the app’s database. If this information has been changed on the LDAP server since the previous login, updates will be reflected in the app. The user’s hashed password is also stored in the database. Usually, this would not be used, but it is necessary for the DB driver to work.

The DB driver is designed to be used as a backup, in case the LDAP (or other external) server is unavailable. Users submit their username and password, and these are checked in the app’s database like a traditional username/password system. As this requires the hashed password to exist in the database, a user must have logged in at some point previously using the external server driver. As the external server is unavailable at this login session, any changes which might exist about the user (name, email, etc) on the external server since the preivous successful login attempt will not be reflected in the app.

## Getting started

1. For a newish Laravel application, install by running:
   ```
   composer require amoschou/laravel-remote-auth
   ```

   If you are installing into an older Laravel application, first, carefully check the migrations and any files that would be modified before continuing.

2. Before running any migration, it is recommended to remove the migration and model files, unless your app has already made use of these files:
   ```
   rm app/Models/User.php
   rm database/factories/UserFactory.php
   rm database/migrations/2014_10_12_000000_create_users_table.php
   rm database/migrations/2014_10_12_100000_create_password_reset_tokens_table.php
   ```

3. Create a new class at `app/Models/User.php` which extends `AMoschou\RemoteAuth\App\Models\User`:
   ```
   <?php

   namespace App\Models;

   use AMoschou\RemoteAuth\App\Models\User as RemoteAuthUser;

   class User extends RemoteAuthUser
   {
       //
   }
   ```

   Alternatively, if you will not be extending the `User` model, then in `config/auth.php`, you can reference the model from the package directly at the key `providers.users.model`:
   ```
   // old:
   // 'model' => App\Models\User::class

   // new:
   'model' => AMoschou\RemoteAuth\App\Models\User::class
   ```

4. Publish the config file `config/remote_auth.php`, and modify its values in this file or in `.env`:
   ```
   php artisan vendor:publish --tag=remote-auth-config
   ```

4. Migrate the database to create tables `remote_auth_users` and `remote_auth_memberships`:
   ```
   php artisan migrate
   ```

5. Create a view to show the login form. You can publish then edit the view from the package:
   ```
   php artisan vendor:publish --tag=remote-auth-views
   ```

   Or build your own. It a username, a password, a checkbox (called `username`, `password` and `remember`) and the CSRF:
   ```
   <form method="post" action="{{ route('login.post') }}">
       @csrf
       <input type="text" name="username" />
       <input type="password" name="password" />
       <input type="checkbox" name="remember" />
       <button type="submit">Submit</button>
   </form>
   ```

6. Typically, you will need a route to show this form, and route to receive the login request. This package does not provide any routes, you must define them yourself in `routes/web.php`. In this setup, we also need a home route to redirect requests to after logging in, and a log out route. You will need to use the `authenticate` and `logout` methods on `LoginController`, but generally, you are free to design your routes and views as you like.
   ```
   use AMoschou\RemoteAuth\App\Http\Controllers\LoginController;

   Route::middleware('guest')->group(function () {
       Route::get('/login', function () { return view('vendor.laravel-remote-auth.login'); })->name('login');
       Route::post('/login', [LoginController::class, 'authenticate'])->name('login.post');
   });

   Route::middleware('auth')->group(function () {
       Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
       Route::get('/home', function () { return view('home'); })->name('home');
   });
   ```

### Other drivers

To install a new driver, create a new class that extends `AMoschou\RemoteAuth\App\Drivers\Driver`. It will need to implement the following:
  - `public function validate($username, $password): bool`
  - `protected function user($username, $password): array`
```
<?php

use AMoschou\RemoteAuth\App\Drivers\Driver;

class NewAuth extends Driver
{
    public function validate($username, $password): bool
    {
        // Returns true for valid credentials or false if invalid.
    }

    protected function user($username, $password): array
    {
        // Returns an array of the following form:
        // [
        //     'username' => $username,
        //     'last_name' => ...,
        //     'first_name' => ...,
        //     'display_name' => ...,
        //     'id' => ...,
        //     'email' => ...,
        //     'groups' => [
        //         ...
        //     ],
        // ]
        // where values are strings, except groups which is an array of strings that represent all groups that the user is a member of.
    }
}
```

Edit `conf/remote_auth.php`, to reference the new driver including the class you just set up and any other configuration details that it would require:
```
'drivers' => [
    'new-auth' => NewAuth::class,
    'ldap' => Ldap::class,
    'db' => Db::class,
],

'settings' => [

    'ldap' => [ ... ],

    'new-auth' => [
        ...
    ],

]
```
