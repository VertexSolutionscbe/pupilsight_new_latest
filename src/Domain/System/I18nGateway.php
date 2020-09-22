<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\System;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v17
 * @since   v17
 */
class I18nGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsighti18n';

    private static $searchableColumns = ['name'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryI18n(QueryCriteria $criteria, $installed = 'Y')
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsighti18nID', 'name', 'code', 'active', 'version', 'systemDefault'
            ])
            ->where('installed = :installed')
            ->bindValue('installed', $installed);

        return $this->runQuery($query, $criteria);
    }

    public function selectActiveI18n()
    {
        $sql = "SELECT * FROM pupilsighti18n WHERE active='Y'";

        return $this->db()->select($sql);
    }

    public function getI18nByID($pupilsighti18nID)
    {
        $data = array('pupilsighti18nID' => $pupilsighti18nID);
        $sql = "SELECT * FROM pupilsighti18n WHERE pupilsighti18nID=:pupilsighti18nID";

        return $this->db()->selectOne($sql, $data);
    }

    public function updateI18nVersion($pupilsighti18nID, $installed, $version)
    {
        $data = array('pupilsighti18nID' => $pupilsighti18nID, 'installed' => $installed, 'version' => $version);
        $sql = "UPDATE pupilsighti18n SET installed=:installed, version=:version WHERE pupilsighti18nID=:pupilsighti18nID";

        return $this->db()->update($sql, $data);
    }
}
