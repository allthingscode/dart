<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduledStopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scheduled_stops', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer( 'schedule_program_id' )->unsigned()->index;
            $table->integer( 'train_trip_id'       )->unsigned()->index;
            $table->integer( 'station_id'          )->unsigned()->index;
            $table->time(    'time'                );

            // Make sure we can't store any duplicate stop times for the same schedule
            $table->unique( [ 'schedule_program_id', 'train_trip_id', 'station_id' ], 'sched_prog_trip_station_unique' );

            // Add foreign key constraints
            $table->foreign( 'schedule_program_id' )->references( 'id' )->on( 'schedule_programs' );
            $table->foreign( 'train_trip_id'       )->references( 'id' )->on( 'train_trips'       );
            $table->foreign( 'station_id'          )->references( 'id' )->on( 'stations'          );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('scheduled_stops');
    }
}
