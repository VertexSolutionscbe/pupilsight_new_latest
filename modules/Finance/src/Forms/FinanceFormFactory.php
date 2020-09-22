<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Finance\Forms;

use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Contracts\Database\Connection;

/**
 * FinanceFormFactory
 *
 * @version v16
 * @since   v16
 */
class FinanceFormFactory extends DatabaseFormFactory
{
    /**
     * Create and return an instance of DatabaseFormFactory.
     * @return  object DatabaseFormFactory
     */
    public static function create(Connection $pdo = null)
    {
        return new FinanceFormFactory($pdo);
    }

    public function createSelectInvoicee($name, $pupilsightSchoolYearID = '', $params = array())
    {
        // Check params and set defaults if not defined
        $params = array_replace(array('allStudents' => false), $params);

        $values = array();

        // Opt Groups
        if ($params['allStudents'] != true) {
            $byRollGroup = __('All Enrolled Students by Roll Group');
            $byName = __('All Enrolled Students by Alphabet');
        }
        else {
            $byRollGroup = __('All Students by Roll Group');
            $byName = __('All Students by Alphabet');
        }

        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        if ($params['allStudents'] != true) {
            $sql = "SELECT pupilsightFinanceInvoiceeID, preferredName, surname, pupilsightRollGroup.nameShort AS rollGroupName, dayType 
                FROM pupilsightPerson
                JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                WHERE pupilsightPerson.status='Full' 
                AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
                ORDER BY pupilsightRollGroup.name, surname, preferredName";
        }
        else {
            $sql = "SELECT pupilsightFinanceInvoiceeID, preferredName, surname, pupilsightRollGroup.nameShort AS rollGroupName, dayType 
                FROM pupilsightPerson
                JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
                ORDER BY pupilsightRollGroup.name, surname, preferredName";
        }

        $results = $this->pdo->executeQuery($data, $sql);
        $students = ($results->rowCount() > 0)? $results->fetchAll() : array();

        // Add students by Roll Group and Name
        foreach ($students as $student) {
            $fullName = formatName('', $student['preferredName'], $student['surname'], 'Student', true);

            $values[$byRollGroup][$student['pupilsightFinanceInvoiceeID']] = $student['rollGroupName'].' - '.$fullName;
            $values[$byName][$student['pupilsightFinanceInvoiceeID']] = $fullName.' - '.$student['rollGroupName'];
        }

        // Sort the byName list so it's not byRollGroup
        if (!empty($values[$byName]) && is_array($values[$byName])) {
            asort($values[$byName]);
        }

        // Add students by Day Type (optionally)
        $dayTypeOptions = getSettingByScope($this->pdo->getConnection(), 'User Admin', 'dayTypeOptions');
        if (!empty($dayTypeOptions)) {
            $dayTypes = explode(',', $dayTypeOptions);

            foreach ($students as $student) {
                if (empty($student['dayType']) || !in_array($student['dayType'], $dayTypes)) continue;

                $byDayType = $student['dayType'].' '.__('Students by Roll Groups');
                $fullName = formatName('', $student['preferredName'], $student['surname'], 'Student', true);
    
                $values[$byDayType][$student['pupilsightFinanceInvoiceeID']] = $student['rollGroupName'].' - '.$fullName;
            }
        }
                
        return $this->createSelect($name)->fromArray($values)->placeholder();
    }

