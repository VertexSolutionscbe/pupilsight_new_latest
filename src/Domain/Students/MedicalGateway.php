<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Students;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v16
 * @since   v16
 */
class MedicalGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightPersonMedical';

    private static $searchableColumns = ['preferredName', 'surname', 'username'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryMedicalFormsBySchoolYear(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightPersonMedicalID', 'bloodType', 'longTermMedication', 'longTermMedicationDetails', 'tetanusWithin10Years', 'comment', 'pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname', 'pupilsightRollGroup.name as rollGroup', '(SELECT COUNT(*) FROM pupilsightPersonMedicalCondition WHERE pupilsightPersonMedicalCondition.pupilsightPersonMedicalID=pupilsightPersonMedical.pupilsightPersonMedicalID) as conditionCount'
            ])
            ->innerJoin('pupilsightPerson', 'pupilsightPerson.pupilsightPersonID=pupilsightPersonMedical.pupilsightPersonID')
            ->innerJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->innerJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->innerJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->where("pupilsightPerson.status = 'Full'")
            ->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        return $this->runQuery($query, $criteria);
    }

    public function selectMedicalConditionsByID($pupilsightPersonMedicalID)
    {
        $data = array('pupilsightPersonMedicalID' => $pupilsightPersonMedicalID);
        $sql = "SELECT pupilsightPersonMedicalCondition.*, pupilsightAlertLevel.name AS risk, pupilsightAlertLevel.color as alertColor, (CASE WHEN pupilsightMedicalCondition.pupilsightMedicalConditionID IS NOT NULL THEN pupilsightMedicalCondition.name ELSE pupilsightPersonMedicalCondition.name END) as name 
                FROM pupilsightPersonMedicalCondition 
                JOIN pupilsightAlertLevel ON (pupilsightPersonMedicalCondition.pupilsightAlertLevelID=pupilsightAlertLevel.pupilsightAlertLevelID) 
                LEFT JOIN pupilsightMedicalCondition ON (pupilsightMedicalCondition.pupilsightMedicalConditionID=pupilsightPersonMedicalCondition.name)
                WHERE pupilsightPersonMedicalCondition.pupilsightPersonMedicalID=:pupilsightPersonMedicalID 
                ORDER BY pupilsightPersonMedicalCondition.name";

        return $this->db()->select($sql, $data);
    }

    public function getMedicalFormByID($pupilsightPersonMedicalID)
    {
        $data = array('pupilsightPersonMedicalID' => $pupilsightPersonMedicalID);
        $sql = "SELECT pupilsightPersonMedical.*, surname, preferredName
                FROM pupilsightPersonMedical
                JOIN pupilsightPerson ON (pupilsightPersonMedical.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                WHERE pupilsightPersonMedicalID=:pupilsightPersonMedicalID";

        return $this->db()->selectOne($sql, $data);
    }

    public function getMedicalConditionByID($pupilsightPersonMedicalConditionID)
    {
        $data = array('pupilsightPersonMedicalConditionID' => $pupilsightPersonMedicalConditionID);
        $sql = "SELECT pupilsightPersonMedicalCondition.*, (CASE WHEN pupilsightMedicalCondition.pupilsightMedicalConditionID IS NOT NULL THEN pupilsightMedicalCondition.name ELSE pupilsightPersonMedicalCondition.name END) as name, surname, preferredName
                FROM pupilsightPersonMedicalCondition
                JOIN pupilsightPersonMedical ON (pupilsightPersonMedicalCondition.pupilsightPersonMedicalID=pupilsightPersonMedical.pupilsightPersonMedicalID)
                JOIN pupilsightPerson ON (pupilsightPersonMedical.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                LEFT JOIN pupilsightMedicalCondition ON (pupilsightMedicalCondition.pupilsightMedicalConditionID=pupilsightPersonMedicalCondition.name)
                WHERE pupilsightPersonMedicalConditionID=:pupilsightPersonMedicalConditionID";

        return $this->db()->selectOne($sql, $data);
    }
}
