<?php

namespace Entities;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stations';



    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];
}
