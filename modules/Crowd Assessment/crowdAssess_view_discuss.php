<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Crowd Assessment/crowdAssess_view_discuss.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get class variable
    $pupilsightPersonID = $_GET['pupilsightPersonID'];
    $pupilsightPlannerEntryID = $_GET['pupilsightPlannerEntryID'];
    $pupilsightPlannerEntryHomeworkID = $_GET['pupilsightPlannerEntryHomeworkID'];

    $urlParams = ['pupilsightPlannerEntryID' => $pupilsightPlannerEntryID];
    $page->breadcrumbs
        ->add(__('View All Assessments'), 'crowdAssess.php')
        ->add(__('View Assessment'), 'crowdAssess_view.php', $urlParams)
        ->add(__('Discuss'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    if ($pupilsightPersonID == '' or $pupilsightPlannerEntryID == '' or $pupilsightPlannerEntryHomeworkID == '') {
        echo "<div class='alert alert-warning'>";
        echo 'Student, lesson or homework has not been specified .';
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

                    //Get details of homework
                    try {
                        $dataWork = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightPlannerEntryHomeworkID' => $pupilsightPlannerEntryHomeworkID);
                        $sqlWork = 'SELECT * FROM pupilsightPlannerEntryHomework WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID AND pupilsightPlannerEntryHomeworkID=:pupilsightPlannerEntryHomeworkID ORDER BY count DESC';
                        $resultWork = $connection2->prepare($sqlWork);
                        $resultWork->execute($dataWork);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    if ($resultWork->rowCount() != 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('There is currently no work to assess.');
                        echo '</div>';
                    } else {
                        $rowWork = $resultWork->fetch();

                        echo "<table class='table'>";
                        echo '<tr>';
                        echo "<td style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>Student</span><br/>";
                        echo "<a href='index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=".$rowList['pupilsightPersonID']."'>".formatName('', $rowList['preferredName'], $rowList['surname'], 'Student').'</a>';
                        echo '</td>';
                        echo "<td style='width: 34%; vertical-align: top'>";
                        echo "<span class='form-label'>Version</span><br/>";
                        if ($rowWork['version'] == 'Final') {
                            $linkText = __('Final');
                        } else {
                            $linkText = __('Draft').$rowWork['count'];
                        }

                        if ($rowWork['type'] == 'File') {
                            echo "<span title='".$rowWork['version'].'. Submitted at '.substr($rowWork['timestamp'], 11, 5).' on '.dateConvertBack($guid, substr($rowWork['timestamp'], 0, 10))."'><a href='".$_SESSION[$guid]['absoluteURL'].'/'.$rowWork['location']."'>$linkText</a></span>";
                        } else {
                            echo "<span title='".$rowWork['version'].'. Submitted at '.substr($rowWork['timestamp'], 11, 5).' on '.dateConvertBack($guid, substr($rowWork['timestamp'], 0, 10))."'><a target='_blank' href='".$rowWork['location']."'>$linkText</a></span>";
                        }
                        echo '</td>';
                        echo '</tr>';
                        echo '</table>';

                        echo "<div style='margin: 0px' class='linkTop'>";
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/crowdAssess_view_discuss_post.php&pupilsightPersonID=$pupilsightPersonID&pupilsightPlannerEntryID=$pupilsightPlannerEntryID&pupilsightPlannerEntryHomeworkID=$pupilsightPlannerEntryHomeworkID'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a>";
                        echo '</div>';

                        //Get discussion
                        echo getThread($guid, $connection2, $rowWork['pupilsightPlannerEntryHomeworkID'], null, 0, null, $pupilsightPersonID, $pupilsightPlannerEntryID);

                        echo '<br/><br/>';
                    }
                }
            }
        }
    }
}
