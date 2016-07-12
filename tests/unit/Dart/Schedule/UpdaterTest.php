<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 6/18/16
 * Time: 1:09 AM
 *
 *
 * @TODO How do I get the app.php boostrap steps completed?!
 * @TODO Determine how to run unit tests such that DB operations work.
 */



namespace Schedule;


class UpdaterTest extends \PHPUnit_Framework_TestCase
{


    public function testUpdateSchedules()
    {
        $scheduleUpdater = new Updater();

        $scheduleUpdater->updateSchedules();

    }

}