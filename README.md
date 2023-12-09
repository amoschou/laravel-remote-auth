# README

Authentication provided by a remote server for Laravel apps.

## Getting started

1. For a newish Laravel application, install by running:
   ```
   composer require amoschou/laravel-remote-auth
   ```

   If you are installing into an older Laravel application, first, carefully check the migrations and any files that would be modified before continuing.

2. Before running any migration, it is recommended to remove the migration and model files:
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
   // 'model' => App\Models\User::class // old
   'model' => AMoschou\RemoteAuth\App\Models\User::class // new
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

   Or build your own. It requires a hidden field, a username, a password, a checkbox (called `hidden`, `username`, `password` and `remember_me`) and the CSRF:
   ```
   <form method="post" action="{{ route('login.post') }}">
       @csrf
       <input type="hidden" name="hidden" />
       <input type="text" name="username" />
       <input type="password" name="password" />
       <input type="checkbox" name="remember_me" />
       <button type="submit">Submit</button>
   </form>
   ```

6. Typically, you will need a route to show this form, and route to receive the login request. In this setup, we also need a dashboard route to redirect requests to after logging in, and a log out route. You will need to use the `authenticate` and `logout` methods on `LoginController`, but generally, you are free to design your routes and views as you like.
   ```
   use AMoschou\RemoteAuth\App\Http\Controllers\LoginController;

   Route::get('/login', function () { return view('vendor.laravel-remote-auth.login'); });
   Route::post('/login', [LoginController::class, 'authenticate'])->name('login.post');
   Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
   Route::get('/dashboard', function () { return view('dashboard'); })->middleware('auth')->name('dashboard');
   ```
## Drivers

### LDAP

This package comes with one driver for `LDAP` authentication. Internally, PHP uses its [LDAP functions](https://www.php.net/manual/en/ref.ldap.php) to achieve this.

### Other drivers

To install a new driver, create a new class that extends `AMoschou\RemoteAuth\App\Drivers\Driver`. It will need to implement the following functions `validate($username, $password)` and `getUser($username, $password)` and set a key:
```
<?php

use AMoschou\RemoteAuth\App\Drivers\Driver;

class NewAuthDriver extends Driver
{
    public $driver = 'new-auth';

    public function validate($username, $password): bool
    {
        // Returns true for valid credentials or false if invalid.
    }

    public function getUser($username, $password): array
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
'default' => 'ldap',

'preferences' => [
    'new-auth',
],

'drivers' => [
    'ldap' => [
        ...
    ],

    'new-auth' => [
        ...
        'class' => NewAuthDriver::class,
    ],

]
```
