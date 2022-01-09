<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\UserAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/users/register', [UserAuthController::class, 'register']);
Route::post('/users/login', [UserAuthController::class, 'login']);
Route::post('/users/getAuthenticatedUser', [UserAuthController::class, 'getAuthenticatedUser']);


Route::resource('users', UserController::class);
Route::resource('categories', CategoryController::class);


