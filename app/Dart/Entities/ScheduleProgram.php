<?php

namespace Entities;

use Illuminate\Database\Eloquent\Model;

class ScheduleProgram extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'schedule_programs';


    /**
     * @param $query
     * @param $name
     * @return mixed
     */
    public function scopeByName( $query, $name )
    {
        $scopedQuery = $query->where( 'name', $name );
        return $scopedQuery;
    }


    /**
     * Constructor using timestamp
     *
     * @param $timestamp
     * @return \Entities\ScheduleProgram
     * @throws \Exception
     */
    public static function getByTimestamp( $timestamp )
    {
        // Determine if we need to be looking at weekday or weekend schedules.
        $asOfTimestampUnix = strtotime( $timestamp );
        if ( false === $asOfTimestampUnix ) {
            throw new \Exception( "Unable to parse timestamp:  {$timestamp}" );
        }
        $scheduleProgramName = 'Weekday';
        switch ( date( 'W', $asOfTimestampUnix ) ) {
            case 0:
            case 6:
                $scheduleProgramName = 'Weekend';
                break;
        }
        $scheduleProgram = self::byName( $scheduleProgramName )->firstOrFail();

        return $scheduleProgram;
    }
}
