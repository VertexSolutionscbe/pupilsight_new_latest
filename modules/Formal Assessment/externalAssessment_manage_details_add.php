<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/externalAssessment_manage_details_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightPersonID = $_GET['pupilsightPersonID'] ?? '';
    $search = $_GET['search'] ?? '';
    $allStudents = $_GET['allStudents'] ?? '';

    $page->breadcrumbs
        ->add(__('View All Assessments'), 'externalAssessment.php')
        ->add(__('Student Details'), 'externalAssessment_details.php', ['pupilsightPersonID' => $pupilsightPersonID])
        ->add(__('Add Assessment'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Formal Assessment/externalAssessment_manage_details_edit.php&pupilsightExternalAssessmentStudentID='.$_GET['editID'].'&search='.$_GET['search'].'&allStudents='.$_GET['allStudents'].'&pupilsightPersonID='.$_GET['pupilsightPersonID'].'&pupilsightExternalAssessmentID='.$_GET['pupilsightExternalAssessmentID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    if ($pupilsightPersonID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            if ($allStudents != 'on') {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID);
                $sql = "SELECT pupilsightPerson.pupilsightPersonID, pupilsightStudentEnrolment.pupilsightYearGroupID, pupilsightStudentEnrolmentID, surname, preferredName, title, image_240, pupilsightYearGroup.name AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup FROM pupilsightPerson, pupilsightStudentEnrolment, pupilsightYearGroup, pupilsightRollGroup WHERE (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) AND (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) AND (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full' AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName";
            } else {
                $data = array('pupilsightPersonID' => $pupilsightPersonID);
                $sql = 'SELECT DISTINCT pupilsightPerson.pupilsightPersonID, surname, preferredName, title, image_240, NULL AS yearGroup, NULL AS rollGroup FROM pupilsightPerson, pupilsightStudentEnrolment WHERE (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName';
            }
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('There are no records to display.');
            echo '</div>';
        } else {
            if ($search != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Formal Assessment/externalAssessment_details.php&pupilsightPersonID=$pupilsightPersonID&search=$search&allStudents=$allStudents'>".__('Back').'</a>';
                echo '</div>';
            }
            $row = $result->fetch();

            echo "<table class='table'>";
            echo '<tr>';
            echo "<td style='width: 34%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Name').'</span><br/>';
            echo Format::name('', $row['preferredName'], $row['surname'], 'Student');
            echo '</td>';
            echo "<td style='width: 33%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Year Group').'</span><br/>';
            if ($row['yearGroup'] != '') {
                echo __($row['yearGroup']);
            }
            echo '</td>';
            echo "<td style='width: 34%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Roll Group').'</span><br/>';
            echo $row['rollGroup'];
            echo '</td>';
            echo '</tr>';
            echo '</table>';

            $step = isset($_GET['step'])? $_GET['step'] : null;
            if ($step != 1 and $step != 2) {
                $step = 1;
            }

            //Step 1
            if ($step == 1) {
                $form = Form::create('addAssessment', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/externalAssessment_manage_details_add.php', 'get');

                $form->addHiddenValue('q', $_GET['q']);
                $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);
                $form->addHiddenValue('step', 2);
                $form->addHiddenValue('search', $search);
                $form->addHiddenValue('allStudents', $allStudents);

                $form->addRow()->addHeading(__('Assessment Type'));

                $sql = "SELECT pupilsightExternalAssessmentID as value, name FROM pupilsightExternalAssessment WHERE active='Y' ORDER BY name";
                $row = $form->addRow();
                    $row->addLabel('pupilsightExternalAssessmentID', __('Choose Assessment'));
                    $row->addSelect('pupilsightExternalAssessmentID')->fromQuery($pdo, $sql)->required()->placeholder();

                $form->toggleVisibilityByClass('copyToGCSE')->onSelect('pupilsightExternalAssessmentID')->when('0002');
                $row = $form->addRow()->addClass('copyToGCSE');
                    $row->addLabel('copyToGCSECheck', __('Copy Target Grades?'))->description(__('These will come from the student\'s last CAT test.'));
                    $row->addCheckbox('copyToGCSECheck')->setValue('Y');

                $form->toggleVisibilityByClass('copyToIB')->onSelect('pupilsightExternalAssessmentID')->when('0003');
                $row = $form->addRow()->addClass('copyToIB');
                    $row->addLabel('copyToIBCheck', __('Create Target Grades?'))->description(__('These will be calculated from the student\'s GCSE grades.'));
                    $row->addSelect('copyToIBCheck')->fromArray(array('Target' => __('From GCSE Target Grades'), 'Final' => __('GCSE Final Grades')))->placeholder();

                $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit(__('Go'))->addClass('submit_align');

                echo $form->getOutput();

            } else {
                $pupilsightExternalAssessmentID = $_GET['pupilsightExternalAssessmentID'];
                $copyToGCSECheck = isset($_GET['copyToGCSECheck'])? $_GET['copyToGCSECheck'] : null;
                $copyToIBCheck = isset($_GET['copyToIBCheck'])? $_GET['copyToIBCheck'] : null;

                try {
                    $dataSelect = array('pupilsightExternalAssessmentID' => $pupilsightExternalAssessmentID);
                    $sqlSelect = "SELECT * FROM pupilsightExternalAssessment WHERE active='Y' AND pupilsightExternalAssessmentID=:pupilsightExternalAssessmentID ORDER BY name";
                    $resultSelect = $connection2->prepare($sqlSelect);
                    $resultSelect->execute($dataSelect);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($resultSelect->rowCount() != 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('The selected record does not exist, or you do not have access to it.');
                    echo '</div>';
                } else {
                    $rowSelect = $resultSelect->fetch();

                    //Attempt to get CATs grades to copy to GCSE target
                    if ($copyToGCSECheck == 'Y') {
                        $grades = array();
                        try {
                            $dataCopy = array('pupilsightPersonID' => $pupilsightPersonID);
                            $sqlCopy = "SELECT * FROM pupilsightExternalAssessment JOIN pupilsightExternalAssessmentStudent ON (pupilsightExternalAssessmentStudent.pupilsightExternalAssessmentID=pupilsightExternalAssessment.pupilsightExternalAssessmentID) WHERE name='Cognitive Abilities Test' AND pupilsightPersonID=:pupilsightPersonID ORDER BY date DESC";
                            $resultCopy = $connection2->prepare($sqlCopy);
                            $resultCopy->execute($dataCopy);
                        } catch (PDOException $e) {
                        }
                        if ($resultCopy->rowCount() > 0) {
                            $rowCopy = $resultCopy->fetch();
                            try {
                                $dataCopy2 = array('category' => '%GCSE Target Grades', 'pupilsightExternalAssessmentStudentID' => $rowCopy['pupilsightExternalAssessmentStudentID']);
                                $sqlCopy2 = 'SELECT * FROM pupilsightExternalAssessmentStudentEntry JOIN pupilsightExternalAssessmentField ON (pupilsightExternalAssessmentStudentEntry.pupilsightExternalAssessmentFieldID=pupilsightExternalAssessmentField.pupilsightExternalAssessmentFieldID) WHERE category LIKE :category AND pupilsightExternalAssessmentStudentID=:pupilsightExternalAssessmentStudentID AND NOT (pupilsightScaleGradeID IS NULL) ORDER BY name';
                                $resultCopy2 = $connection2->prepare($sqlCopy2);
                                $resultCopy2->execute($dataCopy2);
                            } catch (PDOException $e) {
                            }
                            while ($rowCopy2 = $resultCopy2->fetch()) {
                                $grades[$rowCopy2['name']][0] = $rowCopy2['pupilsightScaleGradeID'];
                            }
                        }
                    }
                    //Attempt to get GCSE grades to copy to IB target
                    $regression = array();
                    if ($copyToIBCheck == 'Target' or $copyToIBCheck == 'Final') {
                        $grades = array();
                        $count = 0;
                        $countWeighted = 0;
                        $total = 0;
                        try {
                            $dataCopy = array('pupilsightPersonID' => $pupilsightPersonID);
                            $sqlCopy = "SELECT * FROM pupilsightExternalAssessment JOIN pupilsightExternalAssessmentStudent ON (pupilsightExternalAssessmentStudent.pupilsightExternalAssessmentID=pupilsightExternalAssessment.pupilsightExternalAssessmentID) WHERE name='GCSE/iGCSE' AND pupilsightPersonID=:pupilsightPersonID ORDER BY date DESC";
                            $resultCopy = $connection2->prepare($sqlCopy);
                            $resultCopy->execute($dataCopy);
                        } catch (PDOException $e) { echo $e->getMessage();
                        }

                        if ($resultCopy->rowCount() > 0) {
                            $rowCopy = $resultCopy->fetch();
                            try {
                                $dataCopy2 = array('pupilsightExternalAssessmentStudentID' => $rowCopy['pupilsightExternalAssessmentStudentID']);
                                if ($copyToIBCheck == 'Target') {
                                    $sqlCopy2 = "SELECT * FROM pupilsightExternalAssessmentStudentEntry JOIN pupilsightExternalAssessmentField ON (pupilsightExternalAssessmentStudentEntry.pupilsightExternalAssessmentFieldID=pupilsightExternalAssessmentField.pupilsightExternalAssessmentFieldID) JOIN pupilsightScaleGrade ON (pupilsightExternalAssessmentStudentEntry.pupilsightScaleGradeID=pupilsightScaleGrade.pupilsightScaleGradeID) WHERE category LIKE '%Target Grade' AND pupilsightExternalAssessmentStudentID=:pupilsightExternalAssessmentStudentID AND NOT (pupilsightExternalAssessmentStudentEntry.pupilsightScaleGradeID IS NULL) ORDER BY name";
                                } elseif ($copyToIBCheck == 'Final') {
                                    $sqlCopy2 = "SELECT * FROM pupilsightExternalAssessmentStudentEntry JOIN pupilsightExternalAssessmentField ON (pupilsightExternalAssessmentStudentEntry.pupilsightExternalAssessmentFieldID=pupilsightExternalAssessmentField.pupilsightExternalAssessmentFieldID) JOIN pupilsightScaleGrade ON (pupilsightExternalAssessmentStudentEntry.pupilsightScaleGradeID=pupilsightScaleGrade.pupilsightScaleGradeID) WHERE category LIKE '%Final Grade' AND pupilsightExternalAssessmentStudentID=:pupilsightExternalAssessmentStudentID AND NOT (pupilsightExternalAssessmentStudentEntry.pupilsightScaleGradeID IS NULL) ORDER BY name";
                                }
                                $resultCopy2 = $connection2->prepare($sqlCopy2);
                                $resultCopy2->execute($dataCopy2);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }
                            while ($rowCopy2 = $resultCopy2->fetch()) {
                                //Conert grade to numeric value
                                $grades[$count][0] = $rowCopy2['name'];
                                if ($rowCopy2['value'] == 'A*') {
                                    $grades[$count][1] = 7;
                                } elseif ($rowCopy2['value'] == 'A') {
                                    $grades[$count][1] = 6;
                                } elseif ($rowCopy2['value'] == 'A/B' or $rowCopy2['value'] == 'B') {
                                    $grades[$count][1] = 5;
                                } elseif ($rowCopy2['value'] == 'B/C' or $rowCopy2['value'] == 'C') {
                                    $grades[$count][1] = 4;
                                } elseif ($rowCopy2['value'] == 'C/D' or $rowCopy2['value'] == 'D') {
                                    $grades[$count][1] = 3;
                                } elseif ($rowCopy2['value'] == 'D/E' or $rowCopy2['value'] == 'E') {
                                    $grades[$count][1] = 2;
                                } elseif ($rowCopy2['value'] == 'F') {
                                    $grades[$count][1] = 1;
                                } else {
                                    $grades[$count][1] = 0;
                                }

                                ++$countWeighted;
                                $total += $grades[$count][1];
                                if (isset($grades[$count][2])) {
                                    if ($grades[$count][2] == 'Science - Double Award') {
                                        ++$countWeighted;
                                        $total += $grades[$count][1];
                                    }
                                }
                                ++$count;
                            }

                            //Calculate GCSE numeric mean
                            if ($countWeighted != 0) {
                                $mean = $total / $countWeighted;
                            } else {
                                $mean = 0;
                            }

                            //Apply regression
                            $regression[1][1] = 'Biology';
                            $regression[1][2] = 1.165650007;
                            $regression[1][3] = -2.25440921;
                            $regression[1][4] = round(($mean * $regression[1][2]) + $regression[1][3]);
                            $regression[2][1] = 'Business Studies';
                            $regression[2][2] = 1.130455413;
                            $regression[2][3] = -1.519358653;
                            $regression[2][4] = round(($mean * $regression[2][2]) + $regression[2][3]);
                            $regression[3][1] = 'Chemistry';
                            $regression[3][2] = 1.304881104;
                            $regression[3][3] = -3.490021815;
                            $regression[3][4] = round(($mean * $regression[3][2]) + $regression[3][3]);
                            $regression[4][1] = 'Design Technology';
                            $regression[4][2] = 1.137380235;
                            $regression[4][3] = -2.122401828;
                            $regression[4][4] = round(($mean * $regression[4][2]) + $regression[4][3]);
                            $regression[5][1] = 'Economics';
                            $regression[5][2] = 1.143439044;
                            $regression[5][3] = -1.812296114;
                            $regression[5][4] = round(($mean * $regression[5][2]) + $regression[5][3]);
                            $regression[6][1] = 'Environmental Systems and Society';
                            $regression[6][2] = 1.248948252;
                            $regression[6][3] = -2.747483754;
                            $regression[6][4] = round(($mean * $regression[6][2]) + $regression[6][3]);
                            $regression[7][1] = 'English';
                            $regression[7][2] = 0.927976158;
                            $regression[7][3] = -0.94284584;
                            $regression[7][4] = round(($mean * $regression[7][2]) + $regression[7][3]);
                            $regression[8][1] = 'Film Studies';
                            $regression[8][2] = 1.182838166;
                            $regression[8][3] = -2.360542888;
                            $regression[8][4] = round(($mean * $regression[8][2]) + $regression[8][3]);
                            $regression[9][1] = 'Food Technology';
                            $regression[9][2] = 1.152883638;
                            $regression[9][3] = -2.260685644;
                            $regression[9][4] = round(($mean * $regression[9][2]) + $regression[9][3]);
                            $regression[10][1] = 'French';
                            $regression[10][2] = 1.157342439;
                            $regression[10][3] = -2.203111522;
                            $regression[10][4] = round(($mean * $regression[10][2]) + $regression[10][3]);
                            $regression[11][1] = 'Geography';
                            $regression[11][2] = 1.202926215;
                            $regression[11][3] = -2.385292067;
                            $regression[11][4] = round(($mean * $regression[11][2]) + $regression[11][3]);
                            $regression[12][1] = 'German';
                            $regression[12][2] = 1.137380235;
                            $regression[12][3] = -2.122401828;
                            $regression[12][4] = round(($mean * $regression[12][2]) + $regression[12][3]);
                            $regression[13][1] = 'History';
                            $regression[13][2] = 1.204129207;
                            $regression[13][3] = -2.364351524;
                            $regression[13][4] = round(($mean * $regression[13][2]) + $regression[13][3]);
                            $regression[14][1] = 'Italian';
                            $regression[14][2] = 1.128043332;
                            $regression[14][3] = -1.851982229;
                            $regression[14][4] = round(($mean * $regression[14][2]) + $regression[14][3]);
                            $regression[15][1] = 'Maths Studies';
                            $regression[15][2] = 1.048269401;
                            $regression[15][3] = -0.990598742;
                            $regression[15][4] = round(($mean * $regression[15][2]) + $regression[15][3]);
                            $regression[16][1] = 'Mathematics HL';
                            $regression[16][2] = 1.395775638;
                            $regression[16][3] = -4.717945299;
                            $regression[16][4] = round(($mean * $regression[16][2]) + $regression[16][3]);
                            $regression[17][1] = 'Music';
                            $regression[17][2] = 1.124046791;
                            $regression[17][3] = -1.820212137;
                            $regression[17][4] = round(($mean * $regression[17][2]) + $regression[17][3]);
                            $regression[18][1] = 'Philosophy';
                            $regression[18][2] = 1.201966539;
                            $regression[18][3] = -2.372274051;
                            $regression[18][4] = round(($mean * $regression[18][2]) + $regression[18][3]);
                            $regression[19][1] = 'Physics';
                            $regression[19][2] = 1.343381065;
                            $regression[19][3] = -3.749028496;
                            $regression[19][4] = round(($mean * $regression[19][2]) + $regression[19][3]);
                            $regression[20][1] = 'Psychology';
                            $regression[20][2] = 1.111003966;
                            $regression[20][3] = -1.810597105;
                            $regression[20][4] = round(($mean * $regression[20][2]) + $regression[20][3]);
                            $regression[21][1] = 'Spanish';
                            $regression[21][2] = 1.164894191;
                            $regression[21][3] = -2.334848569;
                            $regression[21][4] = round(($mean * $regression[21][2]) + $regression[21][3]);
                            $regression[22][1] = 'Theatre Arts';
                            $regression[22][2] = 1.102638258;
                            $regression[22][3] = -1.81567801;
                            $regression[22][4] = round(($mean * $regression[22][2]) + $regression[22][3]);
                            $regression[23][1] = 'Visual Arts';
                            $regression[23][2] = 0.981346183;
                            $regression[23][3] = -0.747573107;
                            $regression[23][4] = round(($mean * $regression[23][2]) + $regression[23][3]);
                            $regression[24][1] = 'Mathematics SL';
                            $regression[24][2] = 1.248787179;
                            $regression[24][3] = -3.349326039;
                            $regression[24][4] = round(($mean * $regression[24][2]) + $regression[24][3]);
                            $regression[25][1] = 'World Politics';
                            $regression[25][2] = 1.076900902;
                            $regression[25][3] = -1.663846831;
                            $regression[25][4] = round(($mean * $regression[25][2]) + $regression[25][3]);
                        }
                    }

                    $form = Form::create('addAssessment', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/externalAssessment_manage_details_addProcess.php?search='.$search.'&allStudents='.$allStudents);

                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                    $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);
                    $form->addHiddenValue('pupilsightExternalAssessmentID', $pupilsightExternalAssessmentID);

                    $row = $form->addRow();
                    $row->addLabel('name', __('Assessment Type'));
                    $row->addTextField('name')->required()->readOnly()->setValue(__($rowSelect['name']));

                    $row = $form->addRow();
                    $row->addLabel('date', __('Date'));
                    $row->addDate('date')->required();

                    if ($rowSelect['allowFileUpload'] == 'Y') {
                        $row = $form->addRow();
                        $row->addLabel('file', __('Upload File'))->description(__('Use this to attach raw data, graphical summary, etc.'));
                        $row->addFileUpload('file');
                    }

                    try {
                        $dataField = array('pupilsightExternalAssessmentID' => $pupilsightExternalAssessmentID);
                        $sqlField = 'SELECT category, pupilsightExternalAssessmentField.*, pupilsightScale.usage FROM pupilsightExternalAssessmentField JOIN pupilsightScale ON (pupilsightExternalAssessmentField.pupilsightScaleID=pupilsightScale.pupilsightScaleID) WHERE pupilsightExternalAssessmentID=:pupilsightExternalAssessmentID ORDER BY category, pupilsightExternalAssessmentField.order';
                        $resultField = $connection2->prepare($sqlField);
                        $resultField->execute($dataField);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    if ($resultField->rowCount() <= 0) {
                        $form->addRow()->addAlert(__('There are no fields in this assessment.'), 'warning');
                    } else {
                        $fieldGroup = $resultField->fetchAll(\PDO::FETCH_GROUP);
                        $count = 0;

                        foreach ($fieldGroup as $category => $fields) {
                            $categoryName = (strpos($category, '_') !== false)? substr($category, (strpos($category, '_') + 1)) : $category;

                            $row = $form->addRow();
                            $row->addHeading($categoryName);
                            $row->addContent(__('Grade'))->wrap('<b>', '</b>')->setClass('right');

                            foreach ($fields as $field) {
                                $preselectValue = null;
                                $mode = 'id';
                                if ($copyToGCSECheck == 'Y' and $field['category'] == '0_Target Grade') {
                                    $preselectValue = isset($grades[$field['name']][0])? $grades[$field['name']][0] : '';
                                }
                                if (($copyToIBCheck == 'Target' || $copyToIBCheck == 'Final') && $field['category'] == '0_Target Grade') {
                                    //Compare subject name to $regression and find entry for current subject
                                    foreach ($regression as $subject) {
                                        //Compare subject name to $regression and find entry for current subject
                                        $match = true;
                                        $subjectName = explode(' ', $subject[1]);
                                        foreach ($subjectName as $subjectToken) {
                                            //General/rough match check for all subjects
                                            if (stripos($field['name'], $subjectToken) === false) {
                                                $match = false;
                                            }
                                            //Exact check for mathematics SL & HL
                                            if (stripos($field['name'], 'Mathematics')) {
                                                if ($field['name'] != $subject) {
                                                    $match = false;
                                                }
                                            }
                                        }

                                        if ($match == true) {
                                            $preselectValue = $subject[4];
                                        }
                                    }
                                    $mode = 'value';
                                }

                                $form->addHiddenValue($count.'-pupilsightExternalAssessmentFieldID', $field['pupilsightExternalAssessmentFieldID']);
                                $gradeScale = renderGradeScaleSelect($connection2, $guid, $field['pupilsightScaleID'], $count.'-pupilsightScaleGradeID', 'id', false, '150', $mode, $preselectValue);

                                $row = $form->addRow();
                                $row->addLabel($count.'-pupilsightScaleGradeID', $field['name'])->setTitle($field['usage']);
                                $row->addContent($gradeScale);

                                $count++;
                            }
                        }

                        $form->addHiddenValue('count', $count);
                    }

                    $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit();

                    echo $form->getOutput();
                }
            }
        }
    }
}
