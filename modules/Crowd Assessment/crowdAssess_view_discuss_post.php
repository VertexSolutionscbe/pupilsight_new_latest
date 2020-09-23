<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Crowd Assessment/crowdAssess_view_discuss_post.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get class variable
    $pupilsightPersonID = $_GET['pupilsightPersonID'];
    $pupilsightPlannerEntryID = $_GET['pupilsightPlannerEntryID'];
    $pupilsightPlannerEntryHomeworkID = $_GET['pupilsightPlannerEntryHomeworkID'];

    $urlParams = ['pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightPlannerEntryHomeworkID' => $pupilsightPlannerEntryHomeworkID];
    $page->breadcrumbs
        ->add(__('View All Assessments'), 'crowdAssess.php')
        ->add(__('View Assessment'), 'crowdAssess_view.php', $urlParams)
        ->add(__('Discuss'),'crowdAssess_view_discuss.php', $urlParams)
        ->add(__('Add Post'));    
    
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    if ($pupilsightPersonID == '' or $pupilsightPlannerEntryID == '' or $pupilsightPlannerEntryHomeworkID == '') {
        echo "<div class='alert alert-warning'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    }
    //Check existence of and access to this class.
    else {
        $and = " AND pupilsightPlannerEntryID=$pupilsightPlannerEntryID";
        $sql = getLessons($guid, $connection2, $and);
        try {
            $result = $connection2->prepare($sql[1]);
            $result->execute($sql[0]);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            $row = $result->fetch();

            $role = getCARole($guid, $connection2, $row['pupilsightCourseClassID']);
            $replyTo = null;
            if (isset($_GET['replyTo'])) {
                $replyTo = $_GET['replyTo'];
            }
            $sqlList = getStudents($guid, $connection2, $role, $row['pupilsightCourseClassID'], $row['homeworkCrowdAssessOtherTeachersRead'], $row['homeworkCrowdAssessOtherParentsRead'], $row['homeworkCrowdAssessSubmitterParentsRead'], $row['homeworkCrowdAssessClassmatesParentsRead'], $row['homeworkCrowdAssessOtherStudentsRead'], $row['homeworkCrowdAssessClassmatesRead'], " AND pupilsightPerson.pupilsightPersonID=$pupilsightPersonID");

            if ($sqlList[1] != '') {
                try {
                    $resultList = $connection2->prepare($sqlList[1]);
                    $resultList->execute($sqlList[0]);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($resultList->rowCount() != 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('There is currently no work to assess.');
                    echo '</div>';
                } else {
                    $rowList = $resultList->fetch();

                    $form = Form::create('courseEdit', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/crowdAssess_view_discuss_postProcess.php?pupilsightPlannerEntryID=$pupilsightPlannerEntryID&pupilsightPlannerEntryHomeworkID=$pupilsightPlannerEntryHomeworkID&address=".$_GET['q']."&pupilsightPersonID=$pupilsightPersonID&replyTo=$replyTo");
                
                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                    $column = $form->addRow()->addColumn();
                        $column->addLabel('commentLabel', __('Write your comment below:'));
                        $column->addEditor('comment', $guid)->setRows(10)->required();

                    $form->addRow()->addSubmit();

                    echo $form->getOutput();
                }
            }
        }
    }
}
