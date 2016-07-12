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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function scheduleProgram()
    {
        return $this->hasOne( 'Entities\ScheduleProgram', 'id', 'schedule_program_id' );
    }


    

    /**
     * TODO Split this up into more granular scopes
     *
     * @param $query
     * @param $trainTripProgramId
     * @param $scheduleProgramId
     * @return mixed
     */
    public function scopeByNaturalKey( $query, $trainTripProgramId, $scheduleProgramId )
    {
        $scopedQuery = $query
            ->where( 'train_trip_program_id', $trainTripProgramId )
            ->where( 'schedule_program_id',   $scheduleProgramId  );
        return $scopedQuery;
    }
}
