<?php

use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

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

Route::resource('categories', CategoryController::class);

Route::resource('transactions', TransactionController::class);


