<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainTripProgramTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'train_trip_programs', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer( 'train_id'  )->unsigned()->index;
            $table->string(  'name'      )->unique();
            $table->string(  'direction' );

            // Add foreign key constraints
            $table->foreign( 'train_id' )->references( 'id' )->on( 'trains' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop( 'train_trip_programs' );
    }
}
