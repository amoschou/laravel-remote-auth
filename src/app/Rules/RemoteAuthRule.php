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
    private $driver;

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

        $failMessage = null;

        if (Str::of($username)->isEmpty() || Str::of($password)->isEmpty()) {
            $failMessage = 'No anonymous logins allowed.';
        } else {
            if (is_null($this->getDriver($username, $password))) {
                $failMessage = 'Unable to log in using this username/password at this time.';
            }
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

    private function setDriver($username, $password): void
    {
        if (is_null($this->driver)) {
            $successfulDriver = null;
    
            foreach(config('remote_auth.drivers') as $driverClass) {
                try {
                    if (is_null($successfulDriver)) {
                        if ((new $driverClass)->validate($username, $password)) {
                            $successfulDriver = $driverClass;
                        }
                    }
                } catch (\Throwable $t) {}
            }
    
            $this->driver = $successfulDriver;
        }
    }

    // yes, used in LoginController
    public function getDriver($username = null, $password = null): string|null
    {
        if (is_null($this->driver)) {
            $this->setDriver($username, $password);
        }

        return $this->driver;
    }

    public function getUser($username, $password)
    {
        return (new $this->driver)->getUser($username, $password);
    }
}
