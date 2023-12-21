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
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    private $remoteAuthRule;

    private function rules()
    {
        return [
            'username' => 'required',
            'password' => 'required',
            '_remote_auth_rule' => $this->remoteAuthRule,
        ];
    }

    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $validated = $this->validate($request);

        $user = $this->prepareUser($validated['username'], $validated['password']);

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(config('remote_auth.redirect.after_login_fallback'));
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

    private function validate(Request $request): array
    {
        $upOnly = ['username', 'password'];

        $upValidator = Validator::make(Arr::only($request->all(), $upOnly), Arr::only($this->rules(), $upOnly));

        $upValidated = $upValidator->validated();

        $this->remoteAuthRule = (new RemoteAuthRule)->setDriver($upValidated['username'], $upValidated['password']);

        $validator = Validator::make(Arr::add($request->all(), '_remote_auth_rule', null), $this->rules());

        return $validator->validated();
    }

    private function prepareUser($username, $password): User
    {
        return DB::transaction(function () {
            $aboutUser = $this->remoteAuthRule->getDriver()->getUser($username, $password);

            $aboutUser['username'] = strtolower($aboutUser['username']);

            $user = User::findOr($aboutUser['username'], function () use ($aboutUser) {
                $user = new User;

                $user->username = $aboutUser['username'];
                $user->id = $aboutUser['id'];
                $user->display_name = $aboutUser['display_name'];
                $user->email = $aboutUser['email'];
                $user->last_name = $aboutUser['last_name'];
                $user->first_name = $aboutUser['first_name'];

                $user->save();

                return $user;
            });
    
            $user->id = $aboutUser['id'];
            $user->display_name = $aboutUser['display_name'];
            $user->email = $aboutUser['email'];
            $user->last_name = $aboutUser['last_name'];
            $user->first_name = $aboutUser['first_name'];

            if ($user->isDirty()) {
                $user->save();
            }
    
            DB::table('remote_auth_memberships')
                ->where('username', $aboutUser['username'])
                ->whereNotIn('group', $aboutUser['groups'])
                ->delete();
    
            $memberships = [];

            foreach ($aboutUser['groups'] as $group) {
                $memberships[] = [
                    'username' => $aboutUser['username'],
                    'group' => $group,
                ];
            }

            DB::table('remote_auth_memberships')->insertOrIgnore($memberships);

            return $user;
        });
    }
}
