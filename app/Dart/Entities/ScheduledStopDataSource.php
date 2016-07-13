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
     * @param $query
     * @param $trainTripProgramId
     * @return mixed
     */
    public function scopeByTrainTripProgram( $query, $trainTripProgramId )
    {
        $scopedQuery = $query
            ->where( 'train_trip_program_id', $trainTripProgramId );
        return $scopedQuery;
    }


    /**
     * @param $query
     * @param int $scheduleProgramId
     * @return mixed
     */
    public function scopeByScheduleProgram($query, $scheduleProgramId )
    {
        $scopedQuery = $query
            ->where( 'schedule_program_id',   $scheduleProgramId  );
        return $scopedQuery;
    }
}
