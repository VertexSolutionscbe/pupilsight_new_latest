<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\DataUpdater;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v16
 * @since   v16
 */
class MedicalUpdateGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightPersonMedicalUpdate';

    private static $searchableColumns = [''];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryDataUpdates(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightPersonMedicalUpdateID', 'pupilsightPersonMedicalUpdate.status', 'pupilsightPersonMedicalUpdate.timestamp', 'target.preferredName', 'target.surname', 'updater.title as updaterTitle', 'updater.preferredName as updaterPreferredName', 'updater.surname as updaterSurname'
            ])
            ->leftJoin('pupilsightPerson AS target', 'target.pupilsightPersonID=pupilsightPersonMedicalUpdate.pupilsightPersonID')
            ->leftJoin('pupilsightPerson AS updater', 'updater.pupilsightPersonID=pupilsightPersonMedicalUpdate.pupilsightPersonIDUpdater')
            ->where('pupilsightPersonMedicalUpdate.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        return $this->runQuery($query, $criteria);
    }

    public function selectMedicalConditionUpdatesByID($pupilsightPersonMedicalUpdateID)
    {
        $data = array('pupilsightPersonMedicalUpdateID' => $pupilsightPersonMedicalUpdateID);
        $sql = "SELECT pupilsightPersonMedicalConditionUpdate.*, pupilsightAlertLevel.name AS risk, (CASE WHEN pupilsightMedicalCondition.pupilsightMedicalConditionID IS NOT NULL THEN pupilsightMedicalCondition.name ELSE pupilsightPersonMedicalConditionUpdate.name END) as name 
                FROM pupilsightPersonMedicalConditionUpdate
                JOIN pupilsightAlertLevel ON (pupilsightPersonMedicalConditionUpdate.pupilsightAlertLevelID=pupilsightAlertLevel.pupilsightAlertLevelID)
                LEFT JOIN pupilsightMedicalCondition ON (pupilsightMedicalCondition.pupilsightMedicalConditionID=pupilsightPersonMedicalConditionUpdate.name)
                WHERE pupilsightPersonMedicalConditionUpdate.pupilsightPersonMedicalUpdateID=:pupilsightPersonMedicalUpdateID 
                ORDER BY pupilsightPersonMedicalConditionUpdate.name";

        return $this->db()->select($sql, $data);
    }
}
