<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\RollGroups;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * RollGroup Gateway
 *
 * @version v16
 * @since   v16
 */
class RollGroupGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightRollGroup';
    private static $searchableColumns = [];

    public function queryRollGroups(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightSchoolYear.sequenceNumber',
                'pupilsightSchoolYear.pupilsightSchoolYearID',
                'pupilsightRollGroup.pupilsightRollGroupID',
                'pupilsightSchoolYear.name as yearName',
                'pupilsightRollGroup.name',
                'pupilsightRollGroup.nameShort',
                'pupilsightRollGroup.pupilsightPersonIDTutor',
                'pupilsightRollGroup.pupilsightPersonIDTutor2',
                'pupilsightRollGroup.pupilsightPersonIDTutor3',
                'pupilsightSpace.name AS space',
                'pupilsightRollGroup.website' 

            ])
            ->innerJoin('pupilsightSchoolYear', 'pupilsightRollGroup.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID')
            ->leftJoin('pupilsightSpace', 'pupilsightRollGroup.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID')
            ->where('pupilsightSchoolYear.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        return $this->runQuery($query, $criteria);
    }

    public function selectRollGroupsBySchoolYear($pupilsightSchoolYearID)
    {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'today' => date('Y-m-d'));
        $sql = "SELECT pupilsightRollGroup.pupilsightRollGroupID, pupilsightRollGroup.name, pupilsightRollGroup.nameShort, pupilsightSpace.name AS space, pupilsightRollGroup.website, pupilsightPersonIDTutor, pupilsightPersonIDTutor2, pupilsightPersonIDTutor3, COUNT(DISTINCT students.pupilsightPersonID) as students
                FROM pupilsightRollGroup 
                LEFT JOIN (
                    SELECT pupilsightPerson.pupilsightPersonID, pupilsightStudentEnrolment.pupilsightRollGroupID FROM pupilsightStudentEnrolment 
                    JOIN pupilsightPerson ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                    WHERE status='Full' AND (dateStart IS NULL OR dateStart<=:today) AND (dateEnd IS NULL OR dateEnd>=:today)
                ) AS students ON (students.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                LEFT JOIN pupilsightSpace ON (pupilsightRollGroup.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) 
                WHERE pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID 
                GROUP BY pupilsightRollGroup.pupilsightRollGroupID
                ORDER BY pupilsightRollGroup.name";

        return $this->db()->select($sql, $data);
    }

    public function selectTutorsByRollGroup($pupilsightRollGroupID)
    {
        $data = array('pupilsightRollGroupID' => $pupilsightRollGroupID);
        $sql = "SELECT pupilsightPersonID, title, surname, preferredName 
                FROM pupilsightRollGroup 
                LEFT JOIN pupilsightPerson ON (pupilsightPersonID=pupilsightRollGroup.pupilsightPersonIDTutor OR pupilsightPersonID=pupilsightRollGroup.pupilsightPersonIDTutor2 OR pupilsightPersonID=pupilsightRollGroup.pupilsightPersonIDTutor3)
                WHERE pupilsightRollGroup.pupilsightRollGroupID=:pupilsightRollGroupID 
                ORDER BY pupilsightPersonID=pupilsightRollGroup.pupilsightPersonIDTutor DESC, pupilsightPersonID=pupilsightRollGroup.pupilsightPersonIDTutor2 DESC";

        return $this->db()->select($sql, $data);
    }

    public function selectRollGroupsByTutor($pupilsightPersonID)
    {
        $data = array('pupilsightPersonID' => $pupilsightPersonID);
        $sql = "SELECT pupilsightRollGroup.*, pupilsightSpace.name as spaceName
                FROM pupilsightRollGroup 
                LEFT JOIN pupilsightSpace ON (pupilsightSpace.pupilsightSpaceID=pupilsightRollGroup.pupilsightSpaceID)
                WHERE (pupilsightRollGroup.pupilsightPersonIDTutor = :pupilsightPersonID
                    OR pupilsightRollGroup.pupilsightPersonIDTutor2 = :pupilsightPersonID
                    OR pupilsightRollGroup.pupilsightPersonIDTutor3 = :pupilsightPersonID)
                AND pupilsightSchoolYearID=(SELECT pupilsightSchoolYearID FROM pupilsightSchoolYear WHERE status='Current' LIMIT 1)
                ORDER BY pupilsightRollGroup.nameShort";

        return $this->db()->select($sql, $data);
    }

    public function getRollGroupByID($pupilsightRollGroupID)
    {
        $data = array('pupilsightRollGroupID' => $pupilsightRollGroupID);
        $sql = "SELECT * 
                FROM pupilsightRollGroup
                WHERE pupilsightRollGroupID=:pupilsightRollGroupID";
            
        return $this->db()->selectOne($sql, $data);
    }
}
