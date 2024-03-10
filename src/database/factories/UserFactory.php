<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $username = strtolower("{$firstName}.{$lastName}");
        $groups = $this->groups();

        return [
            'username' => $username,
            'profile' => [
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'display_name' => "{$firstName} {$lastName}",
                'phone' => fake()->mobileNumber(),
                'email' => $this->fakeEmail($firstName, $lastName),
                'groups' => $groups,

            ],
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    private function fakeEmail($firstName, $lastName)
    {
        $randomUsername = match (random_int(1, 4)) {
            1 => "{$firstName}.{$lastName}",
            2 => substr($firstName, 0, 1) . $lastName,
            3 => "{$firstName}",
            4 => "{$lastName}",
        }

        $randomDigits = random_int(0, 1) === 0 ? '' : random_int(1, 99);

        $randomDomain = match (random_int(1, 3)) {
            1 => 'example.com',
            2 => 'example.net',
            3 => 'example.org',
        };

        return strtolower("{$randomUsername}{$randomDigits}@{$randomDomain}");
    }

    private function groups()
    {
        $groups = [
            ['guest'],
            ['staff'],
            ['staff', 'team1'],
            ['staff', 'team2'],
            ['staff', 'team3'],
            ['staff', 'team1', 'team2'],
            ['staff', 'team1', 'team3'],
            ['staff', 'team2', 'team3'],
            ['staff', 'team1', 'team2', 'team3'],
            ['admin', 'staff'],
        ];

        return $groups[random_int(0, 9)];
    }
}
