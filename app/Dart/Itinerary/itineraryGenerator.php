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
        /*
        $itineraries = self::getSingleTripStationToStation(
            $startingStationId,
            $destinationStationId,
            $asOfTimestamp,
            $maxTripOptions
        );
        if ( false === empty( $itineraries ) ) {
            return $itineraries;
            // TODO Do not assume that single trip options are the best ones for a user.
        }
        */

        $itineraries = self::getDoubleTripStationToStation(
            $startingStationId,
            $destinationStationId,
            $asOfTimestamp,
            $maxTripOptions
        );

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
            // Both these joins are on the single train_trip table, so they're both stops for the SAME train trip.
            ->join(  'scheduled_stops AS starting_scheduled_stops', 'starting_scheduled_stops.train_trip_id', '=', 'train_trips.id' )
            ->join(  'scheduled_stops AS ending_scheduled_stops',     'ending_scheduled_stops.train_trip_id', '=', 'train_trips.id' )

            // We only want to look at schedule items that match the program for $asOfTimestamp
            ->where( 'starting_scheduled_stops.schedule_program_id', '=', $scheduleProgram->id )
            ->where(   'ending_scheduled_stops.schedule_program_id', '=', $scheduleProgram->id )

            // Where the same trip includes the starting and ending station
            ->where( 'starting_scheduled_stops.station_id', '=', $startingStationId    )
            ->where(   'ending_scheduled_stops.station_id', '=', $destinationStationId )

            // Obviously, we want to travel in the right direction
            // NOTE:  Stops for the same train trip are stored in travel order.
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



    /**
     * @param int $startingStationId
     * @param int $destinationStationId
     * @param string $asOfTimestamp
     * @param int $maxTripOptions
     * @return Itinerary[]
     */
    private static function getDoubleTripStationToStation(
        $startingStationId,
        $destinationStationId,
        $asOfTimestamp,
        $maxTripOptions )
    {
        /** @var Itinerary[] $itineraries */
        $itineraries = array();

        $scheduleProgram     = \Entities\ScheduleProgram::getByTimestamp( $asOfTimestamp );
        $timestampInDartTime = \Schedule\Analyzer::convertTimestampToDartTime( $asOfTimestamp );

        // TODO Figure out why this is only returning single-trip options.
        // See if we can find a single train trip that covers the start and end station
        $trainTrips = \DB::

             table( 'train_trips AS train_trip_segment1' )  // First  train trip before a connection
            ->join( 'train_trips AS train_trip_segment2', 'train_trip_segment2.id', '=', 'train_trip_segment2.id' )  // Second train trip to take me to the destination

            // Include a list of stops to represent train trips going by the starting station
            ->join(  'scheduled_stops AS starting_scheduled_stops', 'starting_scheduled_stops.train_trip_id', '=', 'train_trip_segment1.id' )

            // Include a list of stops to represent train trips going by the ending station
            ->join(  'scheduled_stops AS ending_scheduled_stops',     'ending_scheduled_stops.train_trip_id', '=', 'train_trip_segment2.id' )

            // Include a list of stops to represent connecting stations
            ->join(  'scheduled_stops AS connecting_stops',     'connecting_stops.train_trip_id', '=', 'train_trip_segment1.id' )
            ->whereColumn(                                      'connecting_stops.train_trip_id', '=', 'train_trip_segment2.id' )

            // We only want to look at schedule items that match the program for $asOfTimestamp
            ->where( 'starting_scheduled_stops.schedule_program_id', '=', $scheduleProgram->id )
            ->where(   'ending_scheduled_stops.schedule_program_id', '=', $scheduleProgram->id )
            ->where(         'connecting_stops.schedule_program_id', '=', $scheduleProgram->id )

            // Where the first train trip includes the starting station
            ->where( 'starting_scheduled_stops.station_id', '=', $startingStationId    )

            // Where the second train trip includes the ending station
            ->where(   'ending_scheduled_stops.station_id', '=', $destinationStationId )

            // Obviously, we want to travel in the right direction
            // NOTE:  Stops for the same train trip are stored in travel order.
            ->whereColumn( 'starting_scheduled_stops.id',   '<', 'connecting_stops.id'         )
            ->whereColumn( 'connecting_stops.id',           '<', 'ending_scheduled_stops.id'   )

            // For simplicity, let's ignore trips that span multiple days (i.e. go past midnight)
            ->whereColumn( 'starting_scheduled_stops.time', '<', 'ending_scheduled_stops.time' )

            // We only care about trips that hit the start station now or in the future
            ->where( 'starting_scheduled_stops.time', '>=', $timestampInDartTime )

            ->select(  'train_trip_segment1.id AS Segment1TripId', 'train_trip_segment2.id AS Segment2TripId' )
            ->groupBy( 'train_trip_segment1.id', 'train_trip_segment2.id' )
            ->take( $maxTripOptions )
            ->get();

        //dd($trainTrips);

        if ( count( $trainTrips ) > 0 ) {
            // This means we have some train trips that will cover station A and B with a single transfer/layover
            foreach ( $trainTrips as $trainTrip ) {

                //dd($trainTrip);

                $itinerary = new \Itinerary\Itinerary();

                // TODO Figure out how to create an itinerary with multiple train trips
                $itinerary->addStopsByTrainTripIdAndStartingStation(
                    $scheduleProgram->id,
                    $trainTrip->Segment1TripId,
                    $startingStationId,
                    $destinationStationId
                );
                $itineraries[] = $itinerary;
            }
        }

        return $itineraries;
    }
}