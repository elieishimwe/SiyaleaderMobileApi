<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// Returns the csrf token for the current visitor's session.
Route::get('api/csrf', function() {
    return Session::token();
});




Route::group(array('prefix' => 'api/v1'), function() {

    Route::get('categories', 'DepartmentsController@index');
    Route::get('myreport', 'ReportController@myReport');
    Route::post('reportImage','ReportController@saveReportImage');
    Route::post('report', 'ReportController@store');
    Route::post('register', 'UserController@store');
    Route::post('login', 'UserController@login');
    Route::post('forgot', 'UserController@forgot');

});

Route::filter('api.csrf', function($route, $request)
{
if (Session::token() != $request->header('X-Csrf-Token') )
{

}
});
