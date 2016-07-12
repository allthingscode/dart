<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 6/24/16
 * Time: 11:50 PM
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Entities\ScheduledStop;


class ScheduleController extends Controller
{


    /**
     * @param int $scheduleProgramId
     * @param int $trainTripProgramId
     * @param int $stationId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showScheduleForScheduleAndTripProgram( $scheduleProgramId, $trainTripProgramId, $stationId = null )
    {
        // These are needed later for the view
        $scheduleProgram  = \Entities\ScheduleProgram::findOrFail(  $scheduleProgramId  );
        $trainTripProgram = \Entities\TrainTripProgram::findOrFail( $trainTripProgramId );

        // TODO Is this appropriate for a controller?

        $trainTripIdsForTrainTripProgram =
            \Entities\TrainTrip::
                byTrainTripProgram( $trainTripProgramId )
                ->pluck( 'id' )
                ->toArray();

        $scheduleQuery = \Entities\ScheduledStop::
              byScheduleProgram( $scheduleProgramId )
            ->byTrainTrips( $trainTripIdsForTrainTripProgram );
        if ( false === is_null( $stationId ) ) {
            $scheduleQuery = $scheduleQuery->byStation( $stationId );
        }
        $schedules = $scheduleQuery
            ->orderBy( 'time', 'asc' )
            ->get();

        return view( 'schedules',
            [
                'scheduleProgram'  => $scheduleProgram,
                'trainTripProgram' => $trainTripProgram,
                'schedules'        => $schedules
            ] );
    }



    /**
     * @param int $stationId
     * @param string $direction
     * @param string $asOfTimestamp
     * @param int $maxTrainStops
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function whereIsMyTrain( $stationId, $direction, $asOfTimestamp, $maxTrainStops )
    {
        $scheduledStops = \Schedule\Analyzer::whereIsMyTrain( $stationId, $direction, $asOfTimestamp, $maxTrainStops );
        $station        = \Entities\Station::findOrFail( $stationId );

        return view( 'schedules',
            [
                'reportName' => "Where's My train at {$station->name}, as of {$asOfTimestamp} heading {$direction}",
                'schedules'  => $scheduledStops
            ] );
    }


    /**
     * @param int $startStationId
     * @param int $endStationId
     * @param string $asOfTimestamp
     * @param int $maxTrainStops
     * @return string
     */
    public function getItinerary( $startStationId, $endStationId, $asOfTimestamp, $maxTrainStops )
    {
        $startStation = \Entities\Station::findOrFail( $startStationId );
        $endStation   = \Entities\Station::findOrFail( $endStationId   );


        $itineraries = \Itinerary\itineraryGenerator::getItinerariesForStationToStation(
            $startStationId,
            $endStationId,
            $asOfTimestamp,
            $maxTrainStops
        );

        $output = '';
        foreach ( $itineraries as $itinerary ) {
            $output .= view( 'schedules',
                [
                    'reportName' => "Itinerary for a trip between {$startStation->name} and {$endStation->name}",
                    'schedules'  => $itinerary->getScheduledStopEntities()
                ] );
        }

        return $output;
    }
}