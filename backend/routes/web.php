<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::get('/test-mail', function () {

    Mail::raw('Laravel email is working successfully!', function ($message) {

        $message->to('birtenigussie@gmail.com')
                ->subject('Laravel Mail Test');

    });

    return 'Email sent successfully!';
});