<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 6/11/16
 * Time: 11:38 PM
 */

namespace Schedule;


use Entities\Train;
use Entities\TrainTripProgram;
use Mockery\CountValidator\Exception;

class Updater
{

    public function __construct()
    {

    }


    /**
     * TODO Refactor all this to use queues for consuming multiple schedules at the same time.
     *
     *
     * @return void
     */
    public function updateSchedules()
    {
        // Iterate all of the schedule programs and train trips
        //   since that represents all the stops that need to be loaded.

        $schedulePrograms  = \Entities\ScheduleProgram::all();
        $trainTripPrograms = \Entities\TrainTripProgram::all();

        // For all combinations, update the schedule
        foreach ( $schedulePrograms as $scheduleProgram ) {
            foreach ( $trainTripPrograms as $trainTripProgram ) {

                // Skip the Orange South Weekends because it is all F*d up
                if ( 'Orange Line South' === $trainTripProgram->name ) {
                    if ( 'Weekend' === $scheduleProgram->name ) {
                        continue;
                    }
                }

                $this->updateSchedule( $scheduleProgram, $trainTripProgram );
            }
        }
    }






    /**
     * TODO Pass id values rather than entities
     *
     * @param \Entities\ScheduleProgram $scheduleProgram
     * @param \Entities\TrainTripProgram $trainTripProgram
     */
    public function updateSchedule( \Entities\ScheduleProgram $scheduleProgram, \Entities\TrainTripProgram $trainTripProgram )
    {
        // Pull the current data source from the dart website
        $railLineSchedules = new \DartOrgWebsite\RailLineSchedules();
        $schedule = $railLineSchedules->getSchedule( $scheduleProgram, $trainTripProgram );

        // Merge the station names (extremely unlikely to change)
        foreach ( $schedule as $tripIdentifier => $scheduleRecord ) {

            foreach ( $scheduleRecord as $stationName => $time ) {

                $mysqlTime = $this->_createMySqlTimeFromDartTime($time);

                // If the time is null, then the train isn't actually going to stop at that station,
                //   so there's really no reason to add a scheduled stop record
                //   ... since there really is no scheduled stop.
                if ( true === is_null( $mysqlTime ) ) {
                    continue;
                }

                // This is kind of wasteful, since the station names are practically in stone and never change.
                $stationEntity = \Entities\Station::firstOrCreate([
                    'name' => $stationName
                ]);
                $trainTripEntity = \Entities\TrainTrip::firstOrCreate([
                    'train_trip_program_id' => $trainTripProgram->id,
                    'trip_identifier'       => $tripIdentifier
                ]);
                \Entities\ScheduledStop::firstOrCreate([
                    'schedule_program_id' => $scheduleProgram->id,
                    'train_trip_id'       => $trainTripEntity->id,
                    'station_id'          => $stationEntity->id,
                    'time'                => $mysqlTime
                ]);
            }
        }
    }


    /**
     * @param string $dartTime
     * @return string
     */
    private function _createMySqlTimeFromDartTime( $dartTime )
    {
        $sanitizedTime = preg_replace( '/[^\d:AP]/', '', strtoupper( trim( $dartTime ) ) );

        // If there is no time, then the proper DB value is NULL
        if ( true === empty( $sanitizedTime ) ) {
            return null;
        }

        if ( preg_match( '/^\d{1,2}:\d{2}[AP]$/', $sanitizedTime ) < 1 ) {
            throw new Exception("Invalid Dart time:  {$sanitizedTime}");
        }

        // TODO Can all this be simplified/reduced using strtotime and date?

        list( $hour, $minute ) = explode( ':', $sanitizedTime );
        if ( 'P' === substr($sanitizedTime, -1 ) ) {
            if ( $hour <> '12' ) {
                $hour += 12;        // 24-hour format for hours after 12pm
            }
        } elseif ( 'A' === substr($sanitizedTime, -1 ) ) {
            if ( '12' == $hour ) {
                $hour = '00';       // 24-hour format for 12am
            }
        }
        if ( 1 == strlen( $hour ) ) {
            $hour = "0{$hour}";
        }
        $minute = substr( $minute, 0, 2 );

        $mysqlTime = "{$hour}:{$minute}";

        if ( $hour > '23' ) {
            throw new Exception( "Unable to convert web page time ({$dartTime}) to standard 24-hour time ({$mysqlTime}).  Invalid Hour." );
        }
        if ( $minute > '59' ) {
            throw new Exception( "Unable to convert web page time ({$dartTime}) to standard 24-hour time ({$mysqlTime}).  Invalid Minute." );
        }

        return $mysqlTime;
    }



}