<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 6/25/16
 * Time: 2:13 AM
 */

namespace Itinerary;


/**
 * This generates Itineraries for various types of input.
 *
 * Class itineraryGenerator
 * @package Itinerary
 */
class itineraryGenerator
{
    // TODO Create functions that take various inputs/constraints


    /**
     * @param int $startingStationId
     * @param int $destinationStationId
     * @param string $asOfTimestamp
     * @param int $maxTripOptions
     * @return Itinerary[]
     */
    public static function getItinerariesForStationToStation(
        $startingStationId,
        $destinationStationId,
        $asOfTimestamp,
        $maxTripOptions = 10 )
    {
        $itineraries = self::getSingleTripStationToStation(
            $startingStationId,
            $destinationStationId,
            $asOfTimestamp,
            $maxTripOptions
        );
        if ( false === empty( $itineraries ) ) {
            return $itineraries;        // We're done!  We found single-trip options.
        }

        // TODO Come up with a travel plan that includes necessary layovers/connections



        //dd($itineraries);

        return $itineraries;
    }



    /**
     * @param int $startingStationId
     * @param int $destinationStationId
     * @param string $asOfTimestamp
     * @param int $maxTripOptions
     * @return Itinerary[]
     */
    private static function getSingleTripStationToStation(
        $startingStationId,
        $destinationStationId,
        $asOfTimestamp,
        $maxTripOptions )
    {
        /** @var Itinerary[] $itineraries */
        $itineraries = array();

        $scheduleProgram     = \Entities\ScheduleProgram::getByTimestamp( $asOfTimestamp );
        $timestampInDartTime = \Schedule\Analyzer::convertTimestampToDartTime( $asOfTimestamp );

        // See if we can find a single train trip that covers the start and end station
        $trainTrips = \DB::table( 'train_trips' )

            // Include a list of stops to represent both the starting and ending stations
            ->join(  'scheduled_stops AS starting_scheduled_stops', 'starting_scheduled_stops.train_trip_id', '=', 'train_trips.id' )
            ->join(  'scheduled_stops AS ending_scheduled_stops',     'ending_scheduled_stops.train_trip_id', '=', 'train_trips.id' )

            // We only want to look at schedule items that match the program for $asOfTimestamp
            ->where( 'starting_scheduled_stops.schedule_program_id', '=', $scheduleProgram->id )
            ->where(   'ending_scheduled_stops.schedule_program_id', '=', $scheduleProgram->id )

            // Where the same trip includes the starting and ending station
            ->where( 'starting_scheduled_stops.station_id', '=', $startingStationId    )
            ->where(   'ending_scheduled_stops.station_id', '=', $destinationStationId )

            // Obviously, we want to travel in the right direction
            // Also, for simplicity, let's ignore trips that span multiple days (i.e. go past midnight)
            ->whereColumn( 'starting_scheduled_stops.id',   '<', 'ending_scheduled_stops.id'   )
            ->whereColumn( 'starting_scheduled_stops.time', '<', 'ending_scheduled_stops.time' )

            // We only care about trips that hit the start station now or in the future
            ->where( 'starting_scheduled_stops.time', '>=', $timestampInDartTime )

            ->select(  'train_trips.id' )
            ->groupBy( 'train_trips.id' )
            ->take( $maxTripOptions )
            ->get();
        if ( count( $trainTrips ) > 0 ) {
            // This means we have some train trips that will cover station A and B without a transfer/layover
            foreach ( $trainTrips as $trainTrip ) {
                $itinerary = new \Itinerary\Itinerary();
                $itinerary->addStopsByTrainTripIdAndStartingStation(
                    $scheduleProgram->id,
                    $trainTrip->id,
                    $startingStationId,
                    $destinationStationId
                );
                $itineraries[] = $itinerary;
            }
        }

        return $itineraries;
    }




}