<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/report_activityChoices_byRollGroup.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Activity Choices by Roll Group'));

    echo '<h2>';
    echo __('Choose Roll Group');
    echo '</h2>';

    $pupilsightRollGroupID = isset($_GET['pupilsightRollGroupID'])? $_GET['pupilsightRollGroupID'] : '';
    $status = isset($_GET['status'])? $_GET['status'] : '';

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php','get');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/report_activityChoices_byRollGroup.php");

    $row = $form->addRow();
        $row->addLabel('pupilsightRollGroupID', __('Roll Group'));
        $row->addSelectRollGroup('pupilsightRollGroupID', $_SESSION[$guid]['pupilsightSchoolYearID'])->selected($pupilsightRollGroupID)->required();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    if ($pupilsightRollGroupID != '') {
        $output = '';
        echo '<h2>';
        echo __('Report Data');
        echo '</h2>';

        try {
            $data = array('pupilsightRollGroupID' => $pupilsightRollGroupID, 'today' => date('Y-m-d'));
            $sql = "SELECT pupilsightPerson.pupilsightPersonID, surname, preferredName, name FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE status='Full' AND (dateStart IS NULL OR dateStart<=:today) AND (dateEnd IS NULL  OR dateEnd>=:today) AND pupilsightStudentEnrolment.pupilsightRollGroupID=:pupilsightRollGroupID ORDER BY surname, preferredName";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() < 1) {
            echo "<div class='alert alert-danger'>";
            echo __('There are no records to display.');
            echo '</div>';
        } else {
            echo "<table cellspacing='0' class='fullWidth colorOddEven'>";
            echo "<tr class='head'>";
            echo '<th>';
            echo __('Student');
            echo '</th>';
            echo '<th>';
            echo __('Activities');
            echo '</th>';
            echo '</tr>';

            while ($row = $result->fetch()) {
                echo '<tr>';
                echo '<td>';
                echo '<b><a href="index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='.$row['pupilsightPersonID'].'&subpage=Activities">'.formatName('', $row['preferredName'], $row['surname'], 'Student', true).'</a>';
                echo '</td>';

                echo '<td>';

                try {
					$dataActivities = array('pupilsightPersonID' => $row['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
					$sqlActivities = "SELECT pupilsightActivity.*, pupilsightActivityStudent.status, GROUP_CONCAT(CONCAT(pupilsightDaysOfWeek.nameShort, ' ', TIME_FORMAT(pupilsightActivitySlot.timeStart, '%H:%i'), ' - ', (CASE WHEN pupilsightActivitySlot.pupilsightSpaceID IS NOT NULL THEN pupilsightSpace.name ELSE pupilsightActivitySlot.locationExternal END)) SEPARATOR '<br/>') as days
                        FROM pupilsightActivity 
                        JOIN pupilsightActivityStudent ON (pupilsightActivity.pupilsightActivityID=pupilsightActivityStudent.pupilsightActivityID) 
                        JOIN pupilsightActivitySlot ON (pupilsightActivitySlot.pupilsightActivityID=pupilsightActivity.pupilsightActivityID)
                        JOIN pupilsightDaysOfWeek ON (pupilsightDaysOfWeek.pupilsightDaysOfWeekID=pupilsightActivitySlot.pupilsightDaysOfWeekID)
                        LEFT JOIN pupilsightSpace ON (pupilsightSpace.pupilsightSpaceID=pupilsightActivitySlot.pupilsightSpaceID)
                        WHERE pupilsightActivityStudent.pupilsightPersonID=:pupilsightPersonID 
                        AND pupilsightActivity.pupilsightSchoolYearID=:pupilsightSchoolYearID 
                        GROUP BY pupilsightActivity.pupilsightActivityID 
                        ORDER BY pupilsightActivity.name";
					$resultActivities = $connection2->prepare($sqlActivities);
					$resultActivities->execute($dataActivities);
				} catch (PDOException $e) {
					echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($resultActivities->rowCount() > 0) {
                    echo '<table class="table">';
                    while ($activity = $resultActivities->fetch()) {
                        $timespan = getActivityTimespan($connection2, $activity['pupilsightActivityID'], $activity['pupilsightSchoolYearTermIDList']); 
                        $timeStatus = '';
                        if (!empty($timespan)) {
                            $timeStatus = (time() < $timespan['start'])? __('Upcoming') : (time() > $timespan['end']? __('Ended') : __('Current'));
                        }
                        echo '<tr>';
                        echo '<td>';
                        echo '<a class="thickbox" title="'.__('View Details').'" href="'.$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/activities_view_full.php&pupilsightActivityID='.$activity['pupilsightActivityID'].'&width=1000&height=550" style="text-decoration: none; color:inherit;">'.$activity['name'].'</a>';
                        echo '</td>';
                        echo '<td width="15%">';
                        if (!empty($timeStatus)) {
                            echo '<span class="emphasis" title="'.formatDateRange('@'.$timespan['start'], '@'.$timespan['end']).'">';
                            echo (time() < $timespan['start'])? __('Upcoming') : (time() > $timespan['end']? __('Ended') : __('Current'));
                            echo '</span>';
                        } else {
                            echo $activity['status'];
                        }
                        echo '</td>';
                        echo '<td width="30%">';
                        echo (!empty($timespan) && $timeStatus != __('Ended') && $activity['status'] == 'Accepted')? $activity['days'] : '';
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }
                
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
    }
}
?>
