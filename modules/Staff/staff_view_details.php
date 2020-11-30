<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Domain\Staff\StaffAbsenceDateGateway;
use Pupilsight\Domain\System\CustomField;

//Module includes for User Admin (for custom fields)
include './modules/User Admin/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_view_details.php') == false) {
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
        $pupilsightPersonID = $_GET['pupilsightPersonID'] ?? '';
        if ($pupilsightPersonID == '') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            $search = $_GET['search'] ?? '';
            $allStaff = $_GET['allStaff'] ?? '';

            $customField  = $container->get(CustomField::class);
            $customField->getPostData("pupilsightPerson", "pupilsightPersonID", $pupilsightPersonID, "staff");

            if ($highestAction == 'View Staff Profile_brief') {
                //Proceed!
                try {
                    $data = array('pupilsightPersonID' => $pupilsightPersonID);
                    $sql = "SELECT title, surname, preferredName, type, pupilsightStaff.jobTitle, email, website, countryOfOrigin, qualifications, biography, image_240 FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='" . date('Y-m-d') . "') AND (dateEnd IS NULL  OR dateEnd>='" . date('Y-m-d') . "') AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                }

                if ($result->rowCount() != 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('The selected record does not exist, or you do not have access to it.');
                    echo '</div>';
                    echo '</div>';
                } else {
                    $row = $result->fetch();

                    $page->breadcrumbs
                        ->add(__('View Staff Profiles'), 'staff_view.php')
                        ->add(Format::name('', $row['preferredName'], $row['surname'], 'Student'));

                    if ($search != '') {
                        echo "<div class='linkTop'>";
                        echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Staff/staff_view.php&search=' . $search . "'>" . __('Back to Search Results') . '</a>';
                        echo '</div>';
                    }

                    echo "<table id='basic_information' class='table'>";
                    echo '<tr>';

                    echo "<td id='type' style='width: 33%; vertical-align: top'>";
                    echo "<span class='form-label'>" . __('Staff Type') . '</span>';
                    echo '<i>' . __($row['type']) . '</i>';
                    echo '</td>';

                    echo "<td id='jobTitle' style='width: 33%; vertical-align: top'>";
                    echo "<span class='form-label'>" . __('Job Title') . '</span>';
                    echo '<i>' . $row['jobTitle'] . '</i>';
                    echo '</td>';

                    echo "<td id='preferredName' style='width: 33%; vertical-align: top'>";
                    echo "<span class='form-label'>" . __('Name') . '</span>';
                    echo '<i>' . Format::name($row['title'], $row['preferredName'], $row['surname'], 'Parent') . '</i>';
                    echo '</td>';

                    echo '</tr>';
                    echo '<tr>';
                    echo "<td id='email' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                    echo "<span class='form-label'>" . __('Email') . '</span>';
                    if ($row['email'] != '') {
                        echo "<i><a href='mailto:" . $row['email'] . "'>" . $row['email'] . '</a></i>';
                    }
                    echo '</td>';
                    echo "<td id='website' style='width: 67%; padding-top: 15px; vertical-align: top' colspan=2>";
                    echo "<span class='form-label'>" . __('Website') . '</span>';
                    if ($row['website'] != '') {
                        echo "<i><a href='" . $row['website'] . "'>" . $row['website'] . '</a></i>';
                    }
                    echo '</td>';
                    echo '</tr>';
                    echo '</table>';

                    echo '<h4>';
                    echo __('Biography');
                    echo '</h4>';
                    echo "<table id='background_information' class='table'>";
                    echo '<tr>';
                    echo "<td id='countryOfOrigin' style='width: 33%; vertical-align: top'>";
                    echo "<span class='form-label'>" . __('Country Of Origin') . '</span>';
                    echo '<i>' . $row['countryOfOrigin'] . '</i>';
                    echo '</td>';
                    echo "<td id='qualifications' style='width: 67%; vertical-align: top' colspan=2>";
                    echo "<span class='form-label'>" . __('Qualifications') . '</span>';
                    echo '<i>' . $row['qualifications'] . '</i>';
                    echo '</td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo "<td id='biography' style='width: 100%; vertical-align: top' colspan=3>";
                    echo "<span class='form-label'>" . __('Biography') . '</span>';
                    echo '<i>' . $row['biography'] . '</i>';
                    echo '</td>';
                    echo '</tr>';
                    echo '</table>';

                    //Set sidebar
                    $_SESSION[$guid]['sidebarExtra'] = getUserPhoto($guid, $row['image_240'], 240);
                }
            } else {
                try {
                    $data = array('pupilsightPersonID' => $pupilsightPersonID);
                    if ($allStaff != 'on') {
                        $sql = "SELECT pupilsightPerson.*, pupilsightStaff.initials, pupilsightStaff.type, pupilsightStaff.jobTitle, countryOfOrigin, qualifications, biography FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full' AND (dateStart IS NULL OR dateStart<='" . date('Y-m-d') . "') AND (dateEnd IS NULL  OR dateEnd>='" . date('Y-m-d') . "') AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID";
                    } else {
                        $sql = 'SELECT pupilsightPerson.*, pupilsightStaff.initials, pupilsightStaff.type, pupilsightStaff.jobTitle, countryOfOrigin, qualifications, biography FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID';
                    }
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

                    $page->breadcrumbs
                        ->add(__('View Staff Profiles'), 'staff_view.php', ['search' => $search, 'allStaff' => $allStaff])
                        ->add(Format::name('', $row['preferredName'], $row['surname'], 'Student'));

                    $subpage = null;
                    if (isset($_GET['subpage'])) {
                        $subpage = $_GET['subpage'];
                    }
                    if ($subpage == '') {
                        $subpage = 'Overview';
                    }

                    if ($search != '') {
                        echo "<div class='linkTop'>";
                        echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Staff/staff_view.php&search=' . $search . "'>" . __('Back to Search Results') . '</a>';
                        echo '</div>';
                    }

                    echo '<h2>';
                    if ($subpage != '') {
                        echo $subpage;
                    }
                    echo '</h2>';

                    if ($subpage == 'Overview') {
                        if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage.php') == true) {
                            echo "<div class='linkTop'>";
                            //echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/User Admin/user_manage_edit.php&pupilsightPersonID=$pupilsightPersonID'><i style='margin: 0 0 -4px 5px' title='".__('Edit')."' class='mdi mdi-pencil-box-outline mdi-24px px-2'></i></a> ";
                            echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Students/student_edit.php&pupilsightPersonID=$pupilsightPersonID'><i style='margin: 0 0 -4px 5px' title='" . __('Edit') . "' class='mdi mdi-pencil-box-outline mdi-24px px-2'></i></a> ";
                            echo '</div>';
                        }

                        // Display a message if the staff member is absent today.
                        $staffAbsenceGateway = $container->get(StaffAbsenceGateway::class);
                        $staffAbsenceDateGateway = $container->get(StaffAbsenceDateGateway::class);

                        $criteria = $staffAbsenceGateway->newQueryCriteria()->filterBy('date', 'Today')->filterBy('status', 'Approved');
                        $absences = $staffAbsenceGateway->queryAbsencesByPerson($criteria, $pupilsightPersonID)->toArray();

                        if (count($absences) > 0) {
                            $absenceMessage = __('{name} is absent today.', [
                                'name' => Format::name($row['title'], $row['preferredName'], $row['surname'], 'Staff', false, true),
                            ]);
                            $absenceMessage .= '<ul>';
                            foreach ($absences as $absence) {
                                $details = $staffAbsenceDateGateway->getByAbsenceAndDate($absence['pupilsightStaffAbsenceID'], date('Y-m-d'));
                                $time = $details['allDay'] == 'N' ? Format::timeRange($details['timeStart'], $details['timeEnd']) : __('All Day');

                                $absenceMessage .= '<li>' . Format::dateRangeReadable($absence['dateStart'], $absence['dateEnd']) . '  ' . $time . '</li>';
                                if ($details['coverage'] == 'Accepted') {
                                    $absenceMessage .= '<li>' . __('Coverage') . ': ' . Format::name($details['titleCoverage'], $details['preferredNameCoverage'], $details['surnameCoverage'], 'Staff', false, true) . '</li>';
                                }
                            }
                            $absenceMessage .= '</ul>';

                            echo Format::alert($absenceMessage, 'warning');
                        }

                        //General Information
                        echo '<h4>';
                        echo __('General Information');
                        echo '</h4>';
                        echo "<table id='basic_information' class='table'>";
                        echo '<tr>';

                        echo "<td id='type' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Staff Type') . '</span>';
                        echo '<i>' . __($row['type']) . '</i>';
                        echo '</td>';
                        echo "<td id='jobTitle' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Job Title') . '</span>';
                        echo '<i>' . $row['jobTitle'] . '</i>';
                        echo '</td>';

                        echo "<td id='preferredName' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Name') . '</span>';
                        echo '<i>' . Format::name($row['title'], $row['preferredName'], $row['surname'], 'Parent') . '</i>';
                        echo '</td>';

                        echo '</tr>';
                        echo '<tr>';
                        echo "<td id='username' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Username') . '</span>';
                        echo '<i>' . $row['username'] . '</i>';
                        echo '</td>';
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
                        echo '</tr>';
                        echo '</table>';

                        echo '<h4>';
                        echo __('Biography');
                        echo '</h4>';
                        echo "<table id='background_information' class='table'>";
                        echo '<tr>';
                        echo "<td id='countryOfOrigin' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Country Of Origin') . '</span>';
                        echo '<i>' . $row['countryOfOrigin'] . '</i>';
                        echo '</td>';
                        echo "<td id='qualifications' style='width: 67%; vertical-align: top' colspan=2>";
                        echo "<span class='form-label'>" . __('Qualifications') . '</span>';
                        echo '<i>' . $row['qualifications'] . '</i>';
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo "<td id='biography' style='width: 100%; vertical-align: top' colspan=3>";
                        echo "<span class='form-label'>" . __('Biography') . '</span>';
                        echo '<i>' . $row['biography'] . '</i>';
                        echo '</td>';
                        echo '</tr>';
                        echo '</table>';

                        //Show timetable
                        echo "<a name='timetable'></a>";
                        echo '<h4>';
                        echo __('Timetable');
                        echo '</h4>';
                        if (isActionAccessible($guid, $connection2, '/modules/Timetable/tt_view.php') == true) {
                            if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit.php') == true) {
                                echo "<div class='linkTop'>";
                                echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit.php&pupilsightPersonID=$pupilsightPersonID&pupilsightSchoolYearID=" . $_SESSION[$guid]['pupilsightSchoolYearID'] . "&type=Staff&allUsers='><i style='margin: 0 0 -4px 5px' title='" . __('Edit') . "' class='mdi mdi-pencil-box-outline mdi-24px px-2'></i></a> ";
                                echo '</div>';
                            }

                            include './modules/Timetable/moduleFunctions.php';
                            $ttDate = '';
                            if (isset($_POST['ttDate'])) {
                                $ttDate = dateConvertToTimestamp(dateConvert($guid, $_POST['ttDate']));
                            }
                            $pupilsightTTID = null;
                            if (isset($_GET['pupilsightTTID'])) {
                                $pupilsightTTID = $_GET['pupilsightTTID'];
                            }
                            $tt = renderTT($guid, $connection2, $pupilsightPersonID, $pupilsightTTID, false, $ttDate, '/modules/Staff/staff_view_details.php', "&pupilsightPersonID=$pupilsightPersonID&search=$search#timetable");
                            if ($tt != false) {
                                echo $tt;
                            } else {
                                echo "<div class='alert alert-danger'>";
                                echo __('The selected record does not exist, or you do not have access to it.');
                                echo '</div>';
                            }
                        }
                    } elseif ($subpage == 'Personal') {
                        if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage.php') == true) {
                            echo "<div class='linkTop'>";
                            echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/User Admin/user_manage_edit.php&pupilsightPersonID=$pupilsightPersonID'><i style='margin: 0 0 -4px 5px' title='" . __('Edit') . "' class='mdi mdi-pencil-box-outline mdi-24px px-2'></i></a> ";
                            echo '</div>';
                        }

                        echo "<table id='basic_information' class='table'>";
                        echo '<tr>';

                        echo "<td id='type' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Staff Type') . '</span>';
                        echo '<i>' . __($row['type']) . '</i>';
                        echo '</td>';
                        echo "<td id='jobTitle' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Job Title') . '</span>';
                        echo '<i>' . $row['jobTitle'] . '</i>';
                        echo '</td>';

                        echo "<td id='preferredName' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Name') . '</span>';
                        echo '<i>' . Format::name($row['title'], $row['preferredName'], $row['surname'], 'Parent') . '</i>';
                        echo '</td>';


                        echo '</tr>';
                        echo '<tr>';
                        echo "<td id='initials' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Initials') . '</span>';
                        echo $row['initials'];
                        echo '</td>';
                        echo "<td id='gender' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Gender') . '</span>';
                        echo $row['gender'];
                        echo '</td>';
                        echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";

                        echo '</td>';
                        echo '</tr>';
                        echo '</table>';

                        echo '<h4>';
                        echo 'Contacts';
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
                                        echo '<i>' . $row['phone' . $i . 'Type'] . ':</i> ';
                                    }
                                    if ($row['phone' . $i . 'CountryCode'] != '') {
                                        echo '+' . $row['phone' . $i . 'CountryCode'] . ' ';
                                    }
                                    echo formatPhone($row['phone' . $i]) . '';
                                    echo '</td>';
                                }
                            }
                            for ($i = ($numberCount + 1); $i < 5; ++$i) {
                                echo "<td width: 33%; style='vertical-align: top'></td>";
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
                        echo "<td id='website' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Website') . '</span>';
                        if ($row['website'] != '') {
                            echo "<i><a href='" . $row['website'] . "'>" . $row['website'] . '</a></i>';
                        }
                        echo '</td>';
                        echo "<td style='width: 33%; padding-top: 15px; vertical-align: top'>";

                        echo '</td>';
                        echo '</tr>';
                        if ($row['address1'] != '') {
                            echo '<tr>';
                            echo "<td id='address1' style='width: 33%; padding-top: 15px; vertical-align: top' colspan=3>";
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
                        echo __('Miscellaneous');
                        echo '</h4>';

                        echo "<table id='miscellaneous' class='table'>";
                        echo '<tr>';
                        echo "<td id='transport' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Transport') . '</span>';
                        echo $row['transport'];
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
                        echo '</table>';

                        //Custom Fields
                        $fields = unserialize($row['fields']);
                        $resultFields = getCustomFields($connection2, $guid, false, true);
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
                                echo "<td id='name' style='width: 33%; padding-top: 15px; vertical-align: top'>";
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
                    } elseif ($subpage == 'Facilities') {
                        try {
                            $data = array('pupilsightPersonID1' => $pupilsightPersonID, 'pupilsightPersonID2' => $pupilsightPersonID, 'pupilsightPersonID3' => $pupilsightPersonID, 'pupilsightPersonID4' => $pupilsightPersonID, 'pupilsightPersonID5' => $pupilsightPersonID, 'pupilsightPersonID6' => $pupilsightPersonID, 'pupilsightSchoolYearID1' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightSchoolYearID2' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                            $sql = '(SELECT pupilsightSpace.*, pupilsightSpacePersonID, usageType, NULL AS \'exception\', pupilsightSpace.phoneInternal FROM pupilsightSpacePerson JOIN pupilsightSpace ON (pupilsightSpacePerson.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) WHERE pupilsightPersonID=:pupilsightPersonID1)
                            UNION
                            (SELECT DISTINCT pupilsightSpace.*, NULL AS pupilsightSpacePersonID, \'Roll Group\' AS usageType, NULL AS \'exception\', pupilsightSpace.phoneInternal FROM pupilsightRollGroup JOIN pupilsightSpace ON (pupilsightRollGroup.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) WHERE (pupilsightPersonIDTutor=:pupilsightPersonID2 OR pupilsightPersonIDTutor2=:pupilsightPersonID3 OR pupilsightPersonIDTutor3=:pupilsightPersonID4) AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID1)
                            UNION
                            (SELECT DISTINCT pupilsightSpace.*, NULL AS pupilsightSpacePersonID, \'Timetable\' AS usageType, pupilsightTTDayRowClassException.pupilsightPersonID AS \'exception\', pupilsightSpace.phoneInternal FROM pupilsightSpace JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) LEFT JOIN pupilsightTTDayRowClassException ON (pupilsightTTDayRowClassException.pupilsightTTDayRowClassID=pupilsightTTDayRowClass.pupilsightTTDayRowClassID AND (pupilsightTTDayRowClassException.pupilsightPersonID=:pupilsightPersonID6 OR pupilsightTTDayRowClassException.pupilsightPersonID IS NULL)) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID5)
                            ORDER BY name';
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
                            echo "<table cellspacing='0' style='width: 100%'>";
                            echo "<tr class='head'>";
                            echo '<th>';
                            echo __('Name');
                            echo '</th>';
                            echo '<th>';
                            echo __('Extension');
                            echo '</th>';
                            echo '<th>';
                            echo __('Usage') . '';
                            echo '</th>';
                            echo '</tr>';

                            $count = 0;
                            $rowNum = 'odd';
                            while ($rowFacility = $result->fetch()) {
                                if ($rowFacility['exception'] == null) {
                                    if ($count % 2 == 0) {
                                        $rowNum = 'even';
                                    } else {
                                        $rowNum = 'odd';
                                    }
                                    ++$count;

                                    echo "<tr class=$rowNum>";
                                    echo '<td id="name">';
                                    echo $rowFacility['name'];
                                    echo '</td>';
                                    echo '<td id="phoneInternal">';
                                    echo $rowFacility['phoneInternal'];
                                    echo '</td>';
                                    echo '<td id="usageType">';
                                    echo __($rowFacility['usageType']);
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            }
                            echo '</table>';
                        }
                    } elseif ($subpage == 'Emergency Contacts') {
                        if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage.php') == true) {
                            echo "<div class='linkTop'>";
                            echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/User Admin/user_manage_edit.php&pupilsightPersonID=$pupilsightPersonID'><i style='margin: 0 0 -4px 5px' title='" . __('Edit') . "' class='mdi mdi-pencil-box-outline mdi-24px px-2'></i></a> ";
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
                            echo __('There is no family information available for the current staff member.');
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
                                echo "<td id='role' style='width: 33%; vertical-align: top'>";
                                echo "<span class='form-label'>" . __('Relationship') . '</span>';
                                if ($rowMember['role'] == 'Parent') {
                                    if ($rowMember['gender'] == 'M') {
                                        echo __('Father');
                                    } elseif ($rowMember['gender'] == 'F') {
                                        echo __('Mother');
                                    } else {
                                        echo $rowMember['role'];
                                    }
                                } else {
                                    echo $rowMember['role'];
                                }
                                echo '</td>';
                                echo "<td id='phone' style='width: 34%; vertical-align: top'>";
                                echo "<span class='form-label'>" . __('Contact By Phone') . '</span>';
                                for ($i = 1; $i < 5; ++$i) {
                                    if ($rowMember['phone' . $i] != '') {
                                        if ($rowMember['phone' . $i . 'Type'] != '') {
                                            echo '<i>' . $rowMember['phone' . $i . 'Type'] . ':</i> ';
                                        }
                                        if ($rowMember['phone' . $i . 'CountryCode'] != '') {
                                            echo '+' . $rowMember['phone' . $i . 'CountryCode'] . ' ';
                                        }
                                        echo formatPhone($rowMember['phone' . $i]) . '';
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
                        echo "<td id='emergency1Name' style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Contact 1') . '</span>';
                        echo '<i>' . $row['emergency1Name'] . '</i>';
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
                        if ($row['emergency1Number2'] != '') {
                            echo $row['emergency1Number2'];
                        }
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo "<td id='emergency2Name' style='width: 33%; padding-top: 15px; vertical-align: top'>";
                        echo "<span class='form-label'>" . __('Contact 2') . '</span>';
                        echo '<i>' . $row['emergency2Name'] . '</i>';
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
                        if ($row['emergency2Number2'] != '') {
                            echo $row['emergency2Number2'];
                        }
                        echo '</td>';
                        echo '</tr>';
                        echo '</table>';
                    } elseif ($subpage == 'Timetable') {
                        if (isActionAccessible($guid, $connection2, '/modules/Timetable/tt_view.php') == false) {
                            echo "<div class='alert alert-danger'>";
                            echo __('The selected record does not exist, or you do not have access to it.');
                            echo '</div>';
                        } else {
                            if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit.php') == true) {
                                echo "<div class='linkTop'>";
                                echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit.php&pupilsightPersonID=$pupilsightPersonID&pupilsightSchoolYearID=" . $_SESSION[$guid]['pupilsightSchoolYearID'] . "&type=Staff&allUsers='><i style='margin: 0 0 -4px 5px' title='" . __('Edit') . "' class='mdi mdi-pencil-box-outline mdi-24px px-2'></i></a> ";
                                echo '</div>';
                            }

                            include './modules/Timetable/moduleFunctions.php';
                            $ttDate = '';
                            if (isset($_POST['ttDate'])) {
                                $ttDate = dateConvertToTimestamp(dateConvert($guid, $_POST['ttDate']));
                            }
                            $pupilsightTTID = null;
                            if (isset($_GET['pupilsightTTID'])) {
                                $pupilsightTTID = $_GET['pupilsightTTID'];
                            }
                            $tt = renderTT($guid, $connection2, $pupilsightPersonID, $pupilsightTTID, false, $ttDate, '/modules/Staff/staff_view_details.php', "&pupilsightPersonID=$pupilsightPersonID&subpage=Timetable&search=$search");
                            if ($tt != false) {
                                echo $tt;
                            } else {
                                echo "<div class='alert alert-danger'>";
                                echo __('The selected record does not exist, or you do not have access to it.');
                                echo '</div>';
                            }
                        }
                    }

                    //Set sidebar
                    $_SESSION[$guid]['sidebarExtra'] = '';

                    //Show pic
                    $_SESSION[$guid]['sidebarExtra'] .= getUserPhoto($guid, $row['image_240'], 240);

                    //PERSONAL DATA MENU ITEMS
                    $_SESSION[$guid]['sidebarExtra'] .= '<div class="column-no-break">';
                    $_SESSION[$guid]['sidebarExtra'] .= '<h4>Personal</h4>';
                    $_SESSION[$guid]['sidebarExtra'] .= "<ul class='moduleMenu' style='display:inline !important;'>";
                    $style = '';
                    if ($subpage == 'Overview') {
                        $style = "style='font-weight: bold'";
                    }
                    $_SESSION[$guid]['sidebarExtra'] .= "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&allStaff=$allStaff&subpage=Overview'>" . __('Overview') . '</a></li>';
                    $style = '';
                    if ($subpage == 'Personal') {
                        $style = "style='font-weight: bold'";
                    }
                    $_SESSION[$guid]['sidebarExtra'] .= "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&allStaff=$allStaff&subpage=Personal'>" . __('Personal') . '</a></li>';
                    $style = '';
                    if ($subpage == 'Facilities') {
                        $style = "style='font-weight: bold'";
                    }
                    $_SESSION[$guid]['sidebarExtra'] .= "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&allStaff=$allStaff&subpage=Facilities'>" . __('Facilities') . '</a></li>';
                    $style = '';
                    if ($subpage == 'Emergency Contacts') {
                        $style = "style='font-weight: bold'";
                    }
                    $_SESSION[$guid]['sidebarExtra'] .= "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&allStaff=$allStaff&subpage=Emergency Contacts'>" . __('Emergency Contacts') . '</a></li>';
                    if (isActionAccessible($guid, $connection2, '/modules/Timetable/tt_view.php')) {
                        $style = '';
                        if ($subpage == 'Timetable') {
                            $style = "style='font-weight: bold'";
                        }
                        $_SESSION[$guid]['sidebarExtra'] .= "<li style='display:inline !important;'><a $style href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . "&pupilsightPersonID=$pupilsightPersonID&search=" . $search . "&allStaff=$allStaff&subpage=Timetable'>" . __('Timetable') . '</a></li>';
                    }
                    $_SESSION[$guid]['sidebarExtra'] .= '</ul>';
                    $_SESSION[$guid]['sidebarExtra'] .= '</div>';
                }
            }
        }
    }
}
