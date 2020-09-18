<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_manage_edit_facility_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightPersonID = $_GET['pupilsightPersonID'] ?? '';
    $pupilsightStaffID = $_GET['pupilsightStaffID'] ?? '';
    $pupilsightSpacePersonID = $_GET['pupilsightSpacePersonID'] ?? '';
    $search = $_GET['search'] ?? '';

    $page->breadcrumbs
        ->add(__('Manage Staff'), 'staff_manage.php')
        ->add(__('Edit Staff'), 'staff_manage_edit.php', ['pupilsightStaffID' => $pupilsightStaffID, 'pupilsightSpacePersonID' => $pupilsightSpacePersonID])
        ->add(__('Add Facility'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    if ($pupilsightStaffID == '' or $pupilsightPersonID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightStaffID' => $pupilsightStaffID, 'pupilsightPersonID' => $pupilsightPersonID);
            $sql = 'SELECT pupilsightStaff.*, preferredName, surname FROM pupilsightStaff JOIN pupilsightPerson ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightStaffID=:pupilsightStaffID AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            $values = $result->fetch();

            if ($search != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Staff/staff_manage_edit.php&pupilsightStaffID=$pupilsightStaffID&search=$search'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/staff_manage_edit_facility_addProcess.php?pupilsightPersonID=$pupilsightPersonID&pupilsightStaffID=$pupilsightStaffID&search=$search");
            $form->setFactory(DatabaseFormFactory::create($pdo));
            
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addLabel('person', __('Person'));
                $row->addTextField('person')->setValue(Format::name('', $values['preferredName'], $values['surname'], 'Student'))->readonly()->required();

            $data = array('pupilsightPersonID' => $pupilsightPersonID);
            $sql = "SELECT pupilsightSpace.pupilsightSpaceID AS value, name
                FROM pupilsightSpace
                    LEFT JOIN pupilsightSpacePerson ON (pupilsightSpacePerson.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID AND (pupilsightSpacePersonID IS NULL OR pupilsightSpacePerson.pupilsightPersonID=:pupilsightPersonID))
                    WHERE pupilsightSpacePerson.pupilsightPersonID IS NULL
                ORDER BY pupilsightSpace.name";
            $row = $form->addRow();
                $row->addLabel('pupilsightSpaceID', __('Facility'));
                $row->addSelect('pupilsightSpaceID')->fromQuery($pdo, $sql, $data)->placeholder()->required();

            $row = $form->addRow();
                $row->addLabel('usageType', __('Usage Type'));
                $row->addSelect('usageType')->fromArray(array('Teaching' => __('Teaching'), 'Office' => __('Office'), 'Other' => __('Other')))->placeholder();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
