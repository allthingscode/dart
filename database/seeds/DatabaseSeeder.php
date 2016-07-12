<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);


        // Define our list of lookup values
        $schedulePrograms = [
            [ 'name' => 'Weekday' ],
            [ 'name' => 'Weekend' ]
        ];
        $trains     = [
            [ 'name' => 'Red Line',    'color' => 'red'    ],
            [ 'name' => 'Blue Line',   'color' => 'blue'   ],
            [ 'name' => 'Green Line',  'color' => 'green'  ],
            [ 'name' => 'Orange Line', 'color' => 'orange' ]
        ];
        $directions = [
            'North',
            'South'
        ];
        $scheduledStopDataSources = [
            'Weekday' => [
                    'North' => [
                            'Red Line'    => 'http://www.dart.org/schedules/w600no.htm',
                            'Blue Line'   => 'http://www.dart.org/schedules/w601no.htm',
                            'Green Line'  => 'http://www.dart.org/schedules/w602no.htm',
                            'Orange Line' => 'http://www.dart.org/schedules/w603no.htm'
                        ],
                    'South' => [
                            'Red Line'    => 'http://www.dart.org/schedules/w600so.htm',
                            'Blue Line'   => 'http://www.dart.org/schedules/w601so.htm',
                            'Green Line'  => 'http://www.dart.org/schedules/w602so.htm',
                            'Orange Line' => 'http://www.dart.org/schedules/w603so.htm'
                        ]
                    ],
            'Weekend' => [
                    'North' => [
                            'Red Line'    => 'http://www.dart.org/schedules/s600no.htm',
                            'Blue Line'   => 'http://www.dart.org/schedules/s601no.htm',
                            'Green Line'  => 'http://www.dart.org/schedules/s602no.htm',
                            'Orange Line' => 'http://www.dart.org/schedules/s603no.htm'
                        ]
                    ,
                    'South' => [
                            'Red Line'    => 'http://www.dart.org/schedules/s600so.htm',
                            'Blue Line'   => 'http://www.dart.org/schedules/s601so.htm',
                            'Green Line'  => 'http://www.dart.org/schedules/s602so.htm',
                            'Orange Line' => 'http://www.dart.org/schedules/s603so.htm'
                        ]
                ]
        ];


        // Add schedule programs
        \DB::table('schedule_programs')->delete();
        foreach ( $schedulePrograms as $scheduleProgram ) {
            Entities\ScheduleProgram::create( $scheduleProgram );
        }
        $scheduleProgramEntities = Entities\ScheduleProgram::all();

        // Add trains and train trips
        \DB::table( 'train_trips'                 )->delete();
        \DB::table( 'trains'                      )->delete();
        \DB::table( 'scheduled_stop_data_sources' )->delete();

        foreach ( $trains as $train ) {

            $trainRecord = Entities\Train::create( $train );

            foreach ( $directions as $direction ) {

                // Add a train trip for each direction
                $trainTripProgram = Entities\TrainTripProgram::create( [
                    'train_id'  => $trainRecord->id,
                    'name'      => "{$trainRecord->name} {$direction}",
                    'direction' => $direction
                ] );

                // Add schedule/stop data sources for each program, trip direction, and train name.
                foreach ( $scheduleProgramEntities as $scheduleProgramEntity ) {
                    Entities\ScheduledStopDataSource::create( [
                        'train_trip_program_id' => $trainTripProgram->id,
                        'schedule_program_id'   => $scheduleProgramEntity->id,
                        'base_url'              => $scheduledStopDataSources[ $scheduleProgramEntity->name ][ $trainTripProgram->direction ][ $trainRecord->name ]
                    ] );
                }
            }
        }



        // The "stations" table is automatically populated from the web schedules.
    }
}
