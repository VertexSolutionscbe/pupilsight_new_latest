<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\System;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * Setting Gateway
 *
 * @version v17
 * @since   v17
 */
class SettingGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightSetting';

    private static $searchableColumns = ['scope', 'name'];
    
    public function getSettingByScope($scope, $name, $returnRow = false)
    {
        $data = ['scope' => $scope, 'name' => $name];
        $sql = $returnRow
            ? "SELECT * FROM pupilsightSetting WHERE scope=:scope AND name=:name"
            : "SELECT value FROM pupilsightSetting WHERE scope=:scope AND name=:name";

        return $this->db()->selectOne($sql, $data);
    }

    public function getAllSettingsByScope($scope)
    {
        $data = ['scope' => $scope];
        $sql = "SELECT * FROM pupilsightSetting WHERE scope=:scope ORDER BY name";

        return $this->db()->select($sql, $data)->fetchAll();
    }

    public function updateSettingByScope($scope, $name, $value)
    {
        $data = ['scope' => $scope, 'name' => $name, 'value' => $value];
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope=:scope AND name=:name";

        return $this->db()->update($sql, $data);
    }
}
