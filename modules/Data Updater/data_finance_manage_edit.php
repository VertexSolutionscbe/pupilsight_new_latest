<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_finance_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = isset($_REQUEST['pupilsightSchoolYearID'])? $_REQUEST['pupilsightSchoolYearID'] : $_SESSION[$guid]['pupilsightSchoolYearID'];

    $urlParams = ['pupilsightSchoolYearID' => $pupilsightSchoolYearID];
    
    $page->breadcrumbs
        ->add(__('Finance Data Updates'), 'data_finance_manage.php', $urlParams)
        ->add(__('Edit Request'));    

    //Check if school year specified
    $pupilsightFinanceInvoiceeUpdateID = $_GET['pupilsightFinanceInvoiceeUpdateID'];
    if ($pupilsightFinanceInvoiceeUpdateID == 'Y') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightFinanceInvoiceeUpdateID' => $pupilsightFinanceInvoiceeUpdateID);
            $sql = "SELECT pupilsightFinanceInvoicee.* FROM pupilsightFinanceInvoiceeUpdate JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoiceeUpdate.pupilsightFinanceInvoiceeID=pupilsightFinanceInvoicee.pupilsightFinanceInvoiceeID) WHERE pupilsightFinanceInvoiceeUpdateID=:pupilsightFinanceInvoiceeUpdateID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            $data = array('pupilsightFinanceInvoiceeUpdateID' => $pupilsightFinanceInvoiceeUpdateID);
            $sql = "SELECT pupilsightFinanceInvoiceeUpdate.* FROM pupilsightFinanceInvoiceeUpdate JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoiceeUpdate.pupilsightFinanceInvoiceeID=pupilsightFinanceInvoicee.pupilsightFinanceInvoiceeID) WHERE pupilsightFinanceInvoiceeUpdateID=:pupilsightFinanceInvoiceeUpdateID";
            $newResult = $pdo->executeQuery($data, $sql);
            
            //Let's go!
            $oldValues = $result->fetch();
            $newValues = $newResult->fetch();

            // Provide a link back to edit the associated record
            if (isActionAccessible($guid, $connection2, '/modules/Finance/invoicees_manage_edit.php') == true && !empty($oldValues['pupilsightFinanceInvoiceeID'])) {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Finance/invoicees_manage_edit.php&pupilsightFinanceInvoiceeID=".$oldValues['pupilsightFinanceInvoiceeID']."&search=&allUsers='>".__('Edit Invoicee')."<img style='margin: 0 0 -4px 5px' title='".__('Edit Invoicee')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                echo '</div>';
            }
            
            // An array of common fields to compare in each data set, and the field label
            $compare = array(
                'invoiceTo'                      => __('Invoice To'),
                'companyName'                    => __('Company Name'),
                'companyContact'                 => __('Company Contact Person'),
                'companyAddress'                 => __('Company Address'),
                'companyEmail'                   => __('Company Email'),
                'companyCCFamily'                => __('CC Family?'),
                'companyPhone'                   => __('Company Phone'),
                'companyAll'                     => __('Company All?'),
                'pupilsightFinanceFeeCategoryIDList' => __('Company Fee Categories'),
            );

            $form = Form::create('updateFinance', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/data_finance_manage_editProcess.php?pupilsightFinanceInvoiceeUpdateID='.$pupilsightFinanceInvoiceeUpdateID);
            
            $form->setClass('fullWidth colorOddEven');
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightFinanceInvoiceeID', $oldValues['pupilsightFinanceInvoiceeID']);

            $row = $form->addRow()->setClass('head heading');
                $row->addContent(__('Field'));
                $row->addContent(__('Current Value'));
                $row->addContent(__('New Value'));
                $row->addContent(__('Accept'));

            foreach ($compare as $fieldName => $label) {
                $isMatching = ($oldValues[$fieldName] != $newValues[$fieldName]);

                $row = $form->addRow();
                $row->addLabel('new'.$fieldName.'On', $label);
                $row->addContent($oldValues[$fieldName]);
                $row->addContent($newValues[$fieldName])->addClass($isMatching ? 'matchHighlightText' : '');
                
                if ($isMatching) {
                    $row->addCheckbox('new'.$fieldName.'On')->checked(true)->setClass('textCenter');
                    $form->addHiddenValue('new'.$fieldName, $newValues[$fieldName]);
                } else {
                    $row->addContent();
                }
            }
            
            $row = $form->addRow();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
