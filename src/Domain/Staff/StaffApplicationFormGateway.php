<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Staff;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * StaffApplicationForm Gateway
 *
 * @version v16
 * @since   v16
 */
class StaffApplicationFormGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightStaffApplicationForm';

    private static $searchableColumns = ['pupilsightStaffApplicationFormID', 'pupilsightStaffApplicationForm.preferredName', 'pupilsightStaffApplicationForm.surname', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname', 'pupilsightStaffJobOpening.jobTitle'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryApplications(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightStaffApplicationForm.pupilsightStaffApplicationFormID', 'pupilsightStaffApplicationForm.status', 'pupilsightStaffApplicationForm.priority', 'pupilsightStaffApplicationForm.timestamp', 'milestones', 'pupilsightStaffJobOpening.jobTitle', 'pupilsightStaffApplicationForm.pupilsightPersonID', 'pupilsightStaffApplicationForm.surname as applicationSurname', 'pupilsightStaffApplicationForm.preferredName as applicationPreferredName', 'pupilsightPerson.surname', 'pupilsightPerson.preferredName'
            ])
            ->innerJoin('pupilsightStaffJobOpening', 'pupilsightStaffApplicationForm.pupilsightStaffJobOpeningID=pupilsightStaffJobOpening.pupilsightStaffJobOpeningID')
            ->leftJoin('pupilsightPerson', 'pupilsightStaffApplicationForm.pupilsightPersonID=pupilsightPerson.pupilsightPersonID');

        return $this->runQuery($query, $criteria);
    }
}
