<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/report_activityChoices_byStudent.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Activity Choices By Student'));
    
    echo '<h2>';
    echo __('Choose Student');
    echo '</h2>';

    $pupilsightPersonID = null;
    if (isset($_GET['pupilsightPersonID'])) {
        $pupilsightPersonID = $_GET['pupilsightPersonID'];
    }

    $form = Form::create('action',  $_SESSION[$guid]['absoluteURL']."/index.php", "get");

    $form->setClass('noIntBorder fullWidth');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/report_activityChoices_byStudent.php");

    $row = $form->addRow();
        $row->addLabel('pupilsightPersonID', __('Student'));
        $row->addSelectStudent('pupilsightPersonID', $_SESSION[$guid]['pupilsightSchoolYearID'], array("allStudents" => false, "byName" => true, "byRoll" => true))->required()->placeholder()->selected($pupilsightPersonID);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    if ($pupilsightPersonID != '') {
        $output = '';
        echo '<h2>';
        echo __('Report Data');
        echo '</h2>';

        try {
            $dataYears = array('pupilsightPersonID' => $pupilsightPersonID);
            $sqlYears = 'SELECT * FROM pupilsightStudentEnrolment JOIN pupilsightSchoolYear ON (pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE pupilsightPersonID=:pupilsightPersonID ORDER BY sequenceNumber DESC';
            $resultYears = $connection2->prepare($sqlYears);
            $resultYears->execute($dataYears);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
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
                    $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sql = "SELECT pupilsightPerson.pupilsightPersonID, surname, preferredName, name FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightStudentEnrolment.pupilsightPersonID=:pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY surname, preferredName";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($result->rowCount() != 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('The specified record does not exist.');
                    echo '</div>';
                } else {
                    $dateType = getSettingByScope($connection2, 'Activities', 'dateType');
                    if ($dateType == 'Term') {
                        $maxPerTerm = getSettingByScope($connection2, 'Activities', 'maxPerTerm');
                    }

                    try {
                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $rowYears['pupilsightSchoolYearID']);
                        $sql = "SELECT pupilsightActivity.*, pupilsightActivityStudent.status, NULL AS role FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivity.pupilsightActivityID=pupilsightActivityStudent.pupilsightActivityID) WHERE pupilsightActivityStudent.pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' ORDER BY name";
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
                        echo "<table cellspacing='0' style='width: 100%'>";
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
                            echo  __('Term');
                        } else {
                            echo  __('Dates');
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
                            echo '<td>';
                            echo $row['name'];
                            echo '</td>';
                            if ($options != '') {
                                echo '<td>';
                                echo trim($row['type']);
                                echo '</td>';
                            }
                            echo '<td>';
                            if ($dateType != 'Date') {
                                $terms = getTerms($connection2, $_SESSION[$guid]['pupilsightSchoolYearID'], true);
                                $termList = '';
                                for ($i = 0; $i < count($terms); $i = $i + 2) {
                                    if (is_numeric(strpos($row['pupilsightSchoolYearTermIDList'], $terms[$i]))) {
                                        $termList .= $terms[($i + 1)].'<br/>';
                                    }
                                }
                                echo $termList;
                            } else {
                                if (substr($row['programStart'], 0, 4) == substr($row['programEnd'], 0, 4)) {
                                    if (substr($row['programStart'], 5, 2) == substr($row['programEnd'], 5, 2)) {
                                        echo date('F', mktime(0, 0, 0, substr($row['programStart'], 5, 2))).' '.substr($row['programStart'], 0, 4);
                                    } else {
                                        echo date('F', mktime(0, 0, 0, substr($row['programStart'], 5, 2))).' - '.date('F', mktime(0, 0, 0, substr($row['programEnd'], 5, 2))).'<br/>'.substr($row['programStart'], 0, 4);
                                    }
                                } else {
                                    echo date('F', mktime(0, 0, 0, substr($row['programStart'], 5, 2))).' '.substr($row['programStart'], 0, 4).' -<br/>'.date('F', mktime(0, 0, 0, substr($row['programEnd'], 5, 2))).' '.substr($row['programEnd'], 0, 4);
                                }
                            }
                            echo '</td>';
                            echo '<td>';
                            if ($row['status'] != '') {
                                echo $row['status'];
                            } else {
                                echo '<i>'.__('NA').'</i>';
                            }
                            echo '</td>';
                            echo '<td>';
                            echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/activities_view_full.php&pupilsightActivityID='.$row['pupilsightActivityID']."&width=1000&height=550'><img title='".__('View Details')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a> ";
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    }
                }
            }
        }
    }
}
?>
