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

Route::group(array('prefix' => 'v1'), function() {
    
    //Calendar
    Route::get('calendars', 'CalendarController@index');
    Route::get('calendars/{id}', 'CalendarController@findById');
    Route::post('calendars', 'CalendarController@store');
    Route::put('calendars/{id}', 'CalendarController@update');
    Route::put('calendars/disable/{id}', 'CalendarController@disable');
});
