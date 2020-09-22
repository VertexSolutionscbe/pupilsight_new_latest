<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoicees_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Invoicees'), 'invoicees_manage.php')
        ->add(__('Edit Invoicee'));        

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    if ($_GET['search'] != '' or $_GET['allUsers'] == 'on') {
        echo "<div class='linkTop'>";
        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/invoicees_manage.php&search='.$_GET['search'].'&allUsers='.$_GET['allUsers']."'>".__('Back to Search Results').'</a>';
        echo '</div>';
    }

    //Check if school year specified
    $pupilsightFinanceInvoiceeID = $_GET['pupilsightFinanceInvoiceeID'];
    if ($pupilsightFinanceInvoiceeID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID);
            $sql = 'SELECT surname, preferredName, status, pupilsightFinanceInvoicee.* FROM pupilsightFinanceInvoicee JOIN pupilsightPerson ON (pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();

            echo "<table class='table' class='smallIntBorder' cellspacing='0' style='width: 100%'>";
            echo '<tr>';
            echo "<td style='width: 34%; vertical-align: top'>";
            echo "<span style='font-size: 115%; font-weight: bold'>".__('Name').'</span><br/>';
            echo formatName('', $values['preferredName'], $values['surname'], 'Student');
            echo '</td>';
            echo "<td style='width: 33%; vertical-align: top'>";
            echo "<span style='font-size: 115%; font-weight: bold'>".__('Status').'</span><br/>';
            echo '<i>'.$values['status'].'</i>';
            echo '</td>';
            echo "<td style='width: 34%; vertical-align: top'>";

            echo '</td>';
            echo '</tr>';
            echo '</table>';

            $form = Form::create('updateFinance', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/invoicees_manage_editProcess.php?pupilsightFinanceInvoiceeID=$pupilsightFinanceInvoiceeID&search=".$_GET['search'].'&allUsers='.$_GET['allUsers']);

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('existing', isset($values['pupilsightFinanceInvoiceeUpdateID'])? $values['pupilsightFinanceInvoiceeUpdateID'] : 'N');

            $form->addRow()->addHeading(__('Invoice To'));

            $form->addRow()->addContent(__('If you choose family, future invoices will be sent according to your family\'s contact preferences, which can be changed at a later date by contacting the school. For example you may wish both parents to receive the invoice, or only one. Alternatively, if you choose Company, you can choose for all or only some fees to be covered by the specified company.'))->wrap('<p>', '</p>');

            $row = $form->addRow();
                $row->addLabel('invoiceTo', __('Send Invoices To'));
                $row->addRadio('invoiceTo')
                    ->fromArray(array('Family' => __('Family'), 'Company' => __('Company')))
                    ->inline();

            $form->toggleVisibilityByClass('paymentCompany')->onRadio('invoiceTo')->when('Company');

            // COMPANY DETAILS
            $row = $form->addRow()->addClass('paymentCompany');
                $row->addLabel('companyName', __('Company Name'));
                $row->addTextField('companyName')->required()->maxLength(100);

            $row = $form->addRow()->addClass('paymentCompany');
                $row->addLabel('companyContact', __('Company Contact Person'));
                $row->addTextField('companyContact')->required()->maxLength(100);

            $row = $form->addRow()->addClass('paymentCompany');
                $row->addLabel('companyAddress', __('Company Address'));
                $row->addTextField('companyAddress')->required()->maxLength(255);

            $row = $form->addRow()->addClass('paymentCompany');
                $row->addLabel('companyEmail', __('Company Emails'))->description(__('Comma-separated list of email address'));
                $row->addTextField('companyEmail')->required();

            $row = $form->addRow()->addClass('paymentCompany');
                $row->addLabel('companyCCFamily', __('CC Family?'))->description(__('Should the family be sent a copy of billing emails?'));
                $row->addYesNo('companyCCFamily')->selected('N');

            $row = $form->addRow()->addClass('paymentCompany');
                $row->addLabel('companyPhone', __('Company Phone'));
                $row->addTextField('companyPhone')->maxLength(20);

            // COMPANY FEE CATEGORIES
            $sqlFees = "SELECT pupilsightFinanceFeeCategoryID as value, name FROM pupilsightFinanceFeeCategory WHERE active='Y' AND NOT pupilsightFinanceFeeCategoryID=1 ORDER BY name";
            $resultFees = $pdo->executeQuery(array(), $sqlFees);

            $form->loadAllValuesFrom($values);

            if (!$resultFees || $resultFees->rowCount() == 0) {
                $form->addHiddenValue('companyAll', 'Y');
            } else {
                $checked = (empty($values['companyAll']) || $values['companyAll'] == 'Y') ? 'Y' : 'N';
                $row = $form->addRow()->addClass('paymentCompany');
                    $row->addLabel('companyAll', __('Company All?'))->description(__('Should all items be billed to the specified company, or just some?'));
                    $row->addRadio('companyAll')->fromArray(array('Y' => __('All'), 'N' => __('Selected')))->checked($checked)->inline();

                $form->toggleVisibilityByClass('paymentCompanyCategories')->onRadio('companyAll')->when('N');

                $row = $form->addRow()->addClass('paymentCompany')->addClass('paymentCompanyCategories');
                $row->addLabel('pupilsightFinanceFeeCategoryIDList[]', __('Company Fee Categories'))
                    ->description(__('If the specified company is not paying all fees, which categories are they paying?'));
                $row->addCheckbox('pupilsightFinanceFeeCategoryIDList[]')
                    ->fromResults($resultFees)
                    ->fromArray(array('0001' => __('Other')))
                    ->loadFromCSV($values);
            }

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();


            echo $form->getOutput();

        }
    }
}
?>
