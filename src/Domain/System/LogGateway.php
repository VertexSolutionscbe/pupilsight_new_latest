<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\System;

use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;
use Pupilsight\Domain\Traits\TableAware;

/**
 * Log Gateway
 *
 * @version v17
 * @since   v17
 */
class LogGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightLog';
    private static $primaryKey = 'pupilsightLogID';

    private static $searchableColumns = ['title'];
    
    public function selectLogsByModuleAndTitle($moduleName, $title)
    {
        $data = array('moduleName' => $moduleName, 'title' => $title);
        $sql = "SELECT pupilsightLog.title as groupBy, pupilsightLog.*, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightPerson.title
                FROM pupilsightLog 
                LEFT JOIN pupilsightModule ON (pupilsightModule.pupilsightModuleID=pupilsightLog.pupilsightModuleID)
                LEFT JOIN pupilsightPerson ON (pupilsightPerson.pupilsightPersonID=pupilsightLog.pupilsightPersonID) 
                WHERE (pupilsightModule.name=:moduleName OR (:moduleName IS NULL AND pupilsightLog.pupilsightModuleID IS NULL))
                AND pupilsightLog.title LIKE :title
                ORDER BY pupilsightLog.timestamp DESC";

        return $this->db()->select($sql, $data);
    }

    public function getLogByID($pupilsightLogID)
    {
        $data = array('pupilsightLogID' => $pupilsightLogID);
        $sql = "SELECT pupilsightLog.*, pupilsightPerson.username, pupilsightPerson.surname, pupilsightPerson.preferredName 
                FROM pupilsightLog
                LEFT JOIN pupilsightPerson ON (pupilsightPerson.pupilsightPersonID=pupilsightLog.pupilsightPersonID) 
                WHERE pupilsightLog.pupilsightLogID=:pupilsightLogID";

        return $this->db()->selectOne($sql, $data);
    }
}
