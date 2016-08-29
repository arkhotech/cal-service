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
    Route::get('calendars/searchByName', 'CalendarController@searchByName');
    Route::get('calendars/{id}', 'CalendarController@findById');
    Route::post('calendars', 'CalendarController@store');
    Route::put('calendars/{id}', 'CalendarController@update');
    Route::put('calendars/disable/{id}', 'CalendarController@disable');
    
    //DayOff
    Route::get('daysOff', 'DayOffController@index');
    Route::post('daysOff', 'DayOffController@store');
    Route::delete('daysOff/{id}', 'DayOffController@destroy');
    
    //Appointment    
    Route::post('appointments/reserve', 'AppointmentController@store');
    Route::put('appointments/{id}', 'AppointmentController@update');
    Route::put('appointments/confirm/{id}', 'AppointmentController@confirm');
    Route::put('appointments/cancel/{id}', 'AppointmentController@cancel');
    Route::put('appointments/assists/{id}', 'AppointmentController@assists');
    Route::get('appointments/listByApplyer/{id}', 'AppointmentController@listByApplyer');
    Route::get('appointments/listByOwner/{id}', 'AppointmentController@listByOwner');
    
    //BlockSchedule
    Route::post('blockSchedules', 'BlockScheduleController@store');
    
});
