<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

echo "<script type='text/javascript'>";
    echo '$(document).ready(function(){';
        echo "autosize($('textarea'));";
    echo '});';
echo '</script>';

if (isActionAccessible($guid, $connection2, '/modules/ATL/atl_write_data.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    // Register scripts available to the core, but not included by default
    $page->scripts->add('chart');
    
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='error'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Check if school year specified
        $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'] ?? '';
        $atlColumnID = $_GET['atlColumnID'];
        if ($pupilsightCourseClassID == '' or $atlColumnID == '') {
            echo "<div class='error'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                if ($highestAction == 'Write ATLs_all') {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourse.name AS courseName, pupilsightCourseClass.nameShort AS class, pupilsightYearGroupIDList FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightCourseClass.reportable='Y' ";
                } else {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID2' => $pupilsightCourseClassID, 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sql = "(SELECT pupilsightCourse.nameShort AS course, pupilsightCourse.name AS courseName, pupilsightCourseClass.nameShort AS class, pupilsightYearGroupIDList FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND pupilsightCourseClass.reportable='Y')
                        UNION
                        (SELECT pupilsightCourse.nameShort AS course, pupilsightCourse.name AS courseName, pupilsightCourseClass.nameShort AS class, pupilsightYearGroupIDList FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID2 AND pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID2 AND pupilsightDepartmentStaff.role='Coordinator' AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClass.reportable='Y')";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='error'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                try {
                    $data2 = array('atlColumnID' => $atlColumnID);
                    $sql2 = 'SELECT * FROM atlColumn WHERE atlColumnID=:atlColumnID';
                    $result2 = $connection2->prepare($sql2);
                    $result2->execute($data2);
                } catch (PDOException $e) {
                    echo "<div class='error'>".$e->getMessage().'</div>';
                }

                if ($result2->rowCount() != 1) {
                    echo "<div class='error'>";
                    echo 'The selected column does not exist, or you do not have access to it.';
                    echo '</div>';
                } else {
                    //Let's go!
                    $class = $result->fetch();
                    $values = $result2->fetch();

                    $page->breadcrumbs
                      ->add(__('Write {courseClass} ATLs', ['courseClass' => $class['course'].'.'.$class['class']]), 'atl_write.php', ['pupilsightCourseClassID' => $pupilsightCourseClassID])
                      ->add(__('Enter ATL Results'));

                    if (isset($_GET['return'])) {
                        returnProcess($guid, $_GET['return'], null, null);
                    }

                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'atlColumnID' => $atlColumnID, 'today' => date('Y-m-d'));
                    $sql = "SELECT pupilsightPerson.pupilsightPersonID, pupilsightPerson.title, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightPerson.dateStart, atlEntry.*
                        FROM pupilsightCourseClassPerson
                        JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                        LEFT JOIN atlEntry ON (atlEntry.pupilsightPersonIDStudent=pupilsightPerson.pupilsightPersonID AND atlEntry.atlColumnID=:atlColumnID)
                        WHERE pupilsightCourseClassPerson.pupilsightCourseClassID=:pupilsightCourseClassID
                        AND pupilsightCourseClassPerson.reportable='Y' AND pupilsightCourseClassPerson.role='Student'
                        AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<=:today) AND (dateEnd IS NULL  OR dateEnd>=:today)
                        ORDER BY pupilsightPerson.surname, pupilsightPerson.preferredName";
                    $result = $pdo->executeQuery($data, $sql);
                    $students = ($result->rowCount() > 0)? $result->fetchAll() : array();

                    $form = Form::create('internalAssessment', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/atl_write_dataProcess.php?pupilsightCourseClassID='.$pupilsightCourseClassID.'&atlColumnID='.$atlColumnID.'&address='.$_SESSION[$guid]['address']);
                    $form->setFactory(DatabaseFormFactory::create($pdo));
                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                    $form->addRow()->addHeading(__('Assessment Details'));

                    if (count($students) == 0) {
                        $form->addRow()->addHeading(__('Students'));
                        $form->addRow()->addAlert(__('There are no records to display.'), 'error');
                    } else {
                        $table = $form->addRow()->addTable()->setClass('smallIntBorder fullWidth colorOddEven noMargin noPadding noBorder');

                        $completeText = !empty($values['completeDate'])? __('Marked on').' '.dateConvertBack($guid, $values['completeDate']) : __('Unmarked');

                        $header = $table->addHeaderRow();
                            $header->addTableCell(__('Student'))->rowSpan(2);
                            $header->addTableCell($values['name'])
                                ->setTitle($values['description'])
                                ->append('<br><span class="small emphasis" style="font-weight:normal;">'.$completeText.'</span>')
                                ->setClass('textCenter')
                                ->colSpan(3);

                        $header = $table->addHeaderRow();
                            $header->addContent(__('Complete'))->setClass('textCenter');
                            $header->addContent(__('Rubric'))->setClass('textCenter');
                    }

                    foreach ($students as $index => $student) {
                        $count = $index+1;
                        $row = $table->addRow();

                        $row->addWebLink(Format::name('', $student['preferredName'], $student['surname'], 'Student', true))
                            ->setURL($_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php')
                            ->addParam('pupilsightPersonID', $student['pupilsightPersonID'])
                            ->addParam('subpage', 'Markbook')
                            ->wrap('<strong>', '</strong>')
                            ->prepend($count.') ');

                        $row->addCheckbox('complete'.$count)->setValue('Y')->checked($student['complete'])->setClass('textCenter');

                        $row->addWebLink('<img title="'.__('Mark Rubric').'" src="./themes/'.$_SESSION[$guid]['pupilsightThemeName'].'/img/rubric.png" style="margin-left:4px;"/>')
                        ->setURL($_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/atl_write_rubric.php')
                        ->setClass('thickbox textCenter')
                        ->addParam('pupilsightRubricID', $values['pupilsightRubricID'])
                        ->addParam('pupilsightCourseClassID', $pupilsightCourseClassID)
                        ->addParam('pupilsightPersonID', $student['pupilsightPersonID'])
                        ->addParam('atlColumnID', $atlColumnID)
                        ->addParam('type', 'effort')
                        ->addParam('width', '1100')
                        ->addParam('height', '550');

                        $form->addHiddenValue($count.'-pupilsightPersonID', $student['pupilsightPersonID']);
                    }

                    $form->addHiddenValue('count', $count);

                    $form->addRow()->addHeading(__('Assessment Complete?'));

                    $row = $form->addRow();
                        $row->addLabel('completeDate', __('Go Live Date'))->prepend('1. ')->append('<br/>'.__('2. Column is hidden until date is reached.'));
                        $row->addDate('completeDate');

                    $row = $form->addRow();
                        $row->addSubmit();

                    $form->loadAllValuesFrom($values);

                    echo $form->getOutput();
                }
            }
        }

        //Print sidebar
        $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $connection2, $pupilsightCourseClassID, 'write', $highestAction);
    }
}
