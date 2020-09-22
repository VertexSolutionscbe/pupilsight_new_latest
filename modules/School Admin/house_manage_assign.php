<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/house_manage_assign.php') == false) {
	//Acess denied
	echo "<div class='alert alert-danger'>" ;
		echo __('You do not have access to this action.');
	echo "</div>" ;
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Houses'), 'house_manage.php')
        ->add(__('Assign Houses'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo '<h2>';
    echo __('Assign Houses');
    echo '</h2>';

    $form = Form::create('houseAssignProcess', $_SESSION[$guid]['absoluteURL'].'/modules/School Admin/house_manage_assignProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
        $row->addLabel('pupilsightYearGroupIDList', __('Year Groups'));
        $row->addSelectYearGroup('pupilsightYearGroupIDList')->selectMultiple()->required();

    $sql = "SELECT pupilsightHouseID as value, name FROM pupilsightHouse ORDER BY name";
    $row = $form->addRow();
        $row->addLabel('pupilsightHouseIDList', __('Houses'));
        $row->addSelect('pupilsightHouseIDList')->fromQuery($pdo, $sql)->required()->selectMultiple()->selectAll();

    $row = $form->addRow();
        $row->addLabel('balanceGender', __('Balance by Gender?'));
        $row->addYesNo('balanceGender');

    $row = $form->addRow();
        $row->addLabel('balanceYearGroup', __('Balance by Year Group?'))->description(__('Attempts to keep houses be as balanced as possible per year group, otherwise balances the house numbers school-wide.'));
        $row->addYesNo('balanceYearGroup');

    $row = $form->addRow();
        $row->addLabel('overwrite', __('Overwrite'))->description(__("Replace a student's existing house assignment? If no, it will only assign houses to students who do not already have one."));
        $row->addYesNo('overwrite')->selected('N');

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
