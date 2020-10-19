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
  
    Route::group([ 'middleware' => ['auth:api','user.active'] ], function() {
        Route::post('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });
});

## User Routes.
Route::group(['prefix' => '/user', 'namespace' => 'App\Http\Controllers\api\v1'], function () {

    Route::group([ 'middleware' => ['auth:api','user.active'] ], function() {
        Route::resource('loan-application', 'LoanApplicationController');
        Route::get('loan-repayment-list/{loan_id}/{type}', 'LoanApplicationController@repaymentInstalmentList');
        Route::post('pay-instalment/{loan_repayment_detail_id}', 'LoanApplicationController@payInstalment');
    });
});

## Admin Routes.
Route::group(['prefix' => '/admin', 'namespace' => 'App\Http\Controllers\api\v1'], function () {

    Route::group([ 'middleware' => ['auth:api','user.active','user.admin'] ], function() {
        Route::post('user', 'AdminActionController@userApproveReject');
        Route::post('loan/{application_no}', 'AdminActionController@loanApproveReject');
        Route::post('payment-update/{loan_repayment_detail_id}', 'AdminActionController@loanPyamentApproveReject');
        // Route::post('release-next-term', 'AdminActionController@loanNextPaymentTermRelease');
    });
});