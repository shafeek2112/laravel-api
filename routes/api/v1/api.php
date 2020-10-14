<?php

use Illuminate\Http\Request;
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

/* Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
}); */

## Auth Routes.
Route::group(['prefix' => '/auth', 'namespace' => 'App\Http\Controllers\api\v1'], function () {

    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@register');
  
    Route::group([ 'middleware' => 'auth:api' ], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });
});

## User Routes.
Route::group(['prefix' => '/user', 'namespace' => 'App\Http\Controllers\api\v1'], function () {

    Route::group([ 'middleware' => 'auth:api' ], function() {
        Route::resource('loan-application', 'LoanApplicationController');
    });
});

## Admin Routes.
/* Route::group(['prefix' => '/admin', 'namespace' => 'App\Http\Controllers\api\v1\Admin'], function () {

    Route::group([ 'middleware' => 'auth:api' ], function() {
        Route::resource('loan-application', 'LoanApplicationController');
    });
}); */