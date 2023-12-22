<?php

namespace AMoschou\RemoteAuth\App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // use HasApiTokens, HasFactory, Notifiable;
    use HasApiTokens, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'remote_auth_users';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'username';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        //
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
    ];

    /**
     * Decide whether the user belongs to a given group.
     *
     * @var string
     * 
     * @return bool
     */
    public function isMemberOf($group): bool
    {
        return DB::table('remote_auth_memberships')
            ->where('username', $this->username)
            ->where('group', $group)
            ->exists();
    }

    /**
     * Get the user’s details.
     *
     * @var string
     * 
     * @return array<string, string>
     */
    public function getAboutUser()
    {
        $groups = DB::table('remote_auth_memberships')
            ->where('username', $this->username)
            ->pluck('group')
            ->toArray();

        return [
            'username' => $this->username,
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'display_name' => $this->display_name,
            'id' => $this->id,
            'email' => $this->email,
            'groups' => $groups,
        ];
    }
}
