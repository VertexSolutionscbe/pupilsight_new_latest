<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_manage_enrolment.php') == false) {
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

    $page->breadcrumbs
        ->add(__('Manage Activities'), 'activities_manage.php')
        ->add(__('Activity Enrolment'));    

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
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
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();
            $dateType = getSettingByScope($connection2, 'Activities', 'dateType');
            if ($_GET['search'] != '' || $_GET['pupilsightSchoolYearTermID'] != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Activities/activities_manage.php&search='.$_GET['search']."&pupilsightSchoolYearTermID=".$_GET['pupilsightSchoolYearTermID']."'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }

            $form = Form::create('activityEnrolment', $_SESSION[$guid]['absoluteURL'].'/index.php');

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
            echo $form->getOutput();


            $enrolment = getSettingByScope($connection2, 'Activities', 'enrolmentType');
            try {
                $data = array('pupilsightActivityID' => $pupilsightActivityID, 'today' => date('Y-m-d'), 'statusCheck' => ($enrolment == 'Competitive'? 'Pending' : 'Waiting List'));
                $sql = "SELECT pupilsightActivityStudent.*, surname, preferredName, pupilsightRollGroup.nameShort as rollGroupNameShort
                        FROM pupilsightActivityStudent
                        JOIN pupilsightPerson ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                        LEFT JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightSchoolYearID=(SELECT pupilsightSchoolYearID FROM pupilsightSchoolYear WHERE status='Current'))
                        LEFT JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightRollGroupID=pupilsightStudentEnrolment.pupilsightRollGroupID)
                        WHERE pupilsightActivityID=:pupilsightActivityID
                        AND NOT pupilsightActivityStudent.status=:statusCheck
                        AND pupilsightPerson.status='Full'
                        AND (dateStart IS NULL OR dateStart<=:today) AND (dateEnd IS NULL OR dateEnd>=:today)
                        ORDER BY pupilsightActivityStudent.status, timestamp";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            echo "<div class='linkTop'>";
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/activities_manage_enrolment_add.php&pupilsightActivityID=$pupilsightActivityID&search=".$_GET['search'].'&pupilsightSchoolYearTermID='.$_GET['pupilsightSchoolYearTermID']."'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a>";
            echo '</div>';

            if ($result->rowCount() < 1) {
                echo "<div class='alert alert-danger'>";
                echo __('There are no records to display.');
                echo '</div>';
            } else {
                echo "<table cellspacing='0' style='width: 100%'>";
                echo "<tr class='head'>";
                echo '<th>';
                echo __('Student');
                echo '</th>';
                echo '<th>';
                echo __('Roll Group');
                echo '</th>';
                echo '<th>';
                echo __('Status');
                echo '</th>';
                echo '<th>';
                echo 'Timestamp';
                echo '</th>';
                echo '<th>';
                echo __('Actions');
                echo '</th>';
                echo '</tr>';

                $canViewStudentDetails = isActionAccessible($guid, $connection2, '/modules/Students/student_view_details.php');

                $count = 0;
                $rowNum = 'odd';
                while ($values = $result->fetch()) {
                    if ($count % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                        $rowNum = 'odd';
                    }
                    ++$count;

                    //COLOR ROW BY STATUS!
                    echo "<tr class=$rowNum>";
                    echo '<td>';
                    $studentName = formatName('', $values['preferredName'], $values['surname'], 'Student', true);
                    if ($canViewStudentDetails) {
                        echo sprintf('<a href="%2$s">%1$s</a>', $studentName, $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='.$values['pupilsightPersonID'].'&subpage=Activities');
                    } else {
                        echo $studentName;
                    }
                    echo '</td>';
                    echo '<td>';
                    echo $values['rollGroupNameShort'];
                    echo '</td>';
                    echo '<td>';
                    echo __($values['status']);
                    echo '</td>';
                    echo '<td>';
                    echo dateConvertBack($guid, substr($values['timestamp'], 0, 10)).' at '.substr($values['timestamp'], 11, 5);
                    echo '</td>';
                    echo '<td>';
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/activities_manage_enrolment_edit.php&pupilsightActivityID='.$values['pupilsightActivityID'].'&pupilsightPersonID='.$values['pupilsightPersonID'].'&search='.$_GET['search'].'&pupilsightSchoolYearTermID='.$_GET['pupilsightSchoolYearTermID']."'><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                    echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/activities_manage_enrolment_delete.php&pupilsightActivityID='.$values['pupilsightActivityID'].'&pupilsightPersonID='.$values['pupilsightPersonID'].'&search='.$_GET['search'].'&pupilsightSchoolYearTermID='.$_GET['pupilsightSchoolYearTermID']."&width=650&height=135'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a> ";
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        }
    }
}
?>
