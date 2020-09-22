<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/report_students_byRollGroup_print.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $pupilsightRollGroupID = (isset($_GET['pupilsightRollGroupID']) ? $_GET['pupilsightRollGroupID'] : null);
    $view = (isset($_GET['view']) ? $_GET['view'] : 'Basic');

    //Proceed!
    echo '<h2>';
    echo __('Students by Roll Group');
    echo '</h2>';

    if ($pupilsightRollGroupID != '') {
        if ($pupilsightRollGroupID != '*') {
            try {
                $data = array('pupilsightRollGroupID' => $pupilsightRollGroupID);
                $sql = 'SELECT * FROM pupilsightRollGroup WHERE pupilsightRollGroupID=:pupilsightRollGroupID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() == 1) {
                $row = $result->fetch();
                echo "<p style='margin-bottom: 0px'><b>".__('Roll Group').'</b>: '.$row['name'].'</p>';

                //Show Tutors
                try {
                    $dataDetail = array('pupilsightPersonIDTutor' => $row['pupilsightPersonIDTutor'], 'pupilsightPersonIDTutor2' => $row['pupilsightPersonIDTutor2'], 'pupilsightPersonIDTutor3' => $row['pupilsightPersonIDTutor3']);
                    $sqlDetail = 'SELECT title, surname, preferredName FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonIDTutor OR pupilsightPersonID=:pupilsightPersonIDTutor2 OR pupilsightPersonID=:pupilsightPersonIDTutor3';
                    $resultDetail = $connection2->prepare($sqlDetail);
                    $resultDetail->execute($dataDetail);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                if ($resultDetail->rowCount() > 0) {
                    $tutorCount = 0;
                    echo "<p style=''><b>".__('Tutors').'</b>: ';
                    while ($rowDetail = $resultDetail->fetch()) {
                        echo Format::name($rowDetail['title'], $rowDetail['preferredName'], $rowDetail['surname'], 'Staff');
                        ++$tutorCount;
                        if ($tutorCount < $resultDetail->rowCount()) {
                            echo ', ';
                        }
                    }
                    echo '</p>';
                }
            }
        }

        try {
            if ($pupilsightRollGroupID == '*') {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sql = "SELECT * FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY pupilsightRollGroup.nameShort, surname, preferredName";
            } else {
                $data = array('pupilsightRollGroupID' => $pupilsightRollGroupID);
                $sql = "SELECT * FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightStudentEnrolment.pupilsightRollGroupID=:pupilsightRollGroupID ORDER BY surname, preferredName";
            }
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        echo "<div class='linkTop'>";
        echo "<a href='javascript:window.print()'>".__('Print')."<img style='margin-left: 5px' title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
        echo '</div>';

        echo "<table class='mini' cellspacing='0' style='width: 100%'>";
        echo "<tr class='head'>";
        echo '<th>';
        echo __('Roll Group');
        echo '</th>';
        echo '<th>';
        echo __('Student');
        echo '</th>';
        if ($view == 'Extended') {
            echo '<th>';
            echo __('Gender');
            echo '</th>';
            echo '<th>';
            echo __('Age').'<br/>';
            echo "<span style='font-style: italic; font-size: 85%'>".__('DOB').'</span>';
            echo '</th>';
            echo '<th>';
            echo __('Nationality');
            echo '</th>';
            echo '<th>';
            echo __('Transport');
            echo '</th>';
            echo '<th>';
            echo __('House');
            echo '</th>';
            echo '<th>';
            echo __('Locker');
            echo '</th>';
            echo '<th>';
            echo __('Medical');
            echo '</th>';
        }
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
            echo '<td>';
            echo $row['name'];
            echo '</td>';
            echo '<td>';
            echo Format::name('', $row['preferredName'], $row['surname'], 'Student', true);
            echo '</td>';
            if ($view == 'Extended') {
                echo '<td>';
                echo $row['gender'];
                echo '</td>';
                echo '<td>';
                if (is_null($row['dob']) == false and $row['dob'] != '0000-00-00') {
                    echo Format::age($row['dob'], true).'<br/>';
                    echo "<span style='font-style: italic; font-size: 85%'>".dateConvertBack($guid, $row['dob']).'</span>';
                }
                echo '</td>';
                echo '<td>';
                if ($row['citizenship1'] != '') {
                    echo $row['citizenship1'].'<br/>';
                }
                if ($row['citizenship2'] != '') {
                    echo $row['citizenship2'].'<br/>';
                }
                echo '</td>';
                echo '<td>';
                echo $row['transport'];
                echo '</td>';
                echo '<td>';
                if ($row['pupilsightHouseID'] != '') {
                    try {
                        $dataHouse = array('pupilsightHouseID' => $row['pupilsightHouseID']);
                        $sqlHouse = 'SELECT * FROM pupilsightHouse WHERE pupilsightHouseID=:pupilsightHouseID';
                        $resultHouse = $connection2->prepare($sqlHouse);
                        $resultHouse->execute($dataHouse);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    if ($resultHouse->rowCount() == 1) {
                        $rowHouse = $resultHouse->fetch();
                        echo $rowHouse['name'];
                    }
                }
                echo '</td>';
                echo '<td>';
                echo $row['lockerNumber'];
                echo '</td>';
                echo '<td>';
                try {
                    $dataForm = array('pupilsightPersonID' => $row['pupilsightPersonID']);
                    $sqlForm = 'SELECT * FROM pupilsightPersonMedical WHERE pupilsightPersonID=:pupilsightPersonID';
                    $resultForm = $connection2->prepare($sqlForm);
                    $resultForm->execute($dataForm);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($resultForm->rowCount() == 1) {
                    $rowForm = $resultForm->fetch();
                    if ($rowForm['longTermMedication'] == 'Y') {
                        echo '<b><i>'.__('Long Term Medication').'</i></b>: '.$rowForm['longTermMedicationDetails'].'<br/>';
                    }
                    $condCount = 1;
                    try {
                        $dataConditions = array('pupilsightPersonMedicalID' => $rowForm['pupilsightPersonMedicalID']);
                        $sqlConditions = 'SELECT * FROM pupilsightPersonMedicalCondition WHERE pupilsightPersonMedicalID=:pupilsightPersonMedicalID';
                        $resultConditions = $connection2->prepare($sqlConditions);
                        $resultConditions->execute($dataConditions);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    while ($rowConditions = $resultConditions->fetch()) {
                        echo '<b><i>'.__('Condition')." $condCount</i></b> ";
                        echo ': '.__($rowConditions['name']);

                        $alert = getAlert($guid, $connection2, $rowConditions['pupilsightAlertLevelID']);
                        if ($alert != false) {
                            echo " <span style='color: #".$alert['color']."; font-weight: bold'>(".__($alert['name']).' '.__('Risk').')</span>';
                            echo '<br/>';
                            ++$condCount;
                        }
                    }
                } else {
                    echo '<i>No medical data</i>';
                }

                echo '</td>';
            }
            echo '</tr>';
        }
        if ($count == 0) {
            echo "<tr class=$rowNum>";
            echo '<td colspan=2>';
            echo __('There are no records to display.');
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
