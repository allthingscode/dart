<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 6/11/16
 * Time: 11:54 PM
 */

namespace DartOrgWebsite;


class RailLineSchedules
{


    /**
     * @param int $scheduleProgramId
     * @param int $trainTripProgramId
     * @return array
     */
    public function getSchedule( $scheduleProgramId, $trainTripProgramId )
    {
        // Get the schedule source URL
        $scheduleSourceUrl = $this->_getScheduleSourceUrl( $scheduleProgramId, $trainTripProgramId );

        $scheduleProgram  = \Entities\ScheduleProgram::findOrFail(  $scheduleProgramId  );
        $trainTripProgram = \Entities\TrainTripProgram::findOrFail( $trainTripProgramId );
        echo "{$scheduleProgram->name} / {$trainTripProgram->name} \t {$scheduleSourceUrl}\n";

        // Get the schedule in a nice clean multi-dimensional array
        $schedule = $this->_getScheduleDetails( $scheduleSourceUrl );

        return $schedule;
    }


    /**
     * @param int $scheduleProgramId
     * @param int $trainTripProgramId
     * @return string
     */
    private function _getScheduleSourceUrl( $scheduleProgramId, $trainTripProgramId )
    {
        $dataSourceUrl =
            \Entities\ScheduledStopDataSource::
                  byTrainTripProgram( $trainTripProgramId )
                ->byScheduleProgram(  $scheduleProgramId  )
                ->value( 'base_url' );

        return $dataSourceUrl;
    }


    /**
     * TODO Create entities for all the web page items and use constructors to neatly organize all this parsing.
     *
     * @param string $scheduleSourceUrl
     * @return array
     * @throws \Exception
     */
    private function _getScheduleDetails( $scheduleSourceUrl )
    {
        $browser = new \Goutte\Client();
        $crawler = $browser->request( 'GET', $scheduleSourceUrl );

        // Make sure we got to the URL
        $requestStatusCode = $browser->getResponse()->getStatus();
        if( 200 != $requestStatusCode ) {
            throw new \Exception("Unable to retrieve Dart schedule from {$scheduleSourceUrl}.  Request status code:  {$requestStatusCode}");
        }

        $headers    = array();
        $schedule   = array();
        $tripNumber = 1;

        $tableRowNodes = $crawler->filter('tr');
        foreach ( $tableRowNodes as $tableRowNode ) {

            /** @var $tableRowNode \DOMNode */

            // If the row does not have at least 10 cells, then it's certainly not a true schedule row.
            if ( $tableRowNode->childNodes->length < 10 ) {
                continue;
            }


            // Process header rows
            // The header rows are repeated throughout the table.
            // This block is expected to load headers from the very first header row.
            // For subsequent header rows, they are skipped.
            if ( 'th' === strtolower( $tableRowNode->firstChild->nodeName ) ) {
                if ( false === empty( $headers ) ) {
                    continue;   // We already have the headers loaded
                }
                foreach ( $tableRowNode->childNodes as $thNode ) {
                    // Skip any elements that are not normal XML element nodes (e.g. Text nodes)
                    if ( $thNode->nodeType <> 1 ) {
                        continue;
                    }
                    // Skip any elements that contain an image.
                    // Images are used in every other cell for spacing.
                    if ( 'img' === strtolower( $thNode->firstChild->nodeName ) ) {
                        continue;
                    }

                    // Make sure we have a station name
                    $stationName = trim( $thNode->textContent );
                    if ( true === empty( $stationName ) ) {
                        throw new \Exception( "Unexpected empty station name on Dart website schedule!  Url:  {$scheduleSourceUrl}" );
                    }

                    $headers[] = $stationName;
                }
                continue;       // We're done processing this header row
            }

            // Process data rows
            if ( 'td' === strtolower( $tableRowNode->firstChild->nodeName ) ) {
                $timeRowCells = array();
                foreach ( $tableRowNode->childNodes as $tdNode ) {
                    // Skip any elements that are not normal XML element nodes (e.g. Text nodes)
                    if ( $tdNode->nodeType <> 1 ) {
                        continue;
                    }
                    // Skip any elements that contain an image.
                    // Images are used in every other cell for spacing.
                    if ( 'img' === strtolower( $tdNode->firstChild->nodeName ) ) {
                        continue;
                    }
                    $time = trim( $tdNode->textContent );
                    if ( false === empty( $time ) ) {
                        $timeRowCells[] = $time;
                    }
                }
                if ( count( $timeRowCells ) < 10 ) {
                    // We didn't find any cells that contained time values.
                    // We expect this to happen on the first table row because it's just images for spacing.
                    continue;
                }

                if ( true === empty( $headers ) ) {
                    throw new \Exception( "Cannot load time row before the headers are set.  Url:  {$scheduleSourceUrl}" );
                }
                $timeRow = array_combine( $headers, $timeRowCells );
                if ( false === $timeRow ) {
                    throw new \Exception( "Unable to create time row array from headers and data arrays.  Url:  {$scheduleSourceUrl}" );
                }

                $schedule[ $tripNumber ] = $timeRow;
                $tripNumber++;

                continue;       // We're done processing this data row
            }


            // This is a row that does not contain a td or th ... strange ...
            throw new \Exception( "Cannot tell if schedule row contains headers or data.  Url:  {$scheduleSourceUrl}" );
        }

        return $schedule;
    }


}