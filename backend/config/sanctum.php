<?php

use Laravel\Sanctum\Sanctum;

return [

    'stateful' => [],   // 👈 EMPTY – No stateful domains (no cookies, no SPA session)

    'guard' => ['web'], // Optional – Leave as is, it's fine for API token auth

    'expiration' => null, // Default – tokens never expire (you can set minutes later if needed)

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];