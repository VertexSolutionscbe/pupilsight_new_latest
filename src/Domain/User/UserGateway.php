<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\User;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\Traits\SharedUserLogic;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * User Gateway
 *
 * @version v16
 * @since   v16
 */
class UserGateway extends QueryableGateway
{
    use TableAware;
    use SharedUserLogic;

    private static $tableName = 'pupilsightPerson';
    private static $primaryKey = 'pupilsightPersonID';

    private static $searchableColumns = ['preferredName', 'surname', 'username', 'studentID', 'email', 'emailAlternate', 'phone1', 'phone2', 'phone3', 'phone4', 'vehicleRegistration', 'pupilsightRole.name'];

    /**
     * Queries the list of users for the Manage Users page.
     *
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryAllUsers(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.surname', 'pupilsightPerson.preferredName', 'pupilsightPerson.username',
                'pupilsightPerson.image_240', 'pupilsightPerson.status', 'pupilsightRole.name as primaryRole'
            ])
            ->leftJoin('pupilsightRole', 'pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID')
            ->where('pupilsightPerson.is_delete = "0" ')
            ->orderBy(['pupilsightPerson.pupilsightPersonID DESC']);

        $criteria->addFilterRules($this->getSharedUserFilterRules());

        return $this->runQuery($query, $criteria);
    }

    /**
     * Selects the family info for a subset of users. Primarily used to join family data to the queryAllUsers results.
     *
     * @param string|array $pupilsightPersonIDList
     * @return Result
     */
    public function selectFamilyDetailsByPersonID($pupilsightPersonIDList)
    {
        $idList = is_array($pupilsightPersonIDList) ? implode(',', $pupilsightPersonIDList) : $pupilsightPersonIDList;
        $data = array('idList' => $idList);
        $sql = "(
            SELECT LPAD(pupilsightFamilyAdult.pupilsightPersonID, 10, '0'), pupilsightFamilyAdult.pupilsightFamilyID, 'adult' AS role, pupilsightFamily.name, (SELECT pupilsightFamilyChild.pupilsightPersonID FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID ORDER BY pupilsightPerson.dob DESC LIMIT 1) as pupilsightPersonIDStudent
            FROM pupilsightFamily 
            JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) 
            WHERE FIND_IN_SET(pupilsightFamilyAdult.pupilsightPersonID, :idList)
        ) UNION (
            SELECT LPAD(pupilsightFamilyChild.pupilsightPersonID, 10, '0'), pupilsightFamilyChild.pupilsightFamilyID, 'child' AS role, pupilsightFamily.name, pupilsightFamilyChild.pupilsightPersonID as pupilsightPersonIDStudent
            FROM pupilsightFamily 
            JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) 
            WHERE FIND_IN_SET(pupilsightFamilyChild.pupilsightPersonID, :idList)
        ) ORDER BY pupilsightFamilyID";

        return $this->db()->select($sql, $data);
    }

    public function selectUserNamesByStatus($status = 'Full')
    {
        $data = array('statusList' => is_array($status) ? implode(',', $status) : $status);
        $sql = "SELECT pupilsightPersonID, surname, preferredName, status, username, pupilsightRole.category as roleCategory
                FROM pupilsightPerson 
                JOIN pupilsightRole ON (pupilsightRole.pupilsightRoleID=pupilsightPerson.pupilsightRoleIDPrimary)
                WHERE FIND_IN_SET(pupilsightPerson.status, :statusList) 
                ORDER BY surname, preferredName";

        return $this->db()->select($sql, $data);
    }

    public function selectNotificationDetailsByPerson($pupilsightPersonID)
    {
        $pupilsightPersonIDList = is_array($pupilsightPersonID) ? $pupilsightPersonID : [$pupilsightPersonID];

        $data = ['pupilsightPersonIDList' => implode(',', $pupilsightPersonIDList)];
        $sql = "SELECT pupilsightPerson.pupilsightPersonID as groupBy, pupilsightPerson.pupilsightPersonID, title, surname, preferredName, pupilsightPerson.status, image_240, username, email, phone1, phone1CountryCode, phone1Type, pupilsightRole.category as roleCategory, pupilsightStaff.jobTitle, pupilsightStaff.type
                FROM pupilsightPerson 
                JOIN pupilsightRole ON (pupilsightRole.pupilsightRoleID=pupilsightPerson.pupilsightRoleIDPrimary)
                LEFT JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                WHERE FIND_IN_SET(pupilsightPerson.pupilsightPersonID, :pupilsightPersonIDList) 
                ORDER BY FIND_IN_SET(pupilsightPerson.pupilsightPersonID, :pupilsightPersonIDList), surname, preferredName";

        return $this->db()->select($sql, $data);
    }

    public function queryAllStudents(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightPerson.pupilsightPersonID', 'pupilsightPerson.surname', 'pupilsightPerson.preferredName', 'pupilsightPerson.username',
                'pupilsightPerson.image_240', 'pupilsightPerson.status', 'pupilsightRole.name as primaryRole'
            ])
            ->leftJoin('pupilsightRole', 'pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID')
            ->where('pupilsightPerson.pupilsightRoleIDPrimary = 003');

        $criteria->addFilterRules($this->getSharedUserFilterRules());

        return $this->runQuery($query, $criteria);
    }
}
