<?php

namespace Entities;

use Illuminate\Database\Eloquent\Model;

class TrainTripProgram extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'train_trip_programs';


    /**
     * Each TrainTrip record has a single train reference
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function train()
    {
        return $this->hasOne( 'Entities\Train', 'id', 'train_id' ) ;
    }



    /**
     * @param $query
     * @param string $name
     * @return mixed
     */
    public function scopeByName( $query, $name )
    {
        $scopedQuery = $query->where( 'name', $name );
        return $scopedQuery;
    }


    /**
     * @param $query
     * @param string $direction
     * @return mixed
     */
    public function scopeByDirection( $query, $direction )
    {
        $scopedQuery = $query->where( 'direction', $direction );
        return $scopedQuery;
    }
}
