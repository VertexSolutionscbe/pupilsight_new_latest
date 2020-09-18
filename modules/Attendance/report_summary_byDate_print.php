<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Attendance/report_summary_byDate.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $countClassAsSchool = getSettingByScope($connection2, 'Attendance', 'countClassAsSchool');
    $dateEnd = (isset($_GET['dateEnd']))? dateConvert($guid, $_GET['dateEnd']) : date('Y-m-d');
    $dateStart = (isset($_GET['dateStart']))? dateConvert($guid, $_GET['dateStart']) : date('Y-m-d', strtotime( $dateEnd.' -1 month') );

    $group = !empty($_GET['group'])? $_GET['group'] : '';
    $sort = !empty($_GET['sort'])? $_GET['sort'] : 'surname, preferredName';
    $pupilsightYearGroupID = !empty($_GET['pupilsightYearGroupID']);
    $pupilsightProgramID = !empty($_GET['pupilsightProgramID']);

    $pupilsightCourseClassID = (isset($_GET["pupilsightCourseClassID"]))? $_GET["pupilsightCourseClassID"] : 0;
    $pupilsightRollGroupID = (isset($_GET["pupilsightRollGroupID"]))? $_GET["pupilsightRollGroupID"] : 0;

    $pupilsightAttendanceCodeID = (isset($_GET["pupilsightAttendanceCodeID"]))? $_GET["pupilsightAttendanceCodeID"] : 0;
    $reportType = (empty($pupilsightAttendanceCodeID))? 'types' : 'reasons';

    // Get attendance codes
    try {
        if (!empty($pupilsightAttendanceCodeID)) {
            $dataCodes = array( 'pupilsightAttendanceCodeID' => $pupilsightAttendanceCodeID);
            $sqlCodes = "SELECT * FROM pupilsightAttendanceCode WHERE pupilsightAttendanceCodeID=:pupilsightAttendanceCodeID";
        } else {
            $dataCodes = array();
            $sqlCodes = "SELECT * FROM pupilsightAttendanceCode WHERE active = 'Y' AND reportable='Y' ORDER BY sequenceNumber ASC, name";
        }

        $resultCodes = $pdo->executeQuery($dataCodes, $sqlCodes);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    if ($resultCodes->rowCount() == 0) {
        echo "<div class='alert alert-danger'>";
        echo __('There are no attendance codes defined.');
        echo '</div>';
    }
    else if ( empty($dateStart) || empty($group)) {
        echo "<div class='alert alert-danger'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {
        echo '<h2>';
        echo __('Report Data').': '. Format::dateRangeReadable($dateStart, $dateEnd);                
        echo '</h2>';

        try {
            $dataSchoolDays = array( 'dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            $sqlSchoolDays = "SELECT COUNT(DISTINCT CASE WHEN date>=pupilsightSchoolYear.firstDay AND date<=pupilsightSchoolYear.lastDay THEN date END) as total, COUNT(DISTINCT CASE WHEN date>=:dateStart AND date <=:dateEnd THEN date END) as dateRange FROM pupilsightAttendanceLogPerson, pupilsightSchoolYearTerm, pupilsightSchoolYear WHERE date>=pupilsightSchoolYearTerm.firstDay AND date <= pupilsightSchoolYearTerm.lastDay AND date <= NOW() AND pupilsightSchoolYearTerm.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID AND pupilsightSchoolYear.pupilsightSchoolYearID=:pupilsightSchoolYearID";

            $resultSchoolDays = $connection2->prepare($sqlSchoolDays);
            $resultSchoolDays->execute($dataSchoolDays);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }
        $schoolDayCounts = $resultSchoolDays->fetch();

        echo '<p style="color:#666;">';
            echo '<strong>' . __('Total number of school days to date:').' '.$schoolDayCounts['total'].'</strong><br/>';
            echo __('Total number of school days in date range:').' '.$schoolDayCounts['dateRange'];
        echo '</p>';


        $sqlPieces = array();

        if ($reportType == 'types') {
            $attendanceCodes = array();

            while( $type = $resultCodes->fetch() ) {
                $sqlPieces[] = "COUNT(DISTINCT CASE WHEN pupilsightAttendanceCode.name='".$type['name']."' THEN date END) AS ".$type['nameShort'];
                $attendanceCodes[ $type['direction'] ][] = $type;
            }
        }
        else if ($reportType == 'reasons') {
            $attendanceCodeInfo = $resultCodes->fetch();
            $attendanceReasons = explode(',', getSettingByScope($connection2, 'Attendance', 'attendanceReasons') );

            foreach( $attendanceReasons as $reason ) {
                $sqlPieces[] = "COUNT(DISTINCT CASE WHEN pupilsightAttendanceLogPerson.reason='".$reason."' THEN date END) AS `".$reason."`";
            }

            $sqlPieces[] = "COUNT(DISTINCT CASE WHEN pupilsightAttendanceLogPerson.reason='' THEN date END) AS `No Reason`";
            $attendanceReasons[] = 'No Reason';
        }

        $sqlSelect = implode( ',', $sqlPieces );

        //Produce array of attendance data
        try {
            $groupBy = 'GROUP BY pupilsightAttendanceLogPerson.pupilsightPersonID';
            $orderBy = ' ORDER BY LENGTH(rollGroup), rollGroup, surname, preferredName';
            $sql = "SELECT pupilsightPerson.pupilsightPersonID AS stuid,pupilsightPerson.pupilsightPersonID, pupilsightRollGroup.nameShort AS rollGroup, surname,officialName, preferredName, $sqlSelect FROM pupilsightAttendanceLogPerson JOIN pupilsightAttendanceCode ON (pupilsightAttendanceLogPerson.type=pupilsightAttendanceCode.name) JOIN pupilsightPerson ON (pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE date>=:dateStart AND date<=:dateEnd AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolment.pupilsightProgramID=:pupilsightProgramID AND pupilsightStudentEnrolment.pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightStudentEnrolment.pupilsightRollGroupID=:pupilsightRollGroupID";
            /*if ( !empty($pupilsightAttendanceCodeID) ) {
            $data['pupilsightAttendanceCodeID'] = $pupilsightAttendanceCodeID;
            $sql .= ' AND pupilsightAttendanceCode.pupilsightAttendanceCodeID=:pupilsightAttendanceCodeID';
            }*/
             $data = array('dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'],'pupilsightProgramID'=>$pupilsightProgramID,'pupilsightYearGroupID'=>$pupilsightYearGroupID,'pupilsightRollGroupID'=>$pupilsightRollGroupID);
            if ($countClassAsSchool == 'N') {
            $sql .= " AND NOT context='Class'";
            }

            $sql .= ' '. $groupBy . ' '. $orderBy;

            $result = $connection2->prepare($sql);
            //echo $sql;
            $result->execute($data);
           /* $data = array('dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);

            $groupBy = 'GROUP BY pupilsightAttendanceLogPerson.pupilsightPersonID';
            $orderBy = 'ORDER BY surname, preferredName';
            if ($sort == 'preferredName')
                $orderBy = 'ORDER BY preferredName, surname';
            if ($sort == 'rollGroup')
                $orderBy = ' ORDER BY LENGTH(rollGroup), rollGroup, surname, preferredName';

            if ($group == 'all') {
                $sql = "SELECT pupilsightPerson.pupilsightPersonID, pupilsightRollGroup.nameShort AS rollGroup, surname, preferredName, $sqlSelect FROM pupilsightAttendanceLogPerson JOIN pupilsightAttendanceCode ON (pupilsightAttendanceLogPerson.type=pupilsightAttendanceCode.name) JOIN pupilsightPerson ON (pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE date>=:dateStart AND date<=:dateEnd AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID";
            }
            else if ($group == 'class') {
                $data['pupilsightCourseClassID'] = $pupilsightCourseClassID;
                $sql = "SELECT pupilsightPerson.pupilsightPersonID, pupilsightRollGroup.nameShort AS rollGroup, surname, preferredName, $sqlSelect FROM pupilsightAttendanceLogPerson JOIN pupilsightAttendanceCode ON (pupilsightAttendanceLogPerson.type=pupilsightAttendanceCode.name) JOIN pupilsightPerson ON (pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE date>=:dateStart AND date<=:dateEnd AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightAttendanceLogPerson.context='Class' AND pupilsightAttendanceLogPerson.pupilsightCourseClassID=:pupilsightCourseClassID";
            }
            else if ($group == 'rollGroup') {
                $data['pupilsightRollGroupID'] = $pupilsightRollGroupID;
                $sql = "SELECT pupilsightPerson.pupilsightPersonID, pupilsightRollGroup.nameShort AS rollGroup, surname, preferredName, $sqlSelect FROM pupilsightAttendanceLogPerson JOIN pupilsightAttendanceCode ON (pupilsightAttendanceLogPerson.type=pupilsightAttendanceCode.name) JOIN pupilsightPerson ON (pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE date>=:dateStart AND date<=:dateEnd AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolment.pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightAttendanceLogPerson.context='Roll Group'";
            }

            if ( !empty($pupilsightAttendanceCodeID) ) {
                $data['pupilsightAttendanceCodeID'] = $pupilsightAttendanceCodeID;
                $sql .= ' AND pupilsightAttendanceCode.pupilsightAttendanceCodeID=:pupilsightAttendanceCodeID';
            }

            if ($countClassAsSchool == 'N' && $group != 'class') {
                $sql .= " AND NOT context='Class'";
            }

            $sql .= ' '. $groupBy . ' '. $orderBy;

            $result = $connection2->prepare($sql);
            $result->execute($data);*/
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() < 1) {
            echo "<div class='alert alert-danger'>";
            echo __('There are no records to display.');
            echo '</div>';
        } else {

            echo '<table class="table fullWidth colorOddEven" >';

            echo "<tr class='head'>";
            echo '<th style="width:80px" rowspan=2>';
            echo __('Roll Group');
            echo '</th>';
            echo '<th rowspan=2>';
            echo __('Name');
            echo '</th>';

            if ($reportType == 'types') {
                echo '<th colspan='.count($attendanceCodes['In']).' class="columnDivider" style="text-align:center;">';
                echo __('IN');
                echo '</th>';
                echo '<th colspan='.count($attendanceCodes['Out']).' class="columnDivider" style="text-align:center;">';
                echo __('OUT');
                echo '</th>';
            } else if ($reportType == 'reasons') {
                echo '<th colspan='.count($attendanceReasons).' class="columnDivider" style="text-align:center;">';
                echo __($attendanceCodeInfo['name'] );
                echo '</th>';
            }
            echo '</tr>';


            echo '<tr class="head" style="min-height:80px;">';

            if ($reportType == 'types') {

                for( $i = 0; $i < count($attendanceCodes['In']); $i++ ) {
                    echo '<th class="'.( $i == 0? 'verticalHeader columnDivider' : 'verticalHeader').'" title="'.$attendanceCodes['In'][$i]['scope'].'">';
                        echo '<div class="verticalText">';
                        echo __($attendanceCodes['In'][$i]['name']);
                        echo '</div>';
                    echo '</th>';
                }

                for( $i = 0; $i < count($attendanceCodes['Out']); $i++ ) {
                    echo '<th class="'.( $i == 0? 'verticalHeader columnDivider' : 'verticalHeader').'" title="'.$attendanceCodes['Out'][$i]['scope'].'">';
                        echo '<div class="verticalText">';
                        echo __($attendanceCodes['Out'][$i]['name']);
                        echo '</div>';
                    echo '</th>';
                }
            } else if ($reportType == 'reasons') {
                for( $i = 0; $i < count($attendanceReasons); $i++ ) {
                    echo '<th class="'.( $i == 0? 'verticalHeader columnDivider' : 'verticalHeader').'">';
                        echo '<div class="verticalText">';
                        echo $attendanceReasons[$i];
                        echo '</div>';
                    echo '</th>';
                }
            }

            echo '</tr>';


            while ($row = $result->fetch()) {

                // ROW
                echo "<tr>";
                echo '<td>';
                    echo $row['rollGroup'];
                echo '</td>';
                echo '<td>';
                    echo formatName('', $row['preferredName'], $row['surname'], 'Student', ($sort != 'preferredName') );
                echo '</td>';

                if ($reportType == 'types') {
                    for( $i = 0; $i < count($attendanceCodes['In']); $i++ ) {
                        echo '<td class="center '.( $i == 0? 'columnDivider' : '').'">';
                            echo $row[ $attendanceCodes['In'][$i]['nameShort'] ];
                        echo '</td>';
                    }

                    for( $i = 0; $i < count($attendanceCodes['Out']); $i++ ) {
                        echo '<td class="center '.( $i == 0? 'columnDivider' : '').'">';
                            echo $row[ $attendanceCodes['Out'][$i]['nameShort'] ];
                        echo '</td>';
                    }
                } else if ($reportType == 'reasons') {
                    for( $i = 0; $i < count($attendanceReasons); $i++ ) {
                        echo '<td class="center '.( $i == 0? 'columnDivider' : '').'">';
                            echo $row[ $attendanceReasons[$i] ];
                        echo '</td>';
                    }
                }
                echo '</tr>';

            }
            if ($result->rowCount() == 0) {
                echo "<tr>";
                echo '<td colspan=5>';
                echo __('All students are present.');
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';


        }
    }
}
?>
