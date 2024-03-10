<?php

namespace AMoschou\RemoteAuth\App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // use HasApiTokens, HasFactory, Notifiable;
    use HasApiTokens, Notifiable;

    /**
     * The table associated with the model.
     */
    protected $table = 'remote_auth_users';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'username';

    /**
     * The model’s ID is not auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the primary key ID.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'profile',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'profile' => 'array',
    ];

    /**
     * Get the groups that the user is a member of.
     *
     * @return array<int, string>
     */
    public function getGroups(): array
    {
        return DB::table('remote_auth_memberships')
            ->where('username', $this->username)
            ->pluck('group')
            ->toArray();
    }

    /**
     * Decide whether the user is a member of a given group.
     */
    public function isIn(string $group, bool $caseInsensitive = false): bool
    {
        $groups = $this->getGroups();

        if ($caseInsensitive) {
            $group = strtolower($group);

            $groups = Arr::map($groups, fn (string $value, string $key) => strtolower($value));
        }

        return in_array($group, $groups, true);
    }

    public function record()
    {
        return (object) [
            'username' => $this->username,
            'email' => $this->email,
            'profile' => $this->profile + [
                'username' => $this->username,
                'email' => $this->email,
            ],
        ];
    }
}
