<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs->add(__('Scope And Sequence'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/scopeAndSequence.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo '<h2>';
    echo __('Choose Course');
    echo '</h2>';

    $pupilsightCourseIDs = array();
    if (isset($_POST['pupilsightCourseID'])) {
        $pupilsightCourseIDs = $_POST['pupilsightCourseID'];
    }
    $pupilsightYearGroupID = '';
    if (isset($_POST['pupilsightYearGroupID'])) {
        $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
    }

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/".$_SESSION[$guid]['module']."/scopeAndSequence.php");

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');

    $options = array();
    try {
        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
        $sql = "SELECT pupilsightCourse.pupilsightCourseID, pupilsightCourse.name, pupilsightDepartment.name AS department FROM pupilsightCourse LEFT JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND NOT pupilsightYearGroupIDList='' AND map='Y' ORDER BY department, pupilsightCourse.nameShort";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) { }
    while ($row = $result->fetch()) {
        $options[$row["department"]][$row["pupilsightCourseID"]] = $row["name"];
    }

    $row = $form->addRow();
        $row->addLabel('pupilsightCourseID', __('Course'));
        $row->addSelect('pupilsightCourseID')->fromArray($options)->selectMultiple()->selected($pupilsightCourseIDs);

    $row = $form->addRow();
        $row->addLabel('pupilsightYearGroupID', __('Year Group'));
        $row->addSelectYearGroup('pupilsightYearGroupID')->selected($pupilsightYearGroupID);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    if (count($pupilsightCourseIDs) > 0) {
        //Set up for edit access
        $highestAction = getHighestGroupedAction($guid, '/modules/Planner/units.php', $connection2);
        $departments = array();
        if ($highestAction == 'Unit Planner_learningAreas') {
            $departmentCount = 1 ;
            try {
                $dataSelect = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlSelect = "SELECT pupilsightDepartment.pupilsightDepartmentID FROM pupilsightDepartment JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID AND (role='Coordinator' OR role='Assistant Coordinator' OR role='Teacher (Curriculum)') ORDER BY pupilsightDepartment.name";
                $resultSelect = $connection2->prepare($sqlSelect);
                $resultSelect->execute($dataSelect);
            } catch (PDOException $e) { echo $e->getMessage(); }
            while ($rowSelect = $resultSelect->fetch()) {
                $departments[$departmentCount] = $rowSelect['pupilsightDepartmentID'];
                $departmentCount ++;
            }
        }

        //Set up stats variables
        $countCourses = 0 ;
        $countCoursesNoUnits = 0 ;
        $coursesNoUnits = '';
        $countUnits = 0;
        $countUnitsNoKeywords = 0 ;
        $unitsNoKeywords = '';

        //Cycle through courses
        foreach ($pupilsightCourseIDs as $pupilsightCourseID) {
            //Check course exists
            try {
                $data = array();
                $sqlWhere = '';
                if ($pupilsightYearGroupID != '') {
                    $data['pupilsightYearGroupID'] = '%'.$pupilsightYearGroupID.'%';
                    $sqlWhere = ' AND pupilsightYearGroupIDList LIKE :pupilsightYearGroupID ';
                }
                $data['pupilsightSchoolYearID'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
                $data['pupilsightCourseID'] = $pupilsightCourseID;
                $sql = "SELECT pupilsightCourse.*, pupilsightDepartment.name AS department FROM pupilsightCourse LEFT JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND NOT pupilsightYearGroupIDList='' AND pupilsightCourseID=:pupilsightCourseID AND map='Y' $sqlWhere ORDER BY department, nameShort";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() == 1) {
                $countCourses ++ ;

                $row = $result->fetch();

                //Can this course's units be edited?
                $canEdit = false ;
                if ($highestAction == 'Unit Planner_all') {
                    $canEdit = true ;
                }
                else if ($highestAction == 'Unit Planner_learningAreas') {
                    foreach ($departments AS $department) {
                        if ($department == $row['pupilsightDepartmentID']) {
                            $canEdit = true ;
                        }
                    }
                }

                echo '<h2 class=\'bigTop\'>';
                echo $row['name'].' - '.$row['nameShort'];
                echo '</h2>';

                try {
                    $dataUnit = array('pupilsightCourseID' => $pupilsightCourseID);
                    $sqlUnit = 'SELECT pupilsightUnitID, pupilsightUnit.name, pupilsightUnit.description, attachment, tags FROM pupilsightUnit JOIN pupilsightCourse ON (pupilsightUnit.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightUnit.pupilsightCourseID=:pupilsightCourseID AND active=\'Y\' AND pupilsightCourse.map=\'Y\' AND pupilsightUnit.map=\'Y\' ORDER BY ordering, name';
                    $resultUnit = $connection2->prepare($sqlUnit);
                    $resultUnit->execute($dataUnit);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($resultUnit->rowCount() < 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('There are no records to display.');
                    echo '</div>';
                    $countCoursesNoUnits ++;
                    $coursesNoUnits .= $row['nameShort'].', ';
                }
                else {
                    echo "<table cellspacing='0' style='width: 100%'>";
                    echo "<tr class='head'>";
                    echo '<th style=\'width: 15%\'>';
                    echo __('Unit');
                    echo '</th>';
                    echo '<th style=\'width: 45%\'>';
                    echo __('Description');
                    echo '</th>';
                    echo "<th style=\'width: 30%\'>";
                    echo __('Concepts & Keywords');
                    echo '</th>';
                    echo "<th style='width: 10%'>";
                    echo __('Actions');
                    echo '</th>';
                    echo '</tr>';

                    $count = 0;
                    $rowNum = 'odd';
                    while ($rowUnit = $resultUnit->fetch()) {
                        if ($count % 2 == 0) {
                            $rowNum = 'even';
                        } else {
                            $rowNum = 'odd';
                        }
                        ++$count;
                        $countUnits ++;

                        //COLOR ROW BY STATUS!
                        echo "<tr class=$rowNum>";
                        echo '<td>';
                        echo $rowUnit['name'].'<br/>';
                        echo '</td>';
                        echo '<td>';
                        echo $rowUnit['description'].'<br/>';
                        if ($rowUnit['attachment'] != '') {
                            echo "<br/><br/><a href='".$_SESSION[$guid]['absoluteURL'].'/'.$rowUnit['attachment']."'>".__('Download Unit Outline').'</a></li>';
                        }
                        echo '</td>';
                        echo '<td>';
                        if ($rowUnit['tags'] == '') {
                            $countUnitsNoKeywords ++;
                            $unitsNoKeywords .= $row['nameShort'].' ('.$rowUnit['name'].'), ';
                        }
                        else {
                            $tags = explode(',', $rowUnit['tags']);
                            $tagsOutput = '' ;
                            foreach ($tags as $tag) {
                                $tagsOutput .= "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/conceptExplorer.php&tag=$tag'>".$tag.'</a>, ';
                            }
                            if ($tagsOutput != '')
                                $tagsOutput = substr($tagsOutput, 0, -2);
                            echo $tagsOutput;
                        }
                        echo '</td>';
                        echo '<td>';
                            if ($canEdit) {
                                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/units_edit.php&pupilsightUnitID=".$rowUnit['pupilsightUnitID']."&pupilsightCourseID=".$row['pupilsightCourseID']."&pupilsightSchoolYearID=".$row['pupilsightSchoolYearID']."'><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                            }
                            echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/units_dump.php&pupilsightCourseID=".$row['pupilsightCourseID']."&pupilsightUnitID=".$rowUnit['pupilsightUnitID']."&pupilsightSchoolYearID=".$row['pupilsightSchoolYearID']."'><img title='".__('View')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a>";
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }
            }
        }

        echo "<div class='alert alert-sucess'>";
            echo '<b>'.__('Total Courses').'</b>: '.$countCourses.'<br/>';
            echo '<b>'.__('Courses Without Units').'</b>: '.$countCoursesNoUnits.'<br/>';
            if ($coursesNoUnits != '') {
                print '<i>'.substr($coursesNoUnits, 0, -2).'</i><br/>';
            }
            echo '<b>'.__('Total Units').'</b>: '.$countUnits.'<br/>';
            echo '<b>'.__('Units Without Concepts & Keywords').'</b>: '.$countUnitsNoKeywords.'<br/>';
            if ($unitsNoKeywords != '') {
                print '<i>'.substr($unitsNoKeywords, 0, -2).'</i><br/>';
            }
        echo "</div>";
    }
}
