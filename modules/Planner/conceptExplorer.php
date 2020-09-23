<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs->add(__('Concept Explorer'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/conceptExplorer.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    //Get all concepts in current year and convert to ordered array
    $tagsAll = getTagList($connection2, $_SESSION[$guid]['pupilsightSchoolYearID']);

    //Deal with paramaters
    $tags = array();
    if (isset($_GET['tags'])) {
        $tags = $_GET['tags'];
    }
    else if (isset($_GET['tag'])) {
        $tags[0] = $_GET['tag'];
    }
    $pupilsightYearGroupID = isset($_GET['pupilsightYearGroupID'])? $_GET['pupilsightYearGroupID'] : '';

    //Display concept cloud
    if (count($tags) == 0) {
        echo '<h2>';
        echo __('Concept Cloud');
        echo '</h2>';
        echo getTagCloud($guid, $connection2, $_SESSION[$guid]['pupilsightSchoolYearID']);
    }

    //Allow tag selection
    $form = Form::create('conceptExplorer', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    
    $form->setTitle(__('Choose Concept'));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/conceptExplorer.php');

    $row = $form->addRow();
        $row->addLabel('tags', __('Concepts & Keywords'));
        $row->addSelect('tags')->fromArray(array_column($tagsAll, 1))->selectMultiple()->required()->selected($tags);

    $row = $form->addRow();
        $row->addLabel('pupilsightYearGroupID', __('Year Group'));
        $row->addSelectYearGroup('pupilsightYearGroupID')->selected($pupilsightYearGroupID);

    $row = $form->addRow();
        $row->addSearchSubmit($pupilsight->session, __('Clear Filters'));

    echo $form->getOutput();

    if (count($tags) > 0) {
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

        //Search for units with these tags
        try {
            $data = array() ;

            //Tag filter
            $sqlWhere = ' AND (';
            $count = 0;
            foreach ($tags as $tag) {
                $data["tag$count"] = "%,$tag,%";
                $sqlWhere .= "concat(',',tags,',') LIKE :"."tag$count"." OR ";
                $count ++;
            }
            if ($sqlWhere == ' AND (')
                $sqlWhere = '';
            else
                $sqlWhere = substr($sqlWhere, 0, -3).')';

            //Year group Filters
            if ($pupilsightYearGroupID != '') {
                $data['pupilsightYearGroupID'] = '%'.$pupilsightYearGroupID.'%';
                $sqlWhere .= ' AND pupilsightYearGroupIDList LIKE :pupilsightYearGroupID ';
            }


            $data['pupilsightSchoolYearID'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $sql = "SELECT pupilsightUnitID, pupilsightUnit.name, pupilsightUnit.description, attachment, tags, pupilsightCourse.name AS course, pupilsightDepartmentID, pupilsightCourse.pupilsightCourseID, pupilsightSchoolYearID FROM pupilsightUnit JOIN pupilsightCourse ON (pupilsightUnit.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' AND pupilsightUnit.map='Y' AND pupilsightCourse.map='Y' $sqlWhere ORDER BY pupilsightUnit.name";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }


        if ($result->rowCount() < 1) {
            echo "<div class='alert alert-danger'>";
            echo __('There are no records to display.');
            echo '</div>';
        }
        else {
            echo '<h2 class=\'bigTop\'>';
            echo __('Results');
            echo '</h2>';

            echo "<table cellspacing='0' style='width: 100%'>";
            echo "<tr class='head'>";
            echo '<th style=\'width: 23%\'>';
            echo __('Unit');
            echo "<br/><span style='font-style: italic; font-size: 85%'>".__('Course').'</span>';
            echo '</th>';
            echo '<th style=\'width: 37%\'>';
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
            while ($row = $result->fetch()) {
                //Can this unit be edited?
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

                if ($count % 2 == 0) {
                    $rowNum = 'even';
                } else {
                    $rowNum = 'odd';
                }
                ++$count;

                //COLOR ROW BY STATUS!
                echo "<tr class=$rowNum>";
                echo '<td>';
                echo $row['name'].'<br/>';
                echo "<span style='font-style: italic; font-size: 85%'>".$row['course'].'</span>';
                echo '</td>';
                echo '<td>';
                echo $row['description'].'<br/>';
                if ($row['attachment'] != '') {
                    echo "<br/><br/><a href='".$_SESSION[$guid]['absoluteURL'].'/'.$row['attachment']."'>".__('Download Unit Outline').'</a></li>';
                }
                echo '</td>';
                echo '<td>';
                $tagsUnit = explode(',', $row['tags']);
                $tagsOutput = '' ;
                foreach ($tagsUnit as $tag) {
                    $style = '';
                    foreach ($tags AS $tagInner) {
                        if ($tagInner == $tag) {
                            $style = 'style=\'color: #000; font-weight: bold\'';
                        }
                    }
                    $tagsOutput .= "<a $style href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/conceptExplorer.php&tag=$tag'>".$tag.'</a>, ';
                }
                if ($tagsOutput != '')
                    $tagsOutput = substr($tagsOutput, 0, -2);
                echo $tagsOutput;
                echo '</td>';
                echo '<td>';
                    if ($canEdit) {
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/units_edit.php&pupilsightUnitID='.$row['pupilsightUnitID']."&pupilsightCourseID=".$row['pupilsightCourseID']."&pupilsightSchoolYearID=".$row['pupilsightSchoolYearID']."'><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/units_dump.php&pupilsightCourseID=".$row['pupilsightCourseID']."&pupilsightUnitID=".$row['pupilsightUnitID']."&pupilsightSchoolYearID=".$row['pupilsightSchoolYearID']."&sidebar=false'><img title='".__('View')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a>";
                    }
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
    }
}
