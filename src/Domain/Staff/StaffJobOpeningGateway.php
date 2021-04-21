<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Staff;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * StaffJobOpening Gateway
 *
 * @version v16
 * @since   v16
 */
class StaffJobOpeningGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightStaffJobOpening';

    private static $searchableColumns = [];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryJobOpenings(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightStaffJobOpening.pupilsightStaffJobOpeningID', 'pupilsightStaffJobOpening.type', 'pupilsightStaffJobOpening.jobTitle', 'pupilsightStaffJobOpening.dateOpen', 'pupilsightStaffJobOpening.active', 'pupilsightStaffJobOpening.description','pupilsightRole.name as typeName'
            ])
            ->leftJoin('pupilsightRole', 'pupilsightStaffJobOpening.type=pupilsightRole.pupilsightRoleID')
            ->orderBy(['pupilsightStaffJobOpening.pupilsightStaffJobOpeningID DESC']);;

        $criteria->addFilterRules([
            'active' => function ($query, $active) {
                return $query
                    ->where('pupilsightStaffJobOpening.active = :active')
                    ->bindValue('active', ucfirst($active));
            },
        ]);

        return $this->runQuery($query, $criteria, true);
    }
}
