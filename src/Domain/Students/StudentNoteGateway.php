<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Students;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v16
 * @since   v16
 */
class StudentNoteGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightStudentNote';
    private static $primaryKey = 'pupilsightStudentNoteID';

    private static $searchableColumns = ['name'];

    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryStudentNoteCategories(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('pupilsightStudentNoteCategory')
            ->cols([
                'pupilsightStudentNoteCategoryID', 'name', 'template', 'active'
            ]);


        return $this->runQuery($query, $criteria);
    }

    public function getNoteCategoryIDByName($name)
    {
        $data = ['name' => $name];
        $sql = "SELECT pupilsightStudentNoteCategoryID FROM pupilsightStudentNoteCategory WHERE name=:name";

        return $this->db()->selectOne($sql, $data);
    }
}
