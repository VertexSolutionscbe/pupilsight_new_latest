<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/internalAssessment_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    //Get class variable
    $pupilsightCourseClassID = null;
    if (isset($_GET['pupilsightCourseClassID'])) {
        $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
    } else {
        try {
            $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sql = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID FROM pupilsightCourse, pupilsightCourseClass, pupilsightCourseClassPerson WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND pupilsightCourse.pupilsightSchoolYearID=(SELECT pupilsightSchoolYearID FROM pupilsightSchoolYear WHERE status='Current') ORDER BY course, class";
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
        echo '<h1>';
        echo 'Manage Internal Assessment';
        echo '</h1>';
        echo "<div class='alert alert-warning'>";
        echo __('Use the class listing on the right to choose a Internal Assessment to edit.');
        echo '</div>';
    }
    //Check existence of and access to this class.
    else {
        try {
            $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sql = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID FROM pupilsightCourse, pupilsightCourseClass WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo '<h1>';
            echo __('Manage Internal Assessment');
            echo '</h1>';
            echo "<div class='alert alert-danger'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            $row = $result->fetch();
            $page->breadcrumbs->add(__('Manage').' '.$row['course'].'.'.$row['class'].' '.__('Internal Assessments'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, array('success0' => 'Your request was completed successfully.'));
            }

            //Add multiple columns
            echo "<div class='linkTop'>";
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/internalAssessment_manage_add.php&pupilsightCourseClassID=$pupilsightCourseClassID'>".__('Add Multiple Columns')."<img style='margin-left: 5px' title='".__('Add Multiple Columns')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new_multi.png'/></a>";
            echo '</div>';

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
                echo '<h3>';
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

            //Print mark
            echo '<h3>';
            echo __('Internal Assessment Columns');
            echo '</h3>';

            //Set pagination variable
            $page = 1;
            if (isset($_GET['page'])) {
                $page = $_GET['page'];
            }
            if ((!is_numeric($page)) or $page < 1) {
                $page = 1;
            }

            try {
                $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                $sql = 'SELECT * FROM pupilsightInternalAssessmentColumn WHERE pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY completeDate DESC, name';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() < 1) {
                echo "<div class='alert alert-danger'>";
                echo __('There are no records to display.');
                echo '</div>';
            } else {
                echo "<table cellspacing='0' style='width: 100%'>";
                echo "<tr class='head'>";
                echo '<th>';
                echo __('Name').'<br/>';
                echo "<span style='font-size: 85%; font-style: italic'>".__('Type').'</span>';
                echo '</th>';
                echo '<th>';
                echo __('Date<br/>Complete');
                echo '</th>';
                echo '<th>';
                echo __('Actions');
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

                    //COLOR ROW BY STATUS!
                    echo "<tr class=$rowNum>";
                    echo '<td>';
                    echo '<b>'.$row['name'].'</b><br/>';
                    echo "<span style='font-size: 85%; font-style: italic'>".$row['type'].'</span>';
                    echo '</td>';
                    echo '<td>';
                    if ($row['complete'] == 'Y') {
                        echo dateConvertBack($guid, $row['completeDate']);
                    }
                    echo '</td>';
                    echo '<td>';
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/internalAssessment_manage_edit.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightInternalAssessmentColumnID=".$row['pupilsightInternalAssessmentColumnID']."'><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                    echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module']."/internalAssessment_manage_delete.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightInternalAssessmentColumnID=".$row['pupilsightInternalAssessmentColumnID']."&width=650&height=135'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a> ";
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/internalAssessment_write_data.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightInternalAssessmentColumnID=".$row['pupilsightInternalAssessmentColumnID']."'><img title='".__('Enter Data')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/markbook.png'/></a> ";
                    echo '</td>';
                    echo '</tr>';

                    ++$count;
                }
                echo '</table>';
            }
        }
    }

    //Print sidebar
    $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $connection2, $pupilsightCourseClassID);
}
