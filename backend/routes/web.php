<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'Research Management System',
        'version' => '1.0.0',
        'status' => 'connected'
    ]);
});
