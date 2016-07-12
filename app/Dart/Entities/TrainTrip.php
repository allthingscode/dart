<?php

namespace Entities;

use Illuminate\Database\Eloquent\Model;

class TrainTrip extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'train_trips';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'train_trip_program_id',
        'trip_identifier'
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function trainTripProgram()
    {
        return $this->hasOne( 'Entities\TrainTripProgram', 'id', 'train_trip_program_id' ) ;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scheduledStops()
    {
        return $this->hasMany( '\Entities\ScheduledStop', 'train_trip_id', 'id' );
    }


    /**
     * @param $query
     * @param int $trainTripProgramId
     * @return mixed
     */
    public function scopeByTrainTripProgram( $query, $trainTripProgramId )
    {
        $scopedQuery = $this->scopeByTrainTripPrograms( $query, array( $trainTripProgramId ) );
        return $scopedQuery;
    }
    /**
     * @param $query
     * @param int[] $trainTripProgramIds
     * @return mixed
     */
    public function scopeByTrainTripPrograms( $query, array $trainTripProgramIds )
    {
        $scopedQuery = $query->whereIn( 'train_trip_program_id', $trainTripProgramIds );
        return $scopedQuery;
    }




}
