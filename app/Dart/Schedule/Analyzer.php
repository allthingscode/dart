<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 6/26/16
 * Time: 6:52 PM
 */

namespace Schedule;

/**
 * TODO Find a way to break down these functions into re-usable pieces so we don't have to keep creating new queries.
 *
 * Class Analyzer
 * @package Schedule
 */
class Analyzer
{
    /**
     * @param int $stationId
     * @param string $direction
     * @param string $asOfTimestamp
     * @param int $maxTrainStops
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Exception
     */
    public static function whereIsMyTrain( $stationId, $direction, $asOfTimestamp, $maxTrainStops )
    {
        // Validate direction input and prevent sql injection attacks
        switch ( strtolower( trim( $direction ) ) ) {
            case 'north':
            case 'south':
                break;
            default:
                throw new \Exception( "Invalid direction value ({$direction}).  Expected 'north' or 'south'." );
        }

        // Get an array of all the train trip programs that travel north/south
        $trainTripProgramIds = \Entities\TrainTripProgram::byDirection(  $direction           )->pluck( 'id' )->toArray();
        $trainTripIds        = \Entities\TrainTrip::byTrainTripPrograms( $trainTripProgramIds )->pluck( 'id' )->toArray();

        // Get X schedule items for this station, starting at time.
        $scheduledStops =
            \Entities\ScheduledStop::byStation( $stationId )
            ->byTrainTrips( $trainTripIds )
            ->byTimeRelativeToTimestamp( $asOfTimestamp, '>=' )
            ->orderBy( 'scheduled_stops.time', 'asc' )
            ->take( $maxTrainStops )
            ->get();

        return $scheduledStops;
    }








    /**
     * Converts a plain text timestamp into a Dart time with no date component.
     *
     * @param string $timestamp
     * @return string
     * @throws \Exception
     */
    public static function convertTimestampToDartTime( $timestamp )
    {
        $timestampUnix = strtotime( $timestamp );
        if ( false === $timestampUnix ) {
            throw new \Exception( "Unable to parse timestamp:  {$timestamp}" );
        }
        $timestampDartTime = date( 'H:i:s', $timestampUnix );

        return $timestampDartTime;
    }


    /**
     * @param string $timestamp
     * @param int $periodInSeconds
     * @return array
     * @throws \Exception
     */
    public static function convertPeriodToDartTimestampRange( $timestamp, $periodInSeconds )
    {
        // This is our starting Dart time
        $asOfDartTime = self::convertTimestampToDartTime( $timestamp );

        // Figure out what the end time should be, based on the $periodInSeconds value
        $asOfTimestampUnix = strtotime( $timestamp );
        if ( false === $asOfTimestampUnix ) {
            throw new \Exception( "Unable to parse timestamp:  {$timestamp}" );
        }
        $untilDartTimeUnix = strtotime( "+{$periodInSeconds} seconds", $asOfTimestampUnix );
        if ( false === $untilDartTimeUnix ) {
            throw new \Exception( "Unable to add {$periodInSeconds} seconds to timestamp:  {$timestamp}" );
        }
        $untilDartTime = date( 'H:i:s', $untilDartTimeUnix );
        // Make sure we don't bleed over into the next day
        if ( date( 'Y-m-d', $untilDartTimeUnix ) > date( 'Y-m-d', $asOfTimestampUnix ) ) {
            $untilDartTime = '23:59:59';            // Use the last second for the same day
        }

        $dartTimestampRange = [ $asOfDartTime, $untilDartTime ];

        return $dartTimestampRange;
    }
}