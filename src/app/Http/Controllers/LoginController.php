<?php

namespace AMoschou\RemoteAuth\App\Http\Controllers;

use AMoschou\RemoteAuth\App\Models\User;
use AMoschou\RemoteAuth\App\Support\LoginValidator;
use AMoschou\RemoteAuth\RemoteAuth;
use AMoschou\RemoteAuth\App\Mail\SignedUrlEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * The provider as decided by the package.
     */
    private $provider;

    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request): RedirectResponse|View|string
    {
        if (true || $caseInsensitive) {
            $request->merge([
                'username' => strtolower($request->input('username')),
            ]);
        }

        $validated = $this->validateLogin($request);

        $username = $validated['username'];

        $minutes = 15;

        if (! $this->provider->getDriver()->isRemote) {
            $signedUrl = URL::temporarySignedRoute(
                'sign',
                now()->addMinutes($minutes),
                [ 'username' => $username ]
            );

            $email = User::find($username)->email;

            Mail::to($email)->send(new SignedUrlEmail($username, $signedUrl, $minutes));

            return view('remote-auth::mail.signed_url-confirmation', [
                'username' => $username,
                'minutes' => $minutes,
            ]);
        }

        $user = $this->syncUser($username, $validated['password']);

        return $this->login($request, $user);
    }

    private function login(Request $request, $user): RedirectResponse
    {
        event(new Registered($user));

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        $request->session()->put('intended', config('remote_auth.redirect.after_login_fallback'));

        return $this->authenticated($request);
    }

    public function sign(Request $request, $username)
    {
        $user = User::find($username);

        return $this->login($request, $user);
    }

    /**
     * Finalise the authentication attempt.
     */
    private function authenticated(Request $request): RedirectResponse
    {
        return $this->calledback($request);
    }

    /**
     * Redirect to the intended destination after authentication.
     */
    private function calledback(Request $request): RedirectResponse
    {
        $intended = $request->session()->get('intended');

        $request->session()->forget('intended');

        return redirect()->intended($intended);
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
    private function validateLogin(Request $request): array
    {
        $credentials = LoginValidator::validateCredentials($request);

        $this->provider = RemoteAuth::findProvider(
            $credentials['username'],
            $credentials['password']
        );

        return LoginValidator::validateRules($request, $this->provider);
    }

    /**
     * Sync the user’s details from the remote server and get the user model.
     */
    private function syncUser($username, $password): User
    {
        $record = $this->provider->getDriver()->getRecord($username, $password);

        $user = DB::transaction(function () use ($username, $password, $record) {
            $user = User::findOr($username, function () use ($username) {
                $user = new User;

                $user->username = $username;

                return $user;
            });

            $user->email = $record->email;

            $user->password = Hash::make($password);

            $user->profile = $record->profile;

            $user->provider = $this->provider->getDriver()->getKey();

            if ($user->isDirty()) {
                $user->save();
            }

            $groups = $record->profile['groups'] ?? [];

            DB::table('remote_auth_memberships')
                ->where('username', $username)
                ->delete();

            $memberships = [];

            foreach ($groups as $group) {
                $memberships[] = [
                    'username' => $username,
                    'group' => $group,
                ];
            }

            DB::table('remote_auth_memberships')->insert($memberships);

            return $user;
        });

        return $user;
    }
}
