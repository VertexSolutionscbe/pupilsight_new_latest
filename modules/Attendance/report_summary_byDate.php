<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Helper\HelperGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

// set page breadcrumb
$page->breadcrumbs->add(__('Attendance Summary by Date'));

if (isActionAccessible($guid, $connection2, '/modules/Attendance/report_summary_byDate.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo '<h2>';
    echo __('Choose Date');
    echo '</h2>';
    //program
    $HelperGateway = $container->get(HelperGateway::class);
    $pupilsightPersonID =   $_SESSION[$guid]['pupilsightPersonID'];
    $pupilsightRoleIDPrimary = $_SESSION[$guid]['pupilsightRoleIDPrimary'];
    $program = array();
    $program2 = array();
    $program1 = array('' => 'Select Program');
    if ($pupilsightRoleIDPrimary != '001') //for staff login
    {
        $staff_person_id = $pupilsightPersonID;
        $sql1 = "SELECT p.pupilsightProgramID,p.name AS program,a.pupilsightYearGroupID FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN assignstaff_toclasssection b ON(a.pupilsightMappingID =b.pupilsightMappingID) LEFT JOIN pupilsightProgram AS p
            ON(p.pupilsightProgramID =a.pupilsightProgramID) WHERE b.pupilsightPersonID=" . $pupilsightPersonID . "  GROUP By a.pupilsightYearGroupID "; //except Admin //0000002962
        $result1 = $connection2->query($sql1);
        $row1 = $result1->fetchAll();
        /* echo "<pre>";
            print_r($row1);*/

        $progrm_id = "Staff_program";
        $class_id = "Staff_class";
        $section_id = "Staff_section";
        foreach ($row1 as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['program'];
        }
        $program = $program1 + $program2;
        $disable_cls = 'dsble_attr';
    } else {
        $staff_person_id = Null;
        $disable_cls = '';
        $progrm_id = "pupilsightProgramID";
        $class_id = "pupilsightYearGroupID";
        $section_id = "pupilsightRollGroupID";
        $sqlp = 'SELECT p.pupilsightProgramID, p.name FROM pupilsightProgram AS p RIGHT JOIN attn_settings AS a ON(p.pupilsightProgramID =a.pupilsightProgramID) ';
        $resultp = $connection2->query($sqlp);
        $rowdataprog = $resultp->fetchAll();

        foreach ($rowdataprog as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['name'];
        }
        $program = $program1 + $program2;
    }


    //program ends
    if (isset($_GET['pupilsightProgramID'])) {
        $pupilsightProgramID = $_GET['pupilsightProgramID'];
        $pupilsightYearGroupID = $_GET['pupilsightYearGroupID'];
        $classes =  $HelperGateway->getClassByProgram_staff($connection2, $pupilsightProgramID, $staff_person_id);
        $sections =  $HelperGateway->getSectionByProgram_staff($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $staff_person_id);
    } else {
        $classes = array('');
        $sections = array('');
        $search = '';
        $pupilsightYearGroupID = "";
        $pupilsightProgramID = "";
    }
    $today = date('Y-m-d');

    $countClassAsSchool = getSettingByScope($connection2, 'Attendance', 'countClassAsSchool');
    $dateEnd = (isset($_REQUEST['dateEnd'])) ? dateConvert($guid, $_REQUEST['dateEnd']) : date('Y-m-d');
    $dateStart = (isset($_REQUEST['dateStart'])) ? dateConvert($guid, $_REQUEST['dateStart']) : date('Y-m-d', strtotime($dateEnd . ' -1 month'));

    // Correct inverse date ranges rather than generating an error
    if ($dateStart > $dateEnd) {
        $swapDates = $dateStart;
        $dateStart = $dateEnd;
        $dateEnd = $swapDates;
    }

    // Limit date range to the current school year
    if ($dateStart < $_SESSION[$guid]['pupilsightSchoolYearFirstDay']) {
        $dateStart = $_SESSION[$guid]['pupilsightSchoolYearFirstDay'];
    }

    if ($dateEnd > $_SESSION[$guid]['pupilsightSchoolYearLastDay']) {
        $dateEnd = $_SESSION[$guid]['pupilsightSchoolYearLastDay'];
    }

    /*$group = !empty($_REQUEST['group'])? $_REQUEST['group'] : '';
    $sort = !empty($_REQUEST['sort'])? $_REQUEST['sort'] : 'surname';*/

    $pupilsightCourseClassID = (isset($_REQUEST["pupilsightCourseClassID"])) ? $_REQUEST["pupilsightCourseClassID"] : 0;
    $pupilsightRollGroupID = (isset($_REQUEST["pupilsightRollGroupID"])) ? $_REQUEST["pupilsightRollGroupID"] : 0;

    $pupilsightAttendanceCodeID = (isset($_REQUEST["pupilsightAttendanceCodeID"])) ? $_REQUEST["pupilsightAttendanceCodeID"] : 0;
    $reportType = (empty($pupilsightAttendanceCodeID)) ? 'types' : 'reasons';

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'] . '/index.php', 'get');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', "/modules/" . $_SESSION[$guid]['module'] . "/report_summary_byDate.php");

    $row = $form->addRow();
    $row->addLabel('dateStart', __('Start Date'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
    $row->addDate('dateStart')->setValue(dateConvertBack($guid, $dateStart))->required();

    $row = $form->addRow();
    $row->addLabel('dateEnd', __('End Date'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
    $row->addDate('dateEnd')->setValue(dateConvertBack($guid, $dateEnd))->required();

    $options = array("all" => __('All Students'));
    if (isActionAccessible($guid, $connection2, "/modules/Attendance/attendance_take_byCourseClass.php")) {
        $options["class"] = __('Class');
    }
    if (isActionAccessible($guid, $connection2, "/modules/Attendance/attendance_take_byRollGroup.php")) {
        $options["rollGroup"] = __('Roll Group');
    }

    $row = $form->addRow();
    $row->addLabel('pupilsightProgramID', __('Program'));
    $row->addSelect('pupilsightProgramID')->setId($progrm_id)->fromArray($program)->addClass('program_class')->selected($pupilsightProgramID)->required();

    $row = $form->addRow();
    $row->addLabel('pupilsightYearGroupID', __('Class'));
    $row->addSelect('pupilsightYearGroupID')->setId($class_id)->fromArray($classes)->selected($pupilsightYearGroupID)->required();

    $row = $form->addRow();
    $row->addLabel('pupilsightRollGroupID', __('Section'));
    $row->addSelect('pupilsightRollGroupID')->required()->fromArray($sections)->setId($section_id)->selected($pupilsightRollGroupID)->placeholder();

    /* $row = $form->addRow();
        $row->addLabel('group', __('Group By'));
        $row->addSelect('group')->fromArray($options)->selected($group)->required();*/

    /* $form->toggleVisibilityByClass('class')->onSelect('group')->when('class');
    $row = $form->addRow()->addClass('class');
        $row->addLabel('pupilsightCourseClassID', __('Class'));
        $row->addSelectClass('pupilsightCourseClassID', $_SESSION[$guid]['pupilsightSchoolYearID'])->selected($pupilsightCourseClassID)->placeholder()->required();*/

    /*$form->toggleVisibilityByClass('rollGroup')->onSelect('group')->when('rollGroup');
    $row = $form->addRow()->addClass('rollGroup');
        $row->addLabel('pupilsightRollGroupID', __('Roll Group'));
        $row->addSelectRollGroup('pupilsightRollGroupID', $_SESSION[$guid]['pupilsightSchoolYearID'])->selected($pupilsightRollGroupID)->placeholder()->required();*/

    /*$row = $form->addRow();
        $row->addLabel('sort', __('Sort By'));
        $row->addSelect('sort')->fromArray(array('surname' => __('Surname'), 'preferredName' => __('Preferred Name'), 'rollGroup' => __('Roll Group')))->selected($sort)->required();*/

    $row = $form->addRow();
    $row->addFooter();
    $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    // Get attendance codes
    try {
        if (!empty($pupilsightAttendanceCodeID)) {
            $dataCodes = array('pupilsightAttendanceCodeID' => $pupilsightAttendanceCodeID);
            $sqlCodes = "SELECT * FROM pupilsightAttendanceCode WHERE pupilsightAttendanceCodeID=:pupilsightAttendanceCodeID";
        } else {
            $dataCodes = array();
            $sqlCodes = "SELECT * FROM pupilsightAttendanceCode WHERE active = 'Y' AND reportable='Y' ORDER BY sequenceNumber ASC, name";
        }

        $resultCodes = $pdo->executeQuery($dataCodes, $sqlCodes);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
    }

    if ($resultCodes->rowCount() == 0) {
        echo "<div class='alert alert-danger'>";
        echo __('There are no attendance codes defined.');
        echo '</div>';
    } else if (empty($dateStart)) {
        // echo "<div class='alert alert-danger'>";
        // echo __('There are no records to display.');
        // echo '</div>';
    } else if ($dateStart > $today || $dateEnd > $today) {
        echo "<div class='alert alert-danger'>";
        echo __('The specified date is in the future: it must be today or earlier.');
        echo '</div>';
    } else {


        try {
            $dataSchoolDays = array('dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            $sqlSchoolDays = "SELECT COUNT(DISTINCT CASE WHEN date>=pupilsightSchoolYear.firstDay AND date<=pupilsightSchoolYear.lastDay THEN date END) as total, COUNT(DISTINCT CASE WHEN date>=:dateStart AND date <=:dateEnd THEN date END) as dateRange FROM pupilsightAttendanceLogPerson, pupilsightSchoolYearTerm, pupilsightSchoolYear WHERE date>=pupilsightSchoolYearTerm.firstDay AND date <= pupilsightSchoolYearTerm.lastDay AND date <= NOW() AND pupilsightSchoolYearTerm.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID AND pupilsightSchoolYear.pupilsightSchoolYearID=:pupilsightSchoolYearID";

            $resultSchoolDays = $connection2->prepare($sqlSchoolDays);
            $resultSchoolDays->execute($dataSchoolDays);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }
        $schoolDayCounts = $resultSchoolDays->fetch();

        $data = array('dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
        $sqlPieces = array();

        if ($reportType == 'types') {
            $attendanceCodes = array();

            $i = 0;

            while ($type = $resultCodes->fetch()) {
                $typeIdentifier = "`" . str_replace("`", "``", $type['nameShort']) . "`";
                $data['type' . $i] = $type['name'];
                $sqlPieces[] = "COUNT(DISTINCT CASE WHEN pupilsightAttendanceCode.name=:type" . $i . " THEN date END) AS " . $typeIdentifier;
                $attendanceCodes[$type['direction']][] = $type;
                $i++;
            }
            // print_r($sqlPieces);die();
        } else if ($reportType == 'reasons') {
            $attendanceCodeInfo = $resultCodes->fetch();
            $attendanceReasons = explode(',', getSettingByScope($connection2, 'Attendance', 'attendanceReasons'));

            for ($i = 0; $i < count($attendanceReasons); $i++) {
                $reasonIdentifier = "`" . str_replace("`", "``", $attendanceReasons[$i]) . "`";
                $data['reason' . $i] = $attendanceReasons[$i];
                $sqlPieces[] = "COUNT(DISTINCT CASE WHEN pupilsightAttendanceLogPerson.reason=:reason" . $i . " THEN date END) AS " . $reasonIdentifier;
            }

            $sqlPieces[] = "COUNT(DISTINCT CASE WHEN pupilsightAttendanceLogPerson.reason='' THEN date END) AS `No Reason`";
            $attendanceReasons[] = 'No Reason';
        }

        $sqlSelect = implode(',', $sqlPieces);

        //Produce array of attendance data
        try {
            $groupBy = 'GROUP BY pupilsightAttendanceLogPerson.pupilsightPersonID';
            $orderBy = ' ORDER BY LENGTH(rollGroup), rollGroup, surname, preferredName';
            $sql = "SELECT pupilsightPerson.pupilsightPersonID AS stuid,pupilsightPerson.pupilsightPersonID, pupilsightRollGroup.nameShort AS rollGroup, surname,officialName, preferredName, $sqlSelect FROM pupilsightAttendanceLogPerson JOIN pupilsightAttendanceCode ON (pupilsightAttendanceLogPerson.type=pupilsightAttendanceCode.name) JOIN pupilsightPerson ON (pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE date>=:dateStart AND date<=:dateEnd AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolment.pupilsightProgramID=:pupilsightProgramID AND pupilsightStudentEnrolment.pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightStudentEnrolment.pupilsightRollGroupID=:pupilsightRollGroupID";
            /*if ( !empty($pupilsightAttendanceCodeID) ) {
                $data['pupilsightAttendanceCodeID'] = $pupilsightAttendanceCodeID;
                $sql .= ' AND pupilsightAttendanceCode.pupilsightAttendanceCodeID=:pupilsightAttendanceCodeID';
            }*/

            if ($countClassAsSchool == 'N') {
                $sql .= " AND NOT context='Class'";
            }

            $sql .= ' ' . $groupBy . ' ' . $orderBy;

            $result = $connection2->prepare($sql);
            //echo $sql;
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }



        if ($result->rowCount() >= 1) {
            echo '<h2>';
            echo __('Report Data') . ': ' . Format::dateRangeReadable($dateStart, $dateEnd);
            echo '</h2>';
            echo '<p style="color:#666;">';
            // echo '<strong>' . __('Total number of school days to date:').' '.$schoolDayCounts['total'].'</strong><br/>';
            //echo __('Total number of school days in date range:').' '.$schoolDayCounts['dateRange'];
            echo '</p>';

            // echo "<div class='alert alert-danger'>";
            // echo __('There are no records to display.');
            // echo '</div>';

            // echo "<a style='display:none' id='clickchnagestatus' href='fullscreen.php?q=/modules/Staff/change_staff_status.php'  class='thickbox '>Change Route</a>";   
            // echo "<div style='height:50px;'><div class='float-right mb-2'><a  id=''  data-toggle='modal' data-target='#large-modal-new_staff' data-noti='2'  class='sendButton_staff btn btn-primary'>Send SMS</a>&nbsp;&nbsp;";  
            // echo "<a  id='' data-toggle='modal' data-noti='1' data-target='#large-modal-new_staff' class='sendButton_attendance btn btn-primary'>Send Email</a>";
            // echo " </div><div class='float-none'></div></div>";


            echo "<div style='height:50px; margin-top:10px;'><div class='float-right mb-2'>
            <a style=' margin-bottom:10px;' data-sdate='" . $dateStart . "' data-edate='" . $dateEnd . "' id='exportExcelSummary_byDate' href='javascript:void(0)' data-sc='" . $countClassAsSchool . "' class='btn btn-primary'>Export Excel</a>
            <a style=' margin-bottom:10px;' href=''  data-toggle='modal' data-target='#large-modal-new_attendance' data-noti='2'  class='sendButton_attendance btn btn-primary' id='sendSMS'>Send SMS</a>";
            echo "&nbsp;&nbsp;<a style=' margin-bottom:10px;' href='' data-toggle='modal' data-noti='1' data-target='#large-modal-new_attendance' class='sendButton_attendance btn btn-primary' id='sendEmail'>Send Email</a>";
            echo " </div><div class='float-none'></div></div>";


            echo "<div class='linkTop'>";
            //echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/report.php?q=/modules/'.$_SESSION[$guid]['module'].'/report_summary_byDate_print.php&dateStart='.dateConvertBack($guid, $dateStart).'&dateEnd='.dateConvertBack($guid, $dateEnd).'&pupilsightCourseClassID='.$pupilsightCourseClassID.'&pupilsightRollGroupID='.$pupilsightRollGroupID.'&pupilsightAttendanceCodeID='. $pupilsightAttendanceCodeID .'&pupilsightProgramID=' . $pupilsightProgramID . '&pupilsightYearGroupID='.$pupilsightYearGroupID.'&sort=' . $sort . "'>".__('Print')."<img style='margin-left: 5px' title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
            echo '</div>';
            echo "<div id='report_data' style='display:none'></div>";
            echo '<table class="table colorOddEven" >';

            echo "<tr class='head'>";

            echo '<th style="width:80px" rowspan=2>';
            echo "<input type='checkbox' name='checkall' id='checkall' class='floatNone checkall'>";
            echo '</th>';

            echo '<th rowspan=2>';
            echo __('Name');
            echo '</th>';

            if ($reportType == 'types') {
                /* echo '<th colspan='.count($attendanceCodes['In']).' class="columnDivider" style="text-align:center;">';
                echo __('IN');
                echo '</th>';
                echo '<th colspan='.count($attendanceCodes['Out']).' class="columnDivider" style="text-align:center;">';
                echo __('OUT');
                echo '</th>';*/
            } else if ($reportType == 'reasons') {
                echo '<th colspan=' . count($attendanceReasons) . ' class="columnDivider" style="text-align:center;">';
                echo __($attendanceCodeInfo['name']);
                echo '</th>';
            }
            echo '</tr>';


            echo '<tr class="head" style="min-height:80px;">';



            if ($reportType == 'types') {

                $href = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . '/report_summary_byDate.php&dateStart=' . dateConvertBack($guid, $dateStart) . '&dateEnd=' . dateConvertBack($guid, $dateEnd) . '&pupilsightCourseClassID=' . $pupilsightCourseClassID . '&pupilsightRollGroupID=' . $pupilsightRollGroupID . '&pupilsightProgramID=' . $pupilsightProgramID . '&pupilsightYearGroupID=' . $pupilsightYearGroupID;

                for ($i = 0; $i < count($attendanceCodes['In']); $i++) {
                    echo '<th class="' . ($i == 0 ? 'verticalHeader columnDivider' : 'verticalHeader') . '" title="' . $attendanceCodes['In'][$i]['scope'] . '">';
                    echo '<a class="verticalText" href="' . $href . '&pupilsightAttendanceCodeID=' . $attendanceCodes['In'][$i]['pupilsightAttendanceCodeID'] . '">';
                    echo __($attendanceCodes['In'][$i]['name']);
                    echo '</a>';
                    echo '</th>';
                }

                for ($i = 0; $i < count($attendanceCodes['Out']); $i++) {
                    echo '<th class="' . ($i == 0 ? 'verticalHeader columnDivider' : 'verticalHeader') . '" title="' . $attendanceCodes['Out'][$i]['scope'] . '">';
                    echo '<a class="verticalText" href="' . $href . '&pupilsightAttendanceCodeID=' . $attendanceCodes['Out'][$i]['pupilsightAttendanceCodeID'] . '">';
                    echo __($attendanceCodes['Out'][$i]['name']);
                    echo '</a>';
                    echo '</th>';
                }
            } else if ($reportType == 'reasons') {
                for ($i = 0; $i < count($attendanceReasons); $i++) {
                    echo '<th class="' . ($i == 0 ? 'verticalHeader columnDivider' : 'verticalHeader') . '">';
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
                echo "<td><input type='checkbox' class='' name='student_id[]' id='" . $row['stuid'] . "' ></td>";
                /* echo '<td>';
                    echo $row['rollGroup'];
                echo '</td>';*/
                echo '<td>';
                echo '<a href="index.php?q=/modules/Attendance/report_studentHistory.php&pupilsightPersonID=' . $row['pupilsightPersonID'] . '" target="_blank">';
                echo $row['officialName'];
                echo '</a> <a class="thickbox" href="fullscreen.php?q=/modules/Attendance/view_attendance_info.php&name=' . $row['officialName'] . '&pid=' . $row['pupilsightPersonID'] . '&starDate=' . $dateStart . '&endDate=' . $dateEnd . '" title="Attendance Info"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                echo '</td>';

                if ($reportType == 'types') {
                    for ($i = 0; $i < count($attendanceCodes['In']); $i++) {
                        echo '<td class="center ' . ($i == 0 ? 'columnDivider' : '') . '">';
                        echo $row[$attendanceCodes['In'][$i]['nameShort']];
                        echo '</td>';
                    }

                    for ($i = 0; $i < count($attendanceCodes['Out']); $i++) {
                        echo '<td class="center ' . ($i == 0 ? 'columnDivider' : '') . '">';
                        echo $row[$attendanceCodes['Out'][$i]['nameShort']];
                        echo '</td>';
                    }
                } else if ($reportType == 'reasons') {
                    for ($i = 0; $i < count($attendanceReasons); $i++) {
                        echo '<td class="center ' . ($i == 0 ? 'columnDivider' : '') . '">';
                        echo $row[$attendanceReasons[$i]];
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
<script type="text/javascript">
    $(document).on('click', '#exportExcelSummary_byDate', function() {
        var dateStart = $(this).attr('data-sdate');
        var dateEnd = $(this).attr('data-edate');
        var countClassAsSchool = $(this).attr('data-sc');
        var pupilsightProgramID = $("#pupilsightProgramID").val();
        var pupilsightYearGroupID = $("#pupilsightYearGroupID").val();
        var pupilsightRollGroupID = $("#pupilsightRollGroupID").val();
        var type = "exportExcelSummary_byDate";
        $.ajax({
            url: 'attendanceSwitch.php',
            type: 'post',
            data: {
                dateStart: dateStart,
                dateEnd: dateEnd,
                pupilsightProgramID: pupilsightProgramID,
                pupilsightYearGroupID: pupilsightYearGroupID,
                pupilsightRollGroupID: pupilsightRollGroupID,
                countClassAsSchool: countClassAsSchool,
                type: type
            },
            async: true,
            success: function(response) {

                $("#report_data").html(response);
                $("#excelexport").table2excel({
                    name: "Report_summary_byDate",
                    filename: "report_summary_byDate.xls",
                    fileext: ".xls",
                    exclude: ".checkall",
                    exclude_inputs: true,
                    exclude_links: true
                });
            }
        });
    });
</script>