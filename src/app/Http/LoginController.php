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
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
            'remember_me' => 'sometimes|in:on',
            'hidden' => new RemoteAuthRule,
        ]);

        $validator->validated();
    
        $validated = $validator->safe()->only('username', 'password', 'remember_me');

        // Get fresh details about the user. This will be used to update the
        // record in the the users table. Remember, there is no need to record
        // anything to do with the password in the database because this is not
        // where credentials are checked.

        $this->login($validated['username'], $validated['password'], $validated['remember_me'] ?? false);

        $request->session()->regenerate();

        return redirect()->intended(config('remote_auth.redirect.after_login_fallback'));
    }

    private function login($username, $password, $rememberMe)
    {
        $aboutUser = $this->getUser($username, $password);

        // Find the user in the database. If found, then update the record with
        // fresh information. But if not found, then insert the record. We also
        // insert into the memberships table.

        $user = DB::transaction(function () use ($aboutUser) {
            $username = $aboutUser['username'];

            $groups = $aboutUser['groups'];

            $freshRecord = [
                'username' => $username,
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
            
            $user = User::firstOrCreate([
                'username' => $username
            ], $freshRecord);
    
            $user->update($freshRecord);
    
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

    private function getUser($username, $password) {
        return Driver::use()->getUser($username, $password);
    }
}
