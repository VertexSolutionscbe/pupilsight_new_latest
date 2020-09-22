<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_view_full_post.php') == false) {
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
        $viewBy = null;
        if (isset($_GET['viewBy'])) {
            $viewBy = $_GET['viewBy'];
        }
        $subView = null;
        if (isset($_GET['subView'])) {
            $subView = $_GET['subView'];
        }
        if ($viewBy != 'date' and $viewBy != 'class') {
            $viewBy = 'date';
        }
        $pupilsightCourseClassID = null;
        $date = null;
        $dateStamp = null;
        if ($viewBy == 'date') {
            $date = $_GET['date'];
            if (isset($_GET['dateHuman'])) {
                $date = dateConvert($guid, $_GET['dateHuman']);
            }
            if ($date == '') {
                $date = date('Y-m-d');
            }
            list($dateYear, $dateMonth, $dateDay) = explode('-', $date);
            $dateStamp = mktime(0, 0, 0, $dateMonth, $dateDay, $dateYear);
        } elseif ($viewBy == 'class') {
            $class = null;
            if (isset($_GET['class'])) {
                $class = $_GET['class'];
            }
            $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
        }
        $replyTo = null;
        if (isset($_GET['replyTo'])) {
            $replyTo = $_GET['replyTo'];
        }
        $search = $_GET['search'] ?? '';

        //Get class variable
        $pupilsightPlannerEntryID = $_GET['pupilsightPlannerEntryID'];

        if ($pupilsightPlannerEntryID == '') {
            echo "<div class='alert alert-warning'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        }
        //Check existence of and access to this class.
        else {
            if ($highestAction == 'Lesson Planner_viewMyChildrensClasses') {
                if ($_GET['search'] == '') {
                    echo "<div class='alert alert-warning'>";
                    echo __('You have not specified one or more required parameters.');
                    echo '</div>';
                } else {
                    $pupilsightPersonID = $_GET['search'];
                    try {
                        $dataChild = array('pupilsightPersonID1' => $pupilsightPersonID, 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID1 AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID2 AND childDataAccess='Y'";
                        $resultChild = $connection2->prepare($sqlChild);
                        $resultChild->execute($dataChild);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    if ($resultChild->rowCount() != 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('The selected record does not exist, or you do not have access to it.');
                        echo '</div>';
                    } else {
                        $data = array('date' => $date);
                        $sql = "(SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightPlannerEntry.name, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, date, timeStart, timeEnd, summary, pupilsightPlannerEntry.description, teachersNotes, homework, homeworkDueDateTime, homeworkDetails, viewableStudents, viewableParents, role, homeworkSubmission, homeworkSubmissionDateOpen, homeworkSubmissionDrafts, homeworkSubmissionType FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=$pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND pupilsightPlannerEntry.pupilsightPlannerEntryID=$pupilsightPlannerEntryID) UNION (SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightPlannerEntry.name, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, date, timeStart, timeEnd, summary, pupilsightPlannerEntry.description, teachersNotes, homework, homeworkDueDateTime, homeworkDetails, viewableStudents, viewableParents, role, homeworkSubmission, homeworkSubmissionDateOpen, homeworkSubmissionDrafts, homeworkSubmissionType FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightPlannerEntryGuest ON (pupilsightPlannerEntryGuest.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE date=:date AND pupilsightPlannerEntryGuest.pupilsightPersonID=$pupilsightPersonID AND pupilsightPlannerEntry.pupilsightPlannerEntryID=$pupilsightPlannerEntryID) ORDER BY date, timeStart";
                    }
                }
            } elseif ($highestAction == 'Lesson Planner_viewMyClasses' or $highestAction == 'Lesson Planner_viewAllEditMyClasses') {
                $data = array('date' => $date);
                $sql = '(SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightPlannerEntry.name, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, date, timeStart, timeEnd, summary, pupilsightPlannerEntry.description, teachersNotes, homework, homeworkDueDateTime, homeworkDetails, viewableStudents, viewableParents, role, homeworkSubmission, homeworkSubmissionDateOpen, homeworkSubmissionDrafts, homeworkSubmissionType FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID='.$_SESSION[$guid]['pupilsightPersonID']." AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND pupilsightPlannerEntry.pupilsightPlannerEntryID=$pupilsightPlannerEntryID) UNION (SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightPlannerEntry.name, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, date, timeStart, timeEnd, summary, pupilsightPlannerEntry.description, teachersNotes, homework, homeworkDueDateTime, homeworkDetails, viewableStudents, viewableParents, role, homeworkSubmission, homeworkSubmissionDateOpen, homeworkSubmissionDrafts, homeworkSubmissionType FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightPlannerEntryGuest ON (pupilsightPlannerEntryGuest.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE date=:date AND pupilsightPlannerEntryGuest.pupilsightPersonID=".$_SESSION[$guid]['pupilsightPersonID']." AND pupilsightPlannerEntry.pupilsightPlannerEntryID=$pupilsightPlannerEntryID) ORDER BY date, timeStart";
            } elseif ($highestAction == 'Lesson Planner_viewEditAllClasses') {
                $data = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                $sql = "SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightPlannerEntry.name, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, date, timeStart, timeEnd, summary, pupilsightPlannerEntry.description, teachersNotes, homework, homeworkDueDateTime, homeworkDetails, viewableStudents, viewableParents, 'Teacher' AS role, homeworkSubmission, homeworkSubmissionDateOpen, homeworkSubmissionDrafts, homeworkSubmissionType FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightPlannerEntry.pupilsightPlannerEntryID=:pupilsightPlannerEntryID ORDER BY date, timeStart";
            }
            try {
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
                $row = $result->fetch();

                // target of the planner
                $target = ($viewBy === 'class') ? $row['course'].'.'.$row['class'] : dateConvertBack($guid, $date);

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
                    ->add(__('Add Comment'));

                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, null);
                }

                if (($row['role'] == 'Student' and $row['viewableStudents'] == 'N') and ($highestAction == 'Lesson Planner_viewMyChildrensClasses' and $row['viewableParents'] == 'N')) {
                    echo "<div class='alert alert-warning'>";
                    echo __('The selected record does not exist, or you do not have access to it.');
                    echo '</div>';
                } else {
                    echo '<h2>';
                    echo __('Planner Discussion Post');
                    echo '</h2>';

                    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/planner_view_full_postProcess.php');

                    $form->addHiddenValue('search', $search);
                    $form->addHiddenValue('replyTo', $replyTo);
                    $form->addHiddenValue('params', $paramsVar);
                    $form->addHiddenValue('pupilsightPlannerEntryID', $pupilsightPlannerEntryID);
                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                    $row = $form->addRow();
                        $column = $row->addColumn();
                        $column->addLabel('comment', __('Write your comment below:'));
                        $column->addEditor('comment', $guid)->setRows(20)->showMedia();

                    $row = $form->addRow();
                        $row->addFooter();
                        $row->addSubmit();

                    echo $form->getOutput();
                }
            }
        }
    }
}
