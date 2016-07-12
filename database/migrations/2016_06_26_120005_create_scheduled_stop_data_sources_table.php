<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduledStopDataSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scheduled_stop_data_sources', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer( 'train_trip_program_id' )->unsigned()->index();
            $table->integer( 'schedule_program_id'   )->unsigned()->index();
            $table->string(  'base_url'              );

            $table->foreign( 'train_trip_program_id' )->references( 'id' )->on( 'train_trip_programs' );
            $table->foreign( 'schedule_program_id'   )->references( 'id' )->on( 'schedule_programs'   );

            // Make sure we can't store any duplicate line+schedule-program sources
            $table->unique( [ 'train_trip_program_id', 'schedule_program_id' ], 'trip_sched_unique' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop( 'scheduled_stop_data_sources' );
    }
}
