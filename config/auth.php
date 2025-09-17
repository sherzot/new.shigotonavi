<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'password' => env('AUTH_PASSWORD_BROKER', 'master_person'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'master_person', // master_personガード
        ],
        'master_company' => [
            'driver' => 'session',
            'provider' => 'master_company', // master_company プロバイダー
            'session_key' => 'session_master_company', // セッション キーはガードごとに固有です。
            'remember' => false,
        ],
        'master_agent' => [
            'driver' => 'session',
            'provider' => 'master_agent', // master_agentプロバイダー
            'session_key' => 'session_master_agent', // セッション キーはガードごとに固有です。
            'remember' => false,
        ],
    ],



    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

   'providers' => [
        'master_person' => [
            'driver' => 'eloquent',
            'model' => App\Models\MasterPerson::class, // ベーシックモデル
        ],
        'master_company' => [
            'driver' => 'eloquent',
            'model' => App\Models\CustomCompanyUser::class, // CustomCompanyUser modeli
        ],
        'master_agent' => [
            'driver' => 'eloquent',
            'model' => App\Models\CustomAgentUser::class, // CustomAgentUser modeli
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'master_person' => [
            'provider' => 'master_person',
            'table' => 'master_person', // master_person テーブルを使用します
            'expire' => 60,
            'throttle' => 60,
        ],
        'master_company' => [
            'provider' => 'master_company',
            'table' => 'master_company', // master_company テーブル
            'expire' => 60,
            'throttle' => 60,
        ],
        'master_agent' => [
            'provider' => 'master_agent',
            'table' => 'master_agent', // master_agent テーブル
            'expire' => 60,
            'throttle' => 60,
        ],
    ],



    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
