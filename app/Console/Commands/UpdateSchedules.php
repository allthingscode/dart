<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;



class UpdateSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:update';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the DART schedules';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $updater = new \Schedule\Updater();
        $updater->updateSchedules();
    }
}
