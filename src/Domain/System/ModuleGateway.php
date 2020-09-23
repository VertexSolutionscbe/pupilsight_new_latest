<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\System;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * Module Gateway
 *
 * @version v16
 * @since   v16
 */
class ModuleGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightModule';

    private static $searchableColumns = ['name'];
    
    /**
     * Queries the list for the Manage Modules page.
     *
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryModules(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightModuleID', 'name', 'description', 'type', 'author', 'url', 'active', 'version'
            ]);

        $criteria->addFilterRules([
            'type' => function ($query, $type) {
                return $query
                    ->where('pupilsightModule.type = :type')
                    ->bindValue('type', ucfirst($type));
            },

            'active' => function ($query, $active) {
                return $query
                    ->where('pupilsightModule.active = :active')
                    ->bindValue('active', ucfirst($active));
            },
        ]);

        return $this->runQuery($query, $criteria);
    }

    /**
     * Gets an unfiltered list of all modules.
     *
     * @return array
     */
    public function getAllModuleNames()
    {
        $sql = "SELECT name FROM pupilsightModule";

        return $this->db()->select($sql)->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function selectModulesByRole($pupilsightRoleID)
    {
        $mainMenuCategoryOrder = getSettingByScope($this->db()->getConnection(), 'System', 'mainMenuCategoryOrder');

        $data = array('pupilsightRoleID' => $pupilsightRoleID, 'menuOrder' => $mainMenuCategoryOrder);
        $sql = "SELECT pupilsightModule.category, pupilsightModule.name, pupilsightModule.type, pupilsightModule.entryURL, URLList,pupilsightAction.entryURL as alternateEntryURL, (CASE WHEN pupilsightModule.type <> 'Core' THEN pupilsightModule.name ELSE NULL END) as textDomain
                FROM pupilsightModule 
                JOIN pupilsightAction ON (pupilsightAction.pupilsightModuleID=pupilsightModule.pupilsightModuleID) 
                JOIN pupilsightPermission ON (pupilsightPermission.pupilsightActionID=pupilsightAction.pupilsightActionID) 
                WHERE pupilsightModule.active='Y' 
                AND pupilsightAction.menuShow='Y' 
                AND pupilsightPermission.pupilsightRoleID=:pupilsightRoleID 
                GROUP BY pupilsightModule.name 
                ORDER BY FIND_IN_SET(pupilsightModule.category, :menuOrder), pupilsightModule.category, pupilsightModule.name, pupilsightAction.name";

        return $this->db()->select($sql, $data);
    }

    public function selectModuleActionsByRole($pupilsightRoleID, $pupilsightModuleID)
    {
        $data = array('pupilsightModuleID' => $pupilsightRoleID, 'pupilsightRoleID' => $pupilsightModuleID);
        $sql = "SELECT pupilsightAction.category, pupilsightModule.entryURL AS moduleEntry, pupilsightModule.name AS moduleName, pupilsightAction.name as actionName,pupilsightAction.order_wise, pupilsightModule.type, pupilsightAction.precedence, pupilsightAction.entryURL, URLList, SUBSTRING_INDEX(pupilsightAction.name, '_', 1) as name, (CASE WHEN pupilsightModule.type <> 'Core' THEN pupilsightModule.name ELSE NULL END) AS textDomain
                FROM pupilsightModule
                JOIN pupilsightAction ON (pupilsightModule.pupilsightModuleID=pupilsightAction.pupilsightModuleID)
                JOIN pupilsightPermission ON (pupilsightAction.pupilsightActionID=pupilsightPermission.pupilsightActionID)
                WHERE (pupilsightModule.pupilsightModuleID=:pupilsightModuleID)
                AND (pupilsightPermission.pupilsightRoleID=:pupilsightRoleID)
                AND NOT pupilsightAction.entryURL=''
                AND pupilsightAction.menuShow='Y'
                GROUP BY name
                ORDER BY pupilsightModule.name, pupilsightAction.category, pupilsightAction.name, precedence DESC";

        return $this->db()->select($sql, $data);
    }
}
