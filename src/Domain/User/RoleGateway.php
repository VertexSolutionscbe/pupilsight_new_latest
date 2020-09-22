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
class RoleGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightRole';

    private static $searchableColumns = ['name', 'nameShort'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryRoles(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightRoleID', 'name', 'nameShort', 'category', 'description', 'type', 'canLoginRole', 'futureYearsLogin', 'pastYearsLogin'
            ]);

        return $this->runQuery($query, $criteria);
    }

    public function getRoleByID($pupilsightRoleID)
    {
        $data = array('pupilsightRoleID' => $pupilsightRoleID);
        $sql = "SELECT * FROM pupilsightRole WHERE pupilsightRoleID=:pupilsightRoleID";

        return $this->db()->selectOne($sql, $data);
    }

    public function selectAllRolesByPerson($pupilsightPersonID)
    {
        $data = array('pupilsightPersonID' => $pupilsightPersonID);
        $sql = "SELECT pupilsightRoleID AS groupBy, pupilsightRole.* 
                FROM pupilsightPerson 
                JOIN pupilsightRole ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll))
                WHERE pupilsightPersonID=:pupilsightPersonID";

        return $this->db()->select($sql, $data);
    }
}
