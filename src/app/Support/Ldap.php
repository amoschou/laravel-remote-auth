<?php

namespace AMoschou\RemoteAuth\App\Support;

class Ldap
{
    use ReadsConfig;

    /**
     * The key that is used to identify the provider in the config file.
     */
    private string $key;

    /**
     * The support data that the driver requires.
     *
     * @var array<string, mixed>
     */
    private array $data = [];

    /**
     * Create a new LDAP support instance.
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * Create a new LDAP support instance using a static interface.
     */
    public static function for(string $key): Ldap
    {
        return new Ldap($key);
    }

    /**
     * Return whether the given credentials are valid on the LDAP server.
     */
    public function validate(string $username, string $password): bool
    {
        return $this
            ->setUsername($username)
            ->bind($password)
            ->unbind()
            ->hasValidCredentials();
    }

    /**
     * Return the record of the user with the given credentials.
     */
    public function record(string $username, string $password)
    {
        $data = $this
            ->setUsername($username)
            ->bind($password)
            ->query()
            ->unbind()
            ->data;

        return (object) [
            'username' => $username,
            'email' => $data['profile']['email'],
            'profile' => $data['profile'],
        ];
    }

    /**
     * Set a username for the driver.
     */
    private function setUsername(string $username): static
    {
        $this->data['validCredentials'] = false;
        $this->data['username'] = $username;
        $this->data['usernameBind'] = $username;

        $domain = $this->config('domain');

        $domainIsHostname = (bool) filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
        $domainIsDn = (bool) ldap_explode_dn($domain, 0);

        if ($domainIsHostname) {
            $this->data['usernameBind'] = "{$username}@{$domain}";
        }

        if ($domainIsDn) {
            $attribute = $this->config('profile_map.username');

            $this->data['usernameBind'] = "{$attribute}={$username},{$domain}";
        }

        return $this;
    }

    /**
     * Establish a working connection to the remote server. This requires a
     * username to have been previously set and a password. Therefore, we
     * can establish here whether the username and password are valid.
     */
    private function bind(string $password): static
    {
        $this->data['validCredentials'] = false;

        try {
            $this->data['validCredentials'] = ldap_bind(
                $this->getConnection(),
                $this->data['usernameBind'],
                $password
            );
        } catch (\Throwable $t) {
            $this->data['validCredentials'] = false;
        }

        return $this;
    }

    /**
     * Determine the details which are required to establish a connection. This
     * does not actually open a connnection but only prepares what is needed
     * to be ready to connect. Returns false if the connection parameters would not be valid [?]
     *
     * To do: Check this.
     */
    private function getConnection()
    {
        if (is_null($this->data['connection'] ?? null)) {
            $this->data['validCredentials'] = false;

            $this->data['connection'] = ldap_connect($this->config('connection'));

            foreach ($this->config('options') ?? [] as $option => $value) {
                ldap_set_option($this->data['connection'], $option, $value);
            }
        }

        return $this->data['connection'];
    }

    /**
     * Close the connection.
     */
    private function unbind(): static
    {
        ldap_unbind($this->getConnection());

        return $this;
    }

    /**
     * Returns whether a bind was successful.
     */
    public function hasValidCredentials(): bool
    {
        return $this->data['validCredentials'] ?? false;
    }

    /**
     * Query the LDAP server to retrieve the user’s profile.
     */
    public function query(): static
    {
        $profileMap = $this->config('profile_map');

        $justThese = array_values($profileMap);

        $ldapKey = $profileMap['username'];

        $username = $this->data['username'];

        $filter = "({$ldapKey}={$username})";

        $search = ldap_search(
            $this->getConnection(),
            $this->config('search'),
            $filter,
            $justThese
        );

        $entries = ldap_get_entries(
            $this->getConnection(),
            $search
        );

        $unparsedResult = array_filter(
            $entries[0],
            fn ($ldapkey) => in_array($ldapkey, $justThese, true),
            ARRAY_FILTER_USE_KEY
        );

        $keysWhichAreArrays = [];

        $easyResults = [];

        foreach ($unparsedResult as $ldapkey => $val) {
            if (is_array($val)) {
                if ($val['count'] === 1) {
                    $easyResults[$ldapkey] = $val[0];
                } else {
                    unset($val['count']);

                    $easyResults[$ldapkey] = $val;

                    $keysWhichAreArrays[] = $ldapkey;
                }
            } else {
                $easyResults[$ldapkey] = $val;
            }
        }

        $multis = [];

        foreach ($keysWhichAreArrays as $ldapkey) {
            $multis[$ldapkey] = [];

            foreach ($easyResults[$ldapkey] as $val) {
                $check = ldap_explode_dn($val, 1)[0];

                $multis[$ldapkey][] = $check === false ? $val : $check;
            }
        }

        $profile = [];

        foreach ($profileMap as $profilekey => $ldapkey) {
            $profile[$profilekey] =
                in_array($ldapkey, $keysWhichAreArrays, true)
                    ? $multis[$ldapkey]
                    : ($easyResults[$ldapkey] ?? null);
        }

        $this->data['profile'] = $profile;

        return $this;
    }
}
