<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Module\Attendance\AttendanceView;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

// set page breadcrumb
$page->breadcrumbs->add(__('Students Not Present'));

if (isActionAccessible($guid, $connection2, '/modules/Attendance/report_studentsNotPresent_byDate.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo '<h2>';
    echo __('Choose Date');
    echo '</h2>';

    if (isset($_GET['currentDate']) == false) {
        $currentDate = date('Y-m-d');
    } else {
        $currentDate = dateConvert($guid, $_GET['currentDate']);
    }

    $allStudents = !empty($_GET["allStudents"])? 1 : 0;
    $sort = !empty($_GET['sort'])? $_GET['sort'] : 'surname, preferredName';
    $pupilsightYearGroupIDList = (!empty($_GET['pupilsightYearGroupIDList']) && is_array($_GET['pupilsightYearGroupIDList'])) ? $_GET['pupilsightYearGroupIDList'] : null ;

    require_once __DIR__ . '/src/AttendanceView.php';
    $attendance = new AttendanceView($pupilsight, $pdo);

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/report_studentsNotPresent_byDate.php");

    $row = $form->addRow();
        $row->addLabel('currentDate', __('Date'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
        $row->addDate('currentDate')->setValue(dateConvertBack($guid, $currentDate))->required();

    $row = $form->addRow();
        $row->addLabel('sort', __('Sort By'));
        $row->addSelect('sort')->fromArray(array('surname' => __('Surname'), 'preferredName' => __('Preferred Name'), 'rollGroup' => __('Roll Group')))->selected($sort)->required();

    $row = $form->addRow();
        $row->addLabel('allStudents', __('All Students'))->description(__('Include all students, even those where attendance has not yet been recorded.'));
        $row->addCheckbox('allStudents')->checked($allStudents);

    $row = $form->addRow();
        $row->addLabel('pupilsightYearGroupIDList', __('Year Groups'))->description(__('Relevant student year groups'));
        if (!empty($pupilsightYearGroupIDList)) {
            $values['pupilsightYearGroupIDList'] = $pupilsightYearGroupIDList;
            $row->addCheckboxYearGroup('pupilsightYearGroupIDList')->addCheckAllNone()->loadFrom($values);
        }
        else {
            $row->addCheckboxYearGroup('pupilsightYearGroupIDList')->addCheckAllNone()->checkAll();
        }

    $row = $form->addRow();
        $row->addFooter();
        $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    if ($currentDate != '') {
        echo '<h2>';
        echo __('Report Data');
        echo '</h2>';

        //Produce array of attendance data
        try {
            $countClassAsSchool = getSettingByScope($connection2, 'Attendance', 'countClassAsSchool');
            $data = array('date' => $currentDate);
            $sql = 'SELECT *
                FROM pupilsightAttendanceLogPerson
                WHERE date=:date';
                if ($countClassAsSchool == "N") {
                    $sql .= ' AND NOT context=\'Class\'';
                }
                $sql .= ' ORDER BY pupilsightPersonID, pupilsightAttendanceLogPersonID DESC';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        $log = array();
        $currentStudent = '';
        $lastStudent = '';
        $count = 0 ;
        while ($row = $result->fetch()) {
            $currentStudent = $row['pupilsightPersonID'];
            if ( $attendance->isTypePresent($row['type']) and $currentStudent != $lastStudent) {
                $log[$row['pupilsightPersonID']] = true;
            }
            $lastStudent = $currentStudent;
        }

        try {
            $orderBy = 'ORDER BY surname, preferredName, LENGTH(rollGroup), rollGroup';
            if ($sort == 'preferredName')
                $orderBy = 'ORDER BY preferredName, surname, LENGTH(rollGroup), rollGroup';
            if ($sort == 'rollGroup')
                $orderBy = 'ORDER BY LENGTH(rollGroup), rollGroup, surname, preferredName';

            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);

            $whereExtra = '';
            if (is_array($pupilsightYearGroupIDList)) {
                $data['pupilsightYearGroupIDList'] = implode(",", $pupilsightYearGroupIDList);
                $whereExtra = ' AND FIND_IN_SET (pupilsightStudentEnrolment.pupilsightYearGroupID, :pupilsightYearGroupIDList)';
            }

            $sql = "SELECT pupilsightPerson.pupilsightPersonID, surname, preferredName, pupilsightRollGroup.pupilsightRollGroupID, pupilsightRollGroup.name as rollGroupName, pupilsightRollGroup.nameShort AS rollGroup FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) LEFT JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID $whereExtra";

            $sql .= $orderBy;

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
            echo "<div class='linkTop'>";
            echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/report.php?q=/modules/'.$_SESSION[$guid]['module'].'/report_studentsNotPresent_byDate_print.php&currentDate='.dateConvertBack($guid, $currentDate)."&allStudents=" . $allStudents . "&sort=" . $sort . "&pupilsightYearGroupIDList=";
            if (is_array($pupilsightYearGroupIDList)) {
                echo implode(",", $pupilsightYearGroupIDList);
            }
            echo "'>".__('Print')."<img style='margin-left: 5px' title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
            echo '</div>';

            $lastPerson = '';

            echo '<table class="table colorOddEven" >';
            echo "<tr class='head'>";
            echo '<th>';
            echo __('Count');
            echo '</th>';
            echo '<th style="width:80px">';
            echo __('Roll Group');
            echo '</th>';
            echo '<th>';
            echo __('Name');
            echo '</th>';
            echo '<th>';
            echo __('Status');
            echo '</th>';
            echo '<th>';
            echo __('Reason');
            echo '</th>';
            echo '<th>';
            echo __('Comment');
            echo '</th>';
            echo '</tr>';


            while ($row = $result->fetch()) {
                if (isset($log[$row['pupilsightPersonID']]) == false) {

                    try {
                        $dataAttendance = array('date' => $currentDate, 'pupilsightPersonID' => $row['pupilsightPersonID']);
                        $sqlAttendance = 'SELECT *
                            FROM pupilsightAttendanceLogPerson
                            WHERE date=:date
                            AND pupilsightPersonID=:pupilsightPersonID';
                            if ($countClassAsSchool == "N") {
                                $sqlAttendance .= ' AND NOT context=\'Class\'';
                            }
                            $sqlAttendance .= ' ORDER BY pupilsightAttendanceLogPersonID DESC';
                        $resultAttendance = $connection2->prepare($sqlAttendance);
                        $resultAttendance->execute($dataAttendance);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    // Skip rows with no record if we're not displaying all students
                    if ($resultAttendance->rowCount()<1 && $allStudents == FALSE) {
                        continue;
                    }

                    $count ++;

                    // ROW
                    echo "<tr>";
                    echo '<td>';
                        echo $count;
                    echo '</td>';
                    echo '<td>';
                        echo $row['rollGroupName'];
                    echo '</td>';
                    echo '<td>';
                        echo formatName('', $row['preferredName'], $row['surname'], 'Student', ($sort != 'preferredName') );
                    echo '</td>';
                    echo '<td>';
                    $rowRollAttendance = null;

                    if ($resultAttendance->rowCount() < 1) {
                        echo '<i>Not registered</i>';
                    } else {
                        $rowRollAttendance = $resultAttendance->fetch();
                        echo $rowRollAttendance['type'];
                    }
                    echo '</td>';
                    echo '<td>';
                        echo $rowRollAttendance['reason'];
                    echo '</td>';
                    echo '<td>';
                        echo $rowRollAttendance['comment'];
                    echo '</td>';
                    echo '</tr>';

                    $lastPerson = $row['pupilsightPersonID'];
                }
            }
            if ($count == 0) {
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
