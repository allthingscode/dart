<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 6/25/16
 * Time: 2:11 AM
 */

namespace Itinerary;


/**
 * This represents a travel plan between 2 train stops.
 *
 * Class Itinerary
 * @package Itinerary
 */
class Itinerary
{
    /**
     * @var \Entities\ScheduledStop[]
     */
    public $scheduledStops = array();


    public $connectionLayoverSeconds = 0;


    /**
     * @param int $scheduleProgramId
     * @param int $trainTripId
     * @param int $startingStationId
     * @param int $endingStationId
     */
    public function addStopsByTrainTripIdAndStartingStation( $scheduleProgramId, $trainTripId, $startingStationId, $endingStationId )
    {
        $scheduledStopsReadyToAdd = array();

        $stops = \Entities\ScheduledStop::byScheduleProgram( $scheduleProgramId )
            ->byTrainTrip( $trainTripId )
            ->get();

        $recordingStopsNow = false;
        foreach ( $stops as $stop ) {
            if ( $stop->station_id == $startingStationId ) {
                $recordingStopsNow = true;
            }
            if ( true === $recordingStopsNow ) {
                $scheduledStopsReadyToAdd[] = $stop->id;
                if ( $stop->station_id == $endingStationId ) {
                    break;      // This is the final station/stop/destination
                }
            }
        }
        $lastStop = \Entities\ScheduledStop::findOrFail( end( $scheduledStopsReadyToAdd ) );
        if ( $lastStop->station_id != $endingStationId ) {
            // This means we have an incomplete route, since we didn't get to the destination.
            // This can happen for train trips that go the wrong direction.
            return;     // BAIL since we didn't get a workable train trip
        }
        reset( $scheduledStopsReadyToAdd );

        $this->scheduledStops = $scheduledStopsReadyToAdd;
    }



    /**
     * Returns a collection of scheduled stops
     *
     * @return mixed
     */
    public function getScheduledStopEntities()
    {
        $scheduledStops = \Entities\ScheduledStop::wherein( 'id', $this->scheduledStops )->get();
        return $scheduledStops;
    }


    /**
     * @return int
     */
    public function getFirstStop()
    {
        reset ( $this->scheduledStops );
        $firstStopId = current( $this->scheduledStops );
        return $firstStopId;
    }

    /**
     * @return int
     */
    public function getLastStop()
    {
        $lastStopId = end( $this->scheduledStops );
        reset ( $this->scheduledStops );
        return $lastStopId;
    }

    /**
     * @return int
     */
    public function getStopCount()
    {
        $stopCount = count( $this->scheduledStops );
        return $stopCount;
    }
}