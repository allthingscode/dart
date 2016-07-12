<?php

namespace Entities;

use Illuminate\Database\Eloquent\Model;

class ScheduledStopDataSource extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'scheduled_stop_data_sources';


    /**
     * Each schedule data source has a train trip reference
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function trainTrip()
    {
        return $this->hasOne( 'Entities\TrainTrip', 'id', 'train_trip_id' );
    }


    public function scheduleProgram()
    {
        return $this->hasOne( 'Entities\ScheduleProgram', 'id', 'schedule_program_id' );
    }




    public function scopeByNaturalKey( $query, $trainTripProgramId, $scheduleProgramId )
    {
        $scopedQuery = $query
            ->where( 'train_trip_program_id', $trainTripProgramId )
            ->where( 'schedule_program_id',   $scheduleProgramId  );
        return $scopedQuery;
    }
}
