<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

//Get alternative header names
$attainmentAlternativeName = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeName');
$attainmentAlternativeNameAbrev = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeNameAbrev');
$effortAlternativeName = getSettingByScope($connection2, 'Markbook', 'effortAlternativeName');
$effortAlternativeNameAbrev = getSettingByScope($connection2, 'Markbook', 'effortAlternativeNameAbrev');

if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/internalAssessment_write.php') == false) {
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
        $alert = getAlert($guid, $connection2, 002);

        //Proceed!
        //Get class variable
        $pupilsightCourseClassID = null;
        if (isset($_GET['pupilsightCourseClassID'])) {
            $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
        }
        if ($pupilsightCourseClassID == '') {
            try {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID FROM pupilsightCourse, pupilsightCourseClass, pupilsightCourseClassPerson WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY course, class';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            if ($result->rowCount() > 0) {
                $row = $result->fetch();
                $pupilsightCourseClassID = $row['pupilsightCourseClassID'];
            }
        }
        if ($pupilsightCourseClassID == '') {
            $page->breadcrumbs->add(__('Write Internal Assessments'));
            echo "<div class='alert alert-warning'>";
            echo 'Use the class listing on the right to choose a Internal Assessment to write.';
            echo '</div>';
        }
        //Check existence of and access to this class.
        else {
            try {
                if ($highestAction == 'Write Internal Assessments_all') {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourse.name AS courseName, pupilsightCourseClass.nameShort AS class, pupilsightYearGroupIDList FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID';
                } else {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourse.name AS courseName, pupilsightCourseClass.nameShort AS class, pupilsightYearGroupIDList FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonID=:pupilsightPersonID AND role='Teacher'";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            if ($result->rowCount() != 1) {
                $page->breadcrumbs->add(__('Write Internal Assessments'));
                echo "<div class='alert alert-danger'>";
                echo __('The specified record does not exist or you do not have access to it.');
                echo '</div>';
            } else {
                $row = $result->fetch();
                $courseName = $row['courseName'] ?? '';
                $pupilsightYearGroupIDList = $row['pupilsightYearGroupIDList'] ?? '';
                $page->breadcrumbs->add(__('Write {courseClass} Internal Assessments', ['courseClass' => $row['course'].'.'.$row['class']]));

                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, array('success0' => 'Your request was completed successfully.'));
                }

                //Get teacher list
                $teaching = false;
                try {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = "SELECT pupilsightPerson.pupilsightPersonID, title, surname, preferredName FROM pupilsightCourseClassPerson JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE role='Teacher' AND pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY surname, preferredName";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($result->rowCount() > 0) {
                    echo "<h3 style='margin-top: 0px'>";
                    echo __('Teachers');
                    echo '</h3>';
                    echo '<ul>';
                    while ($row = $result->fetch()) {
                        echo '<li>'.Format::name($row['title'], $row['preferredName'], $row['surname'], 'Staff').'</li>';
                        if ($row['pupilsightPersonID'] == $_SESSION[$guid]['pupilsightPersonID']) {
                            $teaching = true;
                        }
                    }
                    echo '</ul>';
                }

                //Print marks
                echo '<h3>';
                echo __('Marks');
                echo '</h3>';

                //Count number of columns
                try {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = 'SELECT * FROM pupilsightInternalAssessmentColumn WHERE pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY complete, completeDate DESC';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                $columns = $result->rowCount();
                if ($columns < 1) {
                    echo "<div class='alert alert-warning'>";
                    echo __('There are no records to display.');
                    echo '</div>';
                } else {
                    $x = null;
                    if (isset($_GET['page'])) {
                        $x = $_GET['page'];
                    }
                    if ($x == '') {
                        $x = 0;
                    }
                    $columnsPerPage = 3;
                    $columnsThisPage = 3;

                    if ($columns < 1) {
                        echo "<div class='alert alert-warning'>";
                        echo __('There are no records to display.');
                        echo '</div>';
                    } else {
                        if ($columns < 3) {
                            $columnsThisPage = $columns;
                        }
                        if ($columns - ($x * $columnsPerPage) < 3) {
                            $columnsThisPage = $columns - ($x * $columnsPerPage);
                        }
                        try {
                            $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                            $sql = 'SELECT * FROM pupilsightInternalAssessmentColumn WHERE pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY complete, completeDate DESC LIMIT '.($x * $columnsPerPage).', '.$columnsPerPage;
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        //Work out details for external assessment display
                        $externalAssessment = false;
                        if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/externalAssessment_details.php')) {
                            $pupilsightYearGroupIDListArray = (explode(',', $pupilsightYearGroupIDList));
                            if (count($pupilsightYearGroupIDListArray) == 1) {
                                $primaryExternalAssessmentByYearGroup = unserialize(getSettingByScope($connection2, 'School Admin', 'primaryExternalAssessmentByYearGroup'));
                                if ($primaryExternalAssessmentByYearGroup[$pupilsightYearGroupIDListArray[0]] != '' and $primaryExternalAssessmentByYearGroup[$pupilsightYearGroupIDListArray[0]] != '-') {
                                    $pupilsightExternalAssessmentID = substr($primaryExternalAssessmentByYearGroup[$pupilsightYearGroupIDListArray[0]], 0, strpos($primaryExternalAssessmentByYearGroup[$pupilsightYearGroupIDListArray[0]], '-'));
                                    $pupilsightExternalAssessmentIDCategory = substr($primaryExternalAssessmentByYearGroup[$pupilsightYearGroupIDListArray[0]], (strpos($primaryExternalAssessmentByYearGroup[$pupilsightYearGroupIDListArray[0]], '-') + 1));

                                    try {
                                        $dataExternalAssessment = array('pupilsightExternalAssessmentID' => $pupilsightExternalAssessmentID, 'category' => $pupilsightExternalAssessmentIDCategory);
                                        $courseNameTokens = explode(' ', $courseName);
                                        $courseWhere = ' AND (';
                                        $whereCount = 1;
                                        foreach ($courseNameTokens as $courseNameToken) {
                                            if (strlen($courseNameToken) > 3) {
                                                $dataExternalAssessment['token'.$whereCount] = '%'.$courseNameToken.'%';
                                                $courseWhere .= "pupilsightExternalAssessmentField.name LIKE :token$whereCount OR ";
                                                ++$whereCount;
                                            }
                                        }
                                        if ($whereCount < 1) {
                                            $courseWhere = '';
                                        } else {
                                            $courseWhere = substr($courseWhere, 0, -4).')';
                                        }
                                        $sqlExternalAssessment = "SELECT pupilsightExternalAssessment.name AS assessment, pupilsightExternalAssessmentField.name, pupilsightExternalAssessmentFieldID, category, pupilsightScale.name AS scale
                    						FROM pupilsightExternalAssessmentField
                    							JOIN pupilsightExternalAssessment ON (pupilsightExternalAssessmentField.pupilsightExternalAssessmentID=pupilsightExternalAssessment.pupilsightExternalAssessmentID)
                    							JOIN pupilsightScale ON (pupilsightExternalAssessmentField.pupilsightScaleID=pupilsightScale.pupilsightScaleID)
                    						WHERE pupilsightExternalAssessmentField.pupilsightExternalAssessmentID=:pupilsightExternalAssessmentID
                    							AND category=:category $courseWhere
                    						ORDER BY name
                    						LIMIT 1";
                                        $resultExternalAssessment = $connection2->prepare($sqlExternalAssessment);
                                        $resultExternalAssessment->execute($dataExternalAssessment);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }
                                    if ($resultExternalAssessment->rowCount() >= 1) {
                                        $rowExternalAssessment = $resultExternalAssessment->fetch();
                                        $externalAssessment = true;
                                        $externalAssessmentFields = array();
                                        $externalAssessmentFields[0] = $rowExternalAssessment['pupilsightExternalAssessmentFieldID'];
                                        $externalAssessmentFields[1] = $rowExternalAssessment['name'];
                                        $externalAssessmentFields[2] = $rowExternalAssessment['assessment'];
                                        $externalAssessmentFields[3] = $rowExternalAssessment['category'];
                                        $externalAssessmentFields[4] = $rowExternalAssessment['name'];
                                    }
                                }
                            }
                        }

                        //Print table header
                        echo '<p>';
                        echo __('To see more detail on an item (such as a comment or a grade), hover your mouse over it.');
                        if ($externalAssessment == true) {
                            echo ' '.__('The Baseline column is populated based on student performance in external assessments, and can be used as a reference point for the grades in the Internal Assessment.');
                        }
                        echo '</p>';

                        echo "<div class='linkTop'>";
                        echo "<div style='padding-top: 12px; margin-left: 10px; float: right'>";
                        if ($x <= 0) {
                            echo __('Newer');
                        } else {
                            echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Formal Assessment/internalAssessment_write.php&pupilsightCourseClassID=$pupilsightCourseClassID&page=".($x - 1)."'>".__('Newer').'</a>';
                        }
                        echo ' | ';
                        if ((($x + 1) * $columnsPerPage) >= $columns) {
                            echo __('Older');
                        } else {
                            echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Formal Assessment/internalAssessment_write.php&pupilsightCourseClassID=$pupilsightCourseClassID&page=".($x + 1)."'>".__('Older').'</a>';
                        }
                        echo '</div>';
                        echo '</div>';

                        echo "<table class='mini' cellspacing='0' style='width: 100%; margin-top: 0px'>";
                        echo "<tr class='head' style='height: 120px'>";
                        echo "<th style='width: 150px; max-width: 200px'rowspan=2>";
                        echo __('Student');
                        echo '</th>';

						//Show Baseline data header
						if ($externalAssessment == true) {
							echo "<th rowspan=2 style='width: 20px'>";
							$title = __($externalAssessmentFields[2]).' | ';
							$title .= __(substr($externalAssessmentFields[3], (strpos($externalAssessmentFields[3], '_') + 1))).' | ';
							$title .= __($externalAssessmentFields[1]);
                            $title .= ' | '.$externalAssessmentFields[4].' '.__('Scale').' ';

                            echo "<div style='-webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg); -ms-transform: rotate(-90deg); -o-transform: rotate(-90deg); transform: rotate(-90deg);' title='$title'>";
							echo __('Baseline').'<br/>';
							echo '</div>';
							echo '</th>';
                        }

                        $columnID = array();
                        $attainmentID = array();
                        $effortID = array();
                        for ($i = 0; $i < $columnsThisPage; ++$i) {
                            $row = $result->fetch();
                            if ($row === false) {
                                $columnID[$i] = false;
                            } else {
                                $columnID[$i] = $row['pupilsightInternalAssessmentColumnID'];
                                $attainmentOn[$i] = $row['attainment'];
                                $attainmentID[$i] = $row['pupilsightScaleIDAttainment'];
                                $effortOn[$i] = $row['effort'];
                                $effortID[$i] = $row['pupilsightScaleIDEffort'];
                                $comment[$i] = $row['comment'];
                                $uploadedResponse[$i] = $row['uploadedResponse'];
                                $submission[$i] = false;
                            }

                            //Column count
                            $span = 0;
                            $contents = true;
                            if ($attainmentOn[$i] == 'Y' and $attainmentID[$i] != '') {
                                ++$span;
                            }
                            if ($effortOn[$i] == 'Y' and $effortID[$i] != '') {
                                ++$span;
                            }
                            if ($comment[$i] == 'Y') {
                                ++$span;
                            }
                            if ($uploadedResponse[$i] == 'Y') {
                                ++$span;
                            }
                            if ($span == 0) {
                                $contents = false;
                            }

                            echo "<th style='text-align: center; min-width: 140px' colspan=$span>";
                            echo "<span title='".htmlPrep($row['description'])."'>".$row['name'].'</span><br/>';
                            echo "<span style='font-size: 90%; font-style: italic; font-weight: normal'>";
                            if ($row['completeDate'] != '') {
                                echo __('Marked on').' '.dateConvertBack($guid, $row['completeDate']).'<br/>';
                            } else {
                                echo __('Unmarked').'<br/>';
                            }
                            echo $row['type'];
                            if ($row['attachment'] != '' and file_exists($_SESSION[$guid]['absolutePath'].'/'.$row['attachment'])) {
                                echo " | <a 'title='".__('Download more information')."' href='".$_SESSION[$guid]['absoluteURL'].'/'.$row['attachment']."'>More info</a>";
                            }
                            echo '</span><br/>';
                            if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_edit.php')) {
                                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Formal Assessment/internalAssessment_write_data.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightInternalAssessmentColumnID=".$row['pupilsightInternalAssessmentColumnID']."'><img style='margin-top: 3px' title='".__('Enter Data')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/markbook.png'/></a> ";
                            }
                            echo '</th>';
                        }
                        echo '</tr>';

                        echo "<tr class='head'>";
                        for ($i = 0; $i < $columnsThisPage; ++$i) {
                            if ($columnID[$i] == false or $contents == false) {
                                echo "<th style='text-align: center' colspan=$span>";

                                echo '</th>';
                            } else {
                                $leftBorder = false;
                                if ($attainmentOn[$i] == 'Y' and $attainmentID[$i] != '') {
                                    $leftBorder = true;
                                    echo "<th style='border-left: 2px solid #666; text-align: center; width: 40px'>";
                                    try {
                                        $dataScale = array('pupilsightScaleID' => $attainmentID[$i]);
                                        $sqlScale = 'SELECT * FROM pupilsightScale WHERE pupilsightScaleID=:pupilsightScaleID';
                                        $resultScale = $connection2->prepare($sqlScale);
                                        $resultScale->execute($dataScale);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }
                                    $scale = '';
                                    if ($resultScale->rowCount() == 1) {
                                        $rowScale = $resultScale->fetch();
                                        $scale = ' - '.$rowScale['name'];
                                        if ($rowScale['usage'] != '') {
                                            $scale = $scale.': '.$rowScale['usage'];
                                        }
                                    }
                                    if ($attainmentAlternativeName != '' and $attainmentAlternativeNameAbrev != '') {
                                        echo "<span title='".$attainmentAlternativeName.htmlPrep($scale)."'>".$attainmentAlternativeNameAbrev.'</span>';
                                    } else {
                                        echo "<span title='".__('Attainment').htmlPrep($scale)."'>".__('Att').'</span>';
                                    }
                                    echo '</th>';
                                }

                                if ($effortOn[$i] == 'Y' and $effortID[$i] != '') {
                                    $leftBorderStyle = '';
                                    if ($leftBorder == false) {
                                        $leftBorder = true;
                                        $leftBorderStyle = 'border-left: 2px solid #666;';
                                    }
                                    echo "<th style='$leftBorderStyle text-align: center; width: 40px'>";
                                    try {
                                        $dataScale = array('pupilsightScaleID' => $effortID[$i]);
                                        $sqlScale = 'SELECT * FROM pupilsightScale WHERE pupilsightScaleID=:pupilsightScaleID';
                                        $resultScale = $connection2->prepare($sqlScale);
                                        $resultScale->execute($dataScale);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }
                                    $scale = '';
                                    if ($resultScale->rowCount() == 1) {
                                        $rowScale = $resultScale->fetch();
                                        $scale = ' - '.$rowScale['name'];
                                        if ($rowScale['usage'] != '') {
                                            $scale = $scale.': '.$rowScale['usage'];
                                        }
                                    }
                                    if ($effortAlternativeName != '' and $effortAlternativeNameAbrev != '') {
                                        echo "<span title='".$effortAlternativeName.htmlPrep($scale)."'>".$effortAlternativeNameAbrev.'</span>';
                                    } else {
                                        echo "<span title='".__('Effort').htmlPrep($scale)."'>".__('Eff').'</span>';
                                    }
                                    echo '</th>';
                                }

                                if ($comment[$i] == 'Y') {
                                    $leftBorderStyle = '';
                                    if ($leftBorder == false) {
                                        $leftBorder = true;
                                        $leftBorderStyle = 'border-left: 2px solid #666;';
                                    }
                                    echo "<th style='$leftBorderStyle text-align: center; width: 80px'>";
                                    echo "<span title='".__('Comment')."'>".__('Com').'</span>';
                                    echo '</th>';
                                }
                                if ($uploadedResponse[$i] == 'Y') {
                                    $leftBorderStyle = '';
                                    if ($leftBorder == false) {
                                        $leftBorder = true;
                                        $leftBorderStyle = 'border-left: 2px solid #666;';
                                    }
                                    echo "<th style='$leftBorderStyle text-align: center; width: 30px'>";
                                    echo "<span title='".__('Uploaded Response')."'>".__('Upl').'</span>';
                                    echo '</th>';
                                }
                            }
                        }
                        echo '</tr>';

                        $count = 0;
                        $rowNum = 'odd';

                        try {
                            $dataStudents = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                            $sqlStudents = "SELECT title, surname, preferredName, pupilsightPerson.pupilsightPersonID, dateStart FROM pupilsightCourseClassPerson JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE role='Student' AND pupilsightCourseClassID=:pupilsightCourseClassID AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightCourseClassPerson.reportable='Y' ORDER BY surname, preferredName";
                            $resultStudents = $connection2->prepare($sqlStudents);
                            $resultStudents->execute($dataStudents);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }
                        if ($resultStudents->rowCount() < 1) {
                            echo '<tr>';
                            echo '<td colspan='.($columns + 1).'>';
                            echo '<i>'.__('There are no records to display.').'</i>';
                            echo '</td>';
                            echo '</tr>';
                        } else {
                            while ($rowStudents = $resultStudents->fetch()) {
                                if ($count % 2 == 0) {
                                    $rowNum = 'even';
                                } else {
                                    $rowNum = 'odd';
                                }
                                ++$count;

                                //COLOR ROW BY STATUS!
                                echo "<tr class=$rowNum>";
                                echo '<td>';
                                echo "<div style='padding: 2px 0px'><b><a href='index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=".$rowStudents['pupilsightPersonID'].'&subpage=Internal Assessment#'.$pupilsightCourseClassID."'>".Format::name('', $rowStudents['preferredName'], $rowStudents['surname'], 'Student', true).'</a><br/></div>';
                                echo '</td>';

                                if ($externalAssessment == true) {
                                    echo "<td style='text-align: center'>";
                                    try {
                                        $dataEntry = array('pupilsightPersonID' => $rowStudents['pupilsightPersonID'], 'pupilsightExternalAssessmentFieldID' => $externalAssessmentFields[0]);
                                        $sqlEntry = "SELECT pupilsightScaleGrade.value, pupilsightScaleGrade.descriptor, pupilsightExternalAssessmentStudent.date
                                            FROM pupilsightExternalAssessmentStudentEntry
                                                JOIN pupilsightExternalAssessmentStudent ON (pupilsightExternalAssessmentStudentEntry.pupilsightExternalAssessmentStudentID=pupilsightExternalAssessmentStudent.pupilsightExternalAssessmentStudentID)
                                                JOIN pupilsightScaleGrade ON (pupilsightExternalAssessmentStudentEntry.pupilsightScaleGradeID=pupilsightScaleGrade.pupilsightScaleGradeID)
                                            WHERE pupilsightPersonID=:pupilsightPersonID
                                                AND pupilsightExternalAssessmentFieldID=:pupilsightExternalAssessmentFieldID
                                                AND NOT pupilsightExternalAssessmentStudentEntry.pupilsightScaleGradeID=''
                                                ORDER BY date DESC";
                                        $resultEntry = $connection2->prepare($sqlEntry);
                                        $resultEntry->execute($dataEntry);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }
                                    if ($resultEntry->rowCount() >= 1) {
                                        $rowEntry = $resultEntry->fetch();
                                        echo "<a title='".__($rowEntry['descriptor']).' | '.__('Test taken on').' '.dateConvertBack($guid, $rowEntry['date'])."' href='index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=".$rowStudents['pupilsightPersonID']."&subpage=External Assessment'>".__($rowEntry['value']).'</a>';
                                    }
                                    echo '</td>';
                                }

                                for ($i = 0; $i < $columnsThisPage; ++$i) {
                                    $row = $result->fetch();
                                    try {
                                        $dataEntry = array('pupilsightInternalAssessmentColumnID' => $columnID[($i)], 'pupilsightPersonIDStudent' => $rowStudents['pupilsightPersonID']);
                                        $sqlEntry = 'SELECT * FROM pupilsightInternalAssessmentEntry WHERE pupilsightInternalAssessmentColumnID=:pupilsightInternalAssessmentColumnID AND pupilsightPersonIDStudent=:pupilsightPersonIDStudent';
                                        $resultEntry = $connection2->prepare($sqlEntry);
                                        $resultEntry->execute($dataEntry);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }
                                    if ($resultEntry->rowCount() == 1) {
                                        $rowEntry = $resultEntry->fetch();
                                        $leftBorder = false;

                                        if ($attainmentOn[$i] == 'Y' and $attainmentID[$i] != '') {
                                            $leftBorder = true;
                                            echo "<td style='border-left: 2px solid #666; text-align: center'>";
                                            if ($attainmentID[$i] != '') {
                                                $styleAttainment = '';
                                                $attainment = '';
                                                if ($rowEntry['attainmentValue'] != '') {
                                                    $attainment = __($rowEntry['attainmentValue']);
                                                }
                                                if ($rowEntry['attainmentValue'] == 'Complete') {
                                                    $attainment = __('Com');
                                                } elseif ($rowEntry['attainmentValue'] == 'Incomplete') {
                                                    $attainment = __('Inc');
                                                }
                                                echo "<div $styleAttainment title='".htmlPrep($rowEntry['attainmentDescriptor'])."'>$attainment";
                                            }
                                            if ($attainmentID[$i] != '') {
                                                echo '</div>';
                                            }
                                            echo '</td>';
                                        }
                                        if ($effortOn[$i] == 'Y' and $effortID[$i] != '') {
                                            $leftBorderStyle = '';
                                            if ($leftBorder == false) {
                                                $leftBorder = true;
                                                $leftBorderStyle = 'border-left: 2px solid #666;';
                                            }
                                            echo "<td style='$leftBorderStyle text-align: center;'>";
                                            if ($effortID[$i] != '') {
                                                $styleEffort = '';
                                                $effort = '';
                                                if ($rowEntry['effortValue'] != '') {
                                                    $effort = __($rowEntry['effortValue']);
                                                }
                                                if ($rowEntry['effortValue'] == 'Complete') {
                                                    $effort = __('Com');
                                                } elseif ($rowEntry['effortValue'] == 'Incomplete') {
                                                    $effort = __('Inc');
                                                }
                                                echo "<div $styleEffort title='".htmlPrep($rowEntry['effortDescriptor'])."'>$effort";
                                            }
                                            if ($effortID[$i] != '') {
                                                echo '</div>';
                                            }
                                            echo '</td>';
                                        }

                                        if ($comment[$i] == 'Y') {
                                            $leftBorderStyle = '';
                                            if ($leftBorder == false) {
                                                $leftBorder = true;
                                                $leftBorderStyle = 'border-left: 2px solid #666;';
                                            }
                                            echo "<td style='$leftBorderStyle text-align: center;'>";
                                            $style = '';
                                            if ($rowEntry['comment'] != '') {
                                                if (strlen($rowEntry['comment']) < 11) {
                                                    echo htmlPrep($rowEntry['comment']);
                                                } else {
                                                    echo "<span $style title='".htmlPrep($rowEntry['comment'])."'>".substr($rowEntry['comment'], 0, 10).'...</span>';
                                                }
                                            }
                                            echo '</td>';
                                        }
                                        if ($uploadedResponse[$i] == 'Y') {
                                            $leftBorderStyle = '';
                                            if ($leftBorder == false) {
                                                $leftBorder = true;
                                                $leftBorderStyle = 'border-left: 2px solid #666;';
                                            }
                                            echo "<td style='$leftBorderStyle text-align: center;'>";
                                            if ($rowEntry['response'] != '') {
                                                echo "<a title='".__('Uploaded Response')."' href='".$_SESSION[$guid]['absoluteURL'].'/'.$rowEntry['response']."'>Up</a><br/>";
                                            }
                                        }
                                        echo '</td>';
                                    } else {
                                        $emptySpan = 0;
                                        if ($attainmentOn[$i] == 'Y' and $attainmentID[$i] != '') {
                                            ++$emptySpan;
                                        }
                                        if ($effortOn[$i] == 'Y' and $effortID[$i] != '') {
                                            ++$emptySpan;
                                        }
                                        if ($comment[$i] == 'Y') {
                                            ++$emptySpan;
                                        }
                                        if ($uploadedResponse[$i] == 'Y') {
                                            ++$emptySpan;
                                        }
                                        if ($emptySpan > 0) {
                                            echo "<td style='border-left: 2px solid #666; text-align: center' colspan=$emptySpan></td>";
                                        }
                                    }
                                    if (isset($submission[$i])) {
                                        if ($submission[$i] == true) {
                                            $leftBorderStyle = '';
                                            if ($leftBorder == false) {
                                                $leftBorder = true;
                                                $leftBorderStyle = 'border-left: 2px solid #666;';
                                            }
                                            echo "<td style='$leftBorderStyle text-align: center;'>";
                                            try {
                                                $dataWork = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID[$i], 'pupilsightPersonID' => $rowStudents['pupilsightPersonID']);
                                                $sqlWork = 'SELECT * FROM pupilsightPlannerEntryHomework WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID ORDER BY count DESC';
                                                $resultWork = $connection2->prepare($sqlWork);
                                                $resultWork->execute($dataWork);
                                            } catch (PDOException $e) {
                                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                            }
                                            if ($resultWork->rowCount() > 0) {
                                                $rowWork = $resultWork->fetch();

                                                if ($rowWork['status'] == 'Exemption') {
                                                    $linkText = __('Exe');
                                                } elseif ($rowWork['version'] == 'Final') {
                                                    $linkText = __('Fin');
                                                } else {
                                                    $linkText = __('Dra').$rowWork['count'];
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
                                                    echo "<span title='".$rowWork['version'].". $status. ".__('Submitted at').' '.substr($rowWork['timestamp'], 11, 5).' '.__('on').' '.dateConvertBack($guid, substr($rowWork['timestamp'], 0, 10))."' $style><a href='".$_SESSION[$guid]['absoluteURL'].'/'.$rowWork['location']."'>$linkText</a></span>";
                                                } elseif ($rowWork['type'] == 'Link') {
                                                    echo "<span title='".$rowWork['version'].". $status. ".__('Submitted at').' '.substr($rowWork['timestamp'], 11, 5).' '.__('on').' '.dateConvertBack($guid, substr($rowWork['timestamp'], 0, 10))."' $style><a target='_blank' href='".$rowWork['location']."'>$linkText</a></span>";
                                                } else {
                                                    echo "<span title='$status. ".__('Recorded at').' '.substr($rowWork['timestamp'], 11, 5).' '.__('on').' '.dateConvertBack($guid, substr($rowWork['timestamp'], 0, 10))."' $style>$linkText</span>";
                                                }
                                            } else {
                                                if (date('Y-m-d H:i:s') < $homeworkDueDateTime[$i]) {
                                                    echo "<span title='".__('Pending')."'>Pen</span>";
                                                } else {
                                                    if ($rowStudents['dateStart'] > $lessonDate[$i]) {
                                                        echo "<span title='".__('Student joined school after assessment was given.')."' style='color: #000; font-weight: normal; border: 2px none #ff0000; padding: 2px 4px'>".__('NA').'</span>';
                                                    } else {
                                                        if ($rowSub['homeworkSubmissionRequired'] == 'Compulsory') {
                                                            echo "<span title='".__('Incomplete')."' style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px'>".__('Inc').'</span>';
                                                        } else {
                                                            echo "<span title='".__('Not submitted online')."'>".__('NA').'</span>';
                                                        }
                                                    }
                                                }
                                            }
                                            echo '</td>';
                                        }
                                    }
                                }
                                echo '</tr>';
                            }
                        }
                        echo '</table>';
                    }
                }
            }
        }

        //Print sidebar
        $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $connection2, $pupilsightCourseClassID, 'write');
    }
}
