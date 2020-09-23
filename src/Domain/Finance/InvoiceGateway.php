<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\Finance;

use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

/**
 * Invoice Gateway
 *
 * @version v16
 * @since   v16
 */
class InvoiceGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'pupilsightFinanceInvoice';

    private static $searchableColumns = [];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryInvoicesByYear(QueryCriteria $criteria, $pupilsightSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'pupilsightFinanceInvoice.pupilsightFinanceInvoiceID', 
                'pupilsightFinanceInvoice.invoiceTo',  
                'pupilsightFinanceInvoice.status', 
                'pupilsightFinanceInvoice.invoiceIssueDate', 
                'pupilsightFinanceInvoice.paidDate', 
                'pupilsightFinanceInvoice.paidAmount', 
                'pupilsightFinanceInvoice.notes', 
                'pupilsightPerson.surname', 
                'pupilsightPerson.preferredName', 
                'pupilsightRollGroup.name AS rollGroup',
                "(CASE 
                    WHEN pupilsightFinanceInvoice.status = 'Pending' AND billingScheduleType='Scheduled' THEN pupilsightFinanceBillingSchedule.invoiceDueDate 
                    ELSE pupilsightFinanceInvoice.invoiceDueDate END
                ) AS invoiceDueDate", 
                "(CASE 
                    WHEN pupilsightFinanceInvoice.status = 'Pending' AND billingScheduleType='Scheduled' THEN pupilsightFinanceBillingSchedule.name
                    WHEN billingScheduleType='Ad Hoc' THEN 'Ad Hoc'
                    ELSE pupilsightFinanceBillingSchedule.name END
                ) AS billingSchedule", 
                "FIND_IN_SET(pupilsightFinanceInvoice.status, 'Pending,Issued,Paid,Refunded,Cancelled') as defaultSortOrder"
            ])
            ->innerJoin('pupilsightFinanceInvoicee', 'pupilsightFinanceInvoice.pupilsightFinanceInvoiceeID=pupilsightFinanceInvoicee.pupilsightFinanceInvoiceeID')
            ->innerJoin('pupilsightPerson', 'pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID')
            ->leftJoin('pupilsightFinanceBillingSchedule', 'pupilsightFinanceInvoice.pupilsightFinanceBillingScheduleID=pupilsightFinanceBillingSchedule.pupilsightFinanceBillingScheduleID')
            ->leftJoin('pupilsightStudentEnrolment', 'pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightFinanceInvoice.pupilsightSchoolYearID')
            ->leftJoin('pupilsightRollGroup', 'pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID')
            ->where('pupilsightFinanceInvoice.pupilsightSchoolYearID = :pupilsightSchoolYearID')
            ->bindValue('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->groupBy(['pupilsightFinanceInvoice.pupilsightFinanceInvoiceID']);

        $criteria->addFilterRules([
            'status' => function ($query, $status) {
                switch ($status) {
                    case 'Issued':     
                        $query->where('pupilsightFinanceInvoice.invoiceDueDate >= :today')
                              ->bindValue('today', date('Y-m-d')); break;

                    case 'Issued - Overdue': 
                        $status = 'Issued';
                        $query->where('pupilsightFinanceInvoice.invoiceDueDate < :today')
                              ->bindValue('today', date('Y-m-d')); break;

                    case 'Paid': 
                        $query->where('pupilsightFinanceInvoice.invoiceDueDate >= pupilsightFinanceInvoice.paidDate'); break;

                    case 'Paid - Late': 
                        $status = 'Paid';
                        $query->where('pupilsightFinanceInvoice.invoiceDueDate < pupilsightFinanceInvoice.paidDate'); break;
                }

                return $query
                    ->where('pupilsightFinanceInvoice.status LIKE :status')
                    ->bindValue('status', $status);
            },

            'invoicee' => function ($query, $pupilsightFinanceInvoiceeID) {
                return $query
                    ->where('pupilsightFinanceInvoice.pupilsightFinanceInvoiceeID = :pupilsightFinanceInvoiceeID')
                    ->bindValue('pupilsightFinanceInvoiceeID', $pupilsightFinanceInvoiceeID);
            },

            'month' => function ($query, $monthOfIssue) {
                return $query
                    ->where('MONTH(pupilsightFinanceInvoice.invoiceIssueDate) = :monthOfIssue')
                    ->bindValue('monthOfIssue', $monthOfIssue);
            },

            'billingSchedule' => function ($query, $pupilsightFinanceBillingScheduleID) {
                if ($pupilsightFinanceBillingScheduleID == 'Ad Hoc') {
                    return $query->where('pupilsightFinanceInvoice.billingScheduleType = "Ad Hoc"');
                } else {
                    return $query
                        ->where('pupilsightFinanceInvoice.pupilsightFinanceBillingScheduleID = :pupilsightFinanceBillingScheduleID')
                        ->bindValue('pupilsightFinanceBillingScheduleID', $pupilsightFinanceBillingScheduleID);
                }
            },

            'feeCategory' => function ($query, $pupilsightFinanceFeeCategoryID) {
                return $query
                    ->leftJoin('pupilsightFinanceInvoiceFee', 'pupilsightFinanceInvoiceFee.pupilsightFinanceInvoiceID=pupilsightFinanceInvoice.pupilsightFinanceInvoiceID')
                    ->leftJoin('pupilsightFinanceFee', 'pupilsightFinanceInvoiceFee.pupilsightFinanceFeeID=pupilsightFinanceFee.pupilsightFinanceFeeID')
                    ->where(function($query) {
                        $query->where('pupilsightFinanceInvoiceFee.pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID')
                              ->orWhere("(pupilsightFinanceInvoiceFee.separated='N' AND pupilsightFinanceFee.pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID)");
                    })
                    ->bindValue('pupilsightFinanceFeeCategoryID', $pupilsightFinanceFeeCategoryID);
            },
        ]);

        return $this->runQuery($query, $criteria);
    }
}
