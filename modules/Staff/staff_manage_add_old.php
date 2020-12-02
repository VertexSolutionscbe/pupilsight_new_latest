<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $search = $_GET['search'] ?? '';
    $allStaff = $_GET['allStaff'] ?? '';

    $page->breadcrumbs
        //->add(__('Manage Staff'), 'staff_manage.php', ['search' => $search, 'allStaff' => $allStaff])
        ->add(__('Manage Staff'), 'staff_view.php')
        ->add(__('Add Staff'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Staff/staff_manage_edit.php&pupilsightStaffID=' . $_GET['editID'] . '&search=' . $_GET['search'] . '&allStaff=' . $_GET['allStaff'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    if ($search != '' or $allStaff != '') {
        echo "<div class='linkTop'>";
        echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Staff/staff_manage.php&search=$search&allStaff=$allStaff'>" . __('Back to Search Results') . '</a>';
        echo '</div>';
    }

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . "/staff_manage_addProcess.php?search=$search&allStaff=$allStaff");
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $form->addRow("basic_information")->addHeading(__('Basic Information'));

    $row = $form->addRow("row_pupilsightPersonID");
    $row->addLabel('pupilsightPersonID', __('Person'))->description(__('Must be unique.'));
    $row->addSelectStaff('pupilsightPersonID')->placeholder()->required();

    $row = $form->addRow("row_initials");
    $row->addLabel('initials', __('Initials'))->description(__('Must be unique if set.'));
    $row->addTextField('initials')->maxlength(4);

    $types = array(__('Basic') => array('Teaching' => __('Teaching'), 'Support' => __('Support')));
    $sql = "SELECT name as value, name FROM pupilsightRole WHERE category='Staff' ORDER BY name";
    $result = $pdo->executeQuery(array(), $sql);
    $types[__('System Roles')] = ($result->rowCount() > 0) ? $result->fetchAll(\PDO::FETCH_KEY_PAIR) : array();
    $row = $form->addRow("row_type");
    $row->addLabel('type', __('Type'));
    $row->addSelect('type')->fromArray($types)->placeholder()->required();

    $row = $form->addRow("row_jobTitle");
    $row->addLabel('jobTitle', __('Job Title'));
    $row->addTextField('jobTitle')->maxlength(100);

    $form->addRow()->addHeading(__('First Aid'));

    $row = $form->addRow("row_firstAidQualified");
    $row->addLabel('firstAidQualified', __('First Aid Qualified?'));
    $row->addYesNo('firstAidQualified')->placeHolder();

    $form->toggleVisibilityByClass('firstAid')->onSelect('firstAidQualified')->when('Y');

    $row = $form->addRow("row_firstAidExpiry")->addClass('firstAid');
    $row->addLabel('firstAidExpiry', __('First Aid Expiry'));
    $row->addDate('firstAidExpiry');

    $form->addRow()->addHeading(__('Biography'));

    $row = $form->addRow("row_countryOfOrigin");
    $row->addLabel('countryOfOrigin', __('Country Of Origin'));
    $row->addSelectCountry('countryOfOrigin')->placeHolder();

    $row = $form->addRow("row_qualifications");
    $row->addLabel('qualifications', __('Qualifications'));
    $row->addTextField('qualifications')->maxlength(80);

    $row = $form->addRow("row_biographicalGrouping");
    $row->addLabel('biographicalGrouping', __('Grouping'))->description(__('Used to group staff when creating a staff directory.'));
    $row->addTextField('biographicalGrouping')->maxlength(100);

    $row = $form->addRow("row_biographicalGroupingPriority");
    $row->addLabel('biographicalGroupingPriority', __('Grouping Priority'))->description(__('Higher numbers move teachers up the order within their grouping.'));
    $row->addNumber('biographicalGroupingPriority')->decimalPlaces(0)->maximum(99)->maxLength(2)->setValue('0');

    $row = $form->addRow("row_biography");
    $row->addLabel('biography', __('Biography'));
    $row->addTextArea('biography')->setRows(10);

    $row = $form->addRow("Principle?");
    $row->addLabel('is_principle', __('Principle?'));
    $row->addCheckBox('is_principle')->setValue('1');

    $row = $form->addRow("Signature");
    $row->addLabel('file', __('Signature'));
    $row->addFileUpload('file')
        ->accepts('.jpg,.jpeg,.gif,.png')
        ->setMaxUpload(false);

    $row = $form->addRow("");
    $row->addFooter();
    $row->addSubmit();

    echo $form->getOutput();
}
