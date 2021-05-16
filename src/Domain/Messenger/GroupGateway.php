<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Messenger;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * Group Gateway
 *
 * @version v16
 * @since   v16
 */
class GroupGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightGroup';
    private static $searchableColumns = ['pupilsightGroup.name'];
    
    /**
     * Queries the list of groups for the messenger Manage Groups page.
     *
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryGroups(QueryCriteria $criteria, $pupilsightSchoolYearID, $pupilsightPersonIDOwner = null)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightGroup.pupilsightGroupID', 'pupilsightGroup.name', 'pupilsightGroup.is_chat', 'pupilsightPerson.surname', 'pupilsightPerson.preferredName', 'COUNT(DISTINCT pupilsightGroupPersonID) as count', 'pupilsightSchoolYear.name as schoolYear'
            ])
            ->innerJoin('pupilsightSchoolYear', 'pupilsightSchoolYear.pupilsightSchoolYearID=pupilsightGroup.pupilsightSchoolYearID')
            ->leftJoin('pupilsightGroupPerson', 'pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID')
            ->leftJoin('pupilsightPerson', 'pupilsightPerson.pupilsightPersonID=pupilsightGroup.pupilsightPersonIDOwner')
            ->groupBy(['pupilsightGroup.pupilsightGroupID'])
            ->orderBy(['pupilsightGroup.pupilsightGroupID DESC']);
        
        $query->where('pupilsightGroup.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
        if (!empty($pupilsightPersonIDOwner)) {
            $query->where('pupilsightGroup.pupilsightPersonIDOwner = :pupilsightPersonIDOwner')
                  ->bindValue('pupilsightPersonIDOwner', $pupilsightPersonIDOwner);
        }
        
        return $this->runQuery($query, $criteria);
    }

    /**
     * Queries the group members based on group ID.
     * @param QueryCriteria $criteria
     * @param string $pupilsightGroupID
     * @return DataSet
     */
    public function queryGroupMembers(QueryCriteria $criteria, $pupilsightGroupID)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightGroupPerson')
            ->cols(['pupilsightGroupPerson.pupilsightGroupID','pupilsightRollGroup.name  as section','pupilsightYearGroup.name as classname', 'pupilsightGroupPerson.pupilsightPersonID as ppid', 'pupilsightPerson.surname', 'pupilsightPerson.preferredName', 'pupilsightPerson.email'])
            ->innerJoin('pupilsightPerson', 'pupilsightPerson.pupilsightPersonID=pupilsightGroupPerson.pupilsightPersonID')
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID')
            ->leftJoin('pupilsightYearGroup', 'pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->where('pupilsightGroupPerson.pupilsightGroupID = :pupilsightGroupID')
            ->groupBy(['pupilsightStudentEnrolment.pupilsightPersonID'])
            ->orderBy(['pupilsightStudentEnrolment.pupilsightPersonID DESC'])
            ->bindValue('pupilsightGroupID', $pupilsightGroupID);

        return $this->runQuery($query, $criteria);
    }

    public function selectGroupByID($pupilsightGroupID)
    {
        $data = array('pupilsightGroupID' => $pupilsightGroupID);
        $sql = "SELECT * FROM pupilsightGroup WHERE pupilsightGroupID=:pupilsightGroupID";

        return $this->db()->select($sql, $data);
    }

    public function selectGroupByIDAndOwner($pupilsightGroupID, $pupilsightPersonIDOwner)
    {
        $data = array('pupilsightGroupID' => $pupilsightGroupID, 'pupilsightPersonIDOwner' => $pupilsightPersonIDOwner);
        $sql = "SELECT * FROM pupilsightGroup WHERE pupilsightGroupID=:pupilsightGroupID AND pupilsightPersonIDOwner=:pupilsightPersonIDOwner";

        return $this->db()->select($sql, $data);
    }

    public function selectGroupsByIDList($pupilsightGroupID)
    {
        $pupilsightGroupIDList = is_array($pupilsightGroupID)? $pupilsightGroupID : [$pupilsightGroupID];

        $data = array('pupilsightGroupIDList' => implode(',', $pupilsightGroupIDList));
        $sql = "SELECT pupilsightGroupID, name FROM pupilsightGroup WHERE FIND_IN_SET(pupilsightGroupID, :pupilsightGroupIDList) ORDER BY FIND_IN_SET(pupilsightGroupID, :pupilsightGroupIDList)";

        return $this->db()->select($sql, $data);
    }

    public function selectGroupPersonByID($pupilsightGroupID, $pupilsightPersonID)
    {
        $data = array('pupilsightGroupID' => $pupilsightGroupID, 'pupilsightPersonID' => $pupilsightPersonID);
        $sql = "SELECT * FROM pupilsightGroupPerson WHERE pupilsightGroupID=:pupilsightGroupID AND pupilsightPersonID=:pupilsightPersonID";

        return $this->db()->select($sql, $data);
    }

    public function selectPersonIDsByGroup($pupilsightGroupID)
    {
        $data = array('pupilsightGroupID' => $pupilsightGroupID);
        $sql = "SELECT pupilsightGroupPerson.pupilsightPersonID FROM pupilsightGroupPerson WHERE pupilsightGroupID=:pupilsightGroupID";

        return $this->db()->select($sql, $data);
    }

    public function insertGroup(array $data)
    {
        $sql = "INSERT INTO pupilsightGroup SET pupilsightPersonIDOwner=:pupilsightPersonIDOwner, pupilsightSchoolYearID=:pupilsightSchoolYearID, name=:name, is_chat=:is_chat, timestampCreated=NOW()";

        return $this->db()->insert($sql, $data);
    }

    public function insertGroupPerson(array $data)
    {
        $sql = "INSERT INTO pupilsightGroupPerson SET pupilsightGroupID=:pupilsightGroupID, pupilsightPersonID=:pupilsightPersonID ON DUPLICATE KEY UPDATE pupilsightPersonID=:pupilsightPersonID";

        return $this->db()->insert($sql, $data);
    }

    public function updateGroup(array $data)
    {
        $sql = "UPDATE pupilsightGroup SET name=:name, is_chat=:is_chat WHERE pupilsightGroupID=:pupilsightGroupID";

        return $this->db()->update($sql, $data);
    }

    public function deleteGroup($pupilsightGroupID)
    {
        $data = array('pupilsightGroupID' => $pupilsightGroupID);
        $sql = "DELETE FROM pupilsightGroup WHERE pupilsightGroupID=:pupilsightGroupID";

        return $this->db()->delete($sql, $data);
    }

    public function deleteGroupPerson($pupilsightGroupID, $pupilsightPersonID)
    {
        $data = array('pupilsightGroupID' => $pupilsightGroupID, 'pupilsightPersonID' => $pupilsightPersonID);
        $sql = "DELETE FROM pupilsightGroupPerson WHERE pupilsightGroupID=:pupilsightGroupID AND pupilsightPersonID=:pupilsightPersonID";

        return $this->db()->delete($sql, $data);
    }

    public function deletePeopleByGroupID($pupilsightGroupID)
    {
        $data = array('pupilsightGroupID' => $pupilsightGroupID);
        $sql = "DELETE FROM pupilsightGroupPerson WHERE pupilsightGroupID=:pupilsightGroupID";

        return $this->db()->delete($sql, $data);
    }

    public function queryMessengercategory(QueryCriteria $criteria)
    {

        $query = $this
            ->newQuery()
            ->from('messagewall_category_master')
            ->cols(['messagewall_category_masterID','categoryname','status as categorystatus']);

        return $this->runQuery($query, $criteria);
    }

}
