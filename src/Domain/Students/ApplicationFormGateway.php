<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Students;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * @version v17
 * @since   v17
 */
class ApplicationFormGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightApplicationForm';

    private static $searchableColumns = ['pupilsightApplicationFormID', 'preferredName', 'surname', 'paymentTransactionID'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryApplicationFormsBySchoolYear(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightApplicationFormID', 'pupilsightApplicationForm.status', 'preferredName', 'surname', 'dob', 'priority', 'pupilsightApplicationForm.timestamp', 'milestones', 'pupilsightFamilyID', 'schoolName1', 'schoolDate1', 'schoolName2', 'schoolDate2', 'parent1title', 'parent1preferredName', 'parent1surname', 'parent1email', 'parent2title', 'parent2preferredName', 'parent2surname', 'parent2email', 'paymentMade','pupilsightYearGroup.name AS yearGroup', 'pupilsightPayment.paymentTransactionID'
            ])
            ->innerJoin('pupilsightYearGroup', 'pupilsightApplicationForm.pupilsightYearGroupIDEntry=pupilsightYearGroup.pupilsightYearGroupID')
            ->leftJoin('pupilsightPayment', "pupilsightApplicationForm.pupilsightPaymentID=pupilsightPayment.pupilsightPaymentID AND pupilsightPayment.foreignTable='pupilsightApplicationForm'")
            ->where('pupilsightApplicationForm.pupilsightSchoolYearIDEntry  = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        $criteria->addFilterRules([
            'status' => function ($query, $status) {
                return $query
                    ->where('pupilsightApplicationForm.status = :status')
                    ->bindValue('status', ucwords($status));
            },

            'paid' => function ($query, $paymentMade) {
                return $query
                    ->where('pupilsightApplicationForm.paymentMade = :paymentMade')
                    ->bindValue('paymentMade', ucfirst($paymentMade));
            },

            'rollGroup' => function ($query, $value) {
                return $query
                    ->where(strtoupper($value) == 'Y'
                        ? 'pupilsightApplicationForm.pupilsightRollGroupID IS NOT NULL'
                        : 'pupilsightApplicationForm.pupilsightRollGroupID IS NULL');
            },

            'yearGroup' => function ($query, $pupilsightYearGroupIDEntry) {
                return $query
                    ->where('pupilsightApplicationForm.pupilsightYearGroupIDEntry = :pupilsightYearGroupIDEntry')
                    ->bindValue('pupilsightYearGroupIDEntry', $pupilsightYearGroupIDEntry);
            },

        ]);

        return $this->runQuery($query, $criteria);
    }

    public function selectLinkedApplicationsByID($pupilsightApplicationFormID)
    {
        $data = array('pupilsightApplicationFormID' => $pupilsightApplicationFormID);
        $sql = "SELECT DISTINCT pupilsightApplicationFormID, preferredName, surname, status 
                FROM pupilsightApplicationForm
                JOIN pupilsightApplicationFormLink ON (
                    pupilsightApplicationForm.pupilsightApplicationFormID=pupilsightApplicationFormLink.pupilsightApplicationFormID1 OR pupilsightApplicationForm.pupilsightApplicationFormID=pupilsightApplicationFormLink.pupilsightApplicationFormID2)
                WHERE pupilsightApplicationFormID1=:pupilsightApplicationFormID
                OR pupilsightApplicationFormID2=:pupilsightApplicationFormID 
                ORDER BY pupilsightApplicationFormID";

        return $this->db()->select($sql, $data);
    }
}
