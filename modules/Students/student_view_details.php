<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Module\Attendance\StudentHistoryData;
use Pupilsight\Module\Attendance\StudentHistoryView;
use Pupilsight\Domain\Students\StudentGateway;
use Pupilsight\Domain\System\CustomField;

//Module includes for User Admin (for custom fields)
include './modules/User Admin/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/student_view_details.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->scripts->add('chart');

    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
        return;
    } else {
        $pupilsightPersonID = isset($_GET['pupilsightPersonID']) ? $_GET['pupilsightPersonID'] : '';
        $search = null;
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
        }
        $allStudents = '';
        if (isset($_GET['allStudents'])) {
            $allStudents = $_GET['allStudents'];
        }
        $sort = '';
        if (isset($_GET['sort'])) {
            $sort = $_GET['sort'];
        }

        if (empty($pupilsightPersonID)) {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
            return;
        } else {

            $customField  = $container->get(CustomField::class);
            $customField->getPostData("pupilsightPerson", "pupilsightPersonID", $pupilsightPersonID, "student");


            $enableStudentNotes = getSettingByScope($connection2, 'Students', 'enableStudentNotes');
            $skipBrief = false;

            //Skip brief for those with _full or _fullNoNotes, and _brief
            if ($highestAction == 'Student Profile_full' || $highestAction == 'View Student Profile_fullNoNotes') {
                $skipBrief = true;
            }

            //Test if View Student Profile_brief and View Student Profile_myChildren are both available and parent has access to this student...if so, skip brief, and go to full.
            if (isActionAccessible($guid, $connection2, '/modules/Students/student_view_details.php', 'View Student Profile_brief') and isActionAccessible($guid, $connection2, '/modules/Students/student_view_details.php', 'View Student Profile_myChildren')) {
                try {
                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID1' => $_GET['pupilsightPersonID'], 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='" . date('Y-m-d') . "') AND (dateEnd IS NULL  OR dateEnd>='" . date('Y-m-d') . "') AND pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID1 AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID2 AND childDataAccess='Y'";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                }
                if ($result->rowCount() == 1) {
                    $skipBrief = true;
                }
            }

            if (isActionAccessible($guid, $connection2, '/modules/Students/student_view_details.php', 'View Student Profile_my')) {
                if ($pupilsightPersonID == $_SESSION[$guid]['pupilsightPersonID']) {
                    $skipBrief = true;
                } else if (isActionAccessible($guid, $connection2, '/modules/Students/student_view_details.php', 'View Student Profile_brief')) {
                    $highestAction = 'View Student Profile_brief';
                } else {
                    //Acess denied
                    echo "<div class='alert alert-danger'>";
                    echo __('You do not have access to this action.');
                    echo '</div>';
                    return;
                }
            }

            if (isActionAccessible($guid, $connection2, '/modules/Students/student_view_details.php', 'View Student Profile_brief') and $skipBrief == false) {
                //Proceed!
                try {
                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID);
                    $sql = "SELECT * FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date('Y-m-d') . "') AND (dateEnd IS NULL  OR dateEnd>='" . date('Y-m-d') . "') AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                }

                if ($result->rowCount() != 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('The selected record does not exist, or you do not have access to it.');
                    echo '</div>';
                } else {
                    $row = $result->fetch();
                    $studentImage = $row['image_240'];

                    $page->breadcrumbs
                        ->add(__('View Student Profiles'), 'student_view.php')
                        ->add(Format::name('', $row['preferredName'], $row['surname'], 'Student'));

                    echo "<table class='table'>";
                    echo '<tr>';
                    echo "<td style='width: 33%; vertical-align: top'>";
                    echo "<span class='form-label'>" . __('Class') . '</span>';
                    try {
                        $dataDetail = array('pupilsightYearGroupID' => $row['pupilsightYearGroupID']);
                        $sqlDetail = 'SELECT * FROM pupilsightYearGroup WHERE pupilsightYearGroupID=:pupilsightYearGroupID';
                        $resultDetail = $connection2->prepare($sqlDetail);
                        $resultDetail->execute($dataDetail);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                    }
                    if ($resultDetail->rowCount() == 1) {
                        $rowDetail = $resultDetail->fetch();
                        echo __($rowDetail['name']);
                    }
                    echo '</td>';
                    echo "<td style='width: 34%; vertical-align: top'>";
                    echo "<span class='form-label'>" . __('Section') . '</span>';
                    try {
                        $dataDetail = array('pupilsightRollGroupID' => $row['pupilsightRollGroupID']);
                        $sqlDetail = 'SELECT * FROM pupilsightRollGroup WHERE pupilsightRollGroupID=:pupilsightRollGroupID';
                        $resultDetail = $connection2->prepare($sqlDetail);
                        $resultDetail->execute($dataDetail);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                    }
                    if ($resultDetail->rowCount() == 1) {
                        $rowDetail = $resultDetail->fetch();
                        echo $rowDetail['name'];
                    }
                    echo '</td>';
                    echo "<td style='width: 34%; vertical-align: top'>";
                    echo "<span class='form-label'>" . __('House') . '</span>';
                    try {
                        $dataDetail = array('pupilsightHouseID' => $row['pupilsightHouseID']);
                        $sqlDetail = 'SELECT * FROM pupilsightHouse WHERE pupilsightHouseID=:pupilsightHouseID';
                        $resultDetail = $connection2->prepare($sqlDetail);
                        $resultDetail->execute($dataDetail);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                    }
                    if ($resultDetail->rowCount() == 1) {
                        $rowDetail = $resultDetail->fetch();
                        echo $rowDetail['name'];
                    }
                    echo '</td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                    echo "<span class='form-label'>" . __('Email') . '</span>';
                    if ($row['email'] != '') {
                        echo "<i><a href='mailto:" . $row['email'] . "'>" . $row['email'] . '</a></i>';
                    }
                    echo '</td>';
                    echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                    echo "<span class='form-label'>" . __('Website') . '</span>';
                    if ($row['website'] != '') {
                        echo "<i><a href='" . $row['website'] . "'>" . $row['website'] . '</a></i>';
                    }
                    echo '</td>';
                    echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                    echo "<span class='form-label'>" . __('Student ID') . '</span>';
                    if ($row['studentID'] != '') {
                        echo '<i>' . $row['studentID'] . '</a></i>';
                    }
                    echo '</td>';
                    echo '</tr>';
                    echo '</table>';

                    $extendedBriefProfile = getSettingByScope($connection2, 'Students', 'extendedBriefProfile');
                    if ($extendedBriefProfile == 'Y') {
                        echo '<h3>';
                        echo __('Family Details');
                        echo '</h3>';

                        try {
                            $dataFamily = array('pupilsightPersonID' => $pupilsightPersonID);
                            $sqlFamily = 'SELECT * FROM pupilsightFamily JOIN pupilsightFamilyChild ON (pupilsightFamily.pupilsightFamilyID=pupilsightFamilyChild.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID';
                            $resultFamily = $connection2->prepare($sqlFamily);
                            $resultFamily->execute($dataFamily);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                        }

                        if ($resultFamily->rowCount() < 1) {
                            echo "<div class='alert alert-danger'>";
                            echo __('There are no records to display.');
                            echo '</div>';
                        } else {
                            while ($rowFamily = $resultFamily->fetch()) {
                                $count = 1;

                                //Get adults
                                try {
                                    $dataMember = array('pupilsightFamilyID' => $rowFamily['pupilsightFamilyID']);
                                    $sqlMember = 'SELECT * FROM pupilsightFamilyAdult JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status=\'Full\' ORDER BY contactPriority, surname, preferredName';
                                    $resultMember = $connection2->prepare($sqlMember);
                                    $resultMember->execute($dataMember);
                                } catch (PDOException $e) {
                                    echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                }

                                while ($rowMember = $resultMember->fetch()) {
                                    echo '<h4>';
                                    echo __('Adult') . ' ' . $count;
                                    echo '</h4>';
                                    echo "<table class='table'>";
                                    echo '<tr>';
                                    echo "<td style='width: 33%; vertical-align: top'>";
                                    echo "<span class='form-label'>" . __('Name') . '</span>';
                                    echo Format::name($rowMember['title'], $rowMember['preferredName'], $rowMember['surname'], 'Parent');
                                    echo '</td>';
                                    echo "<td style='width: 33%; vertical-align: top'>";
                                    echo "<span class='form-label'>" . __('First Language') . '</span>';
                                    echo $rowMember['languageFirst'];
                                    echo '</td>';
                                    echo "<td style='width: 34%; vertical-align: top' colspan=2>";
                                    echo "<span class='form-label'>" . __('Second Language') . '</span>';
                                    echo $rowMember['languageSecond'];
                                    echo '</td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo "<td style='width: 33%; padding-top: 15px; width: 33%; vertical-align: top'>";
                                    echo "<span class='form-label'>" . __('Contact By Phone') . '</span>';
                                    if ($rowMember['contactCall'] == 'N') {
                                        echo __('Do not contact by phone.');
                                    } elseif ($rowMember['contactCall'] == 'Y' and ($rowMember['phone1'] != '' or $rowMember['phone2'] != '' or $rowMember['phone3'] != '' or $rowMember['phone4'] != '')) {
                                        for ($i = 1; $i < 5; ++$i) {
                                            if ($rowMember['phone' . $i] != '') {
                                                if ($rowMember['phone' . $i . 'Type'] != '') {
                                                    echo $rowMember['phone' . $i . 'Type'] . ':</i> ';
                                                }
                                                if ($rowMember['phone' . $i . 'CountryCode'] != '') {
                                                    echo '+' . $rowMember['phone' . $i . 'CountryCode'] . ' ';
                                                }
                                                echo formatPhone($rowMember['phone' . $i]) . '';
                                            }
                                        }
                                    }
                                    echo '</td>';
                                    echo "<td style='width: 33%; padding-top: 15px; width: 34%; vertical-align: top' colspan=2>";
                                    echo "<span class='form-label'>" . __('Contact By Email') . '</span>';
                                    if ($rowMember['contactEmail'] == 'N') {
                                        echo __('Do not contact by email.');
                                    } elseif ($rowMember['contactEmail'] == 'Y' and ($rowMember['email'] != '' or $rowMember['emailAlternate'] != '')) {
                                        if ($rowMember['email'] != '') {
                                            echo "<a href='mailto:" . $rowMember['email'] . "'>" . $rowMember['email'] . '</a>';
                                        }
                                        echo '';
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                    echo '</table>';
                                    ++$count;
                                }
                            }
                        }
                    }
                    //Set sidebar
                    $_SESSION[$guid]['sidebarExtra'] = getUserPhoto($guid, $row['image_240'], 240);
                }
                return;
            } else {
                try {
                    if ($highestAction == 'View Student Profile_myChildren') {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID1' => $_GET['pupilsightPersonID'], 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID'], 'today' => date('Y-m-d'));
                        $sql = "SELECT * FROM pupilsightFamilyChild
                            JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
                            JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
                            JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                            JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                            WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full'
                            AND (dateStart IS NULL OR dateStart<=:today) AND (dateEnd IS NULL  OR dateEnd>=:today)
                            AND pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID1
                            AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID2
                            AND childDataAccess='Y'";
                    } else if ($highestAction == 'View Student Profile_my') {
                        $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID, 'today' => date('Y-m-d'));
                        $sql = "SELECT pupilsightPerson.*, pupilsightStudentEnrolment.* FROM pupilsightPerson
                            LEFT JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                            WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID
                            AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full'
                            AND (dateStart IS NULL OR dateStart<=:today) AND (dateEnd IS NULL OR dateEnd>=:today)";
                    } else if ($highestAction == 'Student Profile_full' || $highestAction == 'View Student Profile_fullNoNotes') {
                        if ($allStudents != 'on') {
                            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID, 'today' => date('Y-m-d'));
                            $sql = "SELECT * FROM pupilsightPerson
                                JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                                WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID
                                AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID AND status='Full'
                                AND (dateStart IS NULL OR dateStart<=:today) AND (dateEnd IS NULL  OR dateEnd>=:today) ";
                        } else {
                            $data = array('pupilsightPersonID' => $pupilsightPersonID);
                            $sql = "SELECT DISTINCT pupilsightPerson.* FROM pupilsightPerson
                                LEFT JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                                WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID";
                        }
                    } else {
                        //Acess denied
                        echo "<div class='alert alert-danger'>";
                        echo __('You do not have access to this action.');
                        echo '</div>';
                        return;
                    }
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                    return;
                }

                if ($result->rowCount() != 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('The selected record does not exist, or you do not have access to it.');
                    echo '</div>';
                    return;
                } else {
                    $row = $result->fetch();
                    $studentImage = $row['image_240'];

                    $page->breadcrumbs
                        ->add(__('View Student Profiles'), 'student_view.php')
                        ->add(Format::name('', $row['preferredName'], $row['surname'], 'Student'));


                    $subpage = null;
                    if (isset($_GET['subpage'])) {
                        $subpage = $_GET['subpage'];
                    }
                    $hook = null;
                    if (isset($_GET['hook'])) {
                        $hook = $_GET['hook'];
                    }
                    $module = null;
                    if (isset($_GET['module'])) {
                        $module = $_GET['module'];
                    }
                    $action = null;
                    if (isset($_GET['action'])) {
                        $action = $_GET['action'];
                    }

                    // When viewing left students, they won't have a year group ID
                    if (empty($row['pupilsightYearGroupID'])) $row['pupilsightYearGroupID'] = '';

                    if ($subpage == '' and ($hook == '' or $module == '' or $action == '')) {
                        $subpage = 'Overview';
                    }

                    if ($search != '' or $allStudents != '') {
                        echo "<div class='linkTop'>";
                        echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Students/student_view.php&search=' . $search . "&allStudents=$allStudents'>" . __('Back to Search Results') . '</a>';
                        echo '</div>';
                    }


                    $st = array("Overview", "Personal", "Family", "Emergency", "Medical", "Notes", "Attendance", "Markbook", "Internal Assessment", "External Assessment", "Individual Needs", "Library Borrowing", "Timetable", "Activities", "Homework", "Behaviour", "Academic");
?>
                    <div class="mb-4">
                        <ul class="nav nav-tabs" data-toggle="tabs">
                            <?php
                            $len = count($st);
                            $i = 0;
                            while ($i < $len) {
                                $stactive = "";
                                if ($subpage == $st[$i]) {
                                    $stactive = " active";
                                }
                            ?>
                                <li class="nav-item">
                                    <a href="index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=<?= $pupilsightPersonID . "&subpage=" . $st[$i] ?>" class="nav-link <?= $stactive; ?>"><?= $st[$i] ?></a>
                                </li>
                            <?php
                                $i++;
                            }
                            ?>
                        </ul>
                    </div>


                    <?php
                    /*
                    echo '<h2>';
                    if ($subpage != '') {
                        echo $subpage;
                    } else {
                        echo $hook;
                    }
                    echo '</h2>';
                    */

                    if ($subpage == 'Overview') {


                        if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage.php') == true) {
                            echo "<div class='text-right'>";
                            echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Students/student_edit.php&pupilsightPersonID=$pupilsightPersonID' class='btn btn-link'><span class='mdi mdi-pencil-box-outline mdi-18px mr-1'></span> Edit</a> ";
                            echo '</div>';
                        }

                        //Medical alert!
                        $alert = getHighestMedicalRisk($guid,  $pupilsightPersonID, $connection2);
                        if ($alert != false) {
                            $highestLevel = $alert[1];
                            $highestColour = $alert[3];
                            $highestColourBG = $alert[4];
                            echo "<div class='alert alert-danger' style='background-color: #" . $highestColourBG . '; border: 1px solid #' . $highestColour . '; color: #' . $highestColour . "'>";
                            echo '<b>' . sprintf(__('This student has one or more %1$s risk medical conditions.'), strToLower(__($highestLevel))) . '</b>';
                            echo '</div>';
                        }

                        echo "<table id='table_basic_information' class='table'>";
                        echo '<tr>';

                        echo "<td id='officialName' style='width: 34%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Official Name') . '</span>';
                        echo $row['officialName'];
                        echo '</td>';

                        echo "<td id='preferredName' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Preferred Name') . '</span>';
                        echo Format::name('', $row['preferredName'], $row['surname'], 'Student');
                        echo '</td>';

                        echo "<td id='nameInCharacters' style='width: 34%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Name In Characters') . '</span>';
                        echo $row['nameInCharacters'];
                        echo '</td>';

                        echo '</tr>';
                        echo '<tr>';
                        echo "<td id='pupilsightYearGroupID' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Class') . '</span>';
                        if (isset($row['pupilsightYearGroupID'])) {
                            try {
                                $dataDetail = array('pupilsightYearGroupID' => $row['pupilsightYearGroupID']);
                                $sqlDetail = 'SELECT * FROM pupilsightYearGroup WHERE pupilsightYearGroupID=:pupilsightYearGroupID';
                                $resultDetail = $connection2->prepare($sqlDetail);
                                $resultDetail->execute($dataDetail);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }
                            if ($resultDetail->rowCount() == 1) {
                                $rowDetail = $resultDetail->fetch();
                                echo __($rowDetail['name']);
                                $dayTypeOptions = getSettingByScope($connection2, 'User Admin', 'dayTypeOptions');
                                if ($dayTypeOptions != '') {
                                    echo ' (' . $row['dayType'] . ')';
                                }
                                echo '</i>';
                            }
                        }
                        echo '</td>';
                        echo "<td id='pupilsightRollGroupID' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Section') . '</span>';
                        if (isset($row['pupilsightRollGroupID'])) {
                            try {
                                $dataDetail = array('pupilsightRollGroupID' => $row['pupilsightRollGroupID']);
                                $sqlDetail = 'SELECT * FROM pupilsightRollGroup WHERE pupilsightRollGroupID=:pupilsightRollGroupID';
                                $resultDetail = $connection2->prepare($sqlDetail);
                                $resultDetail->execute($dataDetail);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }
                            if ($resultDetail->rowCount() == 1) {
                                $rowDetail = $resultDetail->fetch();
                                if (isActionAccessible($guid, $connection2, '/modules/Roll Groups/rollGroups_details.php')) {
                                    echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Roll Groups/rollGroups_details.php&pupilsightRollGroupID=' . $rowDetail['pupilsightRollGroupID'] . "'>" . $rowDetail['name'] . '</a>';
                                } else {
                                    echo $rowDetail['name'];
                                }
                                $primaryTutor = $rowDetail['pupilsightPersonIDTutor'];
                            }
                        }
                        echo '</td>';
                        echo "<td id='pupilsightPersonIDTutor' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Tutors') . '</span>';
                        if (isset($rowDetail['pupilsightPersonIDTutor'])) {
                            try {
                                $dataDetail = array('pupilsightPersonIDTutor' => $rowDetail['pupilsightPersonIDTutor'], 'pupilsightPersonIDTutor2' => $rowDetail['pupilsightPersonIDTutor2'], 'pupilsightPersonIDTutor3' => $rowDetail['pupilsightPersonIDTutor3']);
                                $sqlDetail = 'SELECT pupilsightPersonID, title, surname, preferredName FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonIDTutor OR pupilsightPersonID=:pupilsightPersonIDTutor2 OR pupilsightPersonID=:pupilsightPersonIDTutor3';
                                $resultDetail = $connection2->prepare($sqlDetail);
                                $resultDetail->execute($dataDetail);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }
                            while ($rowDetail = $resultDetail->fetch()) {
                                if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_view_details.php')) {
                                    echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Staff/staff_view_details.php&pupilsightPersonID=' . $rowDetail['pupilsightPersonID'] . "'>" . Format::name('', $rowDetail['preferredName'], $rowDetail['surname'], 'Staff', false, true) . '</a>';
                                } else {
                                    echo Format::name($rowDetail['title'], $rowDetail['preferredName'], $rowDetail['surname'], 'Staff');
                                }
                                if ($rowDetail['pupilsightPersonID'] == $primaryTutor and $resultDetail->rowCount() > 1) {
                                    echo ' (' . __('Main Tutor') . ')';
                                }
                                echo '';
                            }
                        }
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo "<td id='username' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Username') . '</span>';
                        echo $row['username'];
                        echo '</td>';
                        echo "<td id='age' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Age') . '</span>';
                        if (is_null($row['dob']) == false and $row['dob'] != '0000-00-00') {
                            echo Format::age($row['dob']);
                        }
                        echo '</td>';
                        echo "<td id='pupilsightYearGroupID' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        try {
                            $dataDetail = array('pupilsightYearGroupID' => $row['pupilsightYearGroupID']);
                            $sqlDetail = "SELECT DISTINCT pupilsightPersonID, title, surname, preferredName FROM pupilsightPerson JOIN pupilsightYearGroup ON (pupilsightYearGroup.pupilsightPersonIDHOY=pupilsightPersonID) WHERE status='Full' AND pupilsightYearGroupID=:pupilsightYearGroupID";
                            $resultDetail = $connection2->prepare($sqlDetail);
                            $resultDetail->execute($dataDetail);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                        }
                        if ($resultDetail->rowCount() == 1) {
                            echo "<span style='font-size: 115%; font-weight: bold;'>" . __('Head of Year') . '</span>';
                            $rowDetail = $resultDetail->fetch();
                            if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_view_details.php')) {
                                echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Staff/staff_view_details.php&pupilsightPersonID=' . $rowDetail['pupilsightPersonID'] . "'>" . Format::name('', $rowDetail['preferredName'], $rowDetail['surname'], 'Staff', false, true) . '</a>';
                            } else {
                                echo Format::name($rowDetail['title'], $rowDetail['preferredName'], $rowDetail['surname'], 'Staff');
                            }
                            echo '';
                        }
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo "<td id='website' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Website') . '</span>';
                        if ($row['website'] != '') {
                            echo "<i><a href='" . $row['website'] . "'>" . $row['website'] . '</a></i>';
                        }
                        echo '</td>';
                        echo "<td id='email' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Email') . '</span>';
                        if ($row['email'] != '') {
                            echo "<i><a href='mailto:" . $row['email'] . "'>" . $row['email'] . '</a></i>';
                        }
                        echo '</td>';
                        echo "<td id='dateStart' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('School History') . '</span>';
                        if ($row['dateStart'] != '') {
                            echo '<u>' . __('Start Date') . '</u>: ' . dateConvertBack($guid, $row['dateStart']) . '</br>';
                        }
                        try {
                            $dataSelect = array('pupilsightPersonID' => $row['pupilsightPersonID']);
                            $sqlSelect = "SELECT pupilsightRollGroup.name AS rollGroup, pupilsightSchoolYear.name AS schoolYear
                                FROM pupilsightStudentEnrolment
                                JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                                JOIN pupilsightSchoolYear ON (pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID)
                                WHERE pupilsightPersonID=:pupilsightPersonID
                                AND (pupilsightSchoolYear.status = 'Current' OR pupilsightSchoolYear.status='Past')
                                ORDER BY pupilsightStudentEnrolment.pupilsightSchoolYearID";
                            $resultSelect = $connection2->prepare($sqlSelect);
                            $resultSelect->execute($dataSelect);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                        }
                        while ($rowSelect = $resultSelect->fetch()) {
                            echo '<u>' . $rowSelect['schoolYear'] . '</u>: ' . $rowSelect['rollGroup'] . '';
                        }
                        if ($row['dateEnd'] != '') {
                            echo '<u>' . __('End Date') . '</u>: ' . dateConvertBack($guid, $row['dateEnd']) . '</br>';
                        }
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo "<td id='lockerNumber' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Locker Number') . '</span>';
                        if ($row['lockerNumber'] != '') {
                            echo $row['lockerNumber'];
                        }
                        echo '</td>';
                        echo "<td id='studentID' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Student ID') . '</span>';
                        if ($row['studentID'] != '') {
                            echo $row['studentID'];
                        }
                        echo '</td>';
                        echo "<td id='pupilsightHouseID' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('House') . '</span>';
                        try {
                            $dataDetail = array('pupilsightHouseID' => $row['pupilsightHouseID']);
                            $sqlDetail = 'SELECT * FROM pupilsightHouse WHERE pupilsightHouseID=:pupilsightHouseID';
                            $resultDetail = $connection2->prepare($sqlDetail);
                            $resultDetail->execute($dataDetail);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                        }
                        if ($resultDetail->rowCount() == 1) {
                            $rowDetail = $resultDetail->fetch();
                            echo $rowDetail['name'];
                        }
                        echo '</td>';
                        echo '</tr>';
                        $privacySetting = getSettingByScope($connection2, 'User Admin', 'privacy');
                        if ($privacySetting == 'Y') {
                            echo '<tr>';
                            echo "<td id='privacy' tyle='width: 33%; padding-top: 15px; vertical-align: top' colspan=3>";
                            echo "<span class='form-label'>" . __('Privacy') . '</span>';
                            if ($row['privacy'] != '') {
                                echo "<span style='color: #cc0000; background-color: #F6CECB'>";
                                echo __('Privacy required:') . ' ' . $row['privacy'];
                                echo '</span>';
                            } else {
                                echo "<span style='color: #390; background-color: #D4F6DC;'>";
                                echo __('Privacy not required or not set.');
                                echo '</span>';
                            }

                            echo '</td>';
                            echo '</tr>';
                        }
                        $studentAgreementOptions = getSettingByScope($connection2, 'School Admin', 'studentAgreementOptions');
                        if ($studentAgreementOptions != '') {
                            echo '<tr>';
                            echo "<td style='width: 33%; padding-top: 15px; vertical-align: top' colspan=3>";
                            echo "<span class='form-label'>" . __('Student Agreements') . '</span>';
                            echo __('Agreements Signed:') . ' ' . $row['studentAgreements'];
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';

                        //Get and display a list of student's teachers

                        echo '<h4>';
                        echo __("Student's Teachers");
                        echo '</h4>';
                        try {
                            $dataDetail = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightYearGroupID' => $row['pupilsightYearGroupID']);
                            $sqlDetail = "
                                (SELECT DISTINCT teacher.surname, teacher.preferredName, teacher.email FROM pupilsightPerson AS teacher JOIN pupilsightCourseClassPerson AS teacherClass ON (teacherClass.pupilsightPersonID=teacher.pupilsightPersonID)  JOIN pupilsightCourseClassPerson AS studentClass ON (studentClass.pupilsightCourseClassID=teacherClass.pupilsightCourseClassID) JOIN pupilsightPerson AS student ON (studentClass.pupilsightPersonID=student.pupilsightPersonID) JOIN pupilsightCourseClass ON (studentClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE teacher.status='Full' AND teacherClass.role='Teacher' AND studentClass.role='Student' AND student.pupilsightPersonID=:pupilsightPersonID AND pupilsightCourse.pupilsightSchoolYearID=(SELECT pupilsightSchoolYearID FROM pupilsightSchoolYear WHERE status='Current') ORDER BY teacher.preferredName, teacher.surname, teacher.email)
                                UNION
                                (SELECT DISTINCT surname, preferredName, email FROM pupilsightPerson JOIN pupilsightYearGroup ON (pupilsightYearGroup.pupilsightPersonIDHOY=pupilsightPersonID) WHERE status='Full' AND pupilsightYearGroupID=:pupilsightYearGroupID)
                                ORDER BY preferredName, surname, email";
                            $resultDetail = $connection2->prepare($sqlDetail);
                            $resultDetail->execute($dataDetail);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                        }
                        if ($resultDetail->rowCount() < 1) {
                            echo "<div class='alert alert-warning'>";
                            echo __('There are no records to display.');
                            echo '</div>';
                        } else {
                            echo '<ul>';
                            while ($rowDetail = $resultDetail->fetch()) {
                                echo '<li>' . htmlPrep(Format::name('', $rowDetail['preferredName'], $rowDetail['surname'], 'Student', false));
                                if ($rowDetail['email'] != '') {
                                    echo htmlPrep(' <' . $rowDetail['email'] . '>');
                                }
                                echo '</li>';
                            }
                            echo '</ul>';
                        }

                        //Get and display a list of student's educational assistants
                        try {
                            $dataDetail = array('pupilsightPersonID1' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID2' => $pupilsightPersonID);
                            $sqlDetail = "(SELECT DISTINCT surname, preferredName, email
                                FROM pupilsightPerson
                                    JOIN pupilsightINAssistant ON (pupilsightINAssistant.pupilsightPersonIDAssistant=pupilsightPerson.pupilsightPersonID)
                                WHERE status='Full'
                                    AND pupilsightPersonIDStudent=:pupilsightPersonID1)
                            UNION
                            (SELECT DISTINCT surname, preferredName, email
                                FROM pupilsightPerson
                                    JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightPersonIDEA=pupilsightPerson.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDEA2=pupilsightPerson.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDEA3=pupilsightPerson.pupilsightPersonID)
                                    JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                                    JOIN pupilsightSchoolYear ON (pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID)
                                WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
                                    AND pupilsightStudentEnrolment.pupilsightPersonID=:pupilsightPersonID2
                            )
                            ORDER BY preferredName, surname, email";
                            $resultDetail = $connection2->prepare($sqlDetail);
                            $resultDetail->execute($dataDetail);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                        }
                        if ($resultDetail->rowCount() > 0) {
                            echo '<h4>';
                            echo __("Student's Educational Assistants");
                            echo '</h4>';

                            echo '<ul>';
                            while ($rowDetail = $resultDetail->fetch()) {
                                echo '<li>' . htmlPrep(Format::name('', $rowDetail['preferredName'], $rowDetail['surname'], 'Student', false));
                                if ($rowDetail['email'] != '') {
                                    echo htmlPrep(' <' . $rowDetail['email'] . '>');
                                }
                                echo '</li>';
                            }
                            echo '</ul>';
                        }

                        //Show timetable
                        echo "<a name='timetable'></a>";
                        //Display timetable if available, otherwise just list classes
                        if (isActionAccessible($guid, $connection2, '/modules/Timetable/tt_view.php') == true) {
                            echo "<div class='row'>";
                            echo "<div class='col-md-6 col-sm-12'>";
                            echo '<h4>';
                            echo __('Timetable');
                            echo '</h4>';
                            echo "</div>";
                            echo "<div class='col-md-6 col-sm-12 text-right'>";
                            if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit.php') == true) {
                                $role = getRoleCategory($row['pupilsightRoleIDPrimary'], $connection2);
                                if ($role == 'Student' or $role == 'Staff') {
                                    echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit.php&pupilsightPersonID=$pupilsightPersonID&pupilsightSchoolYearID=" . $_SESSION[$guid]['pupilsightSchoolYearID'] . "&type=$role' class='btn-link'><span class='mdi mdi-pencil-box-outline mr-2'></i> Edit</a> ";
                                }
                            }
                            echo "</div>";
                            echo "</div>";

                            include './modules/Timetable/moduleFunctions.php';
                            $ttDate = null;
                            if (isset($_POST['ttDate'])) {
                                $ttDate = dateConvertToTimestamp(dateConvert($guid, $_POST['ttDate']));
                            }
                            $tt = renderTT($guid, $connection2, $pupilsightPersonID, '', false, $ttDate, '/modules/Students/student_view_details.php', "&pupilsightPersonID=$pupilsightPersonID&search=$search&allStudents=$allStudents#timetable");
                            if ($tt != false) {
                                echo $tt;
                            } else {
                                echo "<div class='alert alert-danger'>";
                                echo __('There are no records to display.');
                                echo '</div>';
                            }
                        } else {
                            echo '<h4>';
                            echo __('Class List');
                            echo '</h4>';
                            try {
                                $dataDetail = array('pupilsightPersonID' => $pupilsightPersonID);
                                $sqlDetail = "SELECT DISTINCT pupilsightCourse.name AS courseFull, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class
                                    FROM pupilsightCourseClassPerson
                                        JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                                        JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID)
                                    WHERE pupilsightCourseClassPerson.role='Student' AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND pupilsightCourse.pupilsightSchoolYearID=(SELECT pupilsightSchoolYearID FROM pupilsightSchoolYear WHERE status='Current') ORDER BY course, class";
                                $resultDetail = $connection2->prepare($sqlDetail);
                                $resultDetail->execute($dataDetail);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }
                            if ($resultDetail->rowCount() < 1) {
                                echo "<div class='alert alert-danger'>";
                                echo __('There are no records to display.');
                                echo '</div>';
                            } else {
                                echo '<ul>';
                                while ($rowDetail = $resultDetail->fetch()) {
                                    echo '<li>';
                                    echo htmlPrep($rowDetail['courseFull'] . ' (' . $rowDetail['course'] . '.' . $rowDetail['class'] . ')');
                                    echo '</li>';
                                }
                                echo '</ul>';
                            }
                        }
                    } elseif ($subpage == 'Personal') {

                        if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage.php') == true) {
                            echo "<div class='text-right'>";
                            echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Students/student_edit.php&pupilsightPersonID=$pupilsightPersonID' class='btn btn-link'><span class='mdi mdi-pencil-box-outline mdi-18px mr-1'></span> Edit</a> ";
                            echo '</div>';
                        }


                        echo "<table id='basic_information' class='table'>";
                        echo '<tr>';
                        echo "<td id='surname' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Surname') . '</span>';
                        echo $row['surname'];
                        echo '</td>';
                        echo "<td id='firstName' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('First Name') . '</span>';
                        echo $row['firstName'];
                        echo '</td>';
                        echo "<td style='width: 34%; vertical-align: top'>";

                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';

                        echo "<td id='officialName' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Official Name') . '</span>';
                        echo $row['officialName'];
                        echo '</td>';
                        echo "<td id='nameInCharacters' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Name In Characters') . '</span>';
                        echo $row['nameInCharacters'];
                        echo '</td>';

                        echo "<td id='preferredName' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Preferred Name') . '</span>';
                        echo Format::name('', $row['preferredName'], $row['surname'], 'Student');
                        echo '</td>';

                        echo '</tr>';
                        echo '<tr>';
                        echo "<td id='gender' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Gender') . '</span>';
                        echo $row['gender'];
                        echo '</td>';
                        echo "<td id='dob' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Date of Birth') . '</span>';
                        if (is_null($row['dob']) == false and $row['dob'] != '0000-00-00') {
                            echo dateConvertBack($guid, $row['dob']);
                        }
                        echo '</td>';
                        echo "<td id='age' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Age') . '</span>';
                        if (is_null($row['dob']) == false and $row['dob'] != '0000-00-00') {
                            echo Format::age($row['dob']);
                        }
                        echo '</td>';
                        echo '</tr>';
                        echo '</table>';

                        echo '<h4>';
                        echo __('Contacts');
                        echo '</h4>';

                        echo "<table id='contact_information' class='table'>";
                        $numberCount = 0;
                        if ($row['phone1'] != '' or $row['phone2'] != '' or $row['phone3'] != '' or $row['phone4'] != '') {
                            echo '<tr>';
                            for ($i = 1; $i < 5; ++$i) {
                                if ($row['phone' . $i] != '') {
                                    ++$numberCount;
                                    echo "<td id='phone' width: 33%; style='vertical-align: top'>";
                                    echo "<span class='form-label'>" . __('Phone') . " $numberCount</span>";
                                    if ($row['phone' . $i . 'Type'] != '') {
                                        echo $row['phone' . $i . 'Type'] . ':</i> ';
                                    }
                                    if ($row['phone' . $i . 'CountryCode'] != '') {
                                        echo '+' . $row['phone' . $i . 'CountryCode'] . ' ';
                                    }
                                    echo formatPhone($row['phone' . $i]) . '';
                                    echo '</td>';
                                } else {
                                    echo "<td width: 33%; style='vertical-align: top'>";

                                    echo '</td>';
                                }
                            }
                            echo '</tr>';
                        }
                        echo '<tr>';
                        echo "<td id='email' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Email') . '</span>';
                        if ($row['email'] != '') {
                            echo "<i><a href='mailto:" . $row['email'] . "'>" . $row['email'] . '</a></i>';
                        }
                        echo '</td>';
                        echo "<td id='emailAlternate' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Alternate Email') . '</span>';
                        if ($row['emailAlternate'] != '') {
                            echo "<i><a href='mailto:" . $row['emailAlternate'] . "'>" . $row['emailAlternate'] . '</a></i>';
                        }
                        echo '</td>';
                        echo "<td id='website' style='width: 33%; padding-top: 15px; vertical-align: top' colspan=2>";
                        echo "<span class='form-label'>" . __('Website') . '</span>';
                        if ($row['website'] != '') {
                            echo "<i><a href='" . $row['website'] . "'>" . $row['website'] . '</a></i>';
                        }
                        echo '</td>';
                        echo '</tr>';
                        if ($row['address1'] != '') {
                            echo '<tr>';
                            echo "<td id='address1' style='width: 33%; padding-top: 15px; vertical-align: top' colspan=4>";
                            echo "<span class='form-label'>" . __('Address 1') . '</span>';
                            $address1 = addressFormat($row['address1'], $row['address1District'], $row['address1Country']);
                            if ($address1 != false) {
                                echo $address1;
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                        if ($row['address2'] != '') {
                            echo '<tr>';
                            echo "<td id='address2' style='width: 33%; padding-top: 15px; vertical-align: top' colspan=3>";
                            echo "<span class='form-label'>" . __('Address 2') . '</span>';
                            $address2 = addressFormat($row['address2'], $row['address2District'], $row['address2Country']);
                            if ($address2 != false) {
                                echo $address2;
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';

                        echo '<h4>';
                        echo __('School Information');
                        echo '</h4>';

                        echo "<table id='school_information' class='table'>";
                        echo '<tr>';
                        echo "<td id='lastSchool' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Last School') . '</span>';
                        echo $row['lastSchool'];
                        echo '</td>';
                        echo "<td id='dateStart' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Start Date') . '</span>';
                        echo dateConvertBack($guid, $row['dateStart']);
                        echo '</td>';
                        echo "<td id='pupilsightSchoolYearIDClassOf' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Class Of') . '</span>';
                        if ($row['pupilsightSchoolYearIDClassOf'] == '') {
                            echo '<i>' . __('NA') . '</i>';
                        } else {
                            try {
                                $dataDetail = array('pupilsightSchoolYearIDClassOf' => $row['pupilsightSchoolYearIDClassOf']);
                                $sqlDetail = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearIDClassOf';
                                $resultDetail = $connection2->prepare($sqlDetail);
                                $resultDetail->execute($dataDetail);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }
                            if ($resultDetail->rowCount() == 1) {
                                $rowDetail = $resultDetail->fetch();
                                echo $rowDetail['name'];
                            }
                        }

                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo "<td id='nextSchool' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Next School') . '</span>';
                        echo $row['nextSchool'];
                        echo '</td>';
                        echo "<td id='dateEnd' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('End Date') . '</span>';
                        echo dateConvertBack($guid, $row['dateEnd']);
                        echo '</td>';
                        echo "<td id='departureReason' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Departure Reason') . '</span>';
                        echo $row['departureReason'];
                        echo '</td>';
                        echo '</tr>';
                        $dayTypeOptions = getSettingByScope($connection2, 'User Admin', 'dayTypeOptions');
                        if ($dayTypeOptions != '') {
                            echo '<tr>';
                            echo "<td id='dayType' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                            echo "<span class='form-label'>" . __('Day Type') . '</span>';
                            echo $row['dayType'];
                            echo '</td>';
                            echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";

                            echo '</td>';
                            echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";

                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';

                        echo '<h4>';
                        echo __('Background');
                        echo '</h4>';

                        echo "<table id='background_information' class='table'>";
                        echo '<tr>';
                        echo "<td id='countryOfBirth' width: 33%; style='vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Country of Birth') . '</span>';
                        if ($row['countryOfBirth'] != '')
                            echo $row['countryOfBirth'];
                        if ($row['birthCertificateScan'] != '')
                            echo "<a target='_blank' href='" . $_SESSION[$guid]['absoluteURL'] . '/' . $row['birthCertificateScan'] . "'>View Birth Certificate</a>";
                        echo '</td>';
                        echo "<td id='ethnicity' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Ethnicity') . '</span>';
                        echo $row['ethnicity'];
                        echo '</td>';
                        echo "<td id='religion' style='width: 34%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Religion') . '</span>';
                        echo $row['religion'];
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo "<td id='citizenship1' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Citizenship 1') . '</span>';
                        if ($row['citizenship1'] != '')
                            echo $row['citizenship1'];
                        if ($row['citizenship1Passport'] != '')
                            echo $row['citizenship1Passport'];
                        if ($row['citizenship1PassportScan'] != '')
                            echo "<a target='_blank' href='" . $_SESSION[$guid]['absoluteURL'] . '/' . $row['citizenship1PassportScan'] . "'>View Passport</a>";
                        echo '</td>';
                        echo "<td id='citizenship2' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Citizenship 2') . '</span>';
                        echo $row['citizenship2'];
                        if ($row['citizenship2Passport'] != '') {
                            echo '';
                            echo $row['citizenship2Passport'];
                        }
                        echo '</td>';
                        echo "<td id='nationalIDCardNumber' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        if ($_SESSION[$guid]['country'] == '') {
                            echo "<span class='form-label'>" . __('National ID Card') . '</span>';
                        } else {
                            echo "<span class='form-label'>" . $_SESSION[$guid]['country'] . ' ' . __('ID Card') . '</span>';
                        }
                        if ($row['nationalIDCardNumber'] != '')
                            echo $row['nationalIDCardNumber'];
                        if ($row['nationalIDCardScan'] != '')
                            echo "<a target='_blank' href='" . $_SESSION[$guid]['absoluteURL'] . '/' . $row['nationalIDCardScan'] . "'>View ID Card</a>";
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo "<td id='languageFirst' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('First Language') . '</span>';
                        echo $row['languageFirst'];
                        echo '</td>';
                        echo "<td id='languageSecond' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Second Language') . '</span>';
                        echo $row['languageSecond'];
                        echo '</td>';
                        echo "<td  id='languageThird' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Third Language') . '</span>';
                        echo $row['languageThird'];
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo "<td id='residencyStatus' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        if ($_SESSION[$guid]['country'] == '') {
                            echo "<span class='form-label'>" . __('Residency/Visa Type') . '</span>';
                        } else {
                            echo "<span class='form-label'>" . $_SESSION[$guid]['country'] . ' ' . __('Residency/Visa Type') . '</span>';
                        }
                        echo $row['residencyStatus'];
                        echo '</td>';
                        echo "<td id='visaExpiryDate' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        if ($_SESSION[$guid]['country'] == '') {
                            echo "<span class='form-label'>" . __('Visa Expiry Date') . '</span>';
                        } else {
                            echo "<span class='form-label'>" . $_SESSION[$guid]['country'] . ' ' . __('Visa Expiry Date') . '</span>';
                        }
                        if ($row['visaExpiryDate'] != '') {
                            echo dateConvertBack($guid, $row['visaExpiryDate']);
                        }
                        echo '</td>';
                        echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";

                        echo '</td>';
                        echo '</tr>';
                        echo '</table>';

                        echo '<h4>';
                        echo 'School Data';
                        echo '</h4>';
                        echo "<table id='school_information' class='table'>";
                        echo '<tr>';
                        echo "<td id='pupilsightYearGroupID' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Class') . '</span>';
                        if (isset($row['pupilsightYearGroupID'])) {
                            try {
                                $dataDetail = array('pupilsightYearGroupID' => $row['pupilsightYearGroupID']);
                                $sqlDetail = 'SELECT * FROM pupilsightYearGroup WHERE pupilsightYearGroupID=:pupilsightYearGroupID';
                                $resultDetail = $connection2->prepare($sqlDetail);
                                $resultDetail->execute($dataDetail);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }
                            if ($resultDetail->rowCount() == 1) {
                                $rowDetail = $resultDetail->fetch();
                                echo __($rowDetail['name']);
                            }
                        }
                        echo '</td>';
                        echo "<td id='pupilsightPersonIDTutor' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Section') . '</span>';
                        if (isset($row['pupilsightRollGroupID'])) {
                            $sqlDetail = "SELECT * FROM pupilsightRollGroup WHERE pupilsightRollGroupID='" . $row['pupilsightRollGroupID'] . "'";
                            try {
                                $dataDetail = array('pupilsightRollGroupID' => $row['pupilsightRollGroupID']);
                                $sqlDetail = 'SELECT * FROM pupilsightRollGroup WHERE pupilsightRollGroupID=:pupilsightRollGroupID';
                                $resultDetail = $connection2->prepare($sqlDetail);
                                $resultDetail->execute($dataDetail);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }
                            if ($resultDetail->rowCount() == 1) {
                                $rowDetail = $resultDetail->fetch();
                                if (isActionAccessible($guid, $connection2, '/modules/Roll Groups/rollGroups_details.php')) {
                                    echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Roll Groups/rollGroups_details.php&pupilsightRollGroupID=' . $rowDetail['pupilsightRollGroupID'] . "'>" . $rowDetail['name'] . '</a>';
                                } else {
                                    echo $rowDetail['name'];
                                }
                                $primaryTutor = $rowDetail['pupilsightPersonIDTutor'];
                            }
                        }
                        echo '</td>';
                        echo "<td id='pupilsightPersonIDTutor' style='width: 34%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Tutors') . '</span>';
                        if (isset($rowDetail['pupilsightPersonIDTutor'])) {
                            try {
                                $dataDetail = array('pupilsightPersonIDTutor' => $rowDetail['pupilsightPersonIDTutor'], 'pupilsightPersonIDTutor2' => $rowDetail['pupilsightPersonIDTutor2'], 'pupilsightPersonIDTutor3' => $rowDetail['pupilsightPersonIDTutor3']);
                                $sqlDetail = 'SELECT pupilsightPersonID, title, surname, preferredName FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonIDTutor OR pupilsightPersonID=:pupilsightPersonIDTutor2 OR pupilsightPersonID=:pupilsightPersonIDTutor3';
                                $resultDetail = $connection2->prepare($sqlDetail);
                                $resultDetail->execute($dataDetail);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }
                            while ($rowDetail = $resultDetail->fetch()) {
                                if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_view_details.php')) {
                                    echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Staff/staff_view_details.php&pupilsightPersonID=' . $rowDetail['pupilsightPersonID'] . "'>" . Format::name('', $rowDetail['preferredName'], $rowDetail['surname'], 'Staff', false, true) . '</a>';
                                } else {
                                    echo Format::name($rowDetail['title'], $rowDetail['preferredName'], $rowDetail['surname'], 'Staff');
                                }
                                if ($rowDetail['pupilsightPersonID'] == $primaryTutor and $resultDetail->rowCount() > 1) {
                                    echo ' (' . __('Main Tutor') . ')';
                                }
                                echo '';
                            }
                        }
                        echo '</td>';
                        echo '<tr>';
                        echo "<td id='name' style='padding-top: 15px ; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('House') . '</span>';
                        try {
                            $dataDetail = array('pupilsightHouseID' => $row['pupilsightHouseID']);
                            $sqlDetail = 'SELECT * FROM pupilsightHouse WHERE pupilsightHouseID=:pupilsightHouseID';
                            $resultDetail = $connection2->prepare($sqlDetail);
                            $resultDetail->execute($dataDetail);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                        }
                        if ($resultDetail->rowCount() == 1) {
                            $rowDetail = $resultDetail->fetch();
                            echo $rowDetail['name'];
                        }
                        echo '</td>';
                        echo "<td id='studentID' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Student ID') . '</span>';
                        echo $row['studentID'];
                        echo '</td>';
                        echo "<td id='pupilsightYearGroupID' style='width: 34%; vertical-align: top'>";
                        try {
                            $dataDetail = array('pupilsightYearGroupID' => $row['pupilsightYearGroupID']);
                            $sqlDetail = "SELECT DISTINCT pupilsightPersonID, title, surname, preferredName FROM pupilsightPerson JOIN pupilsightYearGroup ON (pupilsightYearGroup.pupilsightPersonIDHOY=pupilsightPersonID) WHERE status='Full' AND pupilsightYearGroupID=:pupilsightYearGroupID";
                            $resultDetail = $connection2->prepare($sqlDetail);
                            $resultDetail->execute($dataDetail);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                        }
                        if ($resultDetail->rowCount() == 1) {
                            echo "<span style='font-size: 115%; font-weight: bold;'>" . __('Head of Year') . '</span>';
                            $rowDetail = $resultDetail->fetch();
                            if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_view_details.php')) {
                                echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Staff/staff_view_details.php&pupilsightPersonID=' . $rowDetail['pupilsightPersonID'] . "'>" . Format::name('', $rowDetail['preferredName'], $rowDetail['surname'], 'Staff', false, true) . '</a>';
                            } else {
                                echo Format::name($rowDetail['title'], $rowDetail['preferredName'], $rowDetail['surname'], 'Staff');
                            }
                            echo '';
                        }
                        echo '</td>';
                        echo '</tr>';
                        echo '</table>';

                        echo '<h4>';
                        echo __('System Data');
                        echo '</h4>';

                        echo "<table id='system_access' class='table'>";
                        echo '<tr>';
                        echo "<td id='username' width: 33%; style='vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Username') . '</span>';
                        echo $row['username'];
                        echo '</td>';
                        echo "<td id='canLogin' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Can Login?') . '</span>';
                        echo ynExpander($guid, $row['canLogin']);
                        echo '</td>';
                        echo "<td id='lastIPAddress' style='width: 34%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Last IP Address') . '</span>';
                        echo $row['lastIPAddress'];
                        echo '</td>';
                        echo '</tr>';
                        echo '</table>';

                        echo '<h4>';
                        echo __('Miscellaneous');
                        echo '</h4>';

                        echo "<table id='miscellaneous' class='table'>";
                        echo '<tr>';
                        echo "<td id='transport' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Transport') . '</span>';
                        echo $row['transport'];
                        if ($row['transportNotes'] != '') {
                            echo '';
                            echo $row['transportNotes'];
                        }
                        echo '</td>';
                        echo "<td id='vehicleRegistration' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Vehicle Registration') . '</span>';
                        echo $row['vehicleRegistration'];
                        echo '</td>';
                        echo "<td id='lockerNumber' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Locker Number') . '</span>';
                        echo $row['lockerNumber'];
                        echo '</td>';
                        echo '</tr>';

                        $privacySetting = getSettingByScope($connection2, 'User Admin', 'privacy');
                        if ($privacySetting == 'Y') {
                            echo '<tr>';
                            echo "<td id='privacy' style='width: 33%; padding-top: 15px; vertical-align: top' colspan=3>";
                            echo "<span class='form-label'>" . __('Image Privacy') . '</span>';
                            if ($row['privacy'] != '') {
                                echo "<span style='color: #cc0000; background-color: #F6CECB'>";
                                echo __('Privacy required:') . ' ' . $row['privacy'];
                                echo '</span>';
                            } else {
                                echo "<span style='color: #390; background-color: #D4F6DC;'>";
                                echo __('Privacy not required or not set.');
                                echo '</span>';
                            }

                            echo '</td>';
                            echo '</tr>';
                        }
                        $studentAgreementOptions = getSettingByScope($connection2, 'School Admin', 'studentAgreementOptions');
                        if ($studentAgreementOptions != '') {
                            echo '<tr>';
                            echo "<td id='studentAgreements' style='width: 33%; padding-top: 15px; vertical-align: top' colspan=3>";
                            echo "<span class='form-label'>" . __('Student Agreements') . '</span>';
                            echo __('Agreements Signed:') . ' ' . $row['studentAgreements'];
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';

                        //Custom Fields
                        $fields = unserialize($row['fields']);
                        $resultFields = getCustomFields($connection2, $guid, true);
                        if ($resultFields->rowCount() > 0) {
                            echo '<h4>';
                            echo __('Custom Fields');
                            echo '</h4>';

                            echo "<table class='table'>";
                            $count = 0;
                            $columns = 3;

                            while ($rowFields = $resultFields->fetch()) {
                                if ($count % $columns == 0) {
                                    echo '<tr>';
                                }
                                echo "<td id='pupilsightPersonFieldID' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                                echo "<span class='form-label'>" . __($rowFields['name']) . '</span>';
                                if (isset($fields[$rowFields['pupilsightPersonFieldID']])) {
                                    if ($rowFields['type'] == 'date') {
                                        echo dateConvertBack($guid, $fields[$rowFields['pupilsightPersonFieldID']]);
                                    } elseif ($rowFields['type'] == 'url') {
                                        echo "<a target='_blank' href='" . $fields[$rowFields['pupilsightPersonFieldID']] . "'>" . $fields[$rowFields['pupilsightPersonFieldID']] . '</a>';
                                    } else {
                                        echo $fields[$rowFields['pupilsightPersonFieldID']];
                                    }
                                }
                                echo '</td>';

                                if ($count % $columns == ($columns - 1)) {
                                    echo '</tr>';
                                }
                                ++$count;
                            }

                            if ($count % $columns != 0) {
                                for ($i = 0; $i < $columns - ($count % $columns); ++$i) {
                                    echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'></td>";
                                }
                                echo '</tr>';
                            }

                            echo '</table>';
                        }
                    } elseif ($subpage == 'Family') {
                        try {
                            $dataFamily = array('pupilsightPersonID' => $pupilsightPersonID);
                            $sqlFamily = 'SELECT * FROM pupilsightFamily JOIN pupilsightFamilyChild ON (pupilsightFamily.pupilsightFamilyID=pupilsightFamilyChild.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID';
                            $resultFamily = $connection2->prepare($sqlFamily);
                            $resultFamily->execute($dataFamily);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                        }

                        if ($resultFamily->rowCount() < 1) {
                            echo "<div class='alert alert-danger'>";
                            echo __('There are no records to display.');
                            echo '</div>';
                        } else {
                            while ($rowFamily = $resultFamily->fetch()) {
                                $count = 1;

                                if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage.php') == true) {
                                    echo "<div class='text-right'>";
                                    echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/User Admin/family_manage_edit.php&pupilsightFamilyID=' . $rowFamily['pupilsightFamilyID'] . "' class='btn btn-link'><span class='mdi mdi-pencil-box-outline mdi-18px mr-1'></span> Edit</a> ";
                                    echo '</div>';
                                }

                                //Print family information
                                echo "<table id='name' class='table'>";
                                echo '<tr>';
                                echo "<td style='width: 33%; vertical-align: top'>";
                                echo "<span class='form-label'>" . __('Family Name') . '</span>';
                                echo $rowFamily['name'];
                                echo '</td>';
                                echo "<td id='status' style='width: 33%; vertical-align: top'>";
                                echo "<span class='form-label'>" . __('Family Status') . '</span>';
                                echo $rowFamily['status'];
                                echo '</td>';
                                echo "<td id='languageHomePrimary' style='width: 34%; vertical-align: top' colspan=2>";
                                echo "<span class='form-label'>" . __('Home Languages') . '</span>';
                                if ($rowFamily['languageHomePrimary'] != '') {
                                    echo $rowFamily['languageHomePrimary'] . '';
                                }
                                if ($rowFamily['languageHomeSecondary'] != '') {
                                    echo $rowFamily['languageHomeSecondary'] . '';
                                }
                                echo '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo "<td id='nameAddress' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                                echo "<span class='form-label'>" . __('Address Name') . '</span>';
                                echo $rowFamily['nameAddress'];
                                echo '</td>';
                                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                                echo '</td>';
                                echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";
                                echo '</td>';
                                echo '</tr>';

                                echo '<tr>';
                                echo "<td id='homeAddress' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                                echo "<span class='form-label'>" . __('Home Address') . '</span>';
                                echo $rowFamily['homeAddress'];
                                echo '</td>';
                                echo "<td id='homeAddressDistrict'style='width: 33%; padding-top: 15px; vertical-align: top'>";
                                echo "<span class='form-label'>" . __('Home Address (District)') . '</span>';
                                echo $rowFamily['homeAddressDistrict'];
                                echo '</td>';
                                echo "<td id='homeAddressCountry'style='width: 33%; padding-top: 15px; vertical-align: top'>";
                                echo "<span class='form-label'>" . __('Home Address (Country)') . '</span>';
                                echo $rowFamily['homeAddressCountry'];
                                echo '</td>';
                                echo '</tr>';
                                echo '</table>';

                                //Get adults
                                try {
                                    $dataMember = array('pupilsightFamilyID' => $rowFamily['pupilsightFamilyID']);
                                    $sqlMember = 'SELECT * FROM pupilsightFamilyAdult JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightFamilyID=:pupilsightFamilyID ORDER BY contactPriority, surname, preferredName';
                                    $resultMember = $connection2->prepare($sqlMember);
                                    $resultMember->execute($dataMember);
                                } catch (PDOException $e) {
                                    echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                }

                                while ($rowMember = $resultMember->fetch()) {
                                    $class = '';
                                    if ($rowMember['status'] != 'Full') {
                                        $class = "class='error'";
                                    }
                                    echo '<h4>';
                                    echo __('Adult') . ' ' . $count;
                                    echo '</h4>';
                                    echo "<table class='table'>";
                                    echo '<tr>';
                                    echo "<td id='image_240' $class style='width: 33%; vertical-align: top' rowspan=2>";
                                    echo getUserPhoto($guid, $rowMember['image_240'], 75);
                                    echo '</td>';
                                    echo "<td $class style='width: 33%; vertical-align: top'>";
                                    echo "<span class='form-label'>" . __('Name') . '</span>';
                                    // echo Format::name($rowMember['title'], $rowMember['preferredName'], $rowMember['surname'], 'Parent');
                                    echo $rowMember['officialName'];
                                    if ($rowMember['status'] != 'Full') {
                                        echo "<span style='font-weight: normal; font-style: italic'> (" . $rowMember['status'] . ')</span>';
                                    }
                                    echo "<div style='font-size: 85%; font-style: italic'>";
                                    try {
                                        $dataRelationship = array('pupilsightPersonID1' => $rowMember['pupilsightPersonID'], 'pupilsightPersonID2' => $pupilsightPersonID, 'pupilsightFamilyID' => $rowFamily['pupilsightFamilyID']);
                                        $sqlRelationship = 'SELECT * FROM pupilsightFamilyRelationship WHERE pupilsightPersonID1=:pupilsightPersonID1 AND pupilsightPersonID2=:pupilsightPersonID2 AND pupilsightFamilyID=:pupilsightFamilyID';
                                        $resultRelationship = $connection2->prepare($sqlRelationship);
                                        $resultRelationship->execute($dataRelationship);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                    }
                                    if ($resultRelationship->rowCount() == 1) {
                                        $rowRelationship = $resultRelationship->fetch();
                                        echo $rowRelationship['relationship'];
                                    } else {
                                        echo '<i>' . __('Relationship Unknown') . '</i>';
                                    }
                                    echo '</div>';
                                    echo '</td>';
                                    echo "<td id='contactPriority' $class style='width: 34%; vertical-align: top' colspan=2>";
                                    echo "<span class='form-label'>" . __('Contact Priority') . '</span>';
                                    echo $rowMember['contactPriority'];
                                    echo '</td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo "<td id='languageFirst' $class style='width: 33%; padding-top: 15px; vertical-align: top'>";
                                    echo "<span class='form-label'>" . __('First Language') . '</span>';
                                    echo $rowMember['languageFirst'];
                                    echo '</td>';
                                    echo "<td id='languageSecond' $class style='width: 33%; padding-top: 15px; vertical-align: top'>";
                                    echo "<span class='form-label'>" . __('Second Language') . '</span>';
                                    echo $rowMember['languageSecond'];
                                    echo '</td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo "<td $class style='width: 33%; padding-top: 15px; width: 33%; vertical-align: top'>";
                                    echo "<span class='form-label'>" . __('Contact By Phone') . '</span>';
                                    if ($rowMember['contactCall'] == 'N') {
                                        echo __('Do not contact by phone.');
                                    } elseif ($rowMember['contactCall'] == 'Y' and ($rowMember['phone1'] != '' or $rowMember['phone2'] != '' or $rowMember['phone3'] != '' or $rowMember['phone4'] != '')) {
                                        for ($i = 1; $i < 5; ++$i) {
                                            if ($rowMember['phone' . $i] != '') {
                                                if ($rowMember['phone' . $i . 'Type'] != '') {
                                                    echo $rowMember['phone' . $i . 'Type'] . ':</i> ';
                                                }
                                                if ($rowMember['phone' . $i . 'CountryCode'] != '') {
                                                    echo '+' . $rowMember['phone' . $i . 'CountryCode'] . ' ';
                                                }
                                                echo formatPhone($rowMember['phone' . $i]) . '';
                                            }
                                        }
                                    }
                                    echo '</td>';
                                    echo "<td id='contactSMS' $class style='width: 33%; padding-top: 15px; width: 33%; vertical-align: top'>";
                                    echo "<span class='form-label'>" . __('Contact By SMS') . '</span>';
                                    if ($rowMember['contactSMS'] == 'N') {
                                        echo __('Do not contact by SMS.');
                                    } elseif ($rowMember['contactSMS'] == 'Y' and ($rowMember['phone1'] != '' or $rowMember['phone2'] != '' or $rowMember['phone3'] != '' or $rowMember['phone4'] != '')) {
                                        for ($i = 1; $i < 5; ++$i) {
                                            if ($rowMember['phone' . $i] != '' and $rowMember['phone' . $i . 'Type'] == 'Mobile') {
                                                if ($rowMember['phone' . $i . 'Type'] != '') {
                                                    echo $rowMember['phone' . $i . 'Type'] . ':</i> ';
                                                }
                                                if ($rowMember['phone' . $i . 'CountryCode'] != '') {
                                                    echo '+' . $rowMember['phone' . $i . 'CountryCode'] . ' ';
                                                }
                                                echo formatPhone($rowMember['phone' . $i]) . '';
                                            }
                                        }
                                    }
                                    echo '</td>';
                                    echo "<td id='email' $class style='width: 33%; padding-top: 15px; width: 34%; vertical-align: top' colspan=2>";
                                    echo "<span class='form-label'>" . __('Contact By Email') . '</span>';
                                    if ($rowMember['contactEmail'] == 'N') {
                                        echo __('Do not contact by email.');
                                    } elseif ($rowMember['contactEmail'] == 'Y' and ($rowMember['email'] != '' or $rowMember['emailAlternate'] != '')) {
                                        if ($rowMember['email'] != '') {
                                            echo __('Email') . ": <a href='mailto:" . $rowMember['email'] . "'>" . $rowMember['email'] . '</a>';
                                        }
                                        if ($rowMember['emailAlternate'] != '') {
                                            echo __('Email') . " 2: <a href='mailto:" . $rowMember['emailAlternate'] . "'>" . $rowMember['emailAlternate'] . '</a>';
                                        }
                                        echo '';
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo "<td id='profession' $class style='width: 33%; padding-top: 15px; vertical-align: top'>";
                                    echo "<span class='form-label'>" . __('Profession') . '</span>';
                                    echo $rowMember['profession'];
                                    echo '</td>';
                                    echo "<td id='employer' $class style='width: 33%; padding-top: 15px; vertical-align: top'>";
                                    echo "<span class='form-label'>" . __('Employer') . '</span>';
                                    echo $rowMember['employer'];
                                    echo '</td>';
                                    echo "<td id='jobTitle' $class style='width: 33%; padding-top: 15px; vertical-align: top'>";
                                    echo "<span class='form-label'>" . __('Job Title') . '</span>';
                                    echo $rowMember['jobTitle'];
                                    echo '</td>';
                                    echo '</tr>';

                                    echo '<tr>';
                                    echo "<td id='vehicleRegistration' $class style='width: 33%; padding-top: 15px; vertical-align: top'>";
                                    echo "<span class='form-label'>" . __('Vehicle Registration') . '</span>';
                                    echo $rowMember['vehicleRegistration'];
                                    echo '</td>';
                                    echo "<td $class style='width: 33%; padding-top: 15px; vertical-align: top'>";

                                    echo '</td>';
                                    echo "<td $class style='width: 33%; padding-top: 15px; vertical-align: top'>";

                                    echo '</td>';
                                    echo '</tr>';

                                    if ($rowMember['comment'] != '') {
                                        echo '<tr>';
                                        echo "<td  id='comment' $class style='width: 33%; vertical-align: top' colspan=3>";
                                        echo "<span class='form-label'>" . __('Comment') . '</span>';
                                        echo $rowMember['comment'];
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    echo '</table>';
                                    ++$count;
                                }

                                //Get siblings
                                try {
                                    $dataMember = array('pupilsightFamilyID' => $rowFamily['pupilsightFamilyID'], 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                                    $sqlMember = 'SELECT pupilsightPerson.pupilsightPersonID, image_240, preferredName, surname, status, pupilsightStudentEnrolmentID FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) LEFT JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND NOT pupilsightPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName';
                                    $resultMember = $connection2->prepare($sqlMember);
                                    $resultMember->execute($dataMember);
                                } catch (PDOException $e) {
                                    echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                }

                                if ($resultMember->rowCount() > 0) {
                                    echo '<h4>';
                                    echo __('Siblings');
                                    echo '</h4>';

                                    echo "<table class='table'>";
                                    $count = 0;
                                    $columns = 3;

                                    while ($rowMember = $resultMember->fetch()) {
                                        if ($count % $columns == 0) {
                                            echo '<tr>';
                                        }
                                        echo "<td id='image_240' style='width:30%; text-align: left; vertical-align: top'>";
                                        //User photo
                                        echo getUserPhoto($guid, $rowMember['image_240'], 75);
                                        echo "<div style='padding-top: 5px'><b>";
                                        $allStudents = '';
                                        if ($rowMember['pupilsightStudentEnrolmentID'] == null)
                                            $allStudents = '&allStudents=on';
                                        if ($rowMember['status'] == 'Full') {
                                            echo "<a href='index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=" . $rowMember['pupilsightPersonID'] . $allStudents . "'>" . Format::name('', $rowMember['preferredName'], $rowMember['surname'], 'Student') . '</a>';
                                        } else {
                                            echo Format::name('', $rowMember['preferredName'], $rowMember['surname'], 'Student') . '';
                                        }
                                        echo "<span style='font-weight: normal; font-style: italic'>" . __('Status') . ': ' . $rowMember['status'] . '</span>';
                                        echo '</div>';
                                        echo '</td>';

                                        if ($count % $columns == ($columns - 1)) {
                                            echo '</tr>';
                                        }
                                        ++$count;
                                    }

                                    for ($i = 0; $i < $columns - ($count % $columns); ++$i) {
                                        echo '<td></td>';
                                    }

                                    if ($count % $columns != 0) {
                                        echo '</tr>';
                                    }

                                    echo '</table>';
                                }
                            }
                        }
                    } elseif ($subpage == 'Emergency Contacts') {

                        if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage.php') == true) {
                            echo "<div class='text-right'>";
                            echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Students/student_edit.php&pupilsightPersonID=$pupilsightPersonID' class='btn btn-link'><span class='mdi mdi-pencil-box-outline mdi-18px mr-1'></span> Edit</a> ";
                            echo '</div>';
                        }

                        echo '<p>';
                        echo __('In an emergency, please try and contact the adult family members listed below first. If these cannot be reached, then try the emergency contacts below.');
                        echo '</p>';

                        echo '<h4>';
                        echo __('Adult Family Members');
                        echo '</h4>';

                        try {
                            $dataFamily = array('pupilsightPersonID' => $pupilsightPersonID);
                            $sqlFamily = 'SELECT * FROM pupilsightFamily JOIN pupilsightFamilyChild ON (pupilsightFamily.pupilsightFamilyID=pupilsightFamilyChild.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID';
                            $resultFamily = $connection2->prepare($sqlFamily);
                            $resultFamily->execute($dataFamily);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                        }

                        if ($resultFamily->rowCount() != 1) {
                            echo "<div class='alert alert-danger'>";
                            echo __('There are no records to display.');
                            echo '</div>';
                        } else {
                            $rowFamily = $resultFamily->fetch();
                            $count = 1;
                            //Get adults
                            try {
                                $dataMember = array('pupilsightFamilyID' => $rowFamily['pupilsightFamilyID']);
                                $sqlMember = 'SELECT * FROM pupilsightFamilyAdult JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightFamilyID=:pupilsightFamilyID ORDER BY contactPriority, surname, preferredName';
                                $resultMember = $connection2->prepare($sqlMember);
                                $resultMember->execute($dataMember);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }

                            while ($rowMember = $resultMember->fetch()) {
                                echo "<table class='table'>";
                                echo '<tr>';
                                echo "<td id='preferredName' style='width: 33%; vertical-align: top'>";
                                echo "<span class='form-label'>" . __('Name') . '</span>';
                                echo Format::name($rowMember['title'], $rowMember['preferredName'], $rowMember['surname'], 'Parent');
                                echo '</td>';
                                echo "<td id='relationship' style='width: 33%; vertical-align: top'>";
                                echo "<span class='form-label'>" . __('Relationship') . '</span>';
                                try {
                                    $dataRelationship = array('pupilsightPersonID1' => $rowMember['pupilsightPersonID'], 'pupilsightPersonID2' => $pupilsightPersonID, 'pupilsightFamilyID' => $rowFamily['pupilsightFamilyID']);
                                    $sqlRelationship = 'SELECT * FROM pupilsightFamilyRelationship WHERE pupilsightPersonID1=:pupilsightPersonID1 AND pupilsightPersonID2=:pupilsightPersonID2 AND pupilsightFamilyID=:pupilsightFamilyID';
                                    $resultRelationship = $connection2->prepare($sqlRelationship);
                                    $resultRelationship->execute($dataRelationship);
                                } catch (PDOException $e) {
                                    echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                }
                                if ($resultRelationship->rowCount() == 1) {
                                    $rowRelationship = $resultRelationship->fetch();
                                    echo $rowRelationship['relationship'];
                                } else {
                                    echo '<i>' . __('Unknown') . '</i>';
                                }

                                echo '</td>';
                                echo "<td style='width: 34%; vertical-align: top'>";
                                echo "<span class='form-label'>" . __('Contact By Phone') . '</span>';
                                for ($i = 1; $i < 5; ++$i) {
                                    if ($rowMember['phone' . $i] != '') {
                                        if ($rowMember['phone' . $i . 'Type'] != '') {
                                            echo $rowMember['phone' . $i . 'Type'] . ':</i> ';
                                        }
                                        if ($rowMember['phone' . $i . 'CountryCode'] != '') {
                                            echo '+' . $rowMember['phone' . $i . 'CountryCode'] . ' ';
                                        }
                                        echo __($rowMember['phone' . $i]) . '';
                                    }
                                }
                                echo '</td>';
                                echo '</tr>';
                                echo '</table>';
                                ++$count;
                            }
                        }

                        echo '<h4>';
                        echo __('Emergency Contacts');
                        echo '</h4>';
                        echo "<table class='table'>";
                        echo '<tr>';
                        echo "<td id='emergency1Relationship' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Contact 1') . '</span>';
                        echo $row['emergency1Name'];
                        if ($row['emergency1Relationship'] != '') {
                            echo ' (' . $row['emergency1Relationship'] . ')';
                        }
                        echo '</td>';
                        echo "<td id='emergency1Number1' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Number 1') . '</span>';
                        echo $row['emergency1Number1'];
                        echo '</td>';
                        echo "<td id='emergency1Number2' style=width: 34%; 'vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Number 2') . '</span>';
                        if ($row['website'] != '') {
                            echo $row['emergency1Number2'];
                        }
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo "<td id='emergency2Name' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Contact 2') . '</span>';
                        echo $row['emergency2Name'];
                        if ($row['emergency2Relationship'] != '') {
                            echo ' (' . $row['emergency2Relationship'] . ')';
                        }
                        echo '</td>';
                        echo "<td id='emergency2Number1' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Number 1') . '</span>';
                        echo $row['emergency2Number1'];
                        echo '</td>';
                        echo "<td id='emergency2Number2' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Number 2') . '</span>';
                        if ($row['website'] != '') {
                            echo $row['emergency2Number2'];
                        }
                        echo '</td>';
                        echo '</tr>';
                        echo '</table>';
                    } elseif ($subpage == 'Medical') {
                        try {
                            $dataMedical = array('pupilsightPersonID' => $pupilsightPersonID);
                            $sqlMedical = 'SELECT * FROM pupilsightPersonMedical JOIN pupilsightPerson ON (pupilsightPersonMedical.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID';
                            $resultMedical = $connection2->prepare($sqlMedical);
                            $resultMedical->execute($dataMedical);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                        }

                        if ($resultMedical->rowCount() != 1) {

                            if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage.php') == true) {
                                echo "<div class='text-right'>";
                                echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Students/medicalForm_manage_add.php&pupilsightPersonID=$pupilsightPersonID&search=' class='btn btn-link'><span class='mdi mdi-plus mdi-18px mr-1'></span> Add Medical Form</a> ";
                                echo '</div>';
                            }

                            echo "<div class='alert alert-danger'>";
                            echo __('There are no records to display.');
                            echo '</div>';
                        } else {
                            $rowMedical = $resultMedical->fetch();

                            if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage.php') == true) {
                                echo "<div class='text-right'>";
                                echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/User Admin/medicalForm_manage_edit.php&pupilsightPersonMedicalID=' . $rowMedical['pupilsightPersonMedicalID'] . "' class='btn btn-link'><span class='mdi mdi-pencil-box-outline mdi-18px mr-1'></span> Edit</a> ";
                                echo '</div>';
                            }

                            //Medical alert!
                            $alert = getHighestMedicalRisk($guid,  $pupilsightPersonID, $connection2);
                            if ($alert != false) {
                                $highestLevel = $alert[1];
                                $highestColour = $alert[3];
                                $highestColourBG = $alert[4];
                                echo "<div class='alert alert-danger' style='background-color: #" . $highestColourBG . '; border: 1px solid #' . $highestColour . '; color: #' . $highestColour . "'>";
                                echo '<b>' . sprintf(__('This student has one or more %1$s risk medical conditions.'), strToLower($highestLevel)) . '</b>.';
                                echo '</div>';
                            }

                            //Get medical conditions
                            try {
                                $dataCondition = array('pupilsightPersonMedicalID' => $rowMedical['pupilsightPersonMedicalID']);
                                $sqlCondition = 'SELECT * FROM pupilsightPersonMedicalCondition WHERE pupilsightPersonMedicalID=:pupilsightPersonMedicalID ORDER BY name';
                                $resultCondition = $connection2->prepare($sqlCondition);
                                $resultCondition->execute($dataCondition);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }

                            echo "<table class='table' cellspacing='0' style='width: 100%'>";
                            echo '<tr>';
                            echo "<td id='longTermMedication' style='width: 33%; vertical-align: top'>";
                            echo "<span class='form-label'>" . __('Long Term Medication') . '</span>';
                            if ($rowMedical['longTermMedication'] == '') {
                                echo '<i>' . __('Unknown') . '</i>';
                            } else {
                                echo $rowMedical['longTermMedication'];
                            }
                            echo '</td>';
                            echo "<td id='longTermMedicationDetails' style='width: 67%; vertical-align: top' colspan=2>";
                            echo "<span class='form-label'>" . __('Details') . '</span>';
                            echo $rowMedical['longTermMedicationDetails'];
                            echo '</td>';
                            echo '</tr>';
                            echo '<tr>';
                            echo "<td id='tetanusWithin10Years' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                            echo "<span class='form-label'>" . __('Tetanus Last 10 Years?') . '</span>';
                            if ($rowMedical['tetanusWithin10Years'] == '') {
                                echo '<i>' . __('Unknown') . '</i>';
                            } else {
                                echo $rowMedical['tetanusWithin10Years'];
                            }
                            echo '</td>';
                            echo "<td id='bloodType' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                            echo "<span class='form-label'>" . __('Blood Type') . '</span>';
                            echo $rowMedical['bloodType'];
                            echo '</td>';
                            echo "<td id='' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                            echo "<span class='form-label'>" . __('Medical Conditions?') . '</span>';
                            if ($resultCondition->rowCount() > 0) {
                                echo __('Yes') . '. ' . __('Details below.');
                            } else {
                                __('No');
                            }
                            echo '</td>';
                            echo '</tr>';
                            if (!empty($rowMedical['comment'])) {
                                echo '<tr>';
                                echo "<td padding-top: 15px; vertical-align: top' colspan=3>";
                                echo "<span class='form-label'>" . __('Comment') . '</span>';
                                echo $rowMedical['comment'];
                                echo '</td>';
                                echo '</tr>';
                            }
                            echo '</table>';

                            while ($rowCondition = $resultCondition->fetch()) {
                                echo '<h4>';
                                $alert = getAlert($guid, $connection2, $rowCondition['pupilsightAlertLevelID']);
                                if ($alert != false) {
                                    echo __($rowCondition['name']) . " <span style='color: #" . $alert['color'] . "'>(" . __($alert['name']) . ' ' . __('Risk') . ')</span>';
                                }
                                echo '</h4>';

                                echo "<table class='table'>";
                                echo '<tr>';
                                echo "<td id='triggers' style='width: 50%; vertical-align: top'>";
                                echo "<span class='form-label'>" . __('Triggers') . '</span>';
                                echo $rowCondition['triggers'];
                                echo '</td>';
                                echo "<td id='reaction' style='width: 50%; vertical-align: top' colspan=2>";
                                echo "<span class='form-label'>" . __('Reaction') . '</span>';
                                echo $rowCondition['reaction'];
                                echo '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo "<td id='response' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                                echo "<span class='form-label'>" . __('Response') . '</span>';
                                echo $rowCondition['response'];
                                echo '</td>';
                                echo "<td id='medication' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                                echo "<span class='form-label'>" . __('Medication') . '</span>';
                                echo $rowCondition['medication'];
                                echo '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo "<td id='lastEpisode' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                                echo "<span class='form-label'>" . __('Last Episode Date') . '</span>';
                                if (is_null($row['dob']) == false and $row['dob'] != '0000-00-00') {
                                    echo dateConvertBack($guid, $rowCondition['lastEpisode']);
                                }
                                echo '</td>';
                                echo "<td id='lastEpisodeTreatment' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                                echo "<span class='form-label'>" . __('Last Episode Treatment') . '</span>';
                                echo $rowCondition['lastEpisodeTreatment'];
                                echo '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo "<td id='comment' style='width: 33%; padding-top: 15px; vertical-align: top' colspan=2>";
                                echo "<span class='form-label'>" . __('Comments') . '</span>';
                                echo $rowCondition['comment'];
                                echo '</td>';
                                echo '</tr>';
                                echo '</table>';
                            }
                        }
                    } elseif ($subpage == 'Notes') {
                        if ($enableStudentNotes != 'Y') {
                            echo "<div class='alert alert-danger'>";
                            echo __('You do not have access to this action.');
                            echo '</div>';
                        } else {
                            if (isActionAccessible($guid, $connection2, '/modules/Students/student_view_details_notes_add.php') == false) {
                                echo "<div class='alert alert-danger'>";
                                echo __('Your request failed because you do not have access to this action.');
                                echo '</div>';
                            } else {
                                if (isset($_GET['return'])) {
                                    returnProcess($guid, $_GET['return'], null, null);
                                }

                                echo '<p>';
                                echo __('Student Notes provide a way to store information on students which does not fit elsewhere in the system, or which you want to be able to see quickly in one place.') . ' <b>' . __('Please remember that notes are visible to other users who have access to full student profiles (this should not generally include parents).') . '</b>';
                                echo '</p>';

                                $categories = false;
                                $category = null;
                                if (isset($_GET['category'])) {
                                    $category = $_GET['category'];
                                }

                                try {
                                    $dataCategories = array();
                                    $sqlCategories = "SELECT * FROM pupilsightStudentNoteCategory WHERE active='Y' ORDER BY name";
                                    $resultCategories = $connection2->prepare($sqlCategories);
                                    $resultCategories->execute($dataCategories);
                                } catch (PDOException $e) {
                                }
                                if ($resultCategories->rowCount() > 0) {
                                    $categories = true;

                                    echo '<h3>';
                                    echo __('Filter');
                                    echo '</h3>';

                                    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'] . '/index.php', 'get');
                                    $form->setClass('noIntBorder fullWidth');

                                    $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/student_view_details.php');
                                    $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);
                                    $form->addHiddenValue('allStudents', $allStudents);
                                    $form->addHiddenValue('search', $search);
                                    $form->addHiddenValue('subpage', 'Notes');

                                    $sql = "SELECT pupilsightStudentNoteCategoryID as value, name FROM pupilsightStudentNoteCategory WHERE active='Y' ORDER BY name";
                                    $rowFilter = $form->addRow();
                                    $rowFilter->addLabel('category', __('Category'));
                                    $rowFilter->addSelect('category')->fromQuery($pdo, $sql)->selected($category)->placeholder();

                                    $rowFilter = $form->addRow();
                                    $rowFilter->addSearchSubmit($pupilsight->session, __('Clear Filters'), array('pupilsightPersonID', 'allStudents', 'search', 'subpage'));

                                    echo $form->getOutput();
                                }

                                try {
                                    if ($category == null) {
                                        $data = array('pupilsightPersonID' => $pupilsightPersonID);
                                        $sql = 'SELECT pupilsightStudentNote.*, pupilsightStudentNoteCategory.name AS category, surname, preferredName FROM pupilsightStudentNote LEFT JOIN pupilsightStudentNoteCategory ON (pupilsightStudentNote.pupilsightStudentNoteCategoryID=pupilsightStudentNoteCategory.pupilsightStudentNoteCategoryID) JOIN pupilsightPerson ON (pupilsightStudentNote.pupilsightPersonIDCreator=pupilsightPerson.pupilsightPersonID) WHERE pupilsightStudentNote.pupilsightPersonID=:pupilsightPersonID ORDER BY timestamp DESC';
                                    } else {
                                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightStudentNoteCategoryID' => $category);
                                        $sql = 'SELECT pupilsightStudentNote.*, pupilsightStudentNoteCategory.name AS category, surname, preferredName FROM pupilsightStudentNote LEFT JOIN pupilsightStudentNoteCategory ON (pupilsightStudentNote.pupilsightStudentNoteCategoryID=pupilsightStudentNoteCategory.pupilsightStudentNoteCategoryID) JOIN pupilsightPerson ON (pupilsightStudentNote.pupilsightPersonIDCreator=pupilsightPerson.pupilsightPersonID) WHERE pupilsightStudentNote.pupilsightPersonID=:pupilsightPersonID AND pupilsightStudentNote.pupilsightStudentNoteCategoryID=:pupilsightStudentNoteCategoryID ORDER BY timestamp DESC';
                                    }
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                }

                                echo "<div class='linkTop'>";
                                echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . "/student_view_details_notes_add.php&pupilsightPersonID=$pupilsightPersonID&search=$search&allStudents=$allStudents&search=$search&allStudents=$allStudents&subpage=Notes&category=$category'>" . __('Add') . "</a>";
                                echo '</div>';

                                if ($result->rowCount() < 1) {
                                    echo "<div class='alert alert-danger'>";
                                    echo __('There are no records to display.');
                                    echo '</div>';
                                } else {
                                    echo "<table class='table'>";
                                    echo "<tr class='head'>";
                                    echo '<th>';
                                    echo __('Date') . '';
                                    echo "<span style='font-size: 75%; font-style: italic'>" . __('Time') . '</span>';
                                    echo '</th>';
                                    echo '<th>';
                                    echo __('Category');
                                    echo '</th>';
                                    echo '<th>';
                                    echo __('Title') . '';
                                    echo "<span style='font-size: 75%; font-style: italic'>" . __('Overview') . '</span>';
                                    echo '</th>';
                                    echo '<th>';
                                    echo __('Note Taker');
                                    echo '</th>';
                                    echo '<th>';
                                    echo __('Actions');
                                    echo '</th>';
                                    echo '</tr>';

                                    $count = 0;
                                    $rowNum = 'odd';
                                    while ($row = $result->fetch()) {
                                        if ($count % 2 == 0) {
                                            $rowNum = 'even';
                                        } else {
                                            $rowNum = 'odd';
                                        }
                                        ++$count;

                                        //COLOR ROW BY STATUS!
                                        echo "<tr class=$rowNum>";
                                        echo '<td id="timestamp">';
                                        echo dateConvertBack($guid, substr($row['timestamp'], 0, 10)) . '';
                                        echo "<span style='font-size: 75%; font-style: italic'>" . substr($row['timestamp'], 11, 5) . '</span>';
                                        echo '</td>';
                                        echo '<td id="category">';
                                        echo $row['category'];
                                        echo '</td>';
                                        echo '<td id="title">';
                                        if ($row['title'] == '') {
                                            echo '<i>' . __('NA') . '</i>';
                                        } else {
                                            echo $row['title'] . '';
                                        }
                                        echo "<span style='font-size: 75%; font-style: italic'>" . substr(strip_tags($row['note']), 0, 60) . '</span>';
                                        echo '</td>';
                                        echo '<td id="">';
                                        echo Format::name('', $row['preferredName'], $row['surname'], 'Staff', false, true);
                                        echo '</td>';
                                        echo '<td id="pupilsightPersonIDCreator">';
                                        if ($row['pupilsightPersonIDCreator'] == $_SESSION[$guid]['pupilsightPersonID']) {
                                            echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . '/student_view_details_notes_edit.php&search=' . $search . '&pupilsightStudentNoteID=' . $row['pupilsightStudentNoteID'] . "&pupilsightPersonID=$pupilsightPersonID&search=$search&allStudents=$allStudents&subpage=Notes&category=$category'><i  title='" . __('Edit') . "' class='mdi mdi-pencil-box-outline mdi-24px px-2'></i></a> ";
                                        }
                                        echo "<script type='text/javascript'>";
                                        echo '$(document).ready(function(){';
                                        echo "\$(\".note-$count\").hide();";
                                        echo "\$(\".show_hide-$count\").fadeIn(1000);";
                                        echo "\$(\".show_hide-$count\").click(function(){";
                                        echo "\$(\".note-$count\").fadeToggle(1000);";
                                        echo '});';
                                        echo '});';
                                        echo '</script>';
                                        echo "<a title='" . __('View Description') . "' class='show_hide-$count' onclick='return false;' href='#'><img title='" . __('View Details') . "' src='./themes/" . $_SESSION[$guid]['pupilsightThemeName'] . "/img/page_down.png'/></a></span>";
                                        echo '</td>';
                                        echo '</tr>';
                                        echo "<tr class='note-$count' id='note-$count'>";
                                        echo '<td id="note" colspan=6>';
                                        echo $row['note'];
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    echo '</table>';
                                }
                            }
                        }
                    } elseif ($subpage == 'Attendance') {
                        if (isActionAccessible($guid, $connection2, '/modules/Attendance/report_studentHistory.php') == false) {
                            echo "<div class='alert alert-danger'>";
                            echo __('Your request failed because you do not have access to this action.');
                            echo '</div>';
                        } else {
                            include './modules/Attendance/moduleFunctions.php';
                            include './modules/Attendance/src/StudentHistoryData.php';
                            include './modules/Attendance/src/StudentHistoryView.php';

                            // ATTENDANCE DATA
                            $attendanceData = $container->get(StudentHistoryData::class)
                                ->getAttendanceData($_SESSION[$guid]['pupilsightSchoolYearID'], $pupilsightPersonID, $row['dateStart'], $row['dateEnd']);

                            // DATA TABLE
                            $renderer = $container->get(StudentHistoryView::class);
                            $renderer->addData('canTakeAttendanceByPerson', isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_byPerson.php'));

                            $table = DataTable::create('studentHistory', $renderer);
                            echo $table->render($attendanceData);
                        }
                    } elseif ($subpage == 'Markbook') {
                        if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_view.php') == false) {
                            echo "<div class='alert alert-danger'>";
                            echo __('Your request failed because you do not have access to this action.');
                            echo '</div>';
                        } else {
                            // Register scripts available to the core, but not included by default
                            $page->scripts->add('chart');

                            $highestAction = getHighestGroupedAction($guid, '/modules/Markbook/markbook_view.php', $connection2);
                            if ($highestAction == false) {
                                echo "<div class='alert alert-danger'>";
                                echo __('The highest grouped action cannot be determined.');
                                echo '</div>';
                            } else {
                                //Module includes
                                include './modules/Markbook/moduleFunctions.php';

                                //Get settings
                                $enableEffort = getSettingByScope($connection2, 'Markbook', 'enableEffort');
                                $enableRubrics = getSettingByScope($connection2, 'Markbook', 'enableRubrics');
                                $attainmentAlternativeName = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeName');
                                $attainmentAlternativeNameAbrev = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeNameAbrev');
                                $effortAlternativeName = getSettingByScope($connection2, 'Markbook', 'effortAlternativeName');
                                $effortAlternativeNameAbrev = getSettingByScope($connection2, 'Markbook', 'effortAlternativeNameAbrev');
                                $enableModifiedAssessment = getSettingByScope($connection2, 'Markbook', 'enableModifiedAssessment');

                                $alert = getAlert($guid, $connection2, 002);
                                $role = getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2);
                                if ($role == 'Parent') {
                                    $showParentAttainmentWarning = getSettingByScope($connection2, 'Markbook', 'showParentAttainmentWarning');
                                    $showParentEffortWarning = getSettingByScope($connection2, 'Markbook', 'showParentEffortWarning');
                                } else {
                                    $showParentAttainmentWarning = 'Y';
                                    $showParentEffortWarning = 'Y';
                                }
                                $entryCount = 0;

                                $and = '';
                                $and2 = '';
                                $dataList = array();
                                $dataEntry = array();
                                $filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : $_SESSION[$guid]['pupilsightSchoolYearID'];

                                if ($filter != '*') {
                                    $dataList['filter'] = $filter;
                                    $and .= ' AND pupilsightSchoolYearID=:filter';
                                }

                                $filter2 = isset($_REQUEST['filter2']) ? $_REQUEST['filter2'] : '*';
                                if ($filter2 != '*') {
                                    $dataList['filter2'] = $filter2;
                                    $and .= ' AND pupilsightDepartmentID=:filter2';
                                }

                                $filter3 = isset($_REQUEST['filter3']) ? $_REQUEST['filter3'] : '';
                                if ($filter3 != '') {
                                    $dataEntry['filter3'] = $filter3;
                                    $and2 .= ' AND type=:filter3';
                                }

                                echo '<p>';
                                echo __('This page displays academic results for a student throughout their school career. Only subjects with published results are shown.');
                                echo '</p>';

                                $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'] . '/index.php', 'get');
                                $form->setClass('noIntBorder fullWidth');

                                $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/student_view_details.php');
                                $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);
                                $form->addHiddenValue('allStudents', $allStudents);
                                $form->addHiddenValue('search', $search);
                                $form->addHiddenValue('subpage', 'Markbook');

                                $sqlSelect = "SELECT pupilsightDepartmentID as value, name FROM pupilsightDepartment WHERE type='Learning Area' ORDER BY name";
                                $rowFilter = $form->addRow();
                                $rowFilter->addLabel('filter2', __('Learning Areas'));
                                $rowFilter->addSelect('filter2')
                                    ->fromArray(array('*' => __('All Learning Areas')))
                                    ->fromQuery($pdo, $sqlSelect)
                                    ->selected($filter2);

                                $dataSelect = array('pupilsightPersonID' => $pupilsightPersonID);
                                $sqlSelect = "SELECT pupilsightSchoolYear.pupilsightSchoolYearID as value, CONCAT(pupilsightSchoolYear.name, ' (', pupilsightYearGroup.name, ')') AS name FROM pupilsightStudentEnrolment JOIN pupilsightSchoolYear ON (pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) WHERE pupilsightPersonID=:pupilsightPersonID ORDER BY pupilsightSchoolYear.sequenceNumber";
                                $rowFilter = $form->addRow();
                                $rowFilter->addLabel('filter', __('School Years'));
                                $rowFilter->addSelect('filter')
                                    ->fromArray(array('*' => __('All Years')))
                                    ->fromQuery($pdo, $sqlSelect, $dataSelect)
                                    ->selected($filter);

                                $types = getSettingByScope($connection2, 'Markbook', 'markbookType');
                                if (!empty($types)) {
                                    $rowFilter = $form->addRow();
                                    $rowFilter->addLabel('filter3', __('Type'));
                                    $rowFilter->addSelect('filter3')
                                        ->fromString($types)
                                        ->selected($filter3)
                                        ->placeholder();
                                }

                                $details = isset($_GET['details']) ? $_GET['details'] : 'Yes';
                                $form->addHiddenValue('details', 'No');
                                $showHide = $form->getFactory()->createCheckbox('details')->addClass('details')->setValue('Yes')->checked($details)->inline(true)
                                    ->description(__('Shqow/Hide Details'))->wrap('&nbsp;<span class="small emphasis displayInlineBlock">', '</span>');

                                $rowFilter = $form->addRow();
                                $rowFilter->addSearchSubmit($pupilsight->session, __('Clear Filters'), array('pupilsightPersonID', 'allStudents', 'search', 'subpage'))->prepend($showHide->getOutput());

                                echo $form->getOutput();
                    ?>

                                <script type="text/javascript">
                                    /* Show/Hide detail control */
                                    $(document).ready(function() {
                                        var updateDetails = function() {
                                            if ($('input[name=details]:checked').val() == "Yes") {
                                                $(".detailItem").slideDown("fast", $(".detailItem").css("{'display' : 'table-row'}"));
                                            } else {
                                                $(".detailItem").slideUp("fast");
                                            }
                                        }
                                        $(".details").click(updateDetails);
                                        updateDetails();
                                    });
                                </script>

<?php
                                if ($highestAction == 'View Markbook_myClasses') {
                                    // Get class list (limited to a teacher's classes)
                                    try {
                                        $dataList['pupilsightPersonIDTeacher'] = $_SESSION[$guid]['pupilsightPersonID'];
                                        $dataList['pupilsightPersonIDStudent'] = $pupilsightPersonID;
                                        $sqlList = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourse.name, pupilsightCourseClass.pupilsightCourseClassID, pupilsightScaleGrade.value AS target
                                            FROM pupilsightCourse
                                            JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID)
                                            JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                                            JOIN pupilsightCourseClassPerson as teacherParticipant ON (teacherParticipant.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                                            LEFT JOIN pupilsightMarkbookTarget ON (
                                                pupilsightMarkbookTarget.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID
                                                AND pupilsightMarkbookTarget.pupilsightPersonIDStudent=:pupilsightPersonIDStudent)
                                            LEFT JOIN pupilsightScaleGrade ON (pupilsightMarkbookTarget.pupilsightScaleGradeID=pupilsightScaleGrade.pupilsightScaleGradeID)
                                            WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonIDStudent
                                            AND teacherParticipant.pupilsightPersonID=:pupilsightPersonIDTeacher
                                            $and ORDER BY course, class";
                                        $resultList = $connection2->prepare($sqlList);
                                        $resultList->execute($dataList);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                    }
                                } else {
                                    // Get class list (all classes)
                                    try {
                                        $dataList['pupilsightPersonIDStudent'] = $pupilsightPersonID;
                                        $sqlList = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourse.name, pupilsightCourseClass.pupilsightCourseClassID, pupilsightScaleGrade.value AS target
                                            FROM pupilsightCourse
                                            JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID)
                                            JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                                            LEFT JOIN pupilsightMarkbookTarget ON (
                                                pupilsightMarkbookTarget.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID
                                                AND pupilsightMarkbookTarget.pupilsightPersonIDStudent=:pupilsightPersonIDStudent)
                                            LEFT JOIN pupilsightScaleGrade ON (pupilsightMarkbookTarget.pupilsightScaleGradeID=pupilsightScaleGrade.pupilsightScaleGradeID)
                                            WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonIDStudent
                                            $and ORDER BY course, class";
                                        $resultList = $connection2->prepare($sqlList);
                                        $resultList->execute($dataList);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                    }
                                }


                                if ($resultList->rowCount() > 0) {
                                    while ($rowList = $resultList->fetch()) {
                                        try {
                                            $dataEntry['pupilsightPersonID'] = $pupilsightPersonID;
                                            $dataEntry['pupilsightCourseClassID'] = $rowList['pupilsightCourseClassID'];
                                            if ($highestAction == 'View Markbook_viewMyChildrensClasses') {
                                                $sqlEntry = "SELECT *, pupilsightMarkbookColumn.comment AS commentOn, pupilsightMarkbookColumn.uploadedResponse AS uploadedResponseOn, pupilsightMarkbookEntry.comment AS comment FROM pupilsightMarkbookEntry JOIN pupilsightMarkbookColumn ON (pupilsightMarkbookEntry.pupilsightMarkbookColumnID=pupilsightMarkbookColumn.pupilsightMarkbookColumnID) WHERE pupilsightPersonIDStudent=:pupilsightPersonID AND pupilsightCourseClassID=:pupilsightCourseClassID AND complete='Y' AND completeDate<='" . date('Y-m-d') . "' AND viewableParents='Y' $and2 ORDER BY completeDate";
                                            } elseif ($highestAction == 'View Markbook_myMarks') {
                                                $sqlEntry = "SELECT *, pupilsightMarkbookColumn.comment AS commentOn, pupilsightMarkbookColumn.uploadedResponse AS uploadedResponseOn, pupilsightMarkbookEntry.comment AS comment FROM pupilsightMarkbookEntry JOIN pupilsightMarkbookColumn ON (pupilsightMarkbookEntry.pupilsightMarkbookColumnID=pupilsightMarkbookColumn.pupilsightMarkbookColumnID) WHERE pupilsightPersonIDStudent=:pupilsightPersonID AND pupilsightCourseClassID=:pupilsightCourseClassID AND complete='Y' AND completeDate<='" . date('Y-m-d') . "' AND viewableStudents='Y' $and2 ORDER BY completeDate";
                                            } else {
                                                $sqlEntry = "SELECT *, pupilsightMarkbookColumn.comment AS commentOn, pupilsightMarkbookColumn.uploadedResponse AS uploadedResponseOn, pupilsightMarkbookEntry.comment AS comment FROM pupilsightMarkbookEntry JOIN pupilsightMarkbookColumn ON (pupilsightMarkbookEntry.pupilsightMarkbookColumnID=pupilsightMarkbookColumn.pupilsightMarkbookColumnID) WHERE pupilsightPersonIDStudent=:pupilsightPersonID AND pupilsightCourseClassID=:pupilsightCourseClassID AND complete='Y' AND completeDate<='" . date('Y-m-d') . "' $and2 ORDER BY completeDate";
                                            }
                                            $resultEntry = $connection2->prepare($sqlEntry);
                                            $resultEntry->execute($dataEntry);
                                        } catch (PDOException $e) {
                                            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                        }

                                        if ($resultEntry->rowCount() > 0) {
                                            echo "<a name='" . $rowList['pupilsightCourseClassID'] . "'></a><h4>" . $rowList['course'] . '.' . $rowList['class'] . " <span style='font-size:85%; font-style: italic'>(" . $rowList['name'] . ')</span></h4>';

                                            try {
                                                $dataTeachers = array('pupilsightCourseClassID' => $rowList['pupilsightCourseClassID']);
                                                $sqlTeachers = "SELECT title, surname, preferredName FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE role='Teacher' AND pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY surname, preferredName";
                                                $resultTeachers = $connection2->prepare($sqlTeachers);
                                                $resultTeachers->execute($dataTeachers);
                                            } catch (PDOException $e) {
                                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                            }

                                            $teachers = '<p><b>' . __('Taught by:') . '</b> ';
                                            while ($rowTeachers = $resultTeachers->fetch()) {
                                                $teachers = $teachers . formatName($rowTeachers['title'], $rowTeachers['preferredName'], $rowTeachers['surname'], 'Staff', false, false) . ', ';
                                            }
                                            $teachers = substr($teachers, 0, -2);
                                            $teachers = $teachers . '</p>';
                                            echo $teachers;

                                            if ($rowList['target'] != '') {
                                                echo "<div style='font-weight: bold' class='linkTop'>";
                                                echo __('Target') . ': ' . $rowList['target'];
                                                echo '</div>';
                                            }

                                            echo "<table cellspacing='0' style='width: 100%'>";
                                            echo "<tr class='head'>";
                                            echo "<th style='width: 120px'>";
                                            echo __('Assessment');
                                            echo '</th>';
                                            if ($enableModifiedAssessment == 'Y') {
                                                echo "<th style='width: 75px'>";
                                                echo __('Modified');
                                                echo '</th>';
                                            }
                                            echo "<th style='width: 75px; text-align: center'>";
                                            if ($attainmentAlternativeName != '') {
                                                echo $attainmentAlternativeName;
                                            } else {
                                                echo __('Attainment');
                                            }
                                            echo '</th>';
                                            if ($enableEffort == 'Y') {
                                                echo "<th style='width: 75px; text-align: center'>";
                                                if ($effortAlternativeName != '') {
                                                    echo $effortAlternativeName;
                                                } else {
                                                    echo __('Effort');
                                                }
                                                echo '</th>';
                                            }
                                            echo '<th>';
                                            echo __('Comment');
                                            echo '</th>';
                                            echo "<th style='width: 75px'>";
                                            echo __('Submission');
                                            echo '</th>';
                                            echo '</tr>';

                                            $count = 0;
                                            while ($rowEntry = $resultEntry->fetch()) {
                                                if ($count % 2 == 0) {
                                                    $rowNum = 'even';
                                                } else {
                                                    $rowNum = 'odd';
                                                }
                                                ++$count;
                                                ++$entryCount;

                                                echo "<tr class=$rowNum>";
                                                echo '<td id="name" >';
                                                echo "<span title='" . htmlPrep($rowEntry['description']) . "'><b><u>" . $rowEntry['name'] . '</u></b></span>';
                                                echo "<span style='font-size: 90%; font-style: italic; font-weight: normal'>";
                                                $unit = getUnit($connection2, $rowEntry['pupilsightUnitID'], $rowEntry['pupilsightCourseClassID']);
                                                if (isset($unit[0])) {
                                                    echo $unit[0] . '';
                                                }
                                                if (isset($unit[1])) {
                                                    if ($unit[1] != '') {
                                                        echo $unit[1] . ' ' . __('Unit') . '</i>';
                                                    }
                                                }
                                                if ($rowEntry['completeDate'] != '') {
                                                    echo __('Marked on') . ' ' . dateConvertBack($guid, $rowEntry['completeDate']) . '';
                                                } else {
                                                    echo __('Unmarked') . '';
                                                }
                                                echo $rowEntry['type'];
                                                if ($rowEntry['attachment'] != '' and file_exists($_SESSION[$guid]['absolutePath'] . '/' . $rowEntry['attachment'])) {
                                                    echo " | <a 'title='" . __('Download more information') . "' href='" . $_SESSION[$guid]['absoluteURL'] . '/' . $rowEntry['attachment'] . "'>" . __('More info') . '</a>';
                                                }
                                                echo '</span>';
                                                echo '</td>';
                                                if ($enableModifiedAssessment == 'Y') {
                                                    if (!is_null($rowEntry['modifiedAssessment'])) {
                                                        echo "<td id='modifiedAssessment'>";
                                                        echo ynExpander($guid, $rowEntry['modifiedAssessment']);
                                                        echo '</td>';
                                                    } else {
                                                        echo "<td id='N/A' class='dull' style='color: #bbb; text-align: center'>";
                                                        echo __('N/A');
                                                        echo '</td>';
                                                    }
                                                }
                                                if ($rowEntry['attainment'] == 'N' or ($rowEntry['pupilsightScaleIDAttainment'] == '' and $rowEntry['pupilsightRubricIDAttainment'] == '')) {
                                                    echo "<td class='dull' style='color: #bbb; text-align: center'>";
                                                    echo __('N/A');
                                                    echo '</td>';
                                                } else {
                                                    echo "<td id='usage' style='text-align: center'>";
                                                    $attainmentExtra = '';
                                                    try {
                                                        $dataAttainment = array('pupilsightScaleIDAttainment' => $rowEntry['pupilsightScaleIDAttainment']);
                                                        $sqlAttainment = 'SELECT * FROM pupilsightScale WHERE pupilsightScaleID=:pupilsightScaleIDAttainment';
                                                        $resultAttainment = $connection2->prepare($sqlAttainment);
                                                        $resultAttainment->execute($dataAttainment);
                                                    } catch (PDOException $e) {
                                                        echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                                    }
                                                    if ($resultAttainment->rowCount() == 1) {
                                                        $rowAttainment = $resultAttainment->fetch();
                                                        $attainmentExtra = '' . __($rowAttainment['usage']);
                                                    }
                                                    $styleAttainment = "style='font-weight: bold'";
                                                    if ($rowEntry['attainmentConcern'] == 'Y' and $showParentAttainmentWarning == 'Y') {
                                                        $styleAttainment = "style='color: #" . $alert['color'] . '; font-weight: bold; border: 2px solid #' . $alert['color'] . '; padding: 2px 4px; background-color: #' . $alert['colorBG'] . "'";
                                                    } elseif ($rowEntry['attainmentConcern'] == 'P' and $showParentAttainmentWarning == 'Y') {
                                                        $styleAttainment = "style='color: #390; font-weight: bold; border: 2px solid #390; padding: 2px 4px; background-color: #D4F6DC'";
                                                    }
                                                    echo "<div $styleAttainment>" . $rowEntry['attainmentValue'];
                                                    if ($rowEntry['pupilsightRubricIDAttainment'] != '' and $enableRubrics == 'Y') {
                                                        echo "<a class='thickbox' href='" . $_SESSION[$guid]['absoluteURL'] . '/fullscreen.php?q=/modules/Markbook/markbook_view_rubric.php&pupilsightRubricID=' . $rowEntry['pupilsightRubricIDAttainment'] . '&pupilsightCourseClassID=' . $rowList['pupilsightCourseClassID'] . '&pupilsightMarkbookColumnID=' . $rowEntry['pupilsightMarkbookColumnID'] . "&pupilsightPersonID=$pupilsightPersonID&mark=FALSE&type=attainment&width=1100&height=550'><img style='margin-bottom: -3px; margin-left: 3px' title='View Rubric' src='./themes/" . $_SESSION[$guid]['pupilsightThemeName'] . "/img/rubric.png'/></a>";
                                                    }
                                                    echo '</div>';
                                                    if ($rowEntry['attainmentValue'] != '') {
                                                        echo "<div class='detailItem' style='font-size: 75%; font-style: italic; margin-top: 2px'><b>" . htmlPrep(__($rowEntry['attainmentDescriptor'])) . '</b>' . __($attainmentExtra) . '</div>';
                                                    }
                                                    echo '</td>';
                                                }
                                                if ($enableEffort == 'Y') {
                                                    if ($rowEntry['effort'] == 'N' or ($rowEntry['pupilsightScaleIDEffort'] == '' and $rowEntry['pupilsightRubricIDEffort'] == '')) {
                                                        echo "<td class='dull' style='color: #bbb; text-align: center'>";
                                                        echo __('N/A');
                                                        echo '</td>';
                                                    } else {
                                                        echo "<td style='text-align: center'>";
                                                        $effortExtra = '';
                                                        try {
                                                            $dataEffort = array('pupilsightScaleIDEffort' => $rowEntry['pupilsightScaleIDEffort']);
                                                            $sqlEffort = 'SELECT * FROM pupilsightScale WHERE pupilsightScaleID=:pupilsightScaleIDEffort';
                                                            $resultEffort = $connection2->prepare($sqlEffort);
                                                            $resultEffort->execute($dataEffort);
                                                        } catch (PDOException $e) {
                                                            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                                        }

                                                        if ($resultEffort->rowCount() == 1) {
                                                            $rowEffort = $resultEffort->fetch();
                                                            $effortExtra = '' . __($rowEffort['usage']);
                                                        }
                                                        $styleEffort = "style='font-weight: bold'";
                                                        if ($rowEntry['effortConcern'] == 'Y' and $showParentEffortWarning == 'Y') {
                                                            $styleEffort = "style='color: #" . $alert['color'] . '; font-weight: bold; border: 2px solid #' . $alert['color'] . '; padding: 2px 4px; background-color: #' . $alert['colorBG'] . "'";
                                                        }
                                                        echo "<div $styleEffort>" . $rowEntry['effortValue'];
                                                        if ($rowEntry['pupilsightRubricIDEffort'] != '' and $enableRubrics == 'Y') {
                                                            echo "<a class='thickbox' href='" . $_SESSION[$guid]['absoluteURL'] . '/fullscreen.php?q=/modules/Markbook/markbook_view_rubric.php&pupilsightRubricID=' . $rowEntry['pupilsightRubricIDEffort'] . '&pupilsightCourseClassID=' . $rowList['pupilsightCourseClassID'] . '&pupilsightMarkbookColumnID=' . $rowEntry['pupilsightMarkbookColumnID'] . "&pupilsightPersonID=$pupilsightPersonID&mark=FALSE&type=effort&width=1100&height=550'><img style='margin-bottom: -3px; margin-left: 3px' title='View Rubric' src='./themes/" . $_SESSION[$guid]['pupilsightThemeName'] . "/img/rubric.png'/></a>";
                                                        }
                                                        echo '</div>';
                                                        if ($rowEntry['effortValue'] != '') {
                                                            echo "<div class='detailItem' style='font-size: 75%; font-style: italic; margin-top: 2px'><b>" . htmlPrep(__($rowEntry['effortDescriptor'])) . '</b>' . __($effortExtra) . '</div>';
                                                        }
                                                        echo '</td>';
                                                    }
                                                }
                                                if ($rowEntry['commentOn'] == 'N' and $rowEntry['uploadedResponseOn'] == 'N') {
                                                    echo "<td class='dull' style='color: #bbb; text-align: center'>";
                                                    echo __('N/A');
                                                    echo '</td>';
                                                } else {
                                                    echo '<td>';
                                                    if ($rowEntry['comment'] != '') {
                                                        if (mb_strlen($rowEntry['comment']) > 200) {
                                                            echo "<script type='text/javascript'>";
                                                            echo '$(document).ready(function(){';
                                                            echo "\$(\".comment-$entryCount\").hide();";
                                                            echo "\$(\".show_hide-$entryCount\").fadeIn(1000);";
                                                            echo "\$(\".show_hide-$entryCount\").click(function(){";
                                                            echo "\$(\".comment-$entryCount\").fadeToggle(1000);";
                                                            echo '});';
                                                            echo '});';
                                                            echo '</script>';
                                                            echo '<span>' . mb_substr($rowEntry['comment'], 0, 200) . '...';
                                                            echo "<a title='" . __('View Description') . "' class='show_hide-$entryCount' onclick='return false;' href='#'>" . __('Read more') . '</a></span>';
                                                        } else {
                                                            echo nl2br($rowEntry['comment']) . '';
                                                        }
                                                    }
                                                    if ($rowEntry['response'] != '') {
                                                        echo "<a title='Uploaded Response' href='" . $_SESSION[$guid]['absoluteURL'] . '/' . $rowEntry['response'] . "'>" . __('Uploaded Response') . '</a>';
                                                    }
                                                    echo '</td>';
                                                }
                                                if ($rowEntry['pupilsightPlannerEntryID'] == 0) {
                                                    echo "<td class='dull' style='color: #bbb; text-align: center'>";
                                                    echo __('N/A');
                                                    echo '</td>';
                                                } else {
                                                    try {
                                                        $dataSub = array('pupilsightPlannerEntryID' => $rowEntry['pupilsightPlannerEntryID']);
                                                        $sqlSub = "SELECT * FROM pupilsightPlannerEntry WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND homeworkSubmission='Y'";
                                                        $resultSub = $connection2->prepare($sqlSub);
                                                        $resultSub->execute($dataSub);
                                                    } catch (PDOException $e) {
                                                        echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                                    }
                                                    if ($resultSub->rowCount() != 1) {
                                                        echo "<td class='dull' style='color: #bbb; text-align: center'>";
                                                        echo __('N/A');
                                                        echo '</td>';
                                                    } else {
                                                        echo '<td>';
                                                        $rowSub = $resultSub->fetch();

                                                        try {
                                                            $dataWork = array('pupilsightPlannerEntryID' => $rowEntry['pupilsightPlannerEntryID'], 'pupilsightPersonID' => $_GET['pupilsightPersonID']);
                                                            $sqlWork = 'SELECT * FROM pupilsightPlannerEntryHomework WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID ORDER BY count DESC';
                                                            $resultWork = $connection2->prepare($sqlWork);
                                                            $resultWork->execute($dataWork);
                                                        } catch (PDOException $e) {
                                                            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                                        }
                                                        if ($resultWork->rowCount() > 0) {
                                                            $rowWork = $resultWork->fetch();

                                                            if ($rowWork['status'] == 'Exemption') {
                                                                $linkText = __('Exemption');
                                                            } elseif ($rowWork['version'] == 'Final') {
                                                                $linkText = __('Final');
                                                            } else {
                                                                $linkText = __('Draft') . ' ' . $rowWork['count'];
                                                            }

                                                            $style = '';
                                                            $status = 'On Time';
                                                            if ($rowWork['status'] == 'Exemption') {
                                                                $status = __('Exemption');
                                                            } elseif ($rowWork['status'] == 'Late') {
                                                                $style = "style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px'";
                                                                $status = __('Late');
                                                            }

                                                            if ($rowWork['type'] == 'File') {
                                                                echo "<span title='" . $rowWork['version'] . ". $status. " . sprintf(__('Submitted at %1$s on %2$s'), substr($rowWork['timestamp'], 11, 5), dateConvertBack($guid, substr($rowWork['timestamp'], 0, 10))) . "' $style><a href='" . $_SESSION[$guid]['absoluteURL'] . '/' . $rowWork['location'] . "'>$linkText</a></span>";
                                                            } elseif ($rowWork['type'] == 'Link') {
                                                                echo "<span title='" . $rowWork['version'] . ". $status. " . sprintf(__('Submitted at %1$s on %2$s'), substr($rowWork['timestamp'], 11, 5), dateConvertBack($guid, substr($rowWork['timestamp'], 0, 10))) . "' $style><a target='_blank' href='" . $rowWork['location'] . "'>$linkText</a></span>";
                                                            } else {
                                                                echo "<span title='$status. " . sprintf(__('Recorded at %1$s on %2$s'), substr($rowWork['timestamp'], 11, 5), dateConvertBack($guid, substr($rowWork['timestamp'], 0, 10))) . "' $style>$linkText</span>";
                                                            }
                                                        } else {
                                                            if (date('Y-m-d H:i:s') < $rowSub['homeworkDueDateTime']) {
                                                                echo "<span title='Pending'>" . __('Pending') . '</span>';
                                                            } else {
                                                                if ($row['dateStart'] > $rowSub['date']) {
                                                                    echo "<span title='" . __('Student joined school after assessment was given.') . "' style='color: #000; font-weight: normal; border: 2px none #ff0000; padding: 2px 4px'>" . __('NA') . '</span>';
                                                                } else {
                                                                    if ($rowSub['homeworkSubmissionRequired'] == 'Compulsory') {
                                                                        echo "<div style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px; margin: 2px 0px'>" . __('Incomplete') . '</div>';
                                                                    } else {
                                                                        echo __('Not submitted online');
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        echo '</td>';
                                                    }
                                                }
                                                echo '</tr>';
                                                if (mb_strlen($rowEntry['comment']) > 200) {
                                                    echo "<tr class='comment-$entryCount' id='comment-$entryCount'>";
                                                    echo '<td colspan=6>';
                                                    echo nl2br($rowEntry['comment']);
                                                    echo '</td>';
                                                    echo '</tr>';
                                                }
                                            }

                                            $enableColumnWeighting = getSettingByScope($connection2, 'Markbook', 'enableColumnWeighting');
                                            $enableDisplayCumulativeMarks = getSettingByScope($connection2, 'Markbook', 'enableDisplayCumulativeMarks');

                                            if ($enableColumnWeighting == 'Y' && $enableDisplayCumulativeMarks == 'Y') {
                                                renderStudentCumulativeMarks($pupilsight, $pdo, $_GET['pupilsightPersonID'], $rowList['pupilsightCourseClassID']);
                                            }

                                            echo '</table>';
                                        }
                                    }
                                }
                                if ($entryCount < 1) {
                                    echo "<div class='alert alert-danger'>";
                                    echo __('There are no records to display.');
                                    echo '</div>';
                                }
                            }
                        }
                    } elseif ($subpage == 'Internal Assessment') {
                        if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/internalAssessment_view.php') == false) {
                            echo "<div class='alert alert-danger'>";
                            echo __('Your request failed because you do not have access to this action.');
                            echo '</div>';
                        } else {
                            $highestAction = getHighestGroupedAction($guid, '/modules/Formal Assessment/internalAssessment_view.php', $connection2);
                            if ($highestAction == false) {
                                echo "<div class='alert alert-danger'>";
                                echo __('The highest grouped action cannot be determined.');
                                echo '</div>';
                            } else {
                                //Module includes
                                include './modules/Formal Assessment/moduleFunctions.php';

                                if ($highestAction == 'View Internal Assessments_all') {
                                    echo getInternalAssessmentRecord($guid, $connection2, $pupilsightPersonID);
                                } elseif ($highestAction == 'View Internal Assessments_myChildrens') {
                                    echo getInternalAssessmentRecord($guid, $connection2, $pupilsightPersonID, 'parent');
                                } elseif ($highestAction == 'View Internal Assessments_mine') {
                                    echo getInternalAssessmentRecord($guid, $connection2, $_SESSION[$guid]['pupilsightPersonID'], 'student');
                                }
                            }
                        }
                    } elseif ($subpage == 'External Assessment') {
                        if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/externalAssessment_details.php') == false and isActionAccessible($guid, $connection2, '/modules/Formal Assessment/externalAssessment_view.php') == false) {
                            echo "<div class='alert alert-danger'>";
                            echo __('Your request failed because you do not have access to this action.');
                            echo '</div>';
                        } else {
                            //Module includes
                            include './modules/Formal Assessment/moduleFunctions.php';

                            //Print assessments
                            $pupilsightYearGroupID = '';
                            if (isset($row['pupilsightYearGroupID'])) {
                                $pupilsightYearGroupID = $row['pupilsightYearGroupID'];
                            }
                            externalAssessmentDetails($guid, $pupilsightPersonID, $connection2, $pupilsightYearGroupID);
                        }
                    } elseif ($subpage == 'Individual Needs') {
                        if (isActionAccessible($guid, $connection2, '/modules/Individual Needs/in_view.php') == false) {
                            echo "<div class='alert alert-danger'>";
                            echo __('Your request failed because you do not have access to this action.');
                            echo '</div>';
                        } else {
                            //Edit link
                            if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage.php') == true) {
                                echo "<div class='text-right'>";
                                echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Individual Needs/in_edit.php&pupilsightPersonID=$pupilsightPersonID' class='btn btn-link'><span class='mdi mdi-pencil-box-outline mdi-18px mr-1'></span> Edit</a> ";
                                echo '</div>';
                            }

                            //Module includes
                            include './modules/Individual Needs/moduleFunctions.php';

                            $statusTable = printINStatusTable($connection2, $guid, $pupilsightPersonID, 'disabled');
                            if ($statusTable == false) {
                                echo "<div class='alert alert-danger'>";
                                echo __('Your request failed due to a database error.');
                                echo '</div>';
                            } else {
                                echo $statusTable;
                            }

                            //Get and display a list of student's educational assistants
                            try {
                                $dataDetail = array('pupilsightPersonID1' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID2' => $pupilsightPersonID);
                                $sqlDetail = "(SELECT DISTINCT surname, preferredName, email
                                    FROM pupilsightPerson
                                        JOIN pupilsightINAssistant ON (pupilsightINAssistant.pupilsightPersonIDAssistant=pupilsightPerson.pupilsightPersonID)
                                    WHERE status='Full'
                                        AND pupilsightPersonIDStudent=:pupilsightPersonID1)
                                UNION
                                (SELECT DISTINCT surname, preferredName, email
                                    FROM pupilsightPerson
                                        JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightPersonIDEA=pupilsightPerson.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDEA2=pupilsightPerson.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDEA3=pupilsightPerson.pupilsightPersonID)
                                        JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                                        JOIN pupilsightSchoolYear ON (pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID)
                                    WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
                                        AND pupilsightStudentEnrolment.pupilsightPersonID=:pupilsightPersonID2
                                )
                                ORDER BY preferredName, surname, email";
                                $resultDetail = $connection2->prepare($sqlDetail);
                                $resultDetail->execute($dataDetail);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }
                            if ($resultDetail->rowCount() > 0) {
                                echo '<h3>';
                                echo __('Educational Assistants');
                                echo '</h3>';

                                echo '<ul>';
                                while ($rowDetail = $resultDetail->fetch()) {
                                    echo '<li>' . htmlPrep(Format::name('', $rowDetail['preferredName'], $rowDetail['surname'], 'Student', false));
                                    if ($rowDetail['email'] != '') {
                                        echo htmlPrep(' <' . $rowDetail['email'] . '>');
                                    }
                                    echo '</li>';
                                }
                                echo '</ul>';
                            }


                            echo '<h3>';
                            echo __('Individual Education Plan');
                            echo '</h3>';
                            try {
                                $dataIN = array('pupilsightPersonID' => $pupilsightPersonID);
                                $sqlIN = 'SELECT * FROM pupilsightIN WHERE pupilsightPersonID=:pupilsightPersonID';
                                $resultIN = $connection2->prepare($sqlIN);
                                $resultIN->execute($dataIN);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }

                            if ($resultIN->rowCount() != 1) {
                                echo "<div class='alert alert-danger'>";
                                echo __('There are no records to display.');
                                echo '</div>';
                            } else {
                                $rowIN = $resultIN->fetch();

                                echo "<div style='font-weight: bold'>" . __('Targets') . '</div>';
                                echo '<p>' . $rowIN['targets'] . '</p>';

                                echo "<div style='font-weight: bold; margin-top: 30px'>" . __('Teaching Strategies') . '</div>';
                                echo '<p>' . $rowIN['strategies'] . '</p>';

                                echo "<div style='font-weight: bold; margin-top: 30px'>" . __('Notes & Review') . 's</div>';
                                echo '<p>' . $rowIN['notes'] . '</p>';
                            }
                        }
                    } elseif ($subpage == 'Library Borrowing') {
                        if (isActionAccessible($guid, $connection2, '/modules/Library/report_studentBorrowingRecord.php') == false) {
                            echo "<div class='alert alert-danger'>";
                            echo __('Your request failed because you do not have access to this action.');
                            echo '</div>';
                        } else {
                            include './modules/Library/moduleFunctions.php';

                            //Print borrowing record
                            $output = getBorrowingRecord($guid, $connection2, $pupilsightPersonID);
                            if ($output == false) {
                                echo "<div class='alert alert-danger'>";
                                echo __('Your request failed due to a database error.');
                                echo '</div>';
                            } else {
                                echo $output;
                            }
                        }
                    } elseif ($subpage == 'Timetable') {
                        if (isActionAccessible($guid, $connection2, '/modules/Timetable/tt_view.php') == false) {
                            echo "<div class='alert alert-danger'>";
                            echo __('Your request failed because you do not have access to this action.');
                            echo '</div>';
                        } else {
                            if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit.php') == true) {
                                $role = getRoleCategory($row['pupilsightRoleIDPrimary'], $connection2);
                                if ($role == 'Student' or $role == 'Staff') {
                                    echo "<div class='text-right'>";
                                    echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit.php&pupilsightPersonID=$pupilsightPersonID&pupilsightSchoolYearID=" . $_SESSION[$guid]['pupilsightSchoolYearID'] . "&type=$role'><i style='margin: 0 0 -4px 5px' title='" . __('Edit') . "' class='mdi mdi-pencil-edit mr-2'></i> Edit</a> ";
                                    echo '</div>';
                                }
                            }

                            include './modules/Timetable/moduleFunctions.php';
                            $ttDate = null;
                            if (isset($_POST['ttDate'])) {
                                $ttDate = dateConvertToTimestamp(dateConvert($guid, $_POST['ttDate']));
                            }
                            $tt = renderTT($guid, $connection2, $pupilsightPersonID, '', false, $ttDate, '/modules/Students/student_view_details.php', "&pupilsightPersonID=$pupilsightPersonID&search=$search&allStudents=$allStudents&subpage=Timetable");
                            if ($tt != false) {
                                echo $tt;
                            } else {
                                echo "<div class='alert alert-danger'>";
                                echo __('There are no records to display.');
                                echo '</div>';
                            }
                        }
                    } elseif ($subpage == 'Activities') {
                        if (!(isActionAccessible($guid, $connection2, '/modules/Activities/report_activityChoices_byStudent'))) {
                            echo "<div class='alert alert-danger'>";
                            echo __('Your request failed because you do not have access to this action.');
                            echo '</div>';
                        } else {
                            echo '<p>';
                            echo __('This report shows the current and historical activities that a student has enroled in.');
                            echo '</p>';

                            $dateType = getSettingByScope($connection2, 'Activities', 'dateType');
                            if ($dateType == 'Term') {
                                $maxPerTerm = getSettingByScope($connection2, 'Activities', 'maxPerTerm');
                            }

                            try {
                                $dataYears = array('pupilsightPersonID' => $pupilsightPersonID);
                                $sqlYears = 'SELECT * FROM pupilsightStudentEnrolment JOIN pupilsightSchoolYear ON (pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE pupilsightPersonID=:pupilsightPersonID ORDER BY sequenceNumber DESC';
                                $resultYears = $connection2->prepare($sqlYears);
                                $resultYears->execute($dataYears);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }

                            if ($resultYears->rowCount() < 1) {
                                echo "<div class='alert alert-danger'>";
                                echo __('There are no records to display.');
                                echo '</div>';
                            } else {
                                $yearCount = 0;
                                while ($rowYears = $resultYears->fetch()) {
                                    $class = '';
                                    if ($yearCount == 0) {
                                        $class = "class='top'";
                                    }
                                    echo "<h3 $class>";
                                    echo $rowYears['name'];
                                    echo '</h3>';

                                    ++$yearCount;
                                    try {
                                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $rowYears['pupilsightSchoolYearID']);
                                        $sql = "SELECT pupilsightActivity.*, pupilsightActivityStudent.status, NULL AS role FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivity.pupilsightActivityID=pupilsightActivityStudent.pupilsightActivityID) WHERE pupilsightActivityStudent.pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' ORDER BY name";
                                        $result = $connection2->prepare($sql);
                                        $result->execute($data);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                    }

                                    if ($result->rowCount() < 1) {
                                        echo "<div class='alert alert-danger'>";
                                        echo __('There are no records to display.');
                                        echo '</div>';
                                    } else {
                                        echo "<table class='table'>";
                                        echo "<tr class='head'>";
                                        echo '<th>';
                                        echo __('Activity');
                                        echo '</th>';
                                        $options = getSettingByScope($connection2, 'Activities', 'activityTypes');
                                        if ($options != '') {
                                            echo '<th>';
                                            echo __('Type');
                                            echo '</th>';
                                        }
                                        echo '<th>';
                                        if ($dateType != 'Date') {
                                            echo __('Term');
                                        } else {
                                            echo __('Dates');
                                        }
                                        echo '</th>';
                                        echo '<th>';
                                        echo __('Status');
                                        echo '</th>';
                                        echo '<th>';
                                        echo __('Actions');
                                        echo '</th>';
                                        echo '</tr>';

                                        $count = 0;
                                        $rowNum = 'odd';
                                        while ($row = $result->fetch()) {
                                            if ($count % 2 == 0) {
                                                $rowNum = 'even';
                                            } else {
                                                $rowNum = 'odd';
                                            }
                                            ++$count;

                                            //COLOR ROW BY STATUS!
                                            echo "<tr class=$rowNum>";
                                            echo '<td id="name">';
                                            echo $row['name'];
                                            echo '</td>';
                                            if ($options != '') {
                                                echo '<td id="type">';
                                                echo trim($row['type']);
                                                echo '</td>';
                                            }
                                            echo '<td id="pupilsightSchoolYearTermIDList">';
                                            if ($dateType != 'Date') {
                                                $terms = getTerms($connection2, $_SESSION[$guid]['pupilsightSchoolYearID'], true);
                                                $termList = '';
                                                for ($i = 0; $i < count($terms); $i = $i + 2) {
                                                    if (is_numeric(strpos($row['pupilsightSchoolYearTermIDList'], $terms[$i]))) {
                                                        $termList .= $terms[($i + 1)] . '';
                                                    }
                                                }
                                                echo $termList;
                                            } else {
                                                if (substr($row['programStart'], 0, 4) == substr($row['programEnd'], 0, 4)) {
                                                    if (substr($row['programStart'], 5, 2) == substr($row['programEnd'], 5, 2)) {
                                                        echo date('F', mktime(0, 0, 0, substr($row['programStart'], 5, 2))) . ' ' . substr($row['programStart'], 0, 4);
                                                    } else {
                                                        echo date('F', mktime(0, 0, 0, substr($row['programStart'], 5, 2))) . ' - ' . date('F', mktime(0, 0, 0, substr($row['programEnd'], 5, 2))) . '' . substr($row['programStart'], 0, 4);
                                                    }
                                                } else {
                                                    echo date('F', mktime(0, 0, 0, substr($row['programStart'], 5, 2))) . ' ' . substr($row['programStart'], 0, 4) . ' -' . date('F', mktime(0, 0, 0, substr($row['programEnd'], 5, 2))) . ' ' . substr($row['programEnd'], 0, 4);
                                                }
                                            }
                                            echo '</td>';
                                            echo '<td>';
                                            if ($row['status'] != '') {
                                                echo $row['status'];
                                            } else {
                                                echo '<i>' . __('NA') . '</i>';
                                            }
                                            echo '</td>';
                                            echo '<td>';
                                            echo "<a class='thickbox' href='" . $_SESSION[$guid]['absoluteURL'] . '/fullscreen.php?q=/modules/Activities/activities_my_full.php&pupilsightActivityID=' . $row['pupilsightActivityID'] . "&width=1000&height=550'><img title='" . __('View Details') . "' src='./themes/" . $_SESSION[$guid]['pupilsightThemeName'] . "/img/plus.png'/></a> ";
                                            echo '</td>';
                                            echo '</tr>';
                                        }
                                        echo '</table>';
                                    }
                                }
                            }
                        }
                    } elseif ($subpage == 'Homework') {
                        if (!(isActionAccessible($guid, $connection2, '/modules/Planner/planner_edit.php') or isActionAccessible($guid, $connection2, '/modules/Planner/planner_view_full.php'))) {
                            echo "<div class='alert alert-danger'>";
                            echo __('Your request failed because you do not have access to this action.');
                            echo '</div>';
                        } else {
                            echo '<h4>';
                            echo __('Upcoming Deadlines');
                            echo '</h4>';

                            try {
                                $dataDeadlines = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                                $sqlDeadlines = "
                                (SELECT 'teacherRecorded' AS type, pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, viewableStudents, viewableParents, homework, homeworkDueDateTime, role FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND homework='Y' AND (role='Teacher' OR (role='Student' AND viewableStudents='Y')) AND homeworkDueDateTime>'" . date('Y-m-d H:i:s') . "' AND ((date<'" . date('Y-m-d') . "') OR (date='" . date('Y-m-d') . "' AND timeEnd<='" . date('H:i:s') . "')))
                                UNION
                                (SELECT 'studentRecorded' AS type, pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, 'Y' AS viewableStudents, 'Y' AS viewableParents, 'Y' AS homework, pupilsightPlannerEntryStudentHomework.homeworkDueDateTime, role FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightPlannerEntryStudentHomework ON (pupilsightPlannerEntryStudentHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID AND pupilsightPlannerEntryStudentHomework.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND (role='Teacher' OR (role='Student' AND viewableStudents='Y')) AND pupilsightPlannerEntryStudentHomework.homeworkDueDateTime>'" . date('Y-m-d H:i:s') . "' AND ((date<'" . date('Y-m-d') . "') OR (date='" . date('Y-m-d') . "' AND timeEnd<='" . date('H:i:s') . "')))
                                ORDER BY homeworkDueDateTime, type";
                                $resultDeadlines = $connection2->prepare($sqlDeadlines);
                                $resultDeadlines->execute($dataDeadlines);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }

                            if ($resultDeadlines->rowCount() < 1) {
                                echo "<div class='alert alert-sucess'>";
                                echo __('No upcoming deadlines!');
                                echo '</div>';
                            } else {
                                echo '<ol>';
                                while ($rowDeadlines = $resultDeadlines->fetch()) {
                                    $diff = (strtotime(substr($rowDeadlines['homeworkDueDateTime'], 0, 10)) - strtotime(date('Y-m-d'))) / 86400;
                                    $style = "style='padding-right: 3px;'";
                                    if ($diff < 2) {
                                        $style = "style='padding-right: 3px; border-right: 10px solid #cc0000'";
                                    } elseif ($diff < 4) {
                                        $style = "style='padding-right: 3px; border-right: 10px solid #D87718'";
                                    }
                                    echo "<li $style>";
                                    echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Planner/planner_view_full.php&search=$pupilsightPersonID&pupilsightPlannerEntryID=" . $rowDeadlines['pupilsightPlannerEntryID'] . '&viewBy=date&date=' . $rowDeadlines['date'] . "&width=1000&height=550'>" . $rowDeadlines['course'] . '.' . $rowDeadlines['class'] . '</a>';
                                    echo "<span style='font-style: italic'>" . sprintf(__('Due at %1$s on %2$s'), substr($rowDeadlines['homeworkDueDateTime'], 11, 5), dateConvertBack($guid, substr($rowDeadlines['homeworkDueDateTime'], 0, 10)));
                                    echo '</li>';
                                }
                                echo '</ol>';
                            }

                            $style = '';

                            echo '<h4>';
                            echo __('Homework History');
                            echo '</h4>';

                            $pupilsightCourseClassIDFilter = null;
                            $filter = null;
                            $filter2 = null;
                            if (isset($_GET['pupilsightCourseClassIDFilter'])) {
                                $pupilsightCourseClassIDFilter = $_GET['pupilsightCourseClassIDFilter'];
                            }
                            $dataHistory = array();
                            if ($pupilsightCourseClassIDFilter != '') {
                                $dataHistory['pupilsightCourseClassIDFilter'] = $pupilsightCourseClassIDFilter;
                                $dataHistory['pupilsightCourseClassIDFilter2'] = $pupilsightCourseClassIDFilter;
                                $filter = ' AND pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassIDFilter';
                                $filte2 = ' AND pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassIDFilte2';
                            }

                            try {
                                $dataHistory['pupilsightPersonID'] = $pupilsightPersonID;
                                $dataHistory['pupilsightSchoolYearID'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
                                $sqlHistory = "
                                (SELECT 'teacherRecorded' AS type, pupilsightPlannerEntryID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, viewableStudents, viewableParents, homework, role, homeworkDueDateTime, homeworkDetails, homeworkSubmission, homeworkSubmissionRequired FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND homework='Y' AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND (date<'" . date('Y-m-d') . "' OR (date='" . date('Y-m-d') . "' AND timeEnd<='" . date('H:i:s') . "')) $filter)
                                UNION
                                (SELECT 'studentRecorded' AS type, pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, 'Y' AS viewableStudents, 'Y' AS viewableParents, 'Y' AS homework, role, pupilsightPlannerEntryStudentHomework.homeworkDueDateTime AS homeworkDueDateTime, pupilsightPlannerEntryStudentHomework.homeworkDetails AS homeworkDetails, 'N' AS homeworkSubmission, '' AS homeworkSubmissionRequired FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightPlannerEntryStudentHomework ON (pupilsightPlannerEntryStudentHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID AND pupilsightPlannerEntryStudentHomework.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND (date<'" . date('Y-m-d') . "' OR (date='" . date('Y-m-d') . "' AND timeEnd<='" . date('H:i:s') . "')) $filter)
                                ORDER BY date DESC, timeStart DESC";
                                $resultHistory = $connection2->prepare($sqlHistory);
                                $resultHistory->execute($dataHistory);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }

                            if ($resultHistory->rowCount() < 1) {
                                echo "<div class='alert alert-danger'>";
                                echo __('There are no records to display.');
                                echo '</div>';
                            } else {
                                echo "<div class='linkTop'>";
                                $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'] . '/index.php', 'get');
                                $form->setClass('blank fullWidth');

                                $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/student_view_details.php');
                                $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);
                                $form->addHiddenValue('allStudents', $allStudents);
                                $form->addHiddenValue('search', $search);
                                $form->addHiddenValue('subpage', 'Homework');

                                $dataSelect = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'date' => date('Y-m-d'));
                                $sqlSelect = "SELECT DISTINCT pupilsightCourseClass.pupilsightCourseClassID as value, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) as name FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND homework='Y' AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND date<=:date ORDER BY name";

                                $rowFilter = $form->addRow();
                                $column = $rowFilter->addColumn()->addClass('inline right');
                                $column->addSelect('pupilsightCourseClassIDFilter')
                                    ->fromQuery($pdo, $sqlSelect, $dataSelect)
                                    ->selected($pupilsightCourseClassIDFilter)
                                    ->setClass('mediumWidth')
                                    ->placeholder();
                                $column->addSubmit(__('Go'));

                                echo $form->getOutput();
                                echo '</div>';

                                echo "<table class='table'>";
                                echo "<tr class='head'>";
                                echo '<th>';
                                echo __('Class') . '</br>';
                                echo "<span style='font-size: 85%; font-style: italic'>" . __('Date') . '</span>';
                                echo '</th>';
                                echo '<th>';
                                echo __('Lesson') . '</br>';
                                echo "<span style='font-size: 85%; font-style: italic'>" . __('Unit') . '</span>';
                                echo '</th>';
                                echo "<th style='min-width: 25%'>";
                                echo __('Type') . '';
                                echo "<span style='font-size: 85%; font-style: italic'>" . __('Details') . '</span>';
                                echo '</th>';
                                echo '<th>';
                                echo __('Deadline');
                                echo '</th>';
                                echo '<th>';
                                echo __('Online Submission');
                                echo '</th>';
                                echo '<th>';
                                echo __('Actions');
                                echo '</th>';
                                echo '</tr>';

                                $count = 0;
                                $rowNum = 'odd';
                                while ($rowHistory = $resultHistory->fetch()) {
                                    if (!($rowHistory['role'] == 'Student' and $rowHistory['viewableParents'] == 'N')) {
                                        if ($count % 2 == 0) {
                                            $rowNum = 'even';
                                        } else {
                                            $rowNum = 'odd';
                                        }
                                        ++$count;

                                        //Highlight class in progress
                                        if ((date('Y-m-d') == $rowHistory['date']) and (date('H:i:s') > $rowHistory['timeStart']) and (date('H:i:s') < $rowHistory['timeEnd'])) {
                                            $rowNum = 'current';
                                        }

                                        //COLOR ROW BY STATUS!
                                        echo "<tr class=$rowNum>";
                                        echo '<td id="course">';
                                        echo '<b>' . $rowHistory['course'] . '.' . $rowHistory['class'] . '</b></br>';
                                        echo "<span style='font-size: 85%; font-style: italic'>" . dateConvertBack($guid, $rowHistory['date']) . '</span>';
                                        echo '</td>';
                                        echo '<td id="name">';
                                        echo '<b>' . $rowHistory['name'] . '</b>';
                                        echo "<span style='font-size: 85%; font-style: italic'>";
                                        if ($rowHistory['pupilsightUnitID'] != '') {
                                            try {
                                                $dataUnit = array('pupilsightUnitID' => $rowHistory['pupilsightUnitID']);
                                                $sqlUnit = 'SELECT * FROM pupilsightUnit WHERE pupilsightUnitID=:pupilsightUnitID';
                                                $resultUnit = $connection2->prepare($sqlUnit);
                                                $resultUnit->execute($dataUnit);
                                            } catch (PDOException $e) {
                                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                            }
                                            if ($resultUnit->rowCount() == 1) {
                                                $rowUnit = $resultUnit->fetch();
                                                echo $rowUnit['name'];
                                            }
                                        }
                                        echo '</span>';
                                        echo '</td>';
                                        echo '<td id="type">';
                                        if ($rowHistory['type'] == 'teacherRecorded') {
                                            echo 'Teacher Recorded';
                                        } else {
                                            echo 'Student Recorded';
                                        }
                                        echo  '';
                                        echo "<span style='font-size: 85%; font-style: italic'>";
                                        if ($rowHistory['homeworkDetails'] != '') {
                                            if (strlen(strip_tags($rowHistory['homeworkDetails'])) < 21) {
                                                echo strip_tags($rowHistory['homeworkDetails']);
                                            } else {
                                                echo "<span $style title='" . htmlPrep(strip_tags($rowHistory['homeworkDetails'])) . "'>" . substr(strip_tags($rowHistory['homeworkDetails']), 0, 20) . '...</span>';
                                            }
                                        }
                                        echo '</span>';
                                        echo '</td>';
                                        echo '<td id="homeworkDueDateTime">';
                                        echo dateConvertBack($guid, substr($rowHistory['homeworkDueDateTime'], 0, 10));
                                        echo '</td>';
                                        echo '<td id="homeworkSubmissionRequired">';
                                        if ($rowHistory['homeworkSubmission'] == 'Y') {
                                            echo '<b>' . $rowHistory['homeworkSubmissionRequired'] . '</b>';
                                            if ($rowHistory['role'] == 'Student') {
                                                try {
                                                    $dataVersion = array('pupilsightPlannerEntryID' => $rowHistory['pupilsightPlannerEntryID'], 'pupilsightPersonID' => $pupilsightPersonID);
                                                    $sqlVersion = 'SELECT * FROM pupilsightPlannerEntryHomework WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID ORDER BY count DESC';
                                                    $resultVersion = $connection2->prepare($sqlVersion);
                                                    $resultVersion->execute($dataVersion);
                                                } catch (PDOException $e) {
                                                    echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                                }
                                                if ($resultVersion->rowCount() < 1) {
                                                    //Before deadline
                                                    if (date('Y-m-d H:i:s') < $rowHistory['homeworkDueDateTime']) {
                                                        echo "<span title='" . __('Pending') . "'>" . __('Pending') . '</span>';
                                                    }
                                                    //After
                                                    else {
                                                        if (@$rowHistory['dateStart'] > @$rowSub['date']) {
                                                            echo "<span title='" . __('Student joined school after assessment was given.') . "' style='color: #000; font-weight: normal; border: 2px none #ff0000; padding: 2px 4px'>" . __('NA') . '</span>';
                                                        } else {
                                                            if ($rowHistory['homeworkSubmissionRequired'] == 'Compulsory') {
                                                                echo "<div style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px; margin: 2px 0px'>" . __('Incomplete') . '</div>';
                                                            } else {
                                                                echo __('Not submitted online');
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    $rowVersion = $resultVersion->fetch();
                                                    if ($rowVersion['status'] == 'On Time' or $rowVersion['status'] == 'Exemption') {
                                                        echo $rowVersion['status'];
                                                    } else {
                                                        if ($rowHistory['homeworkSubmissionRequired'] == 'Compulsory') {
                                                            echo "<div style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px; margin: 2px 0px'>" . $rowVersion['status'] . '</div>';
                                                        } else {
                                                            echo $rowVersion['status'];
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        echo '</td>';
                                        echo '<td id="">';
                                        echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Planner/planner_view_full.php&search=$pupilsightPersonID&pupilsightPlannerEntryID=" . $rowHistory['pupilsightPlannerEntryID'] . '&viewBy=class&pupilsightCourseClassID=' . $rowHistory['pupilsightCourseClassID'] . "&width=1000&height=550'><img title='" . __('View Details') . "' src='./themes/" . $_SESSION[$guid]['pupilsightThemeName'] . "/img/plus.png'/></a> ";
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                }
                                echo '</table>';
                            }
                        }
                    } elseif ($subpage == 'Behaviour') {
                        if (isActionAccessible($guid, $connection2, '/modules/Behaviour/behaviour_view.php') == false) {
                            echo "<div class='alert alert-danger'>";
                            echo __('Your request failed because you do not have access to this action.');
                            echo '</div>';
                        } else {
                            include './modules/Behaviour/moduleFunctions.php';

                            //Print assessments
                            echo getBehaviourRecord($container, $pupilsightPersonID);
                        }
                    }

                    //academic to show ay,class,session,status,list of all subjects(core& electives)

                    elseif ($subpage == 'Academic') {

                        if (isActionAccessible($guid, $connection2, '/modules/Students/student_view_details.php') == false) {
                            echo "<div class='alert alert-danger'>";
                            echo __('Your request failed because you do not have access to this action.');
                            echo '</div>';
                        } else {
                            if (isset($_GET['return'])) {
                                returnProcess($guid, $_GET['return'], null, null);
                            }

                            echo '<p>';
                            echo __('Student Class,Section with status,list of all subjects are showing here .');
                            echo '</p>';
                            $filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : $_SESSION[$guid]['pupilsightSchoolYearID'];
                            $dataSelect = array('pupilsightPersonID' => $pupilsightPersonID);
                            $sqlSelect = "SELECT pupilsightSchoolYear.pupilsightSchoolYearID as value, CONCAT(pupilsightSchoolYear.name, ' (', pupilsightYearGroup.name, ')') AS name FROM pupilsightStudentEnrolment JOIN pupilsightSchoolYear ON (pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) WHERE pupilsightPersonID=:pupilsightPersonID ORDER BY pupilsightSchoolYear.sequenceNumber";



                            $categories = false;
                            $category = null;
                            if (isset($_GET['category'])) {
                                $category = $_GET['category'];
                            }

                            try {
                                $dataCategories = array();
                                $sqlCategories = "SELECT * FROM pupilsightStudentNoteCategory WHERE active='Y' ORDER BY name";
                                $resultCategories = $connection2->prepare($sqlCategories);
                                $resultCategories->execute($dataCategories);
                            } catch (PDOException $e) {
                            }
                            if ($resultCategories->rowCount() > 0) {
                                $categories = true;

                                /*  echo '<h3>';
                echo __('Filter');
                echo '</h3>';

                $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php','get');
                $form->setClass('noIntBorder fullWidth');

                $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/student_view_details.php');
                $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);
                $form->addHiddenValue('allStudents', $allStudents);
                $form->addHiddenValue('search', $search);
                $form->addHiddenValue('subpage', 'Academic');

         /*       $sql = "SELECT pupilsightStudentNoteCategoryID as value, name FROM pupilsightStudentNoteCategory WHERE active='Y' ORDER BY name";
                $rowFilter = $form->addRow();
                    $rowFilter->addLabel('category', __('Category'));
                    $rowFilter->addSelect('category')->fromQuery($pdo, $sql)->selected($category)->placeholder();

                $rowFilter = $form->addRow();
                    $rowFilter->addSearchSubmit($pupilsight->session, __('Clear Filters'), array('pupilsightPersonID', 'allStudents', 'search', 'subpage'));


                /*    $rowFilter = $form->addRow();
                    $rowFilter->addLabel('filter', __('School Years'));
                    $rowFilter->addSelect('filter')
                        ->fromArray(array('*' => __('All Years')))
                        ->fromQuery($pdo, $sqlSelect, $dataSelect)
                        ->selected($filter);
                echo $form->getOutput();*/
                            }

                            /*   try {
                if ($category == null) {
                    $data = array('pupilsightPersonID' => $pupilsightPersonID);
                    $sql = 'SELECT pupilsightStudentNote.*, pupilsightStudentNoteCategory.name AS category, surname, preferredName FROM pupilsightStudentNote LEFT JOIN pupilsightStudentNoteCategory ON (pupilsightStudentNote.pupilsightStudentNoteCategoryID=pupilsightStudentNoteCategory.pupilsightStudentNoteCategoryID) JOIN pupilsightPerson ON (pupilsightStudentNote.pupilsightPersonIDCreator=pupilsightPerson.pupilsightPersonID) WHERE pupilsightStudentNote.pupilsightPersonID=:pupilsightPersonID ORDER BY timestamp DESC';
                } else {
                    $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightStudentNoteCategoryID' => $category);
                    $sql = 'SELECT pupilsightStudentNote.*, pupilsightStudentNoteCategory.name AS category, surname, preferredName FROM pupilsightStudentNote LEFT JOIN pupilsightStudentNoteCategory ON (pupilsightStudentNote.pupilsightStudentNoteCategoryID=pupilsightStudentNoteCategory.pupilsightStudentNoteCategoryID) JOIN pupilsightPerson ON (pupilsightStudentNote.pupilsightPersonIDCreator=pupilsightPerson.pupilsightPersonID) WHERE pupilsightStudentNote.pupilsightPersonID=:pupilsightPersonID AND pupilsightStudentNote.pupilsightStudentNoteCategoryID=:pupilsightStudentNoteCategoryID ORDER BY timestamp DESC';
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
*/
                            $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
                            $pupilsightPersonID = $_GET['pupilsightPersonID'];
                            $studentGateway = $container->get(StudentGateway::class);

                            $criteria = $studentGateway->newQueryCriteria()
                                ->fromPOST();

                            $students = $studentGateway->queryStudentsBySchoolYearandID_with_assigned_subjects($criteria, $pupilsightSchoolYearID, $pupilsightPersonID);
                            $elective_sub = $studentGateway->get_assigned_elect_sub_tostudents($criteria, $pupilsightPersonID);
                            /*  echo "<pre>";
           print_r($elective_sub);
       */
                            echo "<div class='linkTop'>";
                            //   echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/student_view_details_notes_add.php&pupilsightPersonID=$pupilsightPersonID&search=$search&allStudents=$allStudents&search=$search&allStudents=$allStudents&subpage=Notes&category=$category'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a>";
                            echo '</div>';

                            if (count($students) < 1) {
                                echo "<div class='alert alert-danger'>";
                                echo __('There are no records to display.');
                                echo '</div>';
                            } else {
                                echo "<table  class ='table text-nowrap'>";
                                echo "<tr class='head'>";
                                echo '<th>';
                                echo __('Student Name') . '';

                                echo '</th>';
                                echo '<th>';
                                echo __('Student Id');
                                echo '</th>';
                                echo '<th>';
                                echo __('Program');

                                echo '</th>';
                                echo '<th>';
                                echo __('Class');
                                echo '</th>';
                                echo '<th>';
                                echo __('Status');
                                echo '</th>';


                                echo '<th>';
                                echo __('Core Subjects');
                                echo '</th>';
                                echo '<th>';
                                echo __('Elective Subjects');
                                echo '</th>';
                                echo '</tr>';

                                $count = 0;
                                $rowNum = 'odd';
                                foreach ($students as $row) {
                                    if ($count % 2 == 0) {
                                        $rowNum = 'even';
                                    } else {
                                        $rowNum = 'odd';
                                    }
                                    ++$count;

                                    //COLOR ROW BY STATUS!
                                    echo "<tr class=$rowNum>";
                                    echo '<td id="preferredName">';

                                    echo $row['preferredName'] . "," . $row['surname'];
                                    echo '</td>';
                                    echo '<td id="student_id">';

                                    echo $row['student_id'];
                                    echo '</td>';

                                    echo '<td id="program">';
                                    echo $row['program'];

                                    echo '</td>';

                                    echo '<td id="classname">';
                                    echo $row['classname'];

                                    echo '</td>';
                                    echo '<td id="active_status">';
                                    echo $row['active_status'];

                                    echo '</td>';



                                    echo '<td id="coresubject">';
                                    echo '<textarea rows="2" style="width: 170px;" readonly maxlength="20" cols="130">' . $row['coresubject'] . '</textarea>';


                                    echo '</td>';
                                    if (count($elective_sub) != 0) {
                                        foreach ($elective_sub as $el_row) {
                                            echo '<td id="subject">';
                                            echo '<textarea rows="2" style="width: 170px;" readonly maxlength="20" cols="130">' . $el_row['subject'] . '</textarea>';
                                            // echo "<a style=' margin-top: 15px;position: absolute;' href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/remove_assigned_elect_subject_from_student.php&pupilsightPersonID=$pupilsightPersonID'><i style='margin: 0 0 -4px 5px' title='".__('Delete')."' class='mdi mdi-trash-can-outline mdi-24px px-2'></i></a> ";
                                            //  echo "<a style='display:none' id='click_remove_el_sub' href='fullscreen.php?q=/modules/Students/remove_assigned_elect_subject_from_student.php&pupilsightPersonID=$pupilsightPersonID&width=600'  class='thickbox '></a>";
                                            //  echo'<div  ><a id="remove_el_sub" style="height: 34px;   margin-top: -46px; float: right;"class=" "><i style="margin: 0 0 -4px 5px" title="Remove Subject" class="mdi mdi-trash-can-outline mdi-24px px-2"></i></a>&nbsp;&nbsp;</div>';
                                            echo '</td>';
                                        }
                                    } else {
                                        echo '<td> Not assigned';

                                        echo '</td>';
                                    }
                                    echo '</tr>';
                                }
                                echo '</table>';
                            }
                        }
                        $row['privacy'] = "";
                    }
                    //academic to show ay,class,session,status,list of all subjects(core& electives)


                    //GET HOOK IF SPECIFIED
                    if ($hook != '' and $module != '' and $action != '') {
                        //GET HOOKS AND DISPLAY LINKS
                        //Check for hook
                        try {
                            $dataHook = array('pupilsightHookID' => $_GET['pupilsightHookID']);
                            $sqlHook = 'SELECT * FROM pupilsightHook WHERE pupilsightHookID=:pupilsightHookID';
                            $resultHook = $connection2->prepare($sqlHook);
                            $resultHook->execute($dataHook);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                        }
                        if ($resultHook->rowCount() != 1) {
                            echo "<div class='alert alert-danger'>";
                            echo __('There are no records to display.');
                            echo '</div>';
                        } else {
                            $rowHook = $resultHook->fetch();
                            $options = unserialize($rowHook['options']);

                            //Check for permission to hook
                            try {
                                $dataHook = array('pupilsightRoleIDCurrent' => $_SESSION[$guid]['pupilsightRoleIDCurrent'], 'sourceModuleName' => $options['sourceModuleName']);
                                $sqlHook = "SELECT pupilsightHook.name, pupilsightModule.name AS module, pupilsightAction.name AS action FROM pupilsightHook JOIN pupilsightModule ON (pupilsightHook.pupilsightModuleID=pupilsightModule.pupilsightModuleID) JOIN pupilsightAction ON (pupilsightAction.pupilsightModuleID=pupilsightModule.pupilsightModuleID) JOIN pupilsightPermission ON (pupilsightPermission.pupilsightActionID=pupilsightAction.pupilsightActionID) WHERE pupilsightModule.name='" . $options['sourceModuleName'] . "' AND pupilsightAction.name='" . $options['sourceModuleAction'] . "' AND pupilsightAction.pupilsightModuleID=(SELECT pupilsightModuleID FROM pupilsightModule WHERE pupilsightPermission.pupilsightRoleID=:pupilsightRoleIDCurrent AND name=:sourceModuleName) AND pupilsightHook.type='Student Profile' ORDER BY name";
                                $resultHook = $connection2->prepare($sqlHook);
                                $resultHook->execute($dataHook);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }
                            if ($resultHook->rowcount() != 1) {
                                echo "<div class='alert alert-danger'>";
                                echo __('Your request failed because you do not have access to this action.');
                                echo '</div>';
                            } else {
                                $include = $_SESSION[$guid]['absolutePath'] . '/modules/' . $options['sourceModuleName'] . '/' . $options['sourceModuleInclude'];
                                if (!file_exists($include)) {
                                    echo "<div class='alert alert-danger'>";
                                    echo __('The selected page cannot be displayed due to a hook error.');
                                    echo '</div>';
                                } else {
                                    include $include;
                                }
                            }
                        }
                    }


                    //Set sidebar
                    $_SESSION[$guid]['sidebarExtra'] = '';

                    //Show alerts
                    $alert = getAlertBar($guid, $connection2, $pupilsightPersonID, $row['privacy'], '', false, true);
                    $_SESSION[$guid]['sidebarExtra'] .= '<div class="w-48 sm:w-64 h-10 mb-2">';
                    if ($alert == '') {
                        $_SESSION[$guid]['sidebarExtra'] .= '<span class="text-gray text-xs">' . __('No Current Alerts') . '</span>';
                    } else {
                        $_SESSION[$guid]['sidebarExtra'] .= $alert;
                    }
                    $_SESSION[$guid]['sidebarExtra'] .= '</div>';

                    $_SESSION[$guid]['sidebarExtra'] .= getUserPhoto($guid, $studentImage, 240);

                    //PERSONAL DATA MENU ITEMS
                    $_SESSION[$guid]['sidebarExtra'] .= '<div class="column-no-break">';
                    $_SESSION[$guid]['sidebarExtra'] .= '<h4>' . __('Personal') . '</h4>';
                    $_SESSION[$guid]['sidebarExtra'] .= "<ul class='moduleMenu'>";
                    $style = '';
                    if ($subpage == 'Overview') {
                        $style = "style='font-weight: bold'";
                    }
                    $_SESSION[$guid]['sidebarExtra'] .= "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&search=$search&allStudents=$allStudents&subpage=Overview'>" . __('Overview') . '</a></li>';
                    $style = '';
                    if ($subpage == 'Personal') {
                        $style = "style='font-weight: bold'";
                    }
                    $_SESSION[$guid]['sidebarExtra'] .= "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&search=$search&allStudents=$allStudents&subpage=Personal'>" . __('Personal') . '</a></li>';
                    $style = '';
                    if ($subpage == 'Family') {
                        $style = "style='font-weight: bold'";
                    }
                    $_SESSION[$guid]['sidebarExtra'] .= "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&search=$search&allStudents=$allStudents&subpage=Family'>" . __('Family') . '</a></li>';
                    $style = '';
                    if ($subpage == 'Emergency Contacts') {
                        $style = "style='font-weight: bold'";
                    }
                    $_SESSION[$guid]['sidebarExtra'] .= "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&search=$search&allStudents=$allStudents&subpage=Emergency Contacts'>" . __('Emergency Contacts') . '</a></li>';
                    $style = '';
                    if ($subpage == 'Medical') {
                        $style = "style='font-weight: bold'";
                    }
                    $_SESSION[$guid]['sidebarExtra'] .= "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&search=$search&allStudents=$allStudents&subpage=Medical'>" . __('Medical') . '</a></li>';
                    if (isActionAccessible($guid, $connection2, '/modules/Students/student_view_details_notes_add.php')) {
                        if ($enableStudentNotes == 'Y') {
                            $style = '';
                            if ($subpage == 'Notes') {
                                $style = "style='font-weight: bold'";
                            }
                            $_SESSION[$guid]['sidebarExtra'] .= "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&search=$search&allStudents=$allStudents&subpage=Notes'>" . __('Notes') . '</a></li>';
                        }
                    }
                    $_SESSION[$guid]['sidebarExtra'] .= '</ul>';


                    //OTHER MENU ITEMS, DYANMICALLY ARRANGED TO MATCH CUSTOM TOP MENU
                    //Get all modules, with the categories
                    try {
                        $dataMenu = array();
                        $sqlMenu = "SELECT pupilsightModuleID, category, name FROM pupilsightModule WHERE active='Y' ORDER BY category, name";
                        $resultMenu = $connection2->prepare($sqlMenu);
                        $resultMenu->execute($dataMenu);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                    }
                    $mainMenu = array();
                    while ($rowMenu = $resultMenu->fetch()) {
                        $mainMenu[$rowMenu['name']] = $rowMenu['category'];
                    }
                    $studentMenuCateogry = array();
                    $studentMenuName = array();
                    $studentMenuLink = array();
                    $studentMenuCount = 0;

                    //Store items in an array
                    if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_view.php')) {
                        $style = '';
                        if ($subpage == 'Markbook') {
                            $style = "style='font-weight: bold'";
                        }
                        $studentMenuCategory[$studentMenuCount] = $mainMenu['Markbook'];
                        $studentMenuName[$studentMenuCount] = __('Markbook');
                        $studentMenuLink[$studentMenuCount] = "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&search=$search&allStudents=$allStudents&subpage=Markbook'>" . __('Markbook') . '</a></li>';
                        ++$studentMenuCount;
                    }
                    if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/internalAssessment_view.php')) {
                        $style = '';
                        if ($subpage == 'Internal Assessment') {
                            $style = "style='font-weight: bold'";
                        }
                        $studentMenuCategory[$studentMenuCount] = $mainMenu['Formal Assessment'];
                        $studentMenuName[$studentMenuCount] = __('Formal Assessment');
                        $studentMenuLink[$studentMenuCount] = "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&search=$search&allStudents=$allStudents&subpage=Internal%20Assessment'>" . __('Internal Assessment') . '</a></li>';
                        ++$studentMenuCount;
                    }
                    if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/externalAssessment_details.php') or isActionAccessible($guid, $connection2, '/modules/Formal Assessment/externalAssessment_view.php')) {
                        $style = '';
                        if ($subpage == 'External Assessment') {
                            $style = "style='font-weight: bold'";
                        }
                        $studentMenuCategory[$studentMenuCount] = $mainMenu['Formal Assessment'];
                        $studentMenuName[$studentMenuCount] = __('External Assessment');
                        $studentMenuLink[$studentMenuCount] = "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&search=$search&allStudents=$allStudents&subpage=External Assessment'>" . __('External Assessment') . '</a></li>';
                        ++$studentMenuCount;
                    }

                    if (isActionAccessible($guid, $connection2, '/modules/Activities/report_activityChoices_byStudent.php')) {
                        $style = '';
                        if ($subpage == 'Activities') {
                            $style = "style='font-weight: bold'";
                        }
                        $studentMenuCategory[$studentMenuCount] = $mainMenu['Activities'];
                        $studentMenuName[$studentMenuCount] = __('Activities');
                        $studentMenuLink[$studentMenuCount] = "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&search=$search&allStudents=$allStudents&subpage=Activities'>" . __('Activities') . '</a></li>';
                        ++$studentMenuCount;
                    }
                    if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_edit.php') or isActionAccessible($guid, $connection2, '/modules/Planner/planner_view_full.php')) {
                        $style = '';
                        if ($subpage == 'Homework') {
                            $style = "style='font-weight: bold'";
                        }
                        $studentMenuCategory[$studentMenuCount] = $mainMenu['Planner'];
                        $studentMenuName[$studentMenuCount] = __('Homework');
                        $studentMenuLink[$studentMenuCount] = "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&search=$search&allStudents=$allStudents&subpage=Homework'>" . __('Homework') . '</a></li>';
                        ++$studentMenuCount;
                    }
                    if (isActionAccessible($guid, $connection2, '/modules/Individual Needs/in_view.php')) {
                        $style = '';
                        if ($subpage == 'Individual Needs') {
                            $style = "style='font-weight: bold'";
                        }
                        $studentMenuCategory[$studentMenuCount] = $mainMenu['Individual Needs'];
                        $studentMenuName[$studentMenuCount] = __('Individual Needs');
                        $studentMenuLink[$studentMenuCount] = "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&search=$search&allStudents=$allStudents&subpage=Individual Needs'>" . __('Individual Needs') . '</a></li>';
                        ++$studentMenuCount;
                    }
                    if (isActionAccessible($guid, $connection2, '/modules/Library/report_studentBorrowingRecord.php')) {
                        $style = '';
                        if ($subpage == 'Library Borrowing') {
                            $style = "style='font-weight: bold'";
                        }
                        $studentMenuCategory[$studentMenuCount] = $mainMenu['Library'];
                        $studentMenuName[$studentMenuCount] = __('Library Borrowing');
                        $studentMenuLink[$studentMenuCount] = "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&search=$search&allStudents=$allStudents&subpage=Library Borrowing'>" . __('Library Borrowing') . '</a></li>';
                        ++$studentMenuCount;
                    }
                    if (isActionAccessible($guid, $connection2, '/modules/Timetable/tt_view.php')) {
                        $style = '';
                        if ($subpage == 'Timetable') {
                            $style = "style='font-weight: bold'";
                        }
                        $studentMenuCategory[$studentMenuCount] = $mainMenu['Timetable'];
                        $studentMenuName[$studentMenuCount] = __('Timetable');
                        $studentMenuLink[$studentMenuCount] = "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&search=$search&allStudents=$allStudents&subpage=Timetable'>" . __('Timetable') . '</a></li>';
                        ++$studentMenuCount;
                    }
                    if (isActionAccessible($guid, $connection2, '/modules/Attendance/report_studentHistory.php')) {
                        $style = '';
                        if ($subpage == 'Attendance') {
                            $style = "style='font-weight: bold'";
                        }
                        $studentMenuCategory[$studentMenuCount] = $mainMenu['Attendance'];
                        $studentMenuName[$studentMenuCount] = __('Attendance');
                        $studentMenuLink[$studentMenuCount] = "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&search=$search&allStudents=$allStudents&subpage=Attendance'>" . __('Attendance') . '</a></li>';
                        ++$studentMenuCount;
                    }
                    if (isActionAccessible($guid, $connection2, '/modules/Behaviour/behaviour_view.php')) {
                        $style = '';
                        if ($subpage == 'Behaviour') {
                            $style = "style='font-weight: bold'";
                        }
                        $studentMenuCategory[$studentMenuCount] = $mainMenu['Behaviour'];
                        $studentMenuName[$studentMenuCount] = __('Behaviour');
                        $studentMenuLink[$studentMenuCount] = "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&search=$search&allStudents=$allStudents&subpage=Behaviour'>" . __('Behaviour') . '</a></li>';
                        ++$studentMenuCount;
                    }


                    //Check for hooks, and slot them into array
                    try {
                        $dataHooks = array();
                        $sqlHooks = "SELECT * FROM pupilsightHook WHERE type='Student Profile'";
                        $resultHooks = $connection2->prepare($sqlHooks);
                        $resultHooks->execute($dataHooks);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                    }

                    if ($resultHooks->rowCount() > 0) {
                        $hooks = array();
                        $count = 0;
                        while ($rowHooks = $resultHooks->fetch()) {
                            $options = unserialize($rowHooks['options']);
                            //Check for permission to hook
                            try {
                                $dataHook = array('pupilsightRoleIDCurrent' => $_SESSION[$guid]['pupilsightRoleIDCurrent'], 'sourceModuleName' => $options['sourceModuleName']);
                                $sqlHook = "SELECT pupilsightHook.name, pupilsightModule.name AS module, pupilsightAction.name AS action FROM pupilsightHook JOIN pupilsightModule ON (pupilsightHook.pupilsightModuleID=pupilsightModule.pupilsightModuleID) JOIN pupilsightAction ON (pupilsightAction.pupilsightModuleID=pupilsightModule.pupilsightModuleID) JOIN pupilsightPermission ON (pupilsightPermission.pupilsightActionID=pupilsightAction.pupilsightActionID) WHERE pupilsightModule.name='" . $options['sourceModuleName'] . "' AND  pupilsightAction.name='" . $options['sourceModuleAction'] . "' AND pupilsightAction.pupilsightModuleID=(SELECT pupilsightModuleID FROM pupilsightModule WHERE pupilsightPermission.pupilsightRoleID=:pupilsightRoleIDCurrent AND name=:sourceModuleName) AND pupilsightHook.type='Student Profile' ORDER BY name";
                                $resultHook = $connection2->prepare($sqlHook);
                                $resultHook->execute($dataHook);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }
                            if ($resultHook->rowCount() == 1) {
                                $style = '';
                                if ($hook == $rowHooks['name'] and $_GET['module'] == $options['sourceModuleName']) {
                                    $style = "style='font-weight: bold'";
                                }
                                $studentMenuCategory[$studentMenuCount] = $mainMenu[$options['sourceModuleName']];
                                $studentMenuName[$studentMenuCount] = __($rowHooks['name']);
                                $studentMenuLink[$studentMenuCount] = "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . '&hook=' . $rowHooks['name'] . '&module=' . $options['sourceModuleName'] . '&action=' . $options['sourceModuleAction'] . '&pupilsightHookID=' . $rowHooks['pupilsightHookID'] . "'>" . __($rowHooks['name']) . '</a></li>';
                                ++$studentMenuCount;
                                ++$count;
                            }
                        }
                    }

                    //Menu ordering categories
                    $mainMenuCategoryOrder = getSettingByScope($connection2, 'System', 'mainMenuCategoryOrder');
                    $orders = explode(',', $mainMenuCategoryOrder);

                    //Sort array
                    @array_multisort($orders, $studentMenuCategory, $studentMenuName, $studentMenuLink);

                    //Spit out array whilt sorting by $mainMenuCategoryOrder

                    if (count($studentMenuCategory) > 0) {
                        foreach ($orders as $order) {
                            //Check for entries
                            $countEntries = 0;
                            for ($i = 0; $i < count($studentMenuCategory); ++$i) {
                                if ($studentMenuCategory[$i] == $order)
                                    $countEntries++;
                            }

                            if ($countEntries > 0) {
                                $_SESSION[$guid]['sidebarExtra'] .= '<h4>' . __($order) . '</h4>';
                                $_SESSION[$guid]['sidebarExtra'] .= "<ul class='moduleMenu'>";
                                for ($i = 0; $i < count($studentMenuCategory); ++$i) {
                                    if ($studentMenuCategory[$i] == $order)
                                        $_SESSION[$guid]['sidebarExtra'] .= $studentMenuLink[$i];
                                }

                                $_SESSION[$guid]['sidebarExtra'] .= '</ul>';
                            }
                        }
                    }

                    $_SESSION[$guid]['sidebarExtra'] .= '</div>';
                    //clear for profile page

                    //only required adding again rakesh
                    //Show alerts
                    $_SESSION[$guid]['sidebarExtra'] = "";
                    $alert = getAlertBar($guid, $connection2, $pupilsightPersonID, $row['privacy'], '', false, true);
                    $strAlert = "";
                    if ($alert) {
                        $strAlert .= '<div class="w-48 sm:w-64 h-10 mb-2">';
                        $strAlert .= $alert;
                        $strAlert .= '</div>';
                    }
                    $_SESSION[$guid]['sidebarExtra'] .= $strAlert;
                    $_SESSION[$guid]['sidebarExtra'] .= getUserPhoto($guid, $studentImage, 75);
                }
            }
        }
    }
}
