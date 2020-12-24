<?php

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
use App\Http\Controllers\API\UsersController;

Route::namespace('API')->group(function () {
    Route::post('register', [UsersController::class, 'register']);
    Route::post('login', 'UsersController@login');

    Route::middleware(['api.auth'])->group(function () {
        Route::get('users/list', 'UsersController@list');
//        Route::post('users/quiz', 'UsersController@storeQuiz');
        Route::post('users/profile', 'UsersController@updateProfile');
        Route::post('users/like', 'UsersController@storeLike');
        Route::get('users/matches', 'UsersController@getLikesData');
    });
});
