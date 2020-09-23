<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/externalAssessment_manage_details_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightExternalAssessmentStudentID = $_GET['pupilsightExternalAssessmentStudentID'] ?? '';
    $pupilsightPersonID = $_GET['pupilsightPersonID'] ?? '';
    $search = $_GET['search'] ?? '';
    $allStudents = $_GET['allStudents'] ?? '';

    $page->breadcrumbs
        ->add(__('View All Assessments'), 'externalAssessment.php')
        ->add(__('Student Details'), 'externalAssessment_details.php', ['pupilsightPersonID' => $pupilsightPersonID])
        ->add(__('Edit Assessment'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, array('success0' => 'Your request was completed successfully.'));
    }

    //Check if school year specified
    if ($pupilsightExternalAssessmentStudentID == '' or $pupilsightPersonID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightExternalAssessmentStudentID' => $pupilsightExternalAssessmentStudentID);
            $sql = 'SELECT pupilsightExternalAssessmentStudent.*, pupilsightExternalAssessment.name AS assessment, pupilsightExternalAssessment.allowFileUpload FROM pupilsightExternalAssessmentStudent JOIN pupilsightExternalAssessment ON (pupilsightExternalAssessmentStudent.pupilsightExternalAssessmentID=pupilsightExternalAssessment.pupilsightExternalAssessmentID) WHERE pupilsightExternalAssessmentStudentID=:pupilsightExternalAssessmentStudentID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }
        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();

            if ($search != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Formal Assessment/externalAssessment_details.php&pupilsightPersonID=$pupilsightPersonID&search=$search&allStudents=$allStudents'>".__('Back').'</a>';
                echo '</div>';
            }
            
            //Check for all fields
            try {
                $dataCheck = array('pupilsightExternalAssessmentID' => $values['pupilsightExternalAssessmentID']);
                $sqlCheck = 'SELECT * FROM pupilsightExternalAssessmentField WHERE pupilsightExternalAssessmentID=:pupilsightExternalAssessmentID';
                $resultCheck = $connection2->prepare($sqlCheck);
                $resultCheck->execute($dataCheck);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            while ($rowCheck = $resultCheck->fetch()) {
                try {
                    $dataCheck2 = array('pupilsightExternalAssessmentFieldID' => $rowCheck['pupilsightExternalAssessmentFieldID'], 'pupilsightExternalAssessmentStudentID' => $values['pupilsightExternalAssessmentStudentID']);
                    $sqlCheck2 = 'SELECT * FROM pupilsightExternalAssessmentStudentEntry WHERE pupilsightExternalAssessmentFieldID=:pupilsightExternalAssessmentFieldID AND pupilsightExternalAssessmentStudentID=:pupilsightExternalAssessmentStudentID';
                    $resultCheck2 = $connection2->prepare($sqlCheck2);
                    $resultCheck2->execute($dataCheck2);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($resultCheck2->rowCount() < 1) {
                    try {
                        $dataCheck3 = array('pupilsightExternalAssessmentStudentID' => $values['pupilsightExternalAssessmentStudentID'], 'pupilsightExternalAssessmentFieldID' => $rowCheck['pupilsightExternalAssessmentFieldID']);
                        $sqlCheck3 = 'INSERT INTO pupilsightExternalAssessmentStudentEntry SET pupilsightExternalAssessmentStudentID=:pupilsightExternalAssessmentStudentID, pupilsightExternalAssessmentFieldID=:pupilsightExternalAssessmentFieldID';
                        $resultCheck3 = $connection2->prepare($sqlCheck3);
                        $resultCheck3->execute($dataCheck3);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                }
            }
			
            $form = Form::create('editAssessment', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/externalAssessment_manage_details_editProcess.php?search='.$search.'&allStudents='.$allStudents);

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);
            $form->addHiddenValue('pupilsightExternalAssessmentStudentID', $pupilsightExternalAssessmentStudentID);
            
            $row = $form->addRow();
                $row->addLabel('name', __('Assessment Type'));
                $row->addTextField('name')->required()->readOnly()->setValue(__($values['assessment']));

            $row = $form->addRow();
                $row->addLabel('date', __('Date'));
                $row->addDate('date')->required()->loadFrom($values);

            if ($values['allowFileUpload'] == 'Y') {
                $row = $form->addRow();
                $row->addLabel('file', __('Upload File'))->description(__('Use this to attach raw data, graphical summary, etc.'));
                $row->addFileUpload('file')->setAttachment('attachment', $_SESSION[$guid]['absoluteURL'], $values['attachment']);
            }

            try {
                $dataField = array('pupilsightExternalAssessmentID' => $values['pupilsightExternalAssessmentID'], 'pupilsightExternalAssessmentStudentID' => $pupilsightExternalAssessmentStudentID);
                $sqlField = 'SELECT category, pupilsightExternalAssessmentStudentEntryID, pupilsightExternalAssessmentField.*, pupilsightScale.usage, pupilsightExternalAssessmentStudentEntry.pupilsightScaleGradeID FROM pupilsightExternalAssessmentField JOIN pupilsightScale ON (pupilsightExternalAssessmentField.pupilsightScaleID=pupilsightScale.pupilsightScaleID) LEFT JOIN pupilsightExternalAssessmentStudentEntry ON (pupilsightExternalAssessmentField.pupilsightExternalAssessmentFieldID=pupilsightExternalAssessmentStudentEntry.pupilsightExternalAssessmentFieldID) WHERE pupilsightExternalAssessmentID=:pupilsightExternalAssessmentID AND pupilsightExternalAssessmentStudentID=:pupilsightExternalAssessmentStudentID ORDER BY category, pupilsightExternalAssessmentField.order';
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
                        $form->addHiddenValue($count.'-pupilsightExternalAssessmentStudentEntryID', $field['pupilsightExternalAssessmentStudentEntryID']);
                        $gradeScale = renderGradeScaleSelect($connection2, $guid, $field['pupilsightScaleID'], $count.'-pupilsightScaleGradeID', 'id', false, '150', 'id', $field['pupilsightScaleGradeID']);

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
                $row->addSubmit()->addClass('submit_align submt');
            
            echo $form->getOutput();
        }
    }
}
