<?php

namespace AMoschou\RemoteAuth\App\Drivers;

use Illuminate\Support\Facades\Hash;
use League\Csv\Reader;
use stdClass;

class Csv extends BaseDriver
{
    /**
     * Determine whether the given username and password can authenticate using
     * this driver for a CSV file.
     */
    public function validate(string $username, string $password): bool
    {
        $record = $this->record($username, $password);

        return ! is_null($record);
    }

    /**
     * Determine the profile for the given username and password, provided by
     * the CSV file.
     */
    // public function profile(string $username, ?string $password): ?array
    // {
    //     $record = $this->record($username, $password);

    //     if (is_null($record)) {
    //         return null;
    //     }

    //     return json_decode($record['profile'] ?? '', true);

    // }

    protected function record(string $username, string $password): ?stdClass
    {
        $filename = $this->config('connection');

        $reader = Reader::createFromPath($filename, 'r');

        $reader->setHeaderOffset($this->config('header_offset'));

        $header = $this->config('header', ['username', 'email', 'password', 'profile']);

        $headerFlip = array_flip($header);

        $records = $reader->filter(function (array $record) use ($username, $password, $headerFlip) {
            return (($username === $record[$headerFlip['username']]) && Hash::check($password, $record[$headerFlip['password']]));
        })->getRecords($header);

        if (count([...$records]) !== 1) {
            return null;
        }

        $record = [...$records][0];

        $profile = json_decode($record['profile'] ?? '{}', true);

        $record = array_merge($record, ['profile' => $profile]);

        return (object) $record;
    }
}
