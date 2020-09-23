<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/externalAssessment_details.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Get action with highest precendence
        $pupilsightPersonID = $_GET['pupilsightPersonID'] ?? '';
        $search = $_GET['search'] ?? '';
        $allStudents = $_GET['allStudents'] ?? '';

        $page->breadcrumbs
            ->add(__('View All Assessments'), 'externalAssessment.php')
            ->add(__('Student Details'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, array('success0' => 'Your request was completed successfully.'));
        }

        try {
            if ($allStudents != 'on') {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID);
                $sql = "SELECT pupilsightPerson.pupilsightPersonID, pupilsightStudentEnrolment.pupilsightYearGroupID, pupilsightStudentEnrolmentID, surname, preferredName, title, image_240, pupilsightYearGroup.name AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup FROM pupilsightPerson, pupilsightStudentEnrolment, pupilsightYearGroup, pupilsightRollGroup WHERE (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) AND (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) AND (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full' AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName";
            } else {
                $data = array('pupilsightPersonID' => $pupilsightPersonID);
                $sql = 'SELECT DISTINCT pupilsightPerson.pupilsightPersonID, surname, preferredName, title, image_240, NULL AS yearGroup, NULL AS rollGroup FROM pupilsightPerson, pupilsightStudentEnrolment WHERE (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName';
            }
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
            if ($search != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Formal Assessment/externalAssessment.php&search=$search&allStudents=$allStudents'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }

            $row = $result->fetch();

            echo "<table class='table'>";
            echo '<tr>';
            echo "<td style='width: 34%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Name').'</span><br/>';
            echo Format::name('', $row['preferredName'], $row['surname'], 'Student');
            echo '</td>';
            echo "<td style='width: 33%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Year Group').'</span><br/>';
            if ($row['yearGroup'] != '') {
                echo __($row['yearGroup']);
            }
            echo '</td>';
            echo "<td style='width: 34%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Roll Group').'</span><br/>';
            echo $row['rollGroup'];
            echo '</td>';
            echo '</tr>';
            echo '</table>';

            if ($highestAction == 'External Assessment Data_manage') {
                echo "<div class='linkTop'>";
                echo "<a class = 'fw-btn-fill btn-gradient-yellow addbtncss' href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/externalAssessment_manage_details_add.php&pupilsightPersonID=$pupilsightPersonID&search=$search&allStudents=$allStudents' style='width:auto !important'>".__('Add')."<i class='mdi mdi-plus-circle-outline'></i></a>";
                echo '</div>';
            }
            

            //Print assessments
            $manage = false;
            if ($highestAction == 'External Assessment Data_manage') {
                $manage = true;
            }
            externalAssessmentDetails($guid, $pupilsightPersonID, $connection2, '', $manage, $search, $allStudents);

            //Set sidebar
            $_SESSION[$guid]['sidebarExtra'] = getUserPhoto($guid, $row['image_240'], 240);
        }
    }
}
