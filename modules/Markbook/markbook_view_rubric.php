<?php
/*
Pupilsight, Flexible & Open School System
*/

//Rubric includes
require_once __DIR__ . '/../Rubrics/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_view.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    //Check if school year specified
    $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
    $pupilsightMarkbookColumnID = $_GET['pupilsightMarkbookColumnID'];
    $pupilsightPersonID = $_GET['pupilsightPersonID'];
    $pupilsightRubricID = $_GET['pupilsightRubricID'];
    if ($pupilsightCourseClassID == '' or $pupilsightMarkbookColumnID == '' or $pupilsightPersonID == '' or $pupilsightRubricID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        $roleCategory = getRoleCategory($_SESSION[$guid]['pupilsightRoleIDPrimary'], $connection2);
        $contextDBTablePupilsightRubricIDField = 'pupilsightRubricID';
        if ($_GET['type'] == 'attainment') {
            $contextDBTablePupilsightRubricIDField = 'pupilsightRubricIDAttainment';
        } elseif ($_GET['type'] == 'effort') {
            $contextDBTablePupilsightRubricIDField = 'pupilsightRubricIDEffort';
        }

        try {
            if ($roleCategory == 'Staff') {
                $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                $sql = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID FROM pupilsightCourse, pupilsightCourseClass WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
            } elseif ($roleCategory == 'Student') {
                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                $sql = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID FROM pupilsightCourse, pupilsightCourseClass, pupilsightCourseClassPerson WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Student' AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class";
            } elseif ($roleCategory == 'Parent') {
                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                $sql = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID FROM pupilsightCourse, pupilsightCourseClass, pupilsightCourseClassPerson WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Student' AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class";
            }
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            try {
                $data2 = array('pupilsightMarkbookColumnID' => $pupilsightMarkbookColumnID);
                $sql2 = 'SELECT * FROM pupilsightMarkbookColumn WHERE pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID';
                $result2 = $connection2->prepare($sql2);
                $result2->execute($data2);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result2->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                try {
                    $data3 = array('pupilsightRubricID' => $pupilsightRubricID);
                    $sql3 = 'SELECT * FROM pupilsightRubric WHERE pupilsightRubricID=:pupilsightRubricID';
                    $result3 = $connection2->prepare($sql3);
                    $result3->execute($data3);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($result3->rowCount() != 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('The specified record does not exist.');
                    echo '</div>';
                } else {
                    try {
                        $data4 = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                        $sql4 = "SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClassID=:pupilsightCourseClassID AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND role='Student'";
                        $result4 = $connection2->prepare($sql4);
                        $result4->execute($data4);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    if ($result4->rowCount() != 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('The selected record does not exist, or you do not have access to it.');
                        echo '</div>';
                    } else {
                        //Let's go!
                        $row = $result->fetch();
                        $row2 = $result2->fetch();
                        $row3 = $result3->fetch();
                        $row4 = $result4->fetch();

                        echo "<h2 style='margin-bottom: 10px;'>";
                        echo $row3['name'].'<br/>';
                        echo "<span style='font-size: 65%; font-style: italic'>".formatName('', $row4['preferredName'], $row4['surname'], 'Student', true).'</span>';
                        echo '</h2>';

                        $mark = true;
                        if (isset($_GET['mark'])) {
                            if ($_GET['mark'] == 'FALSE') {
                                $mark = false;
                            }
                        }

                        echo rubricView($guid, $connection2, $pupilsightRubricID, $mark, $row4['pupilsightPersonID'], 'pupilsightMarkbookColumn', 'pupilsightMarkbookColumnID', $pupilsightMarkbookColumnID,  $contextDBTablePupilsightRubricIDField, 'name', 'completeDate');
                    }
                }
            }
        }
    }
}
