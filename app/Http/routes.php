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

Route::get('/', function () {
    return view('welcome');
});


/**
 * Schedules
 * Sample request:  http://dart.app/schedule/scheduleProgramId/1/trainTripProgramId/3/stationId/9
 */
Route::get(
    'schedule/scheduleProgramId/{scheduleProgramId}/trainTripProgramId/{trainTripProgramId}/stationId/{stationId?}',
    'ScheduleController@showScheduleForScheduleAndTripProgram'
);


/**
 * WheresMyTrain
 * Sample request:  http://dart.app/wheresmytrain/stationId/52/direction/North/asOfTimestamp/2016-06-29%2017:00:00/maxTrainStops/20
 */
Route::get(
    'wheresmytrain/stationId/{stationId}/direction/{direction}/asOfTimestamp/{asOfTimestamp}/maxTrainStops/{maxTrainStops}',
    'ScheduleController@whereIsMyTrain'
);


/**
 * getItinerary
 * Sample request:  http://dart.app/getItinerary/startStationId/9/endStationId/31/asOfTimestamp/2016-07-02%2017:00:00/maxTripOptions/10
 */
Route::get(
    'getItinerary/startStationId/{startStationId}/endStationId/{endStationId}/asOfTimestamp/{asOfTimestamp}/maxTripOptions/{maxTripOptions}',
    'ScheduleController@getItinerary'
);