    public function createSelectInvoiceStatus($name, $currentStatus = 'All')
    {
        if ($currentStatus == 'Pending' || $currentStatus == 'Cancelled' || $currentStatus == 'Refunded') {
            return $this->createTextField($name)->readonly()->setValue(__($currentStatus));
        }

        $statuses = array();
        if ($currentStatus == 'All') {
            $statuses = array(
                ''                => __('All'),
                'Pending'          => __('Pending'),
                'Issued'           => __('Issued'),
                'Issued - Overdue' => __('Issued - Overdue'),
                'Paid'             => __('Paid'),
                'Paid - Partial'   => __('Paid - Partial'),
                'Paid - Late'      => __('Paid - Late'),
                'Cancelled'        => __('Cancelled'),
                'Refunded'         => __('Refunded'),
            );
        } else if ($currentStatus == 'Issued') {
            $statuses = array(
                'Issued'         => __('Issued'),
                'Paid'           => __('Paid'),
                'Paid - Partial' => __('Paid - Partial'),
                'Cancelled'      => __('Cancelled'),
            );
        } else if ($currentStatus == 'Paid') {
            $statuses = array(
                'Paid'     => __('Paid'),
                'Refunded' => __('Refunded'),
            );
        } else if ($currentStatus == 'Paid - Partial') {
            $statuses = array(
                'Paid - Partial'  => __('Paid - Partial'),
                'Paid - Complete' => __('Paid - Complete'),
                'Refunded'        => __('Refunded'),
            );
        }

        return $this->createSelect($name)->fromArray($statuses);
    }

    public function createSelectBillingSchedule($name, $pupilsightSchoolYearID)
    {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT pupilsightFinanceBillingScheduleID as value, name FROM pupilsightFinanceBillingSchedule 
                WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name";

        return $this->createSelect($name)->fromQuery($this->pdo, $sql, $data)->placeholder();
    }

    public function createSelectFee($name, $pupilsightSchoolYearID)
    {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT pupilsightFinanceFeeCategory.name as groupBy, pupilsightFinanceFee.pupilsightFinanceFeeID as value, pupilsightFinanceFee.name 
                FROM pupilsightFinanceFee 
                JOIN pupilsightFinanceFeeCategory ON (pupilsightFinanceFee.pupilsightFinanceFeeCategoryID=pupilsightFinanceFeeCategory.pupilsightFinanceFeeCategoryID) 
                WHERE pupilsightFinanceFee.pupilsightSchoolYearID=:pupilsightSchoolYearID
                ORDER BY pupilsightFinanceFeeCategory.name, pupilsightFinanceFee.name";

        return $this->createSelect($name)
            ->fromArray(array('' => __('Choose a fee to add it')))
            ->fromArray(array('Ad Hoc Fee' => __('Ad Hoc Fee')))
            ->fromQuery($this->pdo, $sql, $data, 'groupBy');
    }

    public function createSelectFeeCategory($name)
    {
        $sql = "SELECT pupilsightFinanceFeeCategoryID as value, name FROM pupilsightFinanceFeeCategory WHERE active='Y' ORDER BY (pupilsightFinanceFeeCategoryID=1) DESC, name";

        return $this->createSelect($name)->fromQuery($this->pdo, $sql);
    }

    public function createSelectPaymentMethod($name)
    {
        $methods = array(
            'Online'        => __('Online'),
            'Bank Transfer' => __('Bank Transfer'),
            'Cash'          => __('Cash'),
            'Cheque'        => __('Cheque'),
            'Credit Card'   => __('Credit Card'),
            'Other'         => __('Other')
        );

        return $this->createSelect($name)->fromArray($methods)->placeholder();
    }

    public function createSelectMonth($name)
    {
        $months = array_reduce(range(1,12), function($group, $item){
            $month = date('m', mktime(0, 0, 0, $item, 1, 0));
            $group[$month] = $month.' - '.date('F', mktime(0, 0, 0, $item, 1, 0));
            return $group;
        }, array());

        return $this->createSelect($name)->fromArray($months)->placeholder();
    }

