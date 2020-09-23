<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Timetable/spaceChange_manage_add.php') == false) {
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
        //Proceed!
        $page->breadcrumbs
            ->add(__('Manage Facility Changes'), 'spaceChange_manage.php')
            ->add(__('Add Facility Change'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        $step = null;
        if (isset($_GET['step'])) {
            $step = $_GET['step'];
        }
        if ($step != 1 and $step != 2) {
            $step = 1;
        }

        //Step 1
        if ($step == 1) {
            echo '<h2>';
            echo __('Step 1 - Choose Class');
            echo '</h2>';

            $form = Form::create('spaceChangeStep1', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/spaceChange_manage_add.php&step=2');

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $classes = array();

            // My Classes
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sql = "SELECT pupilsightCourseClass.pupilsightCourseClassID as value, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS name FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID ORDER BY name";
            $results = $pdo->executeQuery($data, $sql);
            if ($results->rowCount() > 0) {
                $classes['--'.__('My Classes').'--'] = $results->fetchAll(\PDO::FETCH_KEY_PAIR);
            }

            // All Classes, if we have access
            if ($highestAction == 'Manage Facility Changes_allClasses') {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sql = "SELECT pupilsightCourseClass.pupilsightCourseClassID as value, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS name FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name";
                $results = $pdo->executeQuery($data, $sql);
                if ($results->rowCount() > 0) {
                    $classes['--'.__('All Classes').'--'] = $results->fetchAll(\PDO::FETCH_KEY_PAIR);
                }
            }

            // Classed by Department, if we have access
            if ($highestAction == 'Manage Facility Changes_myDepartment') {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "SELECT pupilsightCourseClass.pupilsightCourseClassID as value, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS name FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND (pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID AND role='Coordinator') ORDER BY name";
                $results = $pdo->executeQuery($data, $sql);
                if ($results->rowCount() > 0) {
                    $classes['--'.__('My Department').'--'] = $results->fetchAll(\PDO::FETCH_KEY_PAIR);
                }
            }

            $row = $form->addRow();
                $row->addLabel('pupilsightCourseClassID', __('Class'));
                $row->addSelect('pupilsightCourseClassID')->fromArray($classes)->required()->placeholder();

            $row = $form->addRow();
                $row->addSubmit(__('Proceed'));

            echo $form->getOutput();

        } elseif ($step == 2) {
            echo '<h2>';
            echo __('Step 2 - Choose Options');
            echo '</h2>';
            echo '<p>';
            echo __('When choosing a facility, remember that they are not mutually exclusive: you can change two classes into one facility, change one class to join another class in their normal room, or assign no facility at all. The facilities listed below are not necessarily free at the requested time: please use the View Available Facilities report to check availability.');
            echo '</p>';

            $pupilsightCourseClassID = null;
            if (isset($_POST['pupilsightCourseClassID'])) {
                $pupilsightCourseClassID = $_POST['pupilsightCourseClassID'];
            }

            try {
                if ($highestAction == 'Manage Facility Changes_allClasses') {
                    $dataSelect = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sqlSelect = 'SELECT pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
                } else if ($highestAction == 'Manage Facility Changes_myDepartment') {
                    $dataSelect = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightSchoolYearID2' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID2' => $pupilsightCourseClassID);
                    $sqlSelect = '(SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID)
                    UNION
                    (SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND (pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID2 AND role=\'Coordinator\') AND pupilsightCourseClassID=:pupilsightCourseClassID2)';
                } else {
                    $dataSelect = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sqlSelect = 'SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
                }
                $resultSelect = $connection2->prepare($sqlSelect);
                $resultSelect->execute($dataSelect);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>";
                echo __('Your request failed due to a database error.');
                echo '</div>';
            }

            if ($resultSelect->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('Your request failed due to a database error.');
                echo '</div>';
            } else {
                $rowSelect = $resultSelect->fetch();

                $form = Form::create('spaceChangeStep2', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/spaceChange_manage_addProcess.php');
                $form->setFactory(DatabaseFormFactory::create($pdo));

                $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                $form->addHiddenValue('pupilsightCourseClassID', $pupilsightCourseClassID);

                $row = $form->addRow();
                    $row->addLabel('class', __('Class'));
                    $row->addTextField('class')->readonly()->setValue($rowSelect['course'].'.'.$rowSelect['class']);

                $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'date1' => date('Y-m-d'), 'date2' => date('Y-m-d'), 'time' => date('H:i:s'));
                $sql = 'SELECT pupilsightTTDayRowClass.pupilsightTTDayRowClassID, pupilsightTTColumnRow.name AS period, timeStart, timeEnd, pupilsightTTDay.name AS day, pupilsightTTDayDate.date, pupilsightTTSpaceChangeID FROM pupilsightTTDayRowClass JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightTTColumnRow ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID) JOIN pupilsightTTDay ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightTTDayDate ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) LEFT JOIN pupilsightTTSpaceChange ON (pupilsightTTSpaceChange.pupilsightTTDayRowClassID=pupilsightTTDayRowClass.pupilsightTTDayRowClassID AND pupilsightTTSpaceChange.date=pupilsightTTDayDate.date) WHERE pupilsightTTDayRowClass.pupilsightCourseClassID=:pupilsightCourseClassID AND (pupilsightTTDayDate.date>:date1 OR (pupilsightTTDayDate.date=:date2 AND timeEnd>:time)) ORDER BY pupilsightTTDayDate.date, timeStart';
                $results = $pdo->executeQuery($data, $sql);
                $classSlots = array_reduce($results->fetchAll(), function($array, $item) use ($guid) {
                    $key = $item['pupilsightTTDayRowClassID'].'-'.$item['date'];
                    $array[$key] = dateConvertBack($guid, $item['date']).' ('.$item['day'].' - '.$item['period'].')';
                    return $array;
                }, array());

                $row = $form->addRow();
                    $row->addLabel('pupilsightTTDayRowClassID', __('Upcoming Class Slots'));
                    $row->addSelect('pupilsightTTDayRowClassID')->fromArray($classSlots)->required()->placeholder();

                $row = $form->addRow();
                    $row->addLabel('pupilsightSpaceID', __('Facility'));
                    $row->addSelectSpace('pupilsightSpaceID');

                $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit();

                echo $form->getOutput();
            }
        }
    }
}
