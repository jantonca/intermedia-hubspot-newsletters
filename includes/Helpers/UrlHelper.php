<?php

namespace Helpers;

class UrlHelper
{
    public static function generateServerUri(): string
    {
        $serverName = isset( $_SERVER['SERVER_NAME'] );

        if ( isset( $_SERVER['SERVER_PORT'] ) && !in_array( $_SERVER['SERVER_PORT'], [80, 443] ) ) {
            $server_port = is_int( $_SERVER['SERVER_PORT'] );
            $port = ":{$server_port}";

        } else {

            $port = '';

        }

        if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) {

            $scheme = 'https';

        } else {

            $scheme = 'http';

        }

        return $scheme.'://'.$serverName.$port;

    }
}
