<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Attendance;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v16
 * @since   v16
 */
class AttendanceCodeGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightAttendanceCode';

    private static $searchableColumns = ['name', 'nameShort'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryAttendanceCodes(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightAttendanceCodeID', 'name', 'nameShort', 'scope', 'active', 'direction', 'type', 'sequenceNumber'
            ]);


        return $this->runQuery($query, $criteria);
    }
}