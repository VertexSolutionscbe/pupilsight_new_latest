<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Get class variable
        $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'] ?? '';
        if ($pupilsightCourseClassID == '') {
            $pupilsightCourseClassID = (isset($_SESSION[$guid]['markbookClass']))? $_SESSION[$guid]['markbookClass'] : '';
        }

        if ($pupilsightCourseClassID == '') {
            $row = getAnyTaughtClass( $pdo, $_SESSION[$guid]['pupilsightPersonID'], $_SESSION[$guid]['pupilsightSchoolYearID'] );
            $pupilsightCourseClassID = (isset($row['pupilsightCourseClassID']))? $row['pupilsightCourseClassID'] : '';
        }

        if ($pupilsightCourseClassID == '') {
            echo '<h1>';
            echo __('Edit Markbook');
            echo '</h1>';
            echo "<div class='alert alert-warning'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';

            //Get class chooser
            echo classChooser($guid, $pdo, $pupilsightCourseClassID);
            return;
        }
        //Check existence of and access to this class.
        else {

            $highestAction2 = getHighestGroupedAction($guid, '/modules/Markbook/markbook_edit.php', $connection2);

            try {
                if ($highestAction == 'Edit Markbook_everything') {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightYearGroupIDList FROM pupilsightCourse, pupilsightCourseClass WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
                } else {
                    $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightYearGroupIDList FROM pupilsightCourse, pupilsightCourseClass, pupilsightCourseClassPerson WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo '<h1>';
                echo __('Edit Markbook');
                echo '</h1>';
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                $row = $result->fetch();

                $page->breadcrumbs->add(__('Edit {courseClass} Markbook', [
                    'courseClass' => Format::courseClassName($row['course'], $row['class']),
                ]));

                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, null);
                }

                //Add multiple columns
                if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_edit.php')) {

                    if ($highestAction2 == 'Edit Markbook_multipleClassesAcrossSchool' or $highestAction2 == 'Edit Markbook_multipleClassesInDepartment' or $highestAction2 == 'Edit Markbook_everything') {

                        //Check highest role in any department
                        $isCoordinator = isDepartmentCoordinator( $pdo, $_SESSION[$guid]['pupilsightPersonID'] );
                        if ($isCoordinator == true or $highestAction2 == 'Edit Markbook_multipleClassesAcrossSchool' or $highestAction2 == 'Edit Markbook_everything') {
                            echo "<div class='linkTop'>";
                            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/markbook_edit_addMulti.php&pupilsightCourseClassID=$pupilsightCourseClassID'>".__('Add Multiple Columns')."<img style='margin-left: 5px' title='".__('Add Multiple Columns')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new_multi.png'/></a>";
                            echo '</div>';
                        }
                    }
                }

                //Get teacher list
                $teacherList = getTeacherList( $pdo, $pupilsightCourseClassID );
                $teaching = (isset($teacherList[ $_SESSION[$guid]['pupilsightPersonID'] ]) );

                $canEditThisClass = ($teaching == true || $isCoordinator == true or $highestAction2 == 'Edit Markbook_multipleClassesAcrossSchool' or $highestAction2 == 'Edit Markbook_everything');

                if (!empty($teacherList)) {
                    echo '<h3>';
                    echo __('Teachers');
                    echo '</h3>';
                    echo '<ul>';
                    foreach ($teacherList as $teacher) {
                        echo '<li>'. $teacher . '</li>';
                    }
                    echo '</ul>';
                }

                //Print mark
                echo '<h3>';
                echo __('Markbook Columns');
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
                    $sql = 'SELECT * FROM pupilsightMarkbookColumn WHERE pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY completeDate DESC, name';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($canEditThisClass) {
                    echo "<div class='linkTop'>";
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/markbook_edit_add.php&pupilsightCourseClassID=$pupilsightCourseClassID'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a>";

                    if (getSettingByScope($connection2, 'Markbook', 'enableColumnWeighting') == 'Y') {
                        if (isActionAccessible($guid, $connection2, '/modules/Markbook/weighting_manage.php') == true) {
                            echo " | <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/weighting_manage.php&pupilsightCourseClassID=$pupilsightCourseClassID'>".__('Manage Weightings')."<img title='".__('Manage Weightings')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/run.png'/></a>";
                        }
                    }

                    echo '</div>';
                }

                if ($result->rowCount() < 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('There are no records to display.');
                    echo '</div>';
                } else {
                    echo "<table cellspacing='0' style='width: 100%'>";
                    echo "<tr class='head'>";
                    echo '<th>';
                    echo __('Name/Unit');
                    echo '</th>';
                    echo '<th>';
                    echo __('Type');
                    echo '</th>';
                    echo '<th>';
                    echo __('Date<br/>Added');
                    echo '</th>';
                    echo '<th>';
                    echo __('Date<br/>Complete');
                    echo '</th>';
                    echo '<th style="width:80px">';
                    echo __('Viewable <br/>to Students');
                    echo '</th>';
                    echo '<th style="width:80px">';
                    echo __('Viewable <br/>to Parents');
                    echo '</th>';
                    echo '<th style="width:125px">';
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
                        $unit = getUnit($connection2, $row['pupilsightUnitID'], $row['pupilsightCourseClassID']);
                        if (isset($unit[0])) {
                            echo $unit[0];
                        }
                        if (isset($unit[1])) {
                            echo '<br/><i>'.$unit[1].' '.__('Unit').'</i>';
                        }
                        echo '</td>';
                        echo '<td>';
                        echo $row['type'];
                        echo '</td>';
                        echo '<td>';
                        if (!empty($row['date']) && $row['date'] != '0000-00-00') {
                            echo dateConvertBack($guid, $row['date']);
                        }
                        echo '</td>';
                        echo '<td>';
                        if ($row['complete'] == 'Y') {
                            echo dateConvertBack($guid, $row['completeDate']);
                        }
                        echo '</td>';
                        echo '<td>';
                        echo $row['viewableStudents'];
                        echo '</td>';
                        echo '<td>';
                        echo $row['viewableParents'];
                        echo '</td>';
                        echo '<td>';
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/markbook_edit_edit.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightMarkbookColumnID=".$row['pupilsightMarkbookColumnID']."'><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                        echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module']."/markbook_edit_delete.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightMarkbookColumnID=".$row['pupilsightMarkbookColumnID']."&width=650&height=135'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a> ";
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/markbook_edit_data.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightMarkbookColumnID=".$row['pupilsightMarkbookColumnID']."'><img title='".__('Enter Data')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/markbook.png'/></a> ";
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/modules/Markbook/markbook_viewExport.php?pupilsightMarkbookColumnID='.$row['pupilsightMarkbookColumnID']."&pupilsightCourseClassID=$pupilsightCourseClassID&return=markbook_edit.php'><img title='".__('Export to Excel')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/download.png'/></a>";
                        echo '</td>';
                        echo '</tr>';

                        ++$count;
                    }
                    echo '</table>';
                }

                echo '<br/>&nbsp;<br/>';

                if ($canEditThisClass) {
                    echo '<h3>';
                    echo __('Copy Markbook Columns');
                    echo '</h1>';

                    $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Markbook/markbook_edit_copy.php&pupilsightCourseClassID='.$pupilsightCourseClassID);
                    $form->setFactory(DatabaseFormFactory::create($pdo));
                    $form->setClass('noIntBorder fullWidth');

                    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/applicationForm_manage.php');

                    $col = $form->addRow()->addColumn()->addClass('inline right');
                        $col->addContent(__('Copy from').' '.__('Class').': &nbsp;');
                        $col->addSelectClass('pupilsightMarkbookCopyClassID', $_SESSION[$guid]['pupilsightSchoolYearID'])->setClass('mediumWidth');
                        $col->addSubmit(__('Go'));

                    echo $form->getOutput();
                }
            }
        }
    }

    // Print the sidebar
    $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $pdo, $_SESSION[$guid]['pupilsightPersonID'], $pupilsightCourseClassID, 'markbook_edit.php');
}
