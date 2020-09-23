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
class FamilyUpdateGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightFamilyUpdate';

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
                'pupilsightFamilyUpdateID', 'pupilsightFamilyUpdate.status', 'pupilsightFamilyUpdate.timestamp', 'pupilsightFamily.name as familyName', 'updater.title as updaterTitle', 'updater.preferredName as updaterPreferredName', 'updater.surname as updaterSurname'
            ])
            ->leftJoin('pupilsightFamily', 'pupilsightFamily.pupilsightFamilyID=pupilsightFamilyUpdate.pupilsightFamilyID')
            ->leftJoin('pupilsightPerson AS updater', 'updater.pupilsightPersonID=pupilsightFamilyUpdate.pupilsightPersonIDUpdater')
            ->where('pupilsightFamilyUpdate.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        return $this->runQuery($query, $criteria);
    }

    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryFamilyUpdaterHistory(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightYearGroupIDList, $requiredUpdatesByType)
    {
        $pupilsightYearGroupIDList = is_array($pupilsightYearGroupIDList)? implode(',', $pupilsightYearGroupIDList) : $pupilsightYearGroupIDList;

        $query = $this
            ->newQuery()
            ->from('pupilsightFamily')
            ->cols([
                'pupilsightFamily.pupilsightFamilyID', 
                'pupilsightFamily.name as familyName', 
                'MAX(pupilsightFamilyUpdate.timestamp) as familyUpdate', 
                "MAX(IFNULL(pupilsightPerson.dateEnd, NOW())) as latestEndDate",
                'pupilsightFamilyUpdate.pupilsightFamilyUpdateID'
            ])
            ->innerJoin('pupilsightFamilyChild', 'pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID')
            ->innerJoin('pupilsightPerson', 'pupilsightPerson.pupilsightPersonID=pupilsightFamilyChild.pupilsightPersonID')
            ->innerJoin('pupilsightStudentEnrolment', 'pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->leftJoin('pupilsightFamilyUpdate', 'pupilsightFamilyUpdate.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID')
            ->where("pupilsightPerson.status='Full'")
            ->where('pupilsightStudentEnrolment.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->where('FIND_IN_SET(pupilsightStudentEnrolment.pupilsightYearGroupID, :pupilsightYearGroupIDList)')
            ->bindValue('pupilsightYearGroupIDList', $pupilsightYearGroupIDList)
            ->groupBy(['pupilsightFamily.pupilsightFamilyID'])
            ->having('latestEndDate >= NOW()');

        $criteria->addFilterRules([
            'cutoff' => function ($query, $cutoffDate) use ($requiredUpdatesByType) {
                $havingCutoff = "(pupilsightFamilyUpdateID IS NULL OR familyUpdate < :cutoffDate)";

                if (in_array('Personal', $requiredUpdatesByType)) {
                    $query->cols([
                        "MAX(IFNULL(studentUpdate.timestamp, '0000-00-00')) as earliestStudentUpdate", 
                        "MAX(IFNULL(adultUpdate.timestamp, '0000-00-00')) as earliestAdultUpdate"
                    ])
                    ->leftJoin('pupilsightPersonUpdate AS studentUpdate', 'studentUpdate.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
                    ->leftJoin('pupilsightFamilyAdult', 'pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID')
                    ->leftJoin('pupilsightPerson AS adult', "adult.pupilsightPersonID=pupilsightFamilyAdult.pupilsightPersonID AND adult.status='Full'")
                    ->leftJoin('pupilsightPersonUpdate AS adultUpdate', 'adultUpdate.pupilsightPersonID=adult.pupilsightPersonID');
                    $havingCutoff .= " OR (earliestStudentUpdate < :cutoffDate) OR (earliestAdultUpdate < :cutoffDate)";
                }

                if (in_array('Medical', $requiredUpdatesByType)) {
                    $query->cols([
                        "MAX(IFNULL(medicalUpdate.timestamp, '0000-00-00')) as earliestMedicalUpdate", 
                    ])
                    ->leftJoin('pupilsightPersonMedicalUpdate AS medicalUpdate', 'medicalUpdate.pupilsightPersonID=pupilsightPerson.pupilsightPersonID');
                    $havingCutoff .= " OR (earliestMedicalUpdate < :cutoffDate)";
                }

                $query->having($havingCutoff)
                    ->bindValue('cutoffDate', $cutoffDate);
            },
        ]);

        return $this->runQuery($query, $criteria);
    }

    public function selectFamilyAdultUpdatesByFamily($pupilsightFamilyIDList)
    {
        $pupilsightFamilyIDList = is_array($pupilsightFamilyIDList) ? implode(',', $pupilsightFamilyIDList) : $pupilsightFamilyIDList;
        $data = array('pupilsightFamilyIDList' => $pupilsightFamilyIDList);
        $sql = "SELECT pupilsightFamilyAdult.pupilsightFamilyID, pupilsightPerson.title, pupilsightPerson.preferredName, pupilsightPerson.surname, pupilsightPerson.status, MAX(pupilsightPersonUpdate.timestamp) as personalUpdate, pupilsightPerson.email
            FROM pupilsightFamilyAdult
            JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
            LEFT JOIN pupilsightPersonUpdate ON (pupilsightPersonUpdate.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
            WHERE FIND_IN_SET(pupilsightFamilyAdult.pupilsightFamilyID, :pupilsightFamilyIDList) 
            AND pupilsightPerson.status='Full'
            GROUP BY pupilsightFamilyAdult.pupilsightPersonID 
            ORDER BY pupilsightPerson.surname, pupilsightPerson.preferredName";

        return $this->db()->select($sql, $data);
    }

    public function selectFamilyChildUpdatesByFamily($pupilsightFamilyIDList, $pupilsightSchoolYearID)
    {
        $pupilsightFamilyIDList = is_array($pupilsightFamilyIDList) ? implode(',', $pupilsightFamilyIDList) : $pupilsightFamilyIDList;
        $data = array('pupilsightFamilyIDList' => $pupilsightFamilyIDList, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT pupilsightFamilyChild.pupilsightFamilyID, '' as title, pupilsightPerson.preferredName, pupilsightPerson.surname, pupilsightPerson.status, pupilsightRollGroup.nameShort as rollGroup, MAX(pupilsightPersonUpdate.timestamp) as personalUpdate, MAX(pupilsightPersonMedicalUpdate.timestamp) as medicalUpdate, pupilsightPerson.dateStart AS dateStart
            FROM pupilsightFamilyChild
            JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
            JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
            JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightRollGroupID=pupilsightStudentEnrolment.pupilsightRollGroupID)
            JOIN pupilsightYearGroup ON (pupilsightYearGroup.pupilsightYearGroupID=pupilsightStudentEnrolment.pupilsightYearGroupID)
            LEFT JOIN pupilsightPersonUpdate ON (pupilsightPersonUpdate.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
            LEFT JOIN pupilsightPersonMedicalUpdate ON (pupilsightPersonMedicalUpdate.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
            WHERE FIND_IN_SET(pupilsightFamilyChild.pupilsightFamilyID, :pupilsightFamilyIDList) 
            AND pupilsightPerson.status='Full'
            AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
            GROUP BY pupilsightFamilyChild.pupilsightPersonID 
            ORDER BY pupilsightYearGroup.sequenceNumber, pupilsightRollGroup.nameShort, pupilsightPerson.surname, pupilsightPerson.preferredName";

        return $this->db()->select($sql, $data);
    }
}
