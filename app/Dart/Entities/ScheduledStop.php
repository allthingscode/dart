<?php

namespace Entities;

use Illuminate\Database\Eloquent\Model;

class ScheduledStop extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'scheduled_stops';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'schedule_program_id',
        'train_trip_id',
        'station_id',
        'time'
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function scheduleProgram()
    {
        return $this->hasOne( 'Entities\ScheduleProgram', 'id', 'schedule_program_id' );
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function trainTrip()
    {
        return $this->hasOne( 'Entities\TrainTrip', 'id', 'train_trip_id' );
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function station()
    {
        return $this->hasOne( 'Entities\Station', 'id', 'station_id' );
    }



    /**
     * @param $query
     * @param int $scheduleProgramId
     * @return mixed
     */
    public function scopeByScheduleProgram( $query, $scheduleProgramId )
    {
        $scopedQuery = $query->where( 'schedule_program_id', $scheduleProgramId );
        return $scopedQuery;
    }

    /**
     * @param $query
     * @param int $trainTripId
     * @return mixed
     */
    public function scopeByTrainTrip( $query, $trainTripId )
    {
        $scopedQuery = $this->scopeByTrainTrips( $query, array( $trainTripId ) );
        return $scopedQuery;
    }
    public function scopeByTrainTrips( $query, array $trainTripIdArray )
    {
        $scopedQuery = $query->whereIn( 'train_trip_id', $trainTripIdArray );
        return $scopedQuery;
    }

    /**
     * @param $query
     * @param int $stationId
     * @return mixed
     */
    public function scopeByStation( $query, $stationId )
    {
        $scopedQuery = $this->scopeByStations( $query, array( $stationId ) );
        return $scopedQuery;
    }
    public function scopeByStations( $query, array $stationIds )
    {
        $scopedQuery = $query->whereIn( 'station_id', $stationIds );
        return $scopedQuery;
    }



    public function scopeByTimeRelativeToTimestamp( $query, $timestamp, $relation )
    {
        $timestampInDartTime = \Schedule\Analyzer::convertTimestampToDartTime( $timestamp );
        $scopedQuery = $this->scopeByTimeRelativeToDartTime( $query, $timestampInDartTime, $relation );
        return $scopedQuery;
    }
    public function scopeByTimeRelativeToTimestampAndPeriod( $query, $timestamp, $periodInSeconds )
    {
        $timestampRange = \Schedule\Analyzer::convertPeriodToDartTimestampRange( $timestamp, $periodInSeconds );
        $scopedQuery = $this->scopeByTimeRelativeToDartTimestampRange( $query, $timestampRange[0], $timestampRange[1] );
        return $scopedQuery;
    }

    public function scopeByTimeRelativeToDartTimestampRange( $query, $dartStartRange, $dartEndRange )
    {
        $scopedQuery = $query->whereBetween( 'time', [ $dartStartRange, $dartEndRange ] );
        return $scopedQuery;
    }
    public function scopeByTimeRelativeToDartTime($query, $dartTime, $relation )
    {
        $scopedQuery = $query->where( 'time', $relation, $dartTime );
        return $scopedQuery;
    }
}
