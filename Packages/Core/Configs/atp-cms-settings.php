<?php
/**
 * Created by PhpStorm.
 * User: AnhPham
 * Date: 2019-03-23
 * Time: 22:25
 */

return [

    'db-prefix' => env('DB_PREFIX', 'atp_'),

    /*
    |--------------------------------------------------------------------------
    | error_reporting
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    */
    'error_reporting' => [
        'via_email' => env('REPORT_EMAIL', false)
    ],

    /*
    |--------------------------------------------------------------------------
    | emails
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    */
    'emails' => [
        'admin' => env('ADMIN_EMAIL', 'phamtuananh.bkit@gmail.com'),
        'refs' => env('ADMIN_REFS_EMAIL'),
        'system' => env('ADMIN_SYSTEM_EMAIL', 'phamtuananh.bkit@gmail.com')
    ],

    /*
    |--------------------------------------------------------------------------
    | Which administration theme to use for the back end interface
    |--------------------------------------------------------------------------
    */
    'prefix-backend' => 'backend',

    /*
    |--------------------------------------------------------------------------
    | Which administration theme to use for the back end interface
    |--------------------------------------------------------------------------
    */
    'admin-theme' => 'MODERN',

    'ajax_prefix_route'  => 'ajax',

    'themes' => [
        /**
         * Absolute paths as to where stylist can discover themes.
         */
        'paths' => [
            base_path('Themes'),
        ],

        /**
         * Specify the name of the theme that you wish to activate. This should be the same
         * as the theme name that is defined within that theme's json file.
         */
        'activate' => null,
    ],
];
