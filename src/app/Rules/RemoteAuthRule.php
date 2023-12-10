<?php

namespace AMoschou\RemoteAuth\App\Rules;

use AMoschou\RemoteAuth\App\Drivers\Driver;
use AMoschou\RemoteAuth\App\Support\Support;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

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

        if ($password === '' || $username === '' || is_null($username) || is_null($password)) {
            $fail('No anonymous logins allowed.');
        } else {
            $drivers = Driver::getOrderedList();

            $successfulDriver = null;

            foreach($drivers as $driver) {
                try {
                    if (is_null($successfulDriver)) {
                        if (Driver::select($driver)->validate($username, $password)) {
                            $successfulDriver = $driver;
                        }
                    }
                } catch (\Throwable $t) {}
            }

            if (is_null($successfulDriver)) {
                $fail('Unable to log in using this username/password at this time.');
            }
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
