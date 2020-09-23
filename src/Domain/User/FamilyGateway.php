<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\User;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v16
 * @since   v16
 */
class FamilyGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightFamily';

    private static $searchableColumns = ['name'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryFamilies(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightFamilyID', 'name', 'status'
            ]);

        return $this->runQuery($query, $criteria);
    }

    public function selectAdultsByFamily($pupilsightFamilyIDList)
    {
        $pupilsightFamilyIDList = is_array($pupilsightFamilyIDList) ? implode(',', $pupilsightFamilyIDList) : $pupilsightFamilyIDList;
        $data = array('pupilsightFamilyIDList' => $pupilsightFamilyIDList);
        $sql = "SELECT pupilsightFamilyAdult.pupilsightFamilyID, pupilsightPerson.title, pupilsightPerson.preferredName, pupilsightPerson.surname, pupilsightPerson.status, pupilsightPerson.email
            FROM pupilsightFamilyAdult
            JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
            WHERE FIND_IN_SET(pupilsightFamilyAdult.pupilsightFamilyID, :pupilsightFamilyIDList) 
            ORDER BY pupilsightPerson.surname, pupilsightPerson.preferredName";

        return $this->db()->select($sql, $data);
    }

    public function selectChildrenByFamily($pupilsightFamilyIDList)
    {
        $pupilsightFamilyIDList = is_array($pupilsightFamilyIDList) ? implode(',', $pupilsightFamilyIDList) : $pupilsightFamilyIDList;
        $data = array('pupilsightFamilyIDList' => $pupilsightFamilyIDList);
        $sql = "SELECT pupilsightFamilyChild.pupilsightFamilyID, '' as title, pupilsightPerson.preferredName, pupilsightPerson.surname, pupilsightPerson.status, pupilsightPerson.email
            FROM pupilsightFamilyChild
            JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
            WHERE FIND_IN_SET(pupilsightFamilyChild.pupilsightFamilyID, :pupilsightFamilyIDList) 
            ORDER BY pupilsightPerson.surname, pupilsightPerson.preferredName";

        return $this->db()->select($sql, $data);
    }

    public function selectFamilyAdultsByStudent($pupilsightPersonID, $allUsers = false)
    {
        $pupilsightPersonIDList = is_array($pupilsightPersonID) ? implode(',', $pupilsightPersonID) : $pupilsightPersonID;
        $data = array('pupilsightPersonIDList' => $pupilsightPersonIDList);
        $sql = "SELECT pupilsightFamilyChild.pupilsightPersonID, pupilsightFamilyAdult.pupilsightFamilyID, pupilsightPerson.*, pupilsightFamilyAdult.childDataAccess, pupilsightFamilyAdult.contactEmail, pupilsightFamilyAdult.contactCall
            FROM pupilsightFamilyChild
            JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamilyChild.pupilsightFamilyID)
            JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
            WHERE FIND_IN_SET(pupilsightFamilyChild.pupilsightPersonID, :pupilsightPersonIDList)";

        if (!$allUsers) $sql .= " AND pupilsightPerson.status='Full'";

        $sql .= " ORDER BY pupilsightFamilyAdult.contactPriority, pupilsightPerson.surname, pupilsightPerson.preferredName";

        return $this->db()->select($sql, $data);
    }

    public function selectFamiliesByStudent($pupilsightPersonID)
    {
        $pupilsightPersonIDList = is_array($pupilsightPersonID) ? implode(',', $pupilsightPersonID) : $pupilsightPersonID;
        $data = array('pupilsightPersonIDList' => $pupilsightPersonIDList);
        $sql = "SELECT pupilsightFamilyChild.pupilsightPersonID, pupilsightFamily.*
            FROM pupilsightFamilyChild
            JOIN pupilsightFamily ON (pupilsightFamily.pupilsightFamilyID=pupilsightFamilyChild.pupilsightFamilyID)
            WHERE FIND_IN_SET(pupilsightFamilyChild.pupilsightPersonID, :pupilsightPersonIDList)
            ORDER BY pupilsightFamily.name";

        return $this->db()->select($sql, $data);
    }
}
