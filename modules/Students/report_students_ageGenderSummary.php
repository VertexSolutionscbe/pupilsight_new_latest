<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/report_students_ageGenderSummary.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Age & Gender Summary'));

    //Work out ages in school
    try {
        $dataList = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
        $sqlList = "SELECT pupilsightStudentEnrolment.pupilsightYearGroupID, dob, gender FROM pupilsightPerson, pupilsightStudentEnrolment, pupilsightYearGroup WHERE pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID AND status='FULL' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY dob DESC";
        $resultList = $connection2->prepare($sqlList);
        $resultList->execute($dataList);
    } catch (PDOException $e) {
    }

    $today = time();
    $ages = array();
    $everything = array();
    $count = 0;
    while ($rowList = $resultList->fetch()) {
        if ($rowList['dob'] != '') {
            $age = floor(($today - strtotime($rowList['dob'])) / 31556926);
            if (isset($ages[$age]) == false) {
                $ages[$age] = $age;
            }
        }
        $everything[$count][0] = $rowList['dob'];
        $everything[$count][1] = $rowList['gender'];
        $everything[$count][2] = $rowList['pupilsightYearGroupID'];
        ++$count;
    }

    $years = getYearGroups($connection2);

    if (count($ages) < 1 or count($years) < 1) {
        echo "<div class='alert alert-danger'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {
        echo '<div style="overflow-x: scroll;">';
        echo "<table class='mini' cellspacing='0' style='max-width: 100%'>";
        echo "<tr class='head'>";
        echo "<th style='width: 100%' rowspan=2>";
        echo __('Age').'<br/>';
        echo "<span style='font-size: 75%; font-style: italic'>".__('As of today').'</span>';
        echo '</th>';
        for ($i = 1; $i < count($years); $i = $i + 2) {
            echo "<th colspan=2 style='text-align: center'>";
            echo __($years[$i]);
            echo '</th>';
        }
        echo "<th colspan=2 style='text-align: center'>";
        echo __('All Years');
        echo '</th>';
        echo '</tr>';

        echo "<tr class='head'>";
        for ($i = 1; $i < count($years); $i = $i + 2) {
            echo "<th style='text-align: center; height: 70px; max-width:30px!important'>";
            echo "<div style='-webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg); -ms-transform: rotate(-90deg); -o-transform: rotate(-90deg); transform: rotate(-90deg);'>";
            echo __('Male');
            echo '</div>';
            echo '</th>';
            echo "<th style='text-align: center; height: 70px; max-width:30px!important'>";
            echo "<div style='-webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg); -ms-transform: rotate(-90deg); -o-transform: rotate(-90deg); transform: rotate(-90deg);'>";
            echo __('Female');
            echo '</div>';
            echo '</th>';
        }
        echo "<th style='text-align: center; height: 70px; max-width:30px!important'>";
        echo "<div style='-webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg); -ms-transform: rotate(-90deg); -o-transform: rotate(-90deg); transform: rotate(-90deg);'>";
        echo __('Male');
        echo '</div>';
        echo '</th>';
        echo "<th style='text-align: center; height: 70px; max-width:30px!important'>";
        echo "<div style='-webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg); -ms-transform: rotate(-90deg); -o-transform: rotate(-90deg); transform: rotate(-90deg);'>";
        echo __('Female');
        echo '</div>';
        echo '</th>';
        echo '</tr>';

        $count = 0;
        $rowNum = 'odd';
        foreach ($ages as $age) {
            if ($count % 2 == 0) {
                $rowNum = 'even';
            } else {
                $rowNum = 'odd';
            }
            ++$count;

            //COLOR ROW BY STATUS!
            echo "<tr class=$rowNum>";
            echo '<td>';
            echo "<b>$age</b>";
            echo '</td>';
            for ($i = 1; $i < count($years); $i = $i + 2) {
                echo "<td style='text-align: center'>";
                $cellCount = 0;
                foreach ($everything as $thing) {
                    if ($thing[2] == $years[$i - 1] and $thing[1] == 'M' and floor(($today - strtotime($thing[0])) / 31556926) == $age) {
                        ++$cellCount;
                    }
                }
                if ($cellCount != 0) {
                    echo $cellCount;
                }
                echo '</td>';
                echo "<td style='text-align: center'>";
                $cellCount = 0;
                foreach ($everything as $thing) {
                    if ($thing[2] == $years[$i - 1] and $thing[1] == 'F' and floor(($today - strtotime($thing[0])) / 31556926) == $age) {
                        ++$cellCount;
                    }
                }
                if ($cellCount != 0) {
                    echo $cellCount;
                }
                echo '</td>';
            }
            echo "<td style='text-align: center'>";
            $cellCount = 0;
            foreach ($everything as $thing) {
                if ($thing[1] == 'M' and floor(($today - strtotime($thing[0])) / 31556926) == $age) {
                    ++$cellCount;
                }
            }
            if ($cellCount != 0) {
                echo $cellCount;
            }
            echo '</td>';
            echo "<td style='text-align: center'>";
            $cellCount = 0;
            foreach ($everything as $thing) {
                if ($thing[1] == 'F' and floor(($today - strtotime($thing[0])) / 31556926) == $age) {
                    ++$cellCount;
                }
            }
            if ($cellCount != 0) {
                echo $cellCount;
            }
            echo '</td>';
            echo '</tr>';
        }
        echo "<tr style='background-color: #FFD2A9'>";
        echo '<td rowspan=2>';
        echo '<b>'.__('All Ages').'</b>';
        echo '</td>';
        for ($i = 1; $i < count($years); $i = $i + 2) {
            echo "<td style='text-align: center; font-weight: bold'>";
            $cellCount = 0;
            foreach ($everything as $thing) {
                if ($thing[2] == $years[$i - 1] and $thing[1] == 'M') {
                    ++$cellCount;
                }
            }
            if ($cellCount != 0) {
                echo $cellCount;
            }
            echo '</td>';
            echo "<td style='text-align: center; font-weight: bold'>";
            $cellCount = 0;
            foreach ($everything as $thing) {
                if ($thing[2] == $years[$i - 1] and $thing[1] == 'F') {
                    ++$cellCount;
                }
            }
            if ($cellCount != 0) {
                echo $cellCount;
            }
            echo '</td>';
        }
        echo "<td style='text-align: center; font-weight: bold'>";
        $cellCount = 0;
        foreach ($everything as $thing) {
            if ($thing[1] == 'M') {
                ++$cellCount;
            }
        }
        if ($cellCount != 0) {
            echo $cellCount;
        }
        echo '</td>';
        echo "<td style='text-align: center; font-weight: bold'>";
        $cellCount = 0;
        foreach ($everything as $thing) {
            if ($thing[1] == 'F') {
                ++$cellCount;
            }
        }
        if ($cellCount != 0) {
            echo $cellCount;
        }
        echo '</td>';
        echo '</tr>';
        echo "<tr style='background-color: #FFD2A9'>";
        for ($i = 1; $i < count($years); $i = $i + 2) {
            echo "<td colspan=2 style='text-align: center; font-weight: bold'>";
            $cellCount = 0;
            foreach ($everything as $thing) {
                if ($thing[2] == $years[$i - 1]) {
                    ++$cellCount;
                }
            }
            if ($cellCount != 0) {
                echo $cellCount;
            }
            echo '</td>';
        }
        echo "<td colspan=2 style='text-align: center; font-weight: bold'>";
        if (count($everything) != 0) {
            echo count($everything);
        }
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</div>';
    }
}
