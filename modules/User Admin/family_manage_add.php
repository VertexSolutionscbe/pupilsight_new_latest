<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/family_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Families'), 'family_manage.php')
        ->add(__('Add Family'));    

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/User Admin/family_manage_edit.php&pupilsightFamilyID='.$_GET['editID'].'&search='.$_GET['search'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $search = $_GET['search'];
    if ($search != '') {
        echo "<div class='linkTop'>";
        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/User Admin/family_manage.php&search=$search'>".__('Back to Search Results').'</a>';
        echo '</div>';
    }

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/family_manage_addProcess.php?search=$search");
    $form->setFactory(DatabaseFormFactory::create($pdo));
    
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $form->addRow()->addHeading(__('General Information'));

    $row = $form->addRow();
        $row->addLabel('name', __('Family Name'));
        $row->addTextField('name')->maxLength(100)->required();

    $row = $form->addRow();
		$row->addLabel('status', __('Marital Status'));
		$row->addSelectMaritalStatus('status')->required();

    $row = $form->addRow();
        $row->addLabel('languageHomePrimary', __('Home Language - Primary'));
        $row->addSelectLanguage('languageHomePrimary');

    $row = $form->addRow();
        $row->addLabel('languageHomeSecondary', __('Home Language - Secondary'));
        $row->addSelectLanguage('languageHomeSecondary');

    $row = $form->addRow();
        $row->addLabel('nameAddress', __('Address Name'))->description(__('Formal name to address parents with.'));
        $row->addTextField('nameAddress')->maxLength(100)->required();

    $row = $form->addRow();
        $row->addLabel('homeAddress', __('Home Address'))->description(__('Unit, Building, Street'));
        $row->addTextField('homeAddress')->maxLength(255);

    $row = $form->addRow();
        $row->addLabel('homeAddressDistrict', __('Home Address (District)'))->description(__('County, State, District'));
        $row->addTextFieldDistrict('homeAddressDistrict');

    $row = $form->addRow();
        $row->addLabel('homeAddressCountry', __('Home Address (Country)'));
        $row->addSelectCountry('homeAddressCountry');

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
?>
