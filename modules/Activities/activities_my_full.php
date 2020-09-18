<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_my_full.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Proceed!
        //Get class variable
        $pupilsightActivityID = $_GET['pupilsightActivityID'];
        if ($pupilsightActivityID == '') {
            echo "<div class='alert alert-warning'>";
            echo __('Your request failed because your inputs were invalid.');
            echo '</div>';
        }
        //Check existence of and access to this class.
        else {
            $today = date('Y-m-d');

            try {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightActivityID' => $pupilsightActivityID);
                $sql = "SELECT * FROM pupilsightActivity WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' AND pupilsightActivityID=:pupilsightActivityID";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='alert alert-warning'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                $row = $result->fetch();
                //Should we show date as term or date?
                $dateType = getSettingByScope($connection2, 'Activities', 'dateType');

                echo '<h1>';
                echo $row['name'].'<br/>';
                $options = getSettingByScope($connection2, 'Activities', 'activityTypes');
                if ($options != '') {
                    echo "<div style='padding-top: 5px; font-size: 65%; font-style: italic'>";
                    echo trim($row['type']);
                    echo '</div>';
                }
                echo '</h1>';

                echo "<table class='blank' cellspacing='0' style='width: 550px; float: left;'>";
                echo '<tr>';
                if ($dateType != 'Date') {
                    echo "<td style='width: 33%; vertical-align: top'>";
                    echo "<span class='form-label'>".__('Terms').'</span><br/>';
                    $terms = getTerms($connection2, $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $termList = '';
                    for ($i = 0; $i < count($terms); $i = $i + 2) {
                        if (is_numeric(strpos($row['pupilsightSchoolYearTermIDList'], $terms[$i]))) {
                            $termList .= $terms[($i + 1)].', ';
                        }
                    }
                    if ($termList == '') {
                        echo '<i>'.__('NA').'</i>';
                    } else {
                        echo substr($termList, 0, -2);
                    }
                    echo '</td>';
                } else {
                    echo "<td style='width: 33%; vertical-align: top'>";
                    echo "<span class='form-label'>".__('Start Date').'</span><br/>';
                    echo dateConvertBack($guid, $row['programStart']);
                    echo '</td>';
                    echo "<td style='width: 33%; vertical-align: top'>";
                    echo "<span class='form-label'>".__('End Date').'</span><br/>';
                    echo dateConvertBack($guid, $row['programEnd']);
                    echo '</td>';
                }
                echo "<td style='width: 33%; vertical-align: top'>";
                echo "<span class='form-label'>".__('Year Groups').'</span><br/>';
                echo getYearGroupsFromIDList($guid, $connection2, $row['pupilsightYearGroupIDList']);
                echo '</td>';
                echo '</tr>';
                echo '<tr>';
                echo "<td style='padding-top: 15px; width: 33%; vertical-align: top'>";
                if ($row['paymentFirmness'] == 'Finalised') {
                    echo "<span class='form-label'>".sprintf(__('Cost (%1$s)'), $row['paymentType']).'</span><br/>';
                }
                else {
                    echo "<span class='form-label'>".sprintf(__('%1$s Cost (%2$s)'), $row['paymentFirmness'], $row['paymentType']).'</span><br/>';
                }
                if ($row['payment'] == 0) {
                    echo '<i>'.__('None').'</i>';
                } else {
                    if (substr($_SESSION[$guid]['currency'], 4) != '') {
                        echo substr($_SESSION[$guid]['currency'], 4);
                    }
                    echo $row['payment'];
                }
                echo '</td>';
                echo "<td style='padding-top: 15px; width: 33%; vertical-align: top'>";
                echo "<span class='form-label'>".__('Maximum Participants').'</span><br/>';
                echo $row['maxParticipants'];
                echo '</td>';
                echo "<td style='padding-top: 15px; width: 33%; vertical-align: top'>";
                echo "<span class='form-label'>".__('Staff').'</span><br/>';
                try {
                    $dataStaff = array('pupilsightActivityID' => $row['pupilsightActivityID']);
                    $sqlStaff = "SELECT title, preferredName, surname, role FROM pupilsightActivityStaff JOIN pupilsightPerson ON (pupilsightActivityStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightActivityID=:pupilsightActivityID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') ORDER BY surname, preferredName";
                    $resultStaff = $connection2->prepare($sqlStaff);
                    $resultStaff->execute($dataStaff);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($resultStaff->rowCount() < 1) {
                    echo '<i>'.__('None').'</i>';
                } else {
                    echo "<ul style='margin-left: 15px'>";
                    while ($rowStaff = $resultStaff->fetch()) {
                        echo '<li>'.formatName($rowStaff['title'], $rowStaff['preferredName'], $rowStaff['surname'], 'Staff').'</li>';
                    }
                    echo '</ul>';
                }
                echo '</td>';
                echo '</tr>';
                echo '<tr>';
                echo "<td style='padding-top: 15px; width: 33%; vertical-align: top' colspan=3>";
                echo "<span class='form-label'>".__('Provider').'</span><br/>';
                echo '<i>';
                if ($row['provider'] == 'School') {
                    echo $_SESSION[$guid]['organisationNameShort'];
                } else {
                    echo __('External');
                };
                echo '</i>';
                echo '</td>';
                echo '</tr>';
                if ($row['description'] != '') {
                    echo '<tr>';
                    echo "<td style='text-align: justify; padding-top: 15px; width: 33%; vertical-align: top' colspan=3>";
                    echo '<h2>'.__('Description').'</h2>';
                    echo $row['description'];
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';

                //Participants & Attendance
                echo "<div style='width:400px; float: right; font-size: 115%; padding-top: 6px'>";
                echo "<h3 style='padding-top: 0px; margin-top: 5px'>".__('Time Slots').'</h3>';

                try {
                    $dataSlots = array('pupilsightActivityID' => $row['pupilsightActivityID']);
                    $sqlSlots = 'SELECT pupilsightActivitySlot.*, pupilsightDaysOfWeek.name AS day, pupilsightSpace.name AS space FROM pupilsightActivitySlot JOIN pupilsightDaysOfWeek ON (pupilsightActivitySlot.pupilsightDaysOfWeekID=pupilsightDaysOfWeek.pupilsightDaysOfWeekID) LEFT JOIN pupilsightSpace ON (pupilsightActivitySlot.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) WHERE pupilsightActivityID=:pupilsightActivityID ORDER BY sequenceNumber';
                    $resultSlots = $connection2->prepare($sqlSlots);
                    $resultSlots->execute($dataSlots);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                $count = 0;
                while ($rowSlots = $resultSlots->fetch()) {
                    echo '<h4>'.__($rowSlots['day']).'</h4>';
                    echo '<p>';
                    echo '<i>'.__('Time').'</i>: '.substr($rowSlots['timeStart'], 0, 5).' - '.substr($rowSlots['timeEnd'], 0, 5).'<br/>';
                    if ($rowSlots['pupilsightSpaceID'] != '') {
                        echo '<i>'.__('Location').'</i>: '.$rowSlots['space'];
                    } else {
                        echo '<i>'.__('Location').'</i>: '.$rowSlots['locationExternal'];
                    }
                    echo '</p>';

                    ++$count;
                }
                if ($count == 0) {
                    echo '<i>'.__('None').'</i>';
                }

                $role = getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2);
                if ($role == 'Staff') {
                    echo '<h3>'.__('Participants').'</h3>';

                    try {
                        $dataStudents = array('pupilsightActivityID' => $row['pupilsightActivityID']);
                        $sqlStudents = "SELECT title, preferredName, surname FROM pupilsightActivityStudent JOIN pupilsightPerson ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightActivityID=:pupilsightActivityID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightActivityStudent.status='Accepted' ORDER BY surname, preferredName";
                        $resultStudents = $connection2->prepare($sqlStudents);
                        $resultStudents->execute($dataStudents);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    if ($resultStudents->rowCount() < 1) {
                        echo '<i>'.__('None').'</i>';
                    } else {
                        echo "<ul style='margin-left: 15px'>";
                        while ($rowStudent = $resultStudents->fetch()) {
                            echo '<li>'.formatName('', $rowStudent['preferredName'], $rowStudent['surname'], 'Student').'</li>';
                        }
                        echo '</ul>';
                    }

                    try {
                        $dataStudents = array('pupilsightActivityID' => $row['pupilsightActivityID']);
                        $sqlStudents = "SELECT title, preferredName, surname FROM pupilsightActivityStudent JOIN pupilsightPerson ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightActivityID=:pupilsightActivityID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightActivityStudent.status='Waiting List' ORDER BY timestamp";
                        $resultStudents = $connection2->prepare($sqlStudents);
                        $resultStudents->execute($dataStudents);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    if ($resultStudents->rowCount() > 0) {
                        echo '<h3>'.__('Waiting List').'</h3>';
                        echo "<ol style='margin-left: 15px'>";
                        while ($rowStudent = $resultStudents->fetch()) {
                            echo '<li>'.formatName('', $rowStudent['preferredName'], $rowStudent['surname'], 'Student').'</li>';
                        }
                        echo '</ol>';
                    }
                }
                echo '</div>';
            }
        }
    }
}
