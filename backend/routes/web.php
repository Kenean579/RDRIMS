<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'Wollo University RDRIMS API',
        'version' => '1.0.0',
        'status' => 'connected'
    ]);
});
