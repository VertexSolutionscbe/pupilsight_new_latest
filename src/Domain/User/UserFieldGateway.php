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
class UserFieldGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightPersonField';

    private static $searchableColumns = ['name'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryUserFields(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightPersonFieldID', 'name', 'type', 'active', 'activePersonStudent', 'activePersonParent', 'activePersonStaff', 'activePersonOther'
            ]);
        
        $criteria->addFilterRules([
            'active' => function ($query, $active) {
                return $query
                    ->where('pupilsightPersonField.active = :active')
                    ->bindValue('active', ucfirst($active));
            },

            'role' => function ($query, $roleCategory) {
                $field = 'activePersonStudent';
                switch ($roleCategory) {
                    case 'student': $field = 'activePersonStudent'; break;
                    case 'parent':  $field = 'activePersonParent'; break;
                    case 'staff':   $field = 'activePersonStaff'; break;
                    case 'other':   $field = 'activePersonOther'; break;
                }
                return $query->where('pupilsightPersonField.`'.$field.'` = 1');
            },
        ]);

        return $this->runQuery($query, $criteria);
    }
}
