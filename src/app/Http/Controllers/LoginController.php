<?php

namespace AMoschou\RemoteAuth\App\Http\Controllers;

use AMoschou\RemoteAuth\App\Drivers\Driver;
use AMoschou\RemoteAuth\App\Rules\RemoteAuthRule;
use AMoschou\RemoteAuth\App\Support\LdapAuth;
use AMoschou\RemoteAuth\App\Models\User;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, DB, Hash, Validator};
use Illuminate\Support\{Arr, Str};

class LoginController extends Controller
{
    /**
     * The validation rule that validates a given username and password against
     * the remote server. This is referenced here, once a driver has been
     * selected, as it is used at multiple parts of the login process. 
     */
    private $remoteAuthRule;

    /**
     * Get the validation rules that apply to the authentication attempt.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
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
        $validated = $this->validateAuthenticationRequest($request);

        $user = $this->syncUser($validated['username'], $validated['password']);

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

    /**
     * Validate the authentication attempt. The username and password are first
     * validated according to the rules array. They are then used to select a
     * driver and are then validated further against the remote server.
     */
    private function validateAuthenticationRequest(Request $request): array
    {
        $keys = ['username', 'password'];

        $credValidator = Validator::make(
            Arr::only($request->all(), $keys),
            Arr::only($this->rules(), $keys)
        );

        $credentials = $credValidator->validated();

        $this->remoteAuthRule = (new RemoteAuthRule)->setDriver($credentials['username'], $credentials['password']);

        $validator = Validator::make(
            Arr::add($request->all(), '_remote_auth_rule', null),
            $this->rules()
        );

        return $validator->validated();
    }

    /**
     * Sync the user’s details from the remote server and get the user model.
     */
    private function syncUser($username, $password): User
    {
        $aboutUser = (new ($this->remoteAuthRule->getDriver()))->getUser($username, $password);

        return DB::transaction(function () use ($aboutUser) {
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
