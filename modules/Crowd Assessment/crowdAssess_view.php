<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Crowd Assessment/crowdAssess_view.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed
    $page->breadcrumbs
        ->add(__('View All Assessments'), 'crowdAssess.php')
        ->add(__('View Assessment'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Get class variable
    $pupilsightPlannerEntryID = $_GET['pupilsightPlannerEntryID'];
    if ($pupilsightPlannerEntryID == '') {
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

            echo "<table class='table'>";
            echo '<tr>';
            echo "<td style='width: 34%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Class').'</span><br/>';
            echo $row['course'].'.'.$row['class'];
            echo '</td>';
            echo "<td style='width: 33%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Name').'</span><br/>';
            echo $row['name'];
            echo '</td>';
            echo "<td style='width: 34%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Date').'</span><br/>';
            echo dateConvertBack($guid, $row['date']);
            echo '</td>';
            echo '</tr>';
            echo '<tr>';
            echo "<td style='padding-top: 15px; width: 34%; vertical-align: top' colspan=3>";
            echo "<span class='form-label'>".__('Homework Details').'</span><br/>';
            echo $row['homeworkDetails'];
            echo '</td>';
            echo '</tr>';
            echo '</table>';

            $role = getCARole($guid, $connection2, $row['pupilsightCourseClassID']);

            $sqlList = getStudents($guid, $connection2, $role, $row['pupilsightCourseClassID'], $row['homeworkCrowdAssessOtherTeachersRead'], $row['homeworkCrowdAssessOtherParentsRead'], $row['homeworkCrowdAssessSubmitterParentsRead'], $row['homeworkCrowdAssessClassmatesParentsRead'], $row['homeworkCrowdAssessOtherStudentsRead'], $row['homeworkCrowdAssessClassmatesRead']);

            //Return $sqlList as table
            if ($sqlList[1] != '') {
                try {
                    $resultList = $connection2->prepare($sqlList[1]);
                    $resultList->execute($sqlList[0]);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($resultList->rowCount() < 1) {
                    echo "<div class='alert alert-danger'>";
                    echo 'There is currently no work to assess.';
                    echo '</div>';
                } else {
                    echo "<table cellspacing='0' style='width: 100%'>";
                    echo "<tr class='head'>";
                    echo '<th>';
                    echo __('Student');
                    echo '</th>';
                    echo '<th>';
                    echo __('Read');
                    echo '</th>';
                    echo '<th>';
                    echo __('Comments');
                    echo '</th>';
                    echo '<th>';
                    echo __('Discuss');
                    echo '</th>';
                    echo '</tr>';

                    $count = 0;
                    $rowNum = 'odd';
                    while ($rowList = $resultList->fetch()) {
                        if ($count % 2 == 0) {
                            $rowNum = 'even';
                        } else {
                            $rowNum = 'odd';
                        }
                        ++$count;

                        //COLOR ROW BY STATUS!
                        echo "<tr class=$rowNum>";
                        echo '<td>';
                        echo "<a href='index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=".$rowList['pupilsightPersonID']."'>".formatName('', $rowList['preferredName'], $rowList['surname'], 'Student', true).'</a>';
                        echo '</td>';
                        echo '<td>';
                        $rowWork = null;
                        try {
                            $dataWork = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $rowList['pupilsightPersonID']);
                            $sqlWork = 'SELECT * FROM pupilsightPlannerEntryHomework WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID ORDER BY count DESC';
                            $resultWork = $connection2->prepare($sqlWork);
                            $resultWork->execute($dataWork);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }
                        if ($resultWork->rowCount() > 0) {
                            $rowWork = $resultWork->fetch();

                            if ($rowWork['status'] == 'Exemption') {
                                $linkText = 'Exemption';
                            } elseif ($rowWork['version'] == 'Final') {
                                $linkText = 'Final';
                            } else {
                                $linkText = 'Draft'.$rowWork['count'];
                            }

                            if ($rowWork['type'] == 'File') {
                                echo "<span title='".$rowWork['version'].'. Submitted at '.substr($rowWork['timestamp'], 11, 5).' on '.dateConvertBack($guid, substr($rowWork['timestamp'], 0, 10))."'><a href='".$_SESSION[$guid]['absoluteURL'].'/'.$rowWork['location']."'>$linkText</a></span>";
                            } elseif ($rowWork['type'] == 'Link') {
                                echo "<span title='".$rowWork['version'].'. Submitted at '.substr($rowWork['timestamp'], 11, 5).' on '.dateConvertBack($guid, substr($rowWork['timestamp'], 0, 10))."'><a target='_blank' href='".$rowWork['location']."'>$linkText</a></span>";
                            } else {
                                echo "<span title='Recorded at ".substr($rowWork['timestamp'], 11, 5).' on '.dateConvertBack($guid, substr($rowWork['timestamp'], 0, 10))."'>$linkText</span>";
                            }
                        }
                        echo '</td>';
                        echo '<td>';
                        $dataDiscuss = array('pupilsightPlannerEntryHomeworkID' => $rowWork['pupilsightPlannerEntryHomeworkID']);
                        $sqlDiscuss = 'SELECT pupilsightCrowdAssessDiscuss.*, title, surname, preferredName, category FROM pupilsightCrowdAssessDiscuss JOIN pupilsightPerson ON (pupilsightCrowdAssessDiscuss.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightPlannerEntryHomeworkID=:pupilsightPlannerEntryHomeworkID';
                        $resultDiscuss = $connection2->prepare($sqlDiscuss);
                        $resultDiscuss->execute($dataDiscuss);
                        echo $resultDiscuss->rowCount();
                        echo '</td>';
                        echo '<td>';
                        if ($rowWork['pupilsightPlannerEntryHomeworkID'] != '' and $rowWork['status'] != 'Exemption') {
                            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/crowdAssess_view_discuss.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID&pupilsightPlannerEntryHomeworkID=".$rowWork['pupilsightPlannerEntryHomeworkID'].'&pupilsightPersonID='.$rowList['pupilsightPersonID']."'><img title='".__('View')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a> ";
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }
            }
        }
    }
}
