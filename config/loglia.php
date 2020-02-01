<?php

return [

    /**
     * Your API key is an application-specific secret key. You can find it in your application settings.
     */
    'api_key' => env('LOGLIA_KEY'),

    'http' => [

        /**
         * Any HTTP headers that you want to scrub from HTTP logs before they are sent to Loglia.
         */
        'header_blacklist' => ['authorization', 'cookie', 'set-cookie', 'proxy-authenticate']

    ],

    'sql' => [

        /**
         * If you don't want to log all of the SQL queries that your app executes, set this to false.
         */
        'enabled' => true,

        /**
         * If you don't want to log SQL bindings that can contain sensitive information such as
         * passwords, you can disable the logging of SQL bindings.
         */
        'log_bindings' => true

    ]
];
