<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

// common variables
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
$pupilsightCourseID = $_GET['pupilsightCourseID'] ?? '';
$pupilsightUnitID = $_GET['pupilsightUnitID'] ?? '';

$page->breadcrumbs
    ->add(__('Unit Planner'), 'units.php', [
        'pupilsightSchoolYearID' => $pupilsightSchoolYearID,
        'pupilsightCourseID' => $pupilsightCourseID,
    ])
    ->add(__('Duplicate Unit'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/units_duplicate.php') == false) {
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
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Check if courseschool year specified
        if ($pupilsightCourseID == '' or $pupilsightSchoolYearID == '') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                if ($highestAction == 'Unit Planner_all') {
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID);
                    $sql = 'SELECT pupilsightCourseID, pupilsightCourse.name, pupilsightCourse.nameShort, pupilsightSchoolYear.name AS schoolYear
                        FROM pupilsightCourse
                        JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID)
                        WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID
                        AND pupilsightCourseID=:pupilsightCourseID';
                } elseif ($highestAction == 'Unit Planner_learningAreas') {
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = "SELECT pupilsightCourseID, pupilsightCourse.name, pupilsightCourse.nameShort, pupilsightSchoolYear.name AS schoolYear
                        FROM pupilsightCourse
                            JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID)
                            JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID)
                            JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID)
                        WHERE pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID
                            AND (role='Coordinator' OR role='Assistant Coordinator' OR role='Teacher (Curriculum)')
                            AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseID=:pupilsightCourseID
                        ORDER BY pupilsightCourse.nameShort";
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
                $values = $result->fetch();
                $courseName = $values['name'];
                $yearName = $values['schoolYear'];

                //Check if unit specified
                if ($pupilsightUnitID == '') {
                    echo "<div class='alert alert-danger'>";
                    echo __('You have not specified one or more required parameters.');
                    echo '</div>';
                } else {
                    if ($pupilsightUnitID == '') {
                        echo "<div class='alert alert-danger'>";
                        echo __('You have not specified one or more required parameters.');
                        echo '</div>';
                    } else {
                        try {
                            $data = array();
                            $sql = "SELECT pupilsightCourse.nameShort AS courseName, pupilsightSchoolYearID, pupilsightUnit.* FROM pupilsightUnit JOIN pupilsightCourse ON (pupilsightUnit.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightUnitID=$pupilsightUnitID AND pupilsightUnit.pupilsightCourseID=$pupilsightCourseID";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        if ($result->rowCount() != 1) {
                            echo "<div class='alert alert-danger'>";
                            echo __('The specified record cannot be found.');
                            echo '</div>';
                        } else {
                            //Let's go!
                            $values = $result->fetch();

                            $step = null;
                            if (isset($_GET['step'])) {
                                $step = $_GET['step'];
                            }
                            if ($step != 1 and $step != 2 and $step != 3) {
                                $step = 1;
                            }

                            //Step 1
                            if ($step == 1) {
                                echo '<h2>';
                                echo __('Step 1');
                                echo '</h2>';

                                $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/units_duplicate.php&step=2&pupilsightUnitID=$pupilsightUnitID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseID");
                                $form->setFactory(DatabaseFormFactory::create($pdo));

                                $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                                $form->addRow()->addHeading(__('Source'));

                                $row = $form->addRow();
                                    $row->addLabel('yearName', __('School Year'));
                                    $row->addTextField('yearName')->readonly()->setValue($yearName);

                                $row = $form->addRow();
                                    $row->addLabel('courseName', __('Course'));
                                    $row->addTextField('courseName')->readonly()->setValue($values['courseName']);

                                $row = $form->addRow();
                                    $row->addLabel('unitName', __('Unit'));
                                    $row->addTextField('unitName')->readonly()->setValue($values['name']);

                                $form->addRow()->addHeading(__('Target'));

                                $row = $form->addRow();
                                    $row->addLabel('pupilsightSchoolYearIDCopyTo', __('School Year'));
                                    $row->addSelectSchoolYear('pupilsightSchoolYearIDCopyTo', 'Active')->required();

                                if ($highestAction == 'Unit Planner_all') {
                                    $data = array();
                                    $sql = 'SELECT pupilsightCourse.pupilsightSchoolYearID as chainedTo, pupilsightCourseID AS value, pupilsightCourse.nameShort AS name FROM pupilsightCourse JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) ORDER BY nameShort';
                                } elseif ($highestAction == 'Unit Planner_learningAreas') {
                                    $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                                    $sql = "SELECT pupilsightCourse.pupilsightSchoolYearID as chainedTo, pupilsightCourseID AS value, pupilsightCourse.nameShort AS name FROM pupilsightCourse JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID AND (role='Coordinator' OR role='Assistant Coordinator' OR role='Teacher (Curriculum)') ORDER BY pupilsightCourse.nameShort";
                                }
                                $row = $form->addRow();
                                    $row->addLabel('pupilsightCourseIDTarget', __('Course'));
                                    $row->addSelect('pupilsightCourseIDTarget')->fromQueryChained($pdo, $sql, $data, 'pupilsightSchoolYearIDCopyTo')->required()->placeholder();

                                $row = $form->addRow();
                                    $row->addLabel('unitName', __('Unit'));
                                    $row->addTextField('unitName')->readonly()->setValue($values['name']);

                                $row = $form->addRow();
                                    $row->addFooter();
                                    $row->addSubmit();

                                echo $form->getOutput();

                            } elseif ($step == 2) {
                                echo '<h2>';
                                echo __('Step 2');
                                echo '</h2>';

                                $pupilsightCourseIDTarget = $_POST['pupilsightCourseIDTarget'] ?? '';

                                if ($pupilsightCourseIDTarget == '') {
                                    echo "<div class='alert alert-danger'>";
                                    echo __('You have not specified one or more required parameters.');
                                    echo '</div>';
                                } else {

                                    try {
                                        $dataSelect2 = array('pupilsightCourseID' => $pupilsightCourseIDTarget);
                                        $sqlSelect2 = 'SELECT pupilsightCourse.name AS course, pupilsightSchoolYear.name AS year FROM pupilsightCourse JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE pupilsightCourseID=:pupilsightCourseID';
                                        $resultSelect2 = $connection2->prepare($sqlSelect2);
                                        $resultSelect2->execute($dataSelect2);
                                    } catch (PDOException $e) {
                                    }
                                    if ($resultSelect2->rowCount() == 1) {
                                        $rowSelect2 = $resultSelect2->fetch();
                                        $access = true;
                                        $course = $rowSelect2['course'];
                                        $year = $rowSelect2['year'];
                                    }

                                    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'] . "/modules/" . $_SESSION[$guid]['module'] ."/units_duplicateProcess.php?pupilsightUnitID=$pupilsightUnitID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseID&address=".$_GET['q']);
                                    $form->setFactory(DatabaseFormFactory::create($pdo));

                                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                                    $form->addHiddenValue('pupilsightCourseIDTarget', $pupilsightCourseIDTarget);

                                    $row = $form->addRow();
                                        $row->addLabel('copyLessons', __('Copy Lessons?'));
                                        $row->addYesNoRadio('copyLessons')->required()->setClass('copyLessons right');

                                    $form->toggleVisibilityByClass('targetClass')->onRadio('copyLessons')->when('Y');

                                    $form->addRow()->addHeading(__('Source'));

                                    $row = $form->addRow();
                                        $row->addLabel('yearName', __('School Year'));
                                        $row->addTextField('yearName')->readonly()->setValue($yearName);

                                    $row = $form->addRow();
                                        $row->addLabel('courseName', __('Course'));
                                        $row->addTextField('courseName')->readonly()->setValue($values['courseName']);

                                    $row = $form->addRow();
                                        $row->addLabel('unitName', __('Unit'));
                                        $row->addTextField('unitName')->readonly()->setValue($values['name']);

                                    $dataSelectClassSource= array('pupilsightCourseID' => $pupilsightCourseID);
                                    $sqlSelectClassSource = "SELECT pupilsightCourseClassID as value, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS name FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourseClass.pupilsightCourseID=:pupilsightCourseID ORDER BY name";

                                    $row = $form->addRow()->addClass('targetClass');
                                        $row->addLabel('pupilsightCourseClassIDSource', __('Source Class'));
                                        $row->addSelect('pupilsightCourseClassIDSource')->fromQuery($pdo, $sqlSelectClassSource, $dataSelectClassSource)->required()->placeholder();

                                    $form->addRow()->addHeading(__('Target'));

                                    $row = $form->addRow();
                                        $row->addLabel('year', __('School Year'));
                                        $row->addTextField('year')->readonly()->setValue($year);

                                    $row = $form->addRow();
                                        $row->addLabel('course', __('Course'));
                                        $row->addTextField('course')->readonly()->setValue($course);

                                    $row = $form->addRow();
                                        $row->addLabel('unitName', __('Unit'));
                                        $row->addTextField('unitName')->readonly()->setValue($values['name']);

                                    $dataSelectClassTarget= array('pupilsightCourseID' => $pupilsightCourseIDTarget);
                                    $sqlSelectClassTarget = "SELECT pupilsightCourseClassID as value, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS name FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourseClass.pupilsightCourseID=:pupilsightCourseID ORDER BY name";

                                    $row = $form->addRow()->addClass('targetClass');
                                        $row->addLabel('pupilsightCourseClassIDTarget[]', __('Classes'));
                                        $row->addSelect('pupilsightCourseClassIDTarget[]')->fromQuery($pdo, $sqlSelectClassTarget, $dataSelectClassTarget)->required()->selectMultiple();

                                    $row = $form->addRow();
                                        $row->addFooter();
                                        $row->addSubmit();

                                    echo $form->getOutput();

                                }
                            }
                        }
                    }
                }
            }
        }
    }
    //Print sidebar
    $_SESSION[$guid]['sidebarExtra'] = sidebarExtraUnits($guid, $connection2, $pupilsightCourseID, $pupilsightSchoolYearID);
}
?>
