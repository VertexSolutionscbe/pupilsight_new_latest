<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable/tt_view.php') == false) {
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
        $pupilsightPersonID = null;
        if (isset($_GET['pupilsightPersonID'])) {
            $pupilsightPersonID = $_GET['pupilsightPersonID'];
        }
        $search = null;
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
        }
        $allUsers = null;
        if (isset($_GET['allUsers'])) {
            $allUsers = $_GET['allUsers'];
        }
        $pupilsightTTID = null;
        if (isset($_GET['pupilsightTTID'])) {
            $pupilsightTTID = $_GET['pupilsightTTID'];
        }

        try {
            if ($highestAction == 'View Timetable by Person_myChildren') {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID1' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonID2' => $pupilsightPersonID);
                $sql = "SELECT pupilsightPerson.pupilsightPersonID, pupilsightStudentEnrolmentID, surname, preferredName, title, image_240, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup, 'Student' AS type, pupilsightRoleIDPrimary
                    FROM pupilsightPerson
                    JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                    JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)
                    JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                    JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID1)
                    JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamilyAdult.pupilsightFamilyID)
                    WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
                    AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID2
                    AND pupilsightPerson.status='Full' AND pupilsightFamilyAdult.childDataAccess='Y'
                    GROUP BY pupilsightPerson.pupilsightPersonID";
            } else {
                if ($allUsers == 'on') {
                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID);
                    $sql = "SELECT pupilsightPerson.pupilsightPersonID, surname, preferredName, title, image_240, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup, 'Student' AS type, pupilsightRoleIDPrimary FROM pupilsightPerson LEFT JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID) LEFT JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) LEFT JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) WHERE (pupilsightPerson.status='Full' OR pupilsightPerson.status='Expected') AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName";
                } else {
                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID1' => $pupilsightPersonID, 'pupilsightPersonID2' => $pupilsightPersonID);
                    $sql = "(SELECT pupilsightPerson.pupilsightPersonID, pupilsightStudentEnrolmentID, surname, preferredName, title, image_240, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup, 'Student' AS type, pupilsightRoleIDPrimary FROM pupilsightPerson, pupilsightStudentEnrolment, pupilsightYearGroup, pupilsightRollGroup WHERE (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) AND (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) AND (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full' AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID1) UNION (SELECT pupilsightPerson.pupilsightPersonID, NULL AS pupilsightStudentEnrolmentID, surname, preferredName, title, image_240, NULL AS yearGroup, NULL AS rollGroup, 'Staff' AS type, pupilsightRoleIDPrimary FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightPerson.pupilsightPersonID=pupilsightStaff.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightRole.pupilsightRoleID=pupilsightPerson.pupilsightRoleIDPrimary) WHERE (pupilsightStaff.type='Teaching' OR pupilsightRole.name = 'Teacher') AND pupilsightPerson.status='Full' AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID2) ORDER BY surname, preferredName";
                }
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
        } else if ($highestAction == 'View Timetable by Person_my' && $pupilsightPersonID != $_SESSION[$guid]['pupilsightPersonID']) {
            echo "<div class='alert alert-danger'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            $row = $result->fetch();

            $page->breadcrumbs
                ->add(__('View Timetable by Person'), 'tt.php', ['allUsers' => $allUsers])
                ->add(Format::name($row['title'], $row['preferredName'], $row['surname'], $row['type']));


            $canEdit = isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit.php');
            $roleCategory = getRoleCategory($row['pupilsightRoleIDPrimary'], $connection2);
            if ($allUsers == 'on' or $search != '' or $canEdit) {
                echo "<div class='linkTop'>";
                if ($search != '') {
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Timetable/tt.php&search='.$search."&allUsers=$allUsers'>".__('Back to Search Results').'</a>';
                }
                if ($canEdit && ($roleCategory == 'Student' or $roleCategory == 'Staff')) {
                    if ($search != '') {
                        echo ' | ';
                    }
                    echo "<a class='font_edit' href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit.php&pupilsightPersonID=$pupilsightPersonID&pupilsightSchoolYearID=".$_SESSION[$guid]['pupilsightSchoolYearID']."&type=$roleCategory&allUsers=$allUsers'>".__('')."
					<i title='".__('Edit')."' class='mdi mdi-pencil-box-outline mdi-24px'></i>
					</a> ";
                }
                echo '</div>';
            }

            echo "<table class='table'>";
            echo '<tr>';
            echo "<td style='width: 34%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Name').'</span><br/>';
            echo Format::name($row['title'], $row['preferredName'], $row['surname'], $row['type'], false);
            echo '</td>';
            echo "<td style='width: 33%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Year Group').'</span><br/>';
            if ($row['yearGroup'] != '') {
                echo '<i>'.__($row['yearGroup']).'</i>';
            }
            echo '</td>';
            echo "<td style='width: 34%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Roll Group').'</span><br/>';
            echo '<i>'.$row['rollGroup'].'</i>';
            echo '</td>';
            echo '</tr>';
            echo '</table>';

            $ttDate = null;
            if (isset($_POST['ttDate'])) {
                $ttDate = dateConvertToTimestamp(dateConvert($guid, $_POST['ttDate']));
            }

            if (isset($_POST['fromTT'])) {
                if ($_POST['fromTT'] == 'Y') {
                    if (@$_POST['schoolCalendar'] == 'on' or @$_POST['schoolCalendar'] == 'Y') {
                        $_SESSION[$guid]['viewCalendarSchool'] = 'Y';
                    } else {
                        $_SESSION[$guid]['viewCalendarSchool'] = 'N';
                    }

                    if (@$_POST['personalCalendar'] == 'on' or @$_POST['personalCalendar'] == 'Y') {
                        $_SESSION[$guid]['viewCalendarPersonal'] = 'Y';
                    } else {
                        $_SESSION[$guid]['viewCalendarPersonal'] = 'N';
                    }

                    $spaceBookingCalendar = $_POST['spaceBookingCalendar'] ?? '';
                    if ($spaceBookingCalendar == 'on' or $spaceBookingCalendar == 'Y') {
                        $_SESSION[$guid]['viewCalendarSpaceBooking'] = 'Y';
                    } else {
                        $_SESSION[$guid]['viewCalendarSpaceBooking'] = 'N';
                    }
                }
            }

            $tt = renderTT($guid, $connection2, $pupilsightPersonID, $pupilsightTTID, false, $ttDate, '/modules/Timetable/tt_view.php', "&pupilsightPersonID=$pupilsightPersonID&allUsers=$allUsers&search=$search");
            if ($tt != false) {
                echo $tt;
            } else {
                echo "<div class='alert alert-danger'>";
                echo __('There are no records to display.');
                echo '</div>';
            }

            //Set sidebar
            $_SESSION[$guid]['sidebarExtra'] = getUserPhoto($guid, $row['image_240'], 240);
        }
    }
}
