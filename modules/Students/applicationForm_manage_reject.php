<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/applicationForm_manage_reject.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightApplicationFormID = $_GET['pupilsightApplicationFormID'] ?? '';
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $search = $_GET['search'] ?? '';

    $page->breadcrumbs
        ->add(__('Manage Applications'), 'applicationForm_manage.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Reject Application'));

    //Check if school year specified
    if ($pupilsightApplicationFormID == '' or $pupilsightSchoolYearID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightApplicationFormID' => $pupilsightApplicationFormID);
            $sql = 'SELECT * FROM pupilsightApplicationForm WHERE pupilsightApplicationFormID=:pupilsightApplicationFormID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            //Let's go!
            $values = $result->fetch();
            $proceed = true;

            echo "<div class='linkTop'>";
            if ($search != '') {
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/applicationForm_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search'>".__('Back to Search Results').'</a>';
            }
            echo '</div>';

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/applicationForm_manage_rejectProcess.php?pupilsightApplicationFormID=$pupilsightApplicationFormID&search=$search");

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
            $form->addHiddenValue('pupilsightApplicationFormID', $pupilsightApplicationFormID);

            $row = $form->addRow();
                $row->addContent(sprintf(__('Are you sure you want to reject the application for %1$s?'), Format::name('', $values['preferredName'], $values['surname'], 'Student')));

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit(__('Yes'));

            echo $form->getOutput();
        }
    }
}
