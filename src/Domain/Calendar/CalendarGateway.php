<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Calendar;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;



/**
 * Archive Gateway
 *
 * @version v17
 * @since   v17
 */
class CalendarGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'calendar_event_type';

    public function listEventType($con){
        $sq = "SELECT * FROM calendar_event_type  order by id desc ";
        $result = $con->query($sq);
        return $result->fetchAll();
    }

    public function listEvent($con){
        $sq = "SELECT e.*, et.title as event_type_title FROM calendar_event as e, calendar_event_type as et ";
        $sq .= "where e.event_type_id = et.id ";
        $sq .= "order by e.id desc ";
        $result = $con->query($sq);
        return $result->fetchAll();
    }

}