<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Timetable;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v16
 * @since   v16
 */
class FacilityBookingGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightTTSpaceBooking';

    private static $searchableColumns = [''];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryFacilityBookings(QueryCriteria $criteria, $pupilsightPersonID = null)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightTTSpaceBookingID', 'date', 'timeStart', 'timeEnd', 'pupilsightSpace.name', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname', 'foreignKey', 'foreignKeyID'
            ])
            ->innerJoin('pupilsightSpace', 'pupilsightTTSpaceBooking.foreignKeyID=pupilsightSpace.pupilsightSpaceID')
            ->innerJoin('pupilsightPerson', 'pupilsightTTSpaceBooking.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->where("foreignKey='pupilsightSpaceID'")
            ->where('date >= :today')
            ->bindValue('today', date('Y-m-d'));

        if (!empty($pupilsightPersonID)) {
            $query->where('pupilsightTTSpaceBooking.pupilsightPersonID = :pupilsightPersonID')
                  ->bindValue('pupilsightPersonID', $pupilsightPersonID);
        }

        $query->unionAll()
            ->from($this->getTableName())
            ->cols([
                'pupilsightTTSpaceBookingID', 'date', 'timeStart', 'timeEnd', 'pupilsightLibraryItem.name', 'pupilsightPerson.preferredName', 'pupilsightPerson.surname', 'foreignKey', 'foreignKeyID'
            ])
            ->innerJoin('pupilsightLibraryItem', 'pupilsightTTSpaceBooking.foreignKeyID=pupilsightLibraryItem.pupilsightLibraryItemID')
            ->innerJoin('pupilsightPerson', 'pupilsightTTSpaceBooking.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->where("foreignKey='pupilsightLibraryItemID'")
            ->where('date >= :today')
            ->bindValue('today', date('Y-m-d'));

        if (!empty($pupilsightPersonID)) {
            $query->where('pupilsightTTSpaceBooking.pupilsightPersonID = :pupilsightPersonID')
                  ->bindValue('pupilsightPersonID', $pupilsightPersonID);
        }

        return $this->runQuery($query, $criteria);
    }
}
