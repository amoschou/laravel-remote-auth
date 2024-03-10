<?php

namespace AMoschou\RemoteAuth\App\Support;

trait ReadsConfig
{
    /**
     * Retrieve a setting from configuration.
     */
    protected function config($input = null, $default = null)
    {
        $input = (string) $input;

        $root = "remote_auth.settings.{$this->key}";

        $glue = $input === '' ? '' : '.';

        return config("{$root}{$glue}{$input}", $default);
    }
}
