<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs->add(__('Work Summary by Roll Group'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/report_workSummary_byRollGroup.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo '<p>';
    echo __('This report draws data from the Markbook, Planner and Behaviour modules to give an overview of student performance and work completion. It only counts Online Submission data when submission is set to compulsory.');
    echo '</p>';

    echo '<h2>';
    echo __('Choose Roll Group');
    echo '</h2>';

    $pupilsightRollGroupID = isset($_GET['pupilsightRollGroupID'])? $_GET['pupilsightRollGroupID'] : null;

    $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/report_workSummary_byRollGroup.php');

    $row = $form->addRow();
        $row->addLabel('pupilsightRollGroupID', __('Roll Group'));
        $row->addSelectRollGroup('pupilsightRollGroupID', $_SESSION[$guid]['pupilsightSchoolYearID'])->required()->selected($pupilsightRollGroupID);

    $row = $form->addRow();
        $row->addSearchSubmit($pupilsight->session, __('Clear Filters'));

    echo $form->getOutput();

    if ($pupilsightRollGroupID != '') {
        echo '<h2>';
        echo __('Report Data');
        echo '</h2>';

        try {
            $data = array('pupilsightRollGroupID' => $pupilsightRollGroupID);
            $sql = "SELECT surname, preferredName, name, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightStudentEnrolment.pupilsightRollGroupID=:pupilsightRollGroupID ORDER BY surname, preferredName";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        echo "<table cellspacing='0' style='width: 100%'>";
        echo "<tr class='head'>";
        echo '<th>';
        echo __('Student');
        echo '</th>';
        echo '<th>';
        echo __('Satisfactory');
        echo '</th>';
        echo '<th>';
        echo __('Unsatisfactory');
        echo '</th>';
        echo '<th>';
        echo __('On Time');
        echo '</th>';
        echo '<th>';
        echo __('Late');
        echo '</th>';
        echo '<th>';
        echo __('Incomplete');
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
            echo "<a href='index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=".$row['pupilsightPersonID']."&subpage=Homework'>".formatName('', $row['preferredName'], $row['surname'], 'Student', true).'</a>';
            echo '</td>';
            echo "<td style='width:15%'>";
            try {
                $dataData = array('pupilsightPersonID' => $row['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sqlData = "SELECT * FROM pupilsightMarkbookEntry JOIN pupilsightMarkbookColumn ON (pupilsightMarkbookEntry.pupilsightMarkbookColumnID=pupilsightMarkbookColumn.pupilsightMarkbookColumnID) JOIN pupilsightCourseClass ON (pupilsightMarkbookColumn.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightPersonIDStudent=:pupilsightPersonID  AND (attainmentConcern='N' OR attainmentConcern IS NULL) AND (effortConcern='N' OR effortConcern IS NULL) AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND complete='Y'";
                $resultData = $connection2->prepare($sqlData);
                $resultData->execute($dataData);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($resultData->rowCount() < 1) {
                echo '0';
            } else {
                echo $resultData->rowCount();
            }
            echo '</td>';
            echo "<td style='width:15%'>";
            //Count up unsatisfactory from markbook
            try {
                $dataData = array('pupilsightPersonID' => $row['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sqlData = "SELECT * FROM pupilsightMarkbookEntry JOIN pupilsightMarkbookColumn ON (pupilsightMarkbookEntry.pupilsightMarkbookColumnID=pupilsightMarkbookColumn.pupilsightMarkbookColumnID) JOIN pupilsightCourseClass ON (pupilsightMarkbookColumn.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightPersonIDStudent=:pupilsightPersonID AND (attainmentConcern='Y' OR effortConcern='Y') AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND complete='Y'";
                $resultData = $connection2->prepare($sqlData);
                $resultData->execute($dataData);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            $dataData2 = array();
            $sqlWhere = ' AND (';
            $countWhere = 0;
            while ($rowData = $resultData->fetch()) {
                if ($rowData['pupilsightPlannerEntryID'] != '') {
                    if ($countWhere > 0) {
                        $sqlWhere .= ' AND ';
                    }
                    $dataData2['data2'.$rowData['pupilsightPlannerEntryID']] = $rowData['pupilsightPlannerEntryID'];
                    $sqlWhere .= ' NOT pupilsightBehaviour.pupilsightPlannerEntryID=:data2'.$rowData['pupilsightPlannerEntryID'];
                    ++$countWhere;
                }
            }
            if ($countWhere > 0) {
                $sqlWhere .= ' OR pupilsightBehaviour.pupilsightPlannerEntryID IS NULL';
            }
            $sqlWhere .= ')';
            if ($sqlWhere == ' AND ()') {
                $sqlWhere = '';
            }

			//Count up unsatisfactory from behaviour, counting out $sqlWhere
			try {
				$dataData2['pupilsightPersonID'] = $row['pupilsightPersonID'];
				$dataData2['pupilsightSchoolYearID'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
				$sqlData2 = "SELECT * FROM pupilsightBehaviour WHERE pupilsightBehaviour.pupilsightPersonID=:pupilsightPersonID AND type='Negative' AND (descriptor='Classwork - Unacceptable' OR descriptor='Homework - Unacceptable') AND pupilsightSchoolYearID=:pupilsightSchoolYearID $sqlWhere";
				$resultData2 = $connection2->prepare($sqlData2);
				$resultData2->execute($dataData2);
			} catch (PDOException $e) {
				echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
			}

            if (($resultData->rowCount() + $resultData2->rowCount()) < 1) {
                echo '0';
            } else {
                echo $resultData->rowCount() + $resultData2->rowCount();
            }
            echo '</td>';


            echo "<td style='width:15%'>";
			//Count up on time in planner
            try {
				$dataData['pupilsightPersonID'] = $row['pupilsightPersonID'];
				$dataData['pupilsightSchoolYearID'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
				$sqlData = "SELECT DISTINCT pupilsightPlannerEntryHomework.pupilsightPlannerEntryID FROM pupilsightPlannerEntryHomework JOIN pupilsightPlannerEntry ON (pupilsightPlannerEntryHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID) JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightPlannerEntryHomework.pupilsightPersonID=:pupilsightPersonID AND status='On Time' AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND homeworkSubmissionRequired='Compulsory'";
				$resultData = $connection2->prepare($sqlData);
				$resultData->execute($dataData);
			} catch (PDOException $e) {
				echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
			}

			//Print out total on times
			if (($resultData->rowCount() < 1)) {
				echo '0';
			} else {
				echo $resultData->rowCount();
			}
            echo '</td>';




            echo "<td style='width:15%'>";
			//Count up lates in markbook
			try {
				$dataData = array('pupilsightPersonID' => $row['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
				$sqlData = "SELECT DISTINCT * FROM pupilsightMarkbookEntry JOIN pupilsightMarkbookColumn ON (pupilsightMarkbookEntry.pupilsightMarkbookColumnID=pupilsightMarkbookColumn.pupilsightMarkbookColumnID) JOIN pupilsightCourseClass ON (pupilsightMarkbookColumn.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightPersonIDStudent=:pupilsightPersonID AND (attainmentValue='Late' OR effortValue='Late') AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND complete='Y'";
				$resultData = $connection2->prepare($sqlData);
				$resultData->execute($dataData);
			} catch (PDOException $e) {
				echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
			}

            $dataData2 = array();
            $dataData3 = array();
            $sqlWhere = '';
            $sqlWhere2 = ' AND (';
            $countWhere = 0;
            while ($rowData = $resultData->fetch()) {
                $dataData2['data2'.$rowData['pupilsightCourseClassID']] = $rowData['pupilsightCourseClassID'];
                $sqlWhere .= ' AND NOT pupilsightPlannerEntry.pupilsightCourseClassID=:data2'.$rowData['pupilsightCourseClassID'];
                if ($rowData['pupilsightPlannerEntryID'] != '') {
                    if ($countWhere > 0) {
                        $sqlWhere2 .= ' AND ';
                    }
                    $sqlWhere2 .= ' NOT pupilsightBehaviour.pupilsightPlannerEntryID='.$rowData['pupilsightPlannerEntryID'];
                    ++$countWhere;
                }
            }
            if ($countWhere > 0) {
                $sqlWhere2 .= ' OR pupilsightBehaviour.pupilsightPlannerEntryID IS NULL';
            }
            $sqlWhere2 .= ')';
            if ($sqlWhere2 == ' AND ()') {
                $sqlWhere2 = '';
            }

			//Count up lates in planner, counting out $sqlWhere
			try {
				$dataData2['pupilsightPersonID'] = $row['pupilsightPersonID'];
				$dataData2['pupilsightSchoolYearID'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
				$sqlData2 = "SELECT DISTINCT pupilsightPlannerEntryHomework.pupilsightPlannerEntryID FROM pupilsightPlannerEntryHomework JOIN pupilsightPlannerEntry ON (pupilsightPlannerEntryHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID) JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightPlannerEntryHomework.pupilsightPersonID=:pupilsightPersonID AND status='Late' AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND homeworkSubmissionRequired='Compulsory' $sqlWhere";
				$resultData2 = $connection2->prepare($sqlData2);
				$resultData2->execute($dataData2);
			} catch (PDOException $e) {
				echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
			}

            $sqlWhere3 = ' AND (';
            $countWhere = 0;
            while ($rowData2 = $resultData2->fetch()) {
                if ($rowData2['pupilsightPlannerEntryID'] != '') {
                    if ($countWhere > 0) {
                        $sqlWhere3 .= ' AND ';
                    }
                    $dataData3['data3'.$rowData2['pupilsightPlannerEntryID']] = $rowData2['pupilsightPlannerEntryID'];
                    $sqlWhere3 .= ' NOT pupilsightBehaviour.pupilsightPlannerEntryID=:data3'.$rowData2['pupilsightPlannerEntryID'];
                    ++$countWhere;
                }
            }
            if ($countWhere > 0) {
                $sqlWhere3 .= ' OR pupilsightBehaviour.pupilsightPlannerEntryID IS NULL';
            }
            $sqlWhere3 .= ')';
            if ($sqlWhere3 == ' AND ()') {
                $sqlWhere3 = '';
            }

			//Count up lates from behaviour, counting out $sqlWhere2 and $sqlWhere3
			try {
				$dataData3['pupilsightPersonID'] = $row['pupilsightPersonID'];
				$dataData3['pupilsightSchoolYearID'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
				$sqlData3 = "SELECT * FROM pupilsightBehaviour WHERE pupilsightBehaviour.pupilsightPersonID=:pupilsightPersonID AND type='Negative' AND (descriptor='Classwork - Late' OR descriptor='Homework - Late') AND pupilsightSchoolYearID=:pupilsightSchoolYearID $sqlWhere2 $sqlWhere3";
				$resultData3 = $connection2->prepare($sqlData3);
				$resultData3->execute($dataData3);
			} catch (PDOException $e) {
				echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
			}
			//Print out total late
			if (($resultData->rowCount() + $resultData2->rowCount() + $resultData3->rowCount()) < 1) {
				echo '0';
			} else {
				echo $resultData->rowCount() + $resultData2->rowCount() + $resultData3->rowCount();
			}
            echo '</td>';
            echo "<td style='width:15%'>";
			//Count up incompletes in markbook
			try {
				$dataData = array('pupilsightPersonID' => $row['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
				$sqlData = "SELECT * FROM pupilsightMarkbookEntry JOIN pupilsightMarkbookColumn ON (pupilsightMarkbookEntry.pupilsightMarkbookColumnID=pupilsightMarkbookColumn.pupilsightMarkbookColumnID) JOIN pupilsightCourseClass ON (pupilsightMarkbookColumn.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightPersonIDStudent=:pupilsightPersonID AND (attainmentValue='Incomplete' OR effortValue='Incomplete') AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND complete='Y'";
				$resultData = $connection2->prepare($sqlData);
				$resultData->execute($dataData);
			} catch (PDOException $e) {
				echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
			}

            $dataData2 = array();
            $dataData3 = array();
            $dataData4 = array();
            $sqlWhere = '';
            $sqlWhere2 = ' AND (';
            $countWhere = 0;
            while ($rowData = $resultData->fetch()) {
                $dataData2['data2'.$rowData['pupilsightCourseClassID']] = $rowData['pupilsightCourseClassID'];
                $sqlWhere .= ' AND NOT pupilsightPlannerEntry.pupilsightCourseClassID=:data2'.$rowData['pupilsightCourseClassID'];
                if ($rowData['pupilsightPlannerEntryID'] != '') {
                    if ($countWhere > 0) {
                        $sqlWhere2 .= ' AND ';
                    }
                    $dataData4['data4'.$rowData['pupilsightPlannerEntryID']] = $rowData['pupilsightPlannerEntryID'];
                    $sqlWhere2 .= ' NOT pupilsightBehaviour.pupilsightPlannerEntryID=:data4'.$rowData['pupilsightPlannerEntryID'];
                    ++$countWhere;
                }
            }
            if ($countWhere > 0) {
                $sqlWhere2 .= ' OR pupilsightBehaviour.pupilsightPlannerEntryID IS NULL';
            }
            $sqlWhere2 .= ')';
            if ($sqlWhere2 == ' AND ()') {
                $sqlWhere2 = '';
            }

			//Count up incompletes in planner, counting out $sqlWhere
			try {
				$dataData2['pupilsightPersonID'] = $row['pupilsightPersonID'];
				$dataData2['pupilsightSchoolYearID'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
				$dataData2['homeworkDueDateTime'] = date('Y-m-d H:i:s');
				$dataData2['date'] = date('Y-m-d');
				$sqlData2 = "SELECT * FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND homeworkSubmission='Y' AND homeworkDueDateTime<:homeworkDueDateTime AND homeworkSubmissionRequired='Compulsory' AND date<=:date $sqlWhere";
				$resultData2 = $connection2->prepare($sqlData2);
				$resultData2->execute($dataData2);
			} catch (PDOException $e) {
				echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
			}

            $countIncomplete = 0;
            $sqlWhere3 = ' AND (';
            $countWhere = 0;
            while ($rowData2 = $resultData2->fetch()) {
                try {
                    $dataData3['pupilsightPersonID'] = $row['pupilsightPersonID'];
                    $dataData3['pupilsightPlannerEntryID'] = $rowData2['pupilsightPlannerEntryID'];
                    $sqlData3 = "SELECT DISTINCT pupilsightPlannerEntryHomework.pupilsightPlannerEntryID FROM pupilsightPlannerEntryHomework WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID AND version='Final'";
                    $resultData3 = $connection2->prepare($sqlData3);
                    $resultData3->execute($dataData3);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($resultData3->rowCount() < 1) {
                    ++$countIncomplete;
                }
                if ($rowData2['pupilsightPlannerEntryID'] != '') {
                    if ($countWhere > 0) {
                        $sqlWhere3 .= ' AND ';
                    }
                    $dataData4['data4'.$rowData2['pupilsightPlannerEntryID']] = $rowData2['pupilsightPlannerEntryID'];
                    $sqlWhere3 .= ' NOT pupilsightBehaviour.pupilsightPlannerEntryID=:data4'.$rowData2['pupilsightPlannerEntryID'];
                    ++$countWhere;
                }
            }
            if ($countWhere > 0) {
                $sqlWhere3 .= ' OR pupilsightBehaviour.pupilsightPlannerEntryID IS NULL';
            }
            $sqlWhere3 .= ')';
            if ($sqlWhere3 == ' AND ()') {
                $sqlWhere3 = '';
            }

			//Count up incompletes from behaviour, counting out $sqlWhere2 and $sqlWhere3
			try {
				$dataData4['pupilsightPersonID'] = $row['pupilsightPersonID'];
				$dataData4['pupilsightSchoolYearID'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
				$sqlData4 = "SELECT * FROM pupilsightBehaviour WHERE pupilsightBehaviour.pupilsightPersonID=:pupilsightPersonID AND type='Negative' AND (descriptor='Classwork - Incomplete' OR descriptor='Homework - Incomplete') AND pupilsightSchoolYearID=:pupilsightSchoolYearID $sqlWhere2 $sqlWhere3";
				$resultData4 = $connection2->prepare($sqlData4);
				$resultData4->execute($dataData4);
			} catch (PDOException $e) {
				echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
			}

			//Print out total lates
			if (($resultData->rowCount() + $countIncomplete + $resultData4->rowCount() < 1)) {
				echo '0';
			} else {
				echo $resultData->rowCount() + $countIncomplete + $resultData4->rowCount();
			}
            echo '</td>';
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
?>
