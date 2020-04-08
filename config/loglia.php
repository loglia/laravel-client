<?php

return [

    /**
     * Your API key is an application-specific secret key. You can find it in your application settings.
     * This is how Loglia will link your logs to the application in your dashboard.
     */
    'api_key' => env('LOGLIA_KEY'),

    'http' => [

        /**
         * Any HTTP headers that you want to scrub from HTTP logs before they are sent to Loglia. We
         * have provided sensible defaults, but you may want to customize this to suit your application.
         */
        'header_blacklist' => ['authorization', 'cookie', 'set-cookie', 'proxy-authenticate']

    ],

    'sql' => [

        /**
         * If you don't want to log all of the SQL queries that your app executes, set this to false.
         * You can turn this on or off during runtime with:
         *
         * config(['loglia.sql.enabled' => true]);
         */
        'enabled' => true,

        /**
         * If you don't want to log SQL bindings that can contain sensitive information, you can disable
         * the logging of SQL bindings. You can also turn this on or off during runtime with:
         *
         * config(['loglia.sql.log_bindings' => true]);
         */
        'log_bindings' => true,

        /**
         * If you don't want to log the stack traces of queries, you can turn that feature off.
         * This means Loglia won't be able to tell you what code triggered a query. Once again,
         * you can turn this on or off during runtime with:
         *
         * config(['loglia.sql.log_traces' => true]);
         */
        'log_traces' => true

    ]
];
