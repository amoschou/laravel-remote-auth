<?php

namespace AMoschou\RemoteAuth\App\Http\Controllers;

use AMoschou\RemoteAuth\App\Drivers\Driver;
use AMoschou\RemoteAuth\App\Rules\RemoteAuthRule;
use AMoschou\RemoteAuth\App\Support\LdapAuth;
use AMoschou\RemoteAuth\App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    private $driver;

    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        // RemoteAuthRule requires a validated username and password to work.
        // This is why we validate these separately first. The username and
        // password are required to determine the successful driver. Then,
        // we store the successful driver in $this->driver. Then we continue
        // with validation and log in.

        $formCredentials = [
            'username' => $request->input('username'),
            'password' => $request->input('password'),
        ];

        $formCredentialsValidator = Validator::make($formCredentials, [
            'username' => 'required',
            'password' => 'required',
        ]);

        $formCredentialsValidator->validated();

        $formCredentialsValidated = $formCredentialsValidator->safe()->only('username', 'password');

        $remoteAuthRule = new RemoteAuthRule;

        $this->driver = $remoteAuthRule->getDriver($formCredentialsValidated);

        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
            'remember_me' => 'sometimes|in:on',
            'hidden' => $remoteAuthRule,
        ]);

        $validator->validated();
    
        $validated = $validator->safe()->only('username', 'password', 'remember_me');

        // Get fresh details about the user. This will be used to update the
        // record in the the users table. Remember, there is no need to record
        // anything to do with the password in the database because this is not
        // where credentials are checked. However, the hashed password would
        // need to be stored to make use of the 'db' driver.

        $this->login(
            $validated['username'],
            $validated['password'],
            $validated['remember_me'] ?? false
        );

        $request->session()->regenerate();

        return redirect()->intended(config('remote_auth.redirect.after_login_fallback'));
    }

    private function login($username, $password, $rememberMe)
    {
        $aboutUser = $this->getUser($username, $password);

        $hash = Hash::make($password);

        $user = DB::transaction(function () use ($aboutUser, $hash) {
            $username = strtolower($aboutUser['username']);

            $groups = $aboutUser['groups'];

            $freshRecord = [
                'id' => $aboutUser['id'],
                'display_name' => $aboutUser['display_name'],
                'email' => $aboutUser['email'],
                'last_name' => $aboutUser['last_name'],
                'first_name' => $aboutUser['first_name'],
            ];

            $memberships = [];

            foreach ($groups as $group) {
                $memberships[] = [
                    'username' => $username,
                    'group' => $group,
                ];
            }

            $user = User::findOr($username, function () use ($username, $freshRecord) {
                $user = new User;

                $user->username = $username;
                $user->id = $freshRecord['id'];
                $user->display_name = $freshRecord['display_name'];
                $user->email = $freshRecord['email'];
                $user->last_name = $freshRecord['last_name'];
                $user->first_name = $freshRecord['first_name'];

                $user->save();

                return $user;
            });
    
            $user->id = $freshRecord['id'];
            $user->display_name = $freshRecord['display_name'];
            $user->email = $freshRecord['email'];
            $user->last_name = $freshRecord['last_name'];
            $user->first_name = $freshRecord['first_name'];

            if ($user->isDirty()) {
                $user->save();
            }
    
            DB::table('remote_auth_memberships')
                ->where('username', $username)
                ->whereNotIn('group', $groups)
                ->delete();
    
            DB::table('remote_auth_memberships')->insertOrIgnore($memberships);

            return $user;
        });

        Auth::login($user, $rememberMe);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
    
        $request->session()->invalidate();
    
        $request->session()->regenerateToken();
    
        return redirect(config('remote_auth.redirect.after_logout'));
    }

    private function getUser($username, $password)
    {
        $drivers = Driver::getOrderedList();

        $successfulResult = null;

        foreach($drivers as $driver) {
            try {
                if (is_null($successfulResult)) {
                    $successfulResult = Driver::select($driver)->getUser($username, $password);
                }
            } catch (\Throwable $t) {}
        }

        return $successfulResult;
    }
}
