<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_manage_enrolment_add.php') == false) {
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
        ->add(__('Add Student'));   

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightActivityID = $_GET['pupilsightActivityID'];

    if ($pupilsightActivityID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightActivityID' => $pupilsightActivityID);
            $sql = 'SELECT * FROM pupilsightActivity WHERE pupilsightActivityID=:pupilsightActivityID';
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
            $values = $result->fetch();
            $dateType = getSettingByScope($connection2, 'Activities', 'dateType');
            if ($_GET['search'] != '' || $_GET['pupilsightSchoolYearTermID'] != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Activities/activities_manage_enrolment.php&search='.$_GET['search']."&pupilsightSchoolYearTermID=".$_GET['pupilsightSchoolYearTermID']."&pupilsightActivityID=$pupilsightActivityID'>".__('Back').'</a>';
                echo '</div>';
			}
			
			$form = Form::create('activityEnrolment', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/activities_manage_enrolment_addProcess.php?pupilsightActivityID=$pupilsightActivityID&search=".$_GET['search']."&pupilsightSchoolYearTermID=".$_GET['pupilsightSchoolYearTermID']);
			
			$form->addHiddenValue('address', $_SESSION[$guid]['address']);

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
                $termList = (!empty($termList))? implode(', ', $termList) : '-';
                                            
                $row = $form->addRow();
                $row->addLabel('termsLabel', __('Terms'));
                $row->addTextField('terms')->readOnly()->setValue($termList);
			}

			$students = array();
			$data = array('pupilsightYearGroupIDList' => $values['pupilsightYearGroupIDList'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'date' => date('Y-m-d'));
			$sql = "SELECT pupilsightPerson.pupilsightPersonID, preferredName, surname, pupilsightRollGroup.name AS rollGroupName 
					FROM pupilsightPerson
					JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) 
					JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
					JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)
					WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
					AND FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, :pupilsightYearGroupIDList)
					AND pupilsightPerson.status='FULL' 
					AND (dateStart IS NULL OR dateStart<=:date) AND (dateEnd IS NULL  OR dateEnd>=:date) 
					ORDER BY rollGroupName, pupilsightPerson.surname, pupilsightPerson.preferredName";
			$result = $pdo->executeQuery($data, $sql);

			if ($result->rowCount() > 0) {
				$students['--'.__('Enrolable Students').'--'] = array_reduce($result->fetchAll(), function($group, $item) {
					$group[$item['pupilsightPersonID']] = $item['rollGroupName'].' - '.formatName('', $item['preferredName'], $item['surname'], 'Student', true);
					return $group;
				}, array());
			}

            $sql = "SELECT pupilsightPersonID, surname, preferredName, status, username FROM pupilsightPerson WHERE status='Full' OR status='Expected' ORDER BY surname, preferredName";
			$result = $pdo->executeQuery(array(), $sql);

            if ($result->rowCount() > 0) {
                $students['--'.__('All Users').'--'] = array_reduce($result->fetchAll(), function ($group, $item) {
                    $group[$item['pupilsightPersonID']] = formatName('', $item['preferredName'], $item['surname'], 'Student', true).' ('.$item['username'].')';
                    return $group;
                }, array());
            }

			$row = $form->addRow();
                $row->addLabel('Members[]', __('Students'));
				$row->addSelect('Members[]')->fromArray($students)->selectMultiple()->required();
				
			$statuses = array('Accepted' => __('Accepted'));
			$enrolment = getSettingByScope($connection2, 'Activities', 'enrolmentType');
			if ($enrolment == 'Competitive') {
				$statuses['Waiting List'] = __('Waiting List');
			} else {
				$statuses['Pending'] = __('Pending');
			}

			$row = $form->addRow();
                $row->addLabel('status', __('Status'));
                $row->addSelect('status')->fromArray($statuses)->required();
			
			$row = $form->addRow();
                $row->addFooter();
				$row->addSubmit();
				
			echo $form->getOutput();
        }
    }
}
