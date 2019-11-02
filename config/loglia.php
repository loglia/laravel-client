<?php

return [

    /**
     * Your API key is an application-specific secret key. You can find it in your application settings.
     */
    'api_key' => env('LOGLIA_API_KEY'),

    'http' => [

        /**
         * Any HTTP headers that you want to scrub from HTTP logs before they are sent to Loglia.
         */
        'header_blacklist' => ['authorization', 'cookie', 'set-cookie', 'proxy-authenticate']

    ]
];
