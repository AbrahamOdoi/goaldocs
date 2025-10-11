<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\RegisterController;

Route::post('/register', [RegisterController::class, 'register']); 