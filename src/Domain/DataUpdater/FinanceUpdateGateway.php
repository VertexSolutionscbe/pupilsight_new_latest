<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\DataUpdater;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v16
 * @since   v16
 */
class FinanceUpdateGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightFinanceInvoiceeUpdate';

    private static $searchableColumns = [''];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryDataUpdates(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightFinanceInvoiceeUpdateID', 'pupilsightFinanceInvoiceeUpdate.status', 'pupilsightFinanceInvoiceeUpdate.timestamp', 'target.preferredName', 'target.surname', 'updater.title as updaterTitle', 'updater.preferredName as updaterPreferredName', 'updater.surname as updaterSurname'
            ])
            ->leftJoin('pupilsightFinanceInvoicee', 'pupilsightFinanceInvoicee.pupilsightFinanceInvoiceeID=pupilsightFinanceInvoiceeUpdate.pupilsightFinanceInvoiceeID')
            ->leftJoin('pupilsightPerson AS target', 'target.pupilsightPersonID=pupilsightFinanceInvoicee.pupilsightPersonID')
            ->leftJoin('pupilsightPerson AS updater', 'updater.pupilsightPersonID=pupilsightFinanceInvoiceeUpdate.pupilsightPersonIDUpdater')
            ->where('pupilsightFinanceInvoiceeUpdate.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        return $this->runQuery($query, $criteria);
    }
}
