<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_manage_enrolment_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightActivityID = (isset($_GET['pupilsightActivityID']))? $_GET['pupilsightActivityID'] : null;
    
    $highestAction = getHighestGroupedAction($guid, '/modules/Activities/activities_manage_enrolment.php', $connection2);
    if ($highestAction == 'My Activities_viewEditEnrolment') {

        try {
            $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightActivityID' => $pupilsightActivityID);
            $sql = "SELECT pupilsightActivity.*, NULL as status, pupilsightActivityStaff.role FROM pupilsightActivity JOIN pupilsightActivityStaff ON (pupilsightActivity.pupilsightActivityID=pupilsightActivityStaff.pupilsightActivityID) WHERE pupilsightActivity.pupilsightActivityID=:pupilsightActivityID AND pupilsightActivityStaff.pupilsightPersonID=:pupilsightPersonID AND pupilsightActivityStaff.role='Organiser' AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' ORDER BY name";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if (!$result || $result->rowCount() == 0) {
            //Acess denied
            echo "<div class='alert alert-danger'>";
            echo __('You do not have access to this action.');
            echo '</div>';
            return;
        }
    }

    $urlParams = ['pupilsightActivityID' => $_GET['pupilsightActivityID'], 'search' => $_GET['search'], 'pupilsightSchoolYearTermID' => $_GET['pupilsightSchoolYearTermID']];

    $page->breadcrumbs
        ->add(__('Manage Activities'), 'activities_manage.php')
        ->add(__('Activity Enrolment'), 'activities_manage_enrolment.php',  $urlParams)
        ->add(__('Edit Enrolment'));      

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightActivityID = $_GET['pupilsightActivityID'];
    $pupilsightPersonID = $_GET['pupilsightPersonID'];
    if ($pupilsightPersonID == '' or $pupilsightActivityID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightActivityID' => $pupilsightActivityID, 'pupilsightPersonID' => $pupilsightPersonID);
            $sql = 'SELECT pupilsightActivity.*, pupilsightActivityStudent.*, surname, preferredName FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivity.pupilsightActivityID=pupilsightActivityStudent.pupilsightActivityID) JOIN pupilsightPerson ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightActivityStudent.pupilsightActivityID=:pupilsightActivityID AND pupilsightActivityStudent.pupilsightPersonID=:pupilsightPersonID';
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
            //Let's go!
            $values = $result->fetch();
            $dateType = getSettingByScope($connection2, 'Activities', 'dateType');

            if ($_GET['search'] != '' || $_GET['pupilsightSchoolYearTermID'] != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Activities/activities_manage_enrolment.php&search='.$_GET['search']."&pupilsightSchoolYearTermID=".$_GET['pupilsightSchoolYearTermID']."&pupilsightActivityID=$pupilsightActivityID'>".__('Back').'</a>';
                echo '</div>';
            }

            $form = Form::create('activityEnrolment', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/activities_manage_enrolment_editProcess.php?pupilsightActivityID=$pupilsightActivityID&pupilsightPersonID=$pupilsightPersonID&search=".$_GET['search']."&pupilsightSchoolYearTermID=".$_GET['pupilsightSchoolYearTermID']);
			
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);

            $row = $form->addRow();
                $row->addLabel('nameLabel', __('Name'));
                $row->addTextField('name')->readOnly()->setValue($values['name']);

            if ($dateType == 'Date') {
                $row = $form->addRow();
                $row->addLabel('listingDatesLabel', __('Listing Dates'));
                $row->addTextField('listingDates')->readOnly()->setValue(dateConvertBack($guid, $values['listingStart']).'-'.dateConvertBack($guid, $values['listingEnd']));

                $row = $form->addRow();
                $row->addLabel('programDatesLabel', __('Program Dates'));
                $row->addTextField('programDates')->readOnly()->setValue(dateConvertBack($guid, $values['programStart']).'-'.dateConvertBack($guid, $values['programEnd']));
            } else {
                $schoolTerms = getTerms($connection2, $_SESSION[$guid]['pupilsightSchoolYearID']);
                $termList = array_filter(array_map(function ($item) use ($schoolTerms) {
                    $index = array_search($item, $schoolTerms);
                    return ($index !== false && isset($schoolTerms[$index+1]))? $schoolTerms[$index+1] : '';
                }, explode(',', $values['pupilsightSchoolYearTermIDList'])));
                $termList = (!empty($termList)) ? implode(', ', $termList) : '-';

                $row = $form->addRow();
                $row->addLabel('termsLabel', __('Terms'));
                $row->addTextField('terms')->readOnly()->setValue($termList);
	    }

            $row = $form->addRow();
            $row->addLabel('student', __('Student'));
            $row->addTextField('student')->readOnly()->setValue(formatName('', htmlPrep($values['preferredName']), htmlPrep($values['surname']), 'Student'));
				
            $statuses = array('Accepted' => __('Accepted'));
            $enrolment = getSettingByScope($connection2, 'Activities', 'enrolmentType');
            if ($enrolment == 'Competitive') {
                $statuses['Waiting List'] = __('Waiting List');
            } else {
                $statuses['Pending'] = __('Pending');
            }
            $statuses['Not Accepted'] = __('Not Accepted');

            $row = $form->addRow();
            $row->addLabel('status', __('Status'));
            $row->addSelect('status')->fromArray($statuses)->required();
			
            $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();
                
            $form->loadAllValuesFrom($values);
				
            echo $form->getOutput();
        }
    }
}
