<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiAuthController;

Route::post('/registro', [ApiAuthController::class, 'registro']);