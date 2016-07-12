<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainTripTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_trips', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer( 'train_trip_program_id' )->unsigned()->index;
            $table->string(  'trip_identifier'       );

            // Avoid duplicate records
            $table->unique( [ 'train_trip_program_id', 'trip_identifier' ] );

            // Add foreign key constraints
            $table->foreign( 'train_trip_program_id' )->references( 'id' )->on( 'train_trip_programs' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('train_trips');
    }
}
