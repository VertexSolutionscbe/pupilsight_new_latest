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
class PersonUpdateGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightPersonUpdate';

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
                'pupilsightPersonUpdateID', 'pupilsightPersonUpdate.status', 'pupilsightPersonUpdate.timestamp', 'target.preferredName', 'target.surname', 'updater.title as updaterTitle', 'updater.preferredName as updaterPreferredName', 'updater.surname as updaterSurname'
            ])
            ->leftJoin('pupilsightPerson AS target', 'target.pupilsightPersonID=pupilsightPersonUpdate.pupilsightPersonID')
            ->leftJoin('pupilsightPerson AS updater', 'updater.pupilsightPersonID=pupilsightPersonUpdate.pupilsightPersonIDUpdater')
            ->where('pupilsightPersonUpdate.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        return $this->runQuery($query, $criteria);
    }

    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryStudentUpdaterHistory(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightPersonIDList)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightPerson')
            ->cols([
                'pupilsightPerson.pupilsightPersonID', 
                'pupilsightPerson.surname', 
                'pupilsightPerson.preferredName', 
                'pupilsightPerson.pupilsightPersonID', 
                'pupilsightRollGroup.name as rollGroupName', 
                'pupilsightPersonUpdate.pupilsightPersonUpdateID', 
                'pupilsightPersonMedicalUpdate.pupilsightPersonMedicalUpdateID', 
                "MAX(pupilsightPersonUpdate.timestamp) as personalUpdate", 
                "MAX(pupilsightPersonMedicalUpdate.timestamp) as medicalUpdate"
            ])
            ->innerJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->innerJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->leftJoin('pupilsightPersonUpdate', 'pupilsightPersonUpdate.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->leftJoin('pupilsightPersonMedicalUpdate', 'pupilsightPersonMedicalUpdate.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->where("pupilsightPerson.status = 'Full'")
            ->where("FIND_IN_SET(pupilsightPerson.pupilsightPersonID, :pupilsightPersonIDList)")
            ->where("pupilsightStudentEnrolment.pupilsightSchoolYearID = :pupilsightSchoolYearID")
            ->bindValue('pupilsightPersonIDList', implode(',', $pupilsightPersonIDList))
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->groupBy(['pupilsightPerson.pupilsightPersonID'])
            ;

        $criteria->addFilterRules([
            'cutoff' => function ($query, $cutoffDate) {
                $query->having("((pupilsightPersonUpdateID IS NULL OR personalUpdate < :cutoffDate)
                    OR (pupilsightPersonMedicalUpdateID IS NULL OR medicalUpdate < :cutoffDate))");
                $query->bindValue('cutoffDate', $cutoffDate);
            },
        ]);

        return $this->runQuery($query, $criteria);
    }

    public function selectParentEmailsByPersonID($pupilsightPersonIDList)
    {
        $pupilsightPersonIDList = is_array($pupilsightPersonIDList) ? implode(',', $pupilsightPersonIDList) : $pupilsightPersonIDList;
        $data = array('pupilsightPersonIDList' => $pupilsightPersonIDList);
        $sql = "SELECT pupilsightFamilyChild.pupilsightPersonID, adult.email 
            FROM pupilsightFamilyChild
            LEFT JOIN pupilsightFamilyAdult ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamilyAdult.pupilsightFamilyID)
            LEFT JOIN pupilsightPerson as adult ON (adult.pupilsightPersonID=pupilsightFamilyAdult.pupilsightPersonID)
            WHERE FIND_IN_SET(pupilsightFamilyChild.pupilsightPersonID, :pupilsightPersonIDList)
            AND adult.status='Full' AND adult.email <> ''
            AND pupilsightFamilyAdult.contactEmail<>'N' 
            AND pupilsightFamilyAdult.childDataAccess='Y'
            ORDER BY pupilsightFamilyAdult.contactPriority, adult.surname, adult.preferredName";

        return $this->db()->select($sql, $data);
    }
}