    public function createInvoiceEmailCheckboxes($checkboxName, $hiddenValueName, $values, $session) 
    {
        $table = $this->createTable()->setClass('fullWidth');

        // Company Emails
        if ($values['invoiceTo'] == 'Company') {
            if (empty($values['companyEmail']) || empty($values['companyContact']) || empty($values['companyName'])) {
                $table->addRow()->addTableCell(__('There is no company contact available to send this invoice to.'))->colSpan(2)->wrap('<div class="warning">', '</div>');
            } else {
                $row = $table->addRow();
                    $row->addLabel($checkboxName, $values['companyContact'])->description($values['companyName']);
                    $row->addCheckbox($checkboxName)
                        ->description($values['companyEmail'])
                        ->setValue($values['companyEmail'])
                        ->checked($values['companyEmail'])
                        ->append('<input type="hidden" name="'.$hiddenValueName.'" value="'.$values['companyContact'].'">');
            }
        }

        // Family Emails
        if ($values['invoiceTo'] == 'Family' || ($values['invoiceTo'] == 'Company' && $values['companyCCFamily'] == 'Y')) {
            $data = array('pupilsightFinanceInvoiceeID' => $values['pupilsightFinanceInvoiceeID']);
            $sql = "SELECT parent.title, parent.surname, parent.preferredName, parent.email, pupilsightFamilyRelationship.relationship
                    FROM pupilsightFinanceInvoicee 
                    JOIN pupilsightPerson AS student ON (pupilsightFinanceInvoicee.pupilsightPersonID=student.pupilsightPersonID) 
                    JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightPersonID=student.pupilsightPersonID) 
                    JOIN pupilsightFamilyAdult ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamilyAdult.pupilsightFamilyID) 
                    JOIN pupilsightPerson AS parent ON (pupilsightFamilyAdult.pupilsightPersonID=parent.pupilsightPersonID) 
                    LEFT JOIN pupilsightFamilyRelationship ON (pupilsightFamilyRelationship.pupilsightFamilyID=pupilsightFamilyChild.pupilsightFamilyID && pupilsightFamilyRelationship.pupilsightPersonID1=parent.pupilsightPersonID && pupilsightFamilyRelationship.pupilsightPersonID2=student.pupilsightPersonID)
                    WHERE pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID 
                    AND (contactPriority=1 OR (contactPriority=2 AND contactEmail='Y')) 
                    AND parent.status='Full'
                    GROUP BY parent.pupilsightPersonID
                    ORDER BY contactPriority, surname, preferredName";

            $result = $this->pdo->executeQuery($data, $sql);

            if ($result->rowCount() == 0) {
                $table->addRow()->addTableCell(__('There are no family members available to send this receipt to.'))->colSpan(2)->wrap('<div class="warning">', '</div>');
            } else {
                while ($person = $result->fetch()) {
                    $name = formatName(htmlPrep($person['title']), htmlPrep($person['preferredName']), htmlPrep($person['surname']), 'Parent', false);
                    $row = $table->addRow();
                        $row->addLabel($checkboxName, $name)->description($values['invoiceTo'] == 'Company'? __('(Family CC)') : '')->description($person['relationship']);
                        $row->onlyIf(!empty($person['email']))
                            ->addCheckbox($checkboxName)
                            ->description($person['email'])
                            ->setValue($person['email'])
                            ->checked($person['email'])
                            ->append('<input type="hidden" name="'.$hiddenValueName.'" value="'.$name.'">');
                        $row->onlyIf(empty($person['email']))
                            ->addContent(__('No email address.'))
                            ->addClass('right')
                            ->wrap('<span class="small emphasis">', '</span>');
                }
            }
        }

        // CC Self
        if (!empty($session->get('email'))) {
            $name = formatName('', htmlPrep($session->get('preferredName')), htmlPrep($session->get('surname')), 'Parent', false);
            $row = $table->addRow()->addClass('emailReceiptSection');
                $row->addLabel($checkboxName, $name)->description(__('(CC Self?)'));
                $row->addCheckbox($checkboxName)
                    ->description($session->get('email'))
                    ->setValue($session->get('email'))
                    ->append('<input type="hidden" name="'.$hiddenValueName.'" value="'.$name.'">');
        }

        return $table;
    }
}
