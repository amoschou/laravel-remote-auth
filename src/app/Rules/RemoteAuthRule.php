<?php

namespace AMoschou\RemoteAuth\App\Rules;

use AMoschou\RemoteAuth\RemoteAuth;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class RemoteAuthRule implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $username = $this->data['username'];
        $password = $this->data['password'];
        $provider = $this->data['_remote_auth_rule'];

        $failMessage = null;

        if (Str::of($username)->isEmpty() || Str::of($password)->isEmpty()) {
            $failMessage = 'No anonymous logins allowed.';
        } elseif (is_null($provider)) {
            $failMessage = 'Unable to log in using this username/password at this time.';
            // No servers available? Wrong password?
        }

        if (! is_null($failMessage)) {
            $fail($failMessage);
        }
    }

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
