<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_view_full_submit_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        $viewBy = $_GET['viewBy'];
		$subView = $_GET['subView'];
		$class = null;
		$date = null;
		$pupilsightCourseClassID = null;
        if ($viewBy != 'date' and $viewBy != 'class') {
            $viewBy = 'date';
        }
        if ($viewBy == 'date') {
            $date = $_GET['date'];
            if (!empty($_GET['dateHuman'])) {
                $date = dateConvert($guid, $_GET['dateHuman']);
            }
            if ($date == '') {
                $date = date('Y-m-d');
            }
            list($dateYear, $dateMonth, $dateDay) = explode('-', $date);
            $dateStamp = mktime(0, 0, 0, $dateMonth, $dateDay, $dateYear);
        } elseif ($viewBy == 'class') {
            if (isset($_GET['class'])) {
                $class = $_GET['class'];
            }
            $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
        }

        //Get class variable
        $pupilsightPlannerEntryID = $_GET['pupilsightPlannerEntryID'];

        if ($pupilsightPlannerEntryID == '') {
            echo "<div class='alert alert-warning'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        }
        //Check existence of and access to this class.
        else {
            try {
                if ($highestAction == 'Lesson Planner_viewAllEditMyClasses') {
                    $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'date' => $date, 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPlannerEntryID2' => $pupilsightPlannerEntryID);
                    $sql = "(SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightPlannerEntry.name, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, date, timeStart, timeEnd, summary, pupilsightPlannerEntry.description, teachersNotes, homework, homeworkDueDateTime, homeworkDetails, viewableStudents, viewableParents, role, homeworkSubmission, homeworkSubmissionDateOpen, homeworkSubmissionDrafts, homeworkSubmissionType FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND pupilsightPlannerEntry.pupilsightPlannerEntryID=:pupilsightPlannerEntryID) UNION (SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightPlannerEntry.name, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, date, timeStart, timeEnd, summary, pupilsightPlannerEntry.description, teachersNotes, homework, homeworkDueDateTime, homeworkDetails, viewableStudents, viewableParents, role, homeworkSubmission, homeworkSubmissionDateOpen, homeworkSubmissionDrafts, homeworkSubmissionType FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightPlannerEntryGuest ON (pupilsightPlannerEntryGuest.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE date=:date AND pupilsightPlannerEntryGuest.pupilsightPersonID=:pupilsightPersonID AND pupilsightPlannerEntry.pupilsightPlannerEntryID=:pupilsightPlannerEntryID2) ORDER BY date, timeStart";
                } elseif ($highestAction == 'Lesson Planner_viewEditAllClasses') {
                    $data = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                    $sql = "SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightPlannerEntry.name, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, date, timeStart, timeEnd, summary, pupilsightPlannerEntry.description, teachersNotes, homework, homeworkDueDateTime, homeworkDetails, viewableStudents, viewableParents, 'Teacher' AS role, homeworkSubmission, homeworkSubmissionDateOpen, homeworkSubmissionDrafts, homeworkSubmissionType FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightPlannerEntry.pupilsightPlannerEntryID=:pupilsightPlannerEntryID ORDER BY date, timeStart";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='alert alert-warning'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                $values = $result->fetch();

                // target of the planner
                $target = ($viewBy === 'class') ? $values['course'].'.'.$values['class'] : dateConvertBack($guid, $date);

                // planner's parameters
                $params = [];
                if ($date != '') {
                    $params['date'] = $_GET['date'];
                }
                if ($viewBy != '') {
                    $params['viewBy'] = $_GET['viewBy'] ?? '';
                }
                if ($pupilsightCourseClassID != '') {
                    $params['pupilsightCourseClassID'] = $pupilsightCourseClassID;
                }
                $params['subView'] = $subView;
                $paramsVar = '&' . http_build_query($params); // for backward compatibile uses below (should be get rid of)

                $page->breadcrumbs
                    ->add(__('Planner for {classDesc}', [
                        'classDesc' => $target,
                    ]), 'planner.php', $params)
                    ->add(__('View Lesson Plan'), 'planner_view_full.php', $params + ['pupilsightPlannerEntryID' => $pupilsightPlannerEntryID])
                    ->add(__('Add Submission'));

                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, null);
                }

                if ($_GET['submission'] != 'true' and $_GET['submission'] != 'false') {
                    echo "<div class='alert alert-warning'>";
                    echo __('You have not specified one or more required parameters.');
                    echo '</div>';
                } else {
                    if ($_GET['submission'] == 'true') {
                        $submission = true;
                        $pupilsightPlannerEntryHomeworkID = $_GET['pupilsightPlannerEntryHomeworkID'];
                    } else {
                        $submission = false;
                        $pupilsightPersonID = $_GET['pupilsightPersonID'];
                    }

                    if (($submission == true and $pupilsightPlannerEntryHomeworkID == '') or ($submission == false and $pupilsightPersonID == '')) {
                        echo "<div class='alert alert-warning'>";
                        echo __('You have not specified one or more required parameters.');
                        echo '</div>';
                    } else {
                        if ($submission == true) {
                            echo '<h2>';
                            echo __('Update Submission');
                            echo '</h2>';

                            try {
                                $dataSubmission = array('pupilsightPlannerEntryHomeworkID' => $pupilsightPlannerEntryHomeworkID);
                                $sqlSubmission = 'SELECT pupilsightPlannerEntryHomework.*, surname, preferredName FROM pupilsightPlannerEntryHomework JOIN pupilsightPerson ON (pupilsightPlannerEntryHomework.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPlannerEntryHomeworkID=:pupilsightPlannerEntryHomeworkID';
                                $resultSubmission = $connection2->prepare($sqlSubmission);
                                $resultSubmission->execute($dataSubmission);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }

                            if ($resultSubmission->rowCount() != 1) {
                                echo "<div class='alert alert-warning'>";
                                echo __('The selected record does not exist, or you do not have access to it.');
                                echo '</div>';
                            } else {
                                $rowSubmission = $resultSubmission->fetch();

                                $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/planner_view_full_submit_editProcess.php');

                                $form->addHiddenValue('search', '');
                                $form->addHiddenValue('params', $paramsVar);
                                $form->addHiddenValue('pupilsightPlannerEntryID', $pupilsightPlannerEntryID);
                                $form->addHiddenValue('submission', 'true');
                                $form->addHiddenValue('pupilsightPlannerEntryHomeworkID', $pupilsightPlannerEntryHomeworkID);
                                $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                                $row = $form->addRow();
                                    $row->addLabel('student', __('Student'));
                                    $row->addTextField('student')->setValue(formatName('', htmlPrep($rowSubmission['preferredName']), htmlPrep($rowSubmission['surname']), 'Student'))->readonly()->required();

                                $statuses = array(
                                    'On Time' => __('On Time'),
                                    'Late' => __('Late'),
                                    'Exemption' => __('Exemption')
                                );
                                $row = $form->addRow();
                                    $row->addLabel('status', __('Status'));
                                    $row->addSelect('status')->fromArray($statuses)->required()->selected($rowSubmission['status']);


                                $row = $form->addRow();
                                    $row->addFooter();
                                    $row->addSubmit();

                                echo $form->getOutput();
                            }
                        } else {
                            echo '<h2>';
                            echo __('Add Submission');
                            echo '</h2>';

                            try {
                                $dataSubmission = array('pupilsightPersonID' => $pupilsightPersonID);
                                $sqlSubmission = 'SELECT surname, preferredName FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
                                $resultSubmission = $connection2->prepare($sqlSubmission);
                                $resultSubmission->execute($dataSubmission);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }

                            if ($resultSubmission->rowCount() != 1) {
                                echo "<div class='alert alert-warning'>";
                                echo 'There are no records to display.';
                                echo '</div>';
                            } else {
                                $rowSubmission = $resultSubmission->fetch();

                                $count = 0;
                                try {
                                    $dataVersion = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                                    $sqlVersion = 'SELECT * FROM pupilsightPlannerEntryHomework WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                                    $resultVersion = $connection2->prepare($sqlVersion);
                                    $resultVersion->execute($dataVersion);
                                } catch (PDOException $e) {
                                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                }
                                if ($resultVersion->rowCount() < 1) {
                                    $count = $resultVersion->rowCount();
                                }

                                $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/planner_view_full_submit_editProcess.php');

                                $form->addHiddenValue('count', $count);
                                $form->addHiddenValue('lesson', $values['name']);
                                $form->addHiddenValue('search', '');
                                $form->addHiddenValue('params', $paramsVar);
                                $form->addHiddenValue('pupilsightPlannerEntryID', $pupilsightPlannerEntryID);
                                $form->addHiddenValue('submission', 'false');
                                $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);
                                $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                                $row = $form->addRow();
                                    $row->addLabel('student', __('Student'));
                                    $row->addTextField('student')->setValue(formatName('', htmlPrep($rowSubmission['preferredName']), htmlPrep($rowSubmission['surname']), 'Student'))->readonly()->required();

                                $types = array(
                                    'None' => __('None')
                                );
                                if ($values['homeworkSubmissionType'] == 'Link' || $values['homeworkSubmissionType'] == 'Link/File') {
                                    $types['Link'] = __('Link');
                                }
                                if ($values['homeworkSubmissionType'] == 'File' || $values['homeworkSubmissionType'] == 'Link/File') {
                                    $types['File'] = __('File');
                                }
                                $row = $form->addRow();
                                    $row->addLabel('type', __('Type'));
                                    $row->addRadio('type')->fromArray($types)->required()->checked('None')->inline(true);

                                $versions = array();
                                if ($values['homeworkSubmissionDrafts'] > 0) {
                                    $versions['Draft'] = __('Draft');
                                }
                                $versions['Final'] = __('Final');
                                $row = $form->addRow();
                                    $row->addLabel('version', __('Version'));
                                    $row->addSelect('version')->fromArray($versions)->required();

                                $form->toggleVisibilityByClass('file')->onRadio('type')->when('File');
                                $row = $form->addRow()->addClass('file');
                                    $row->addLabel('file', __('Submit File'));
                                    $row->addFileUpload('file')->required();

                                    $form->toggleVisibilityByClass('link')->onRadio('type')->when('Link');
                                    $row = $form->addRow()->addClass('link');
                                    $row->addLabel('link', __('Submit Link'));
                                    $row->addURL('link')->required();

                                $statuses = array(
                                    'On Time' => __('On Time'),
                                    'Late' => __('Late'),
                                    'Exemption' => __('Exemption')
                                );
                                $row = $form->addRow();
                                    $row->addLabel('status', __('Status'));
                                    $row->addSelect('status')->fromArray($statuses)->required();


                                $row = $form->addRow();
                                    $row->addFooter();
                                    $row->addSubmit();

                                echo $form->getOutput();
                            }
                        }
                    }
                }
            }
        }
    }
}
