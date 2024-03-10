<?php

namespace AMoschou\RemoteAuth\App\Support;

use AMoschou\RemoteAuth\App\Rules\RemoteAuthRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;

class LoginValidator
{
    /**
     * Get the validation rules that apply to the authentication attempt.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    private static function rules()
    {
        return [
            'username' => 'required',
            'password' => 'required',
            '_remote_auth_rule' => new RemoteAuthRule,
        ];
    }

    public static function validateCredentials(Request $request)
    {
        $keys = ['username', 'password'];

        $credentialsValidator = Validator::make(
            Arr::only($request->all(), $keys),
            Arr::only(self::rules(), $keys)
        );

        return $credentialsValidator->validated();
    }

    public static function validateRules(Request $request, $provider)
    {
        $validator = Validator::make(
            Arr::add($request->all(), '_remote_auth_rule', $provider),
            self::rules()
        );

        return $validator->validated();
    }
}
