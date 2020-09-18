<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_edit_targets.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Check if school year specified
        $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'] ?? '';
        if ($pupilsightCourseClassID == '') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                if ($highestAction == 'Edit Markbook_everything') {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightYearGroupIDList, pupilsightScaleIDTarget FROM pupilsightCourse, pupilsightCourseClass WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
                } else {
                    $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightYearGroupIDList, pupilsightScaleIDTarget FROM pupilsightCourse, pupilsightCourseClass, pupilsightCourseClassPerson WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class";
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
                //Let's go!
                $course = $result->fetch();

                $page->breadcrumbs
                    ->add(
                        __('View {courseClass} Markbook', [
                            'courseClass' => Format::courseClassName($course['course'], $course['class']),
                        ]),
                        'markbook_view.php',
                        [
                            'pupilsightCourseClassID' => $pupilsightCourseClassID,
                        ]
                    )
                    ->add(__('Set Personalised Attainment Targets'));

                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, null);
                }

                $form = Form::create('markbookTargets', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/markbook_edit_targetsProcess.php?pupilsightCourseClassID='.$pupilsightCourseClassID);
                $form->setFactory(DatabaseFormFactory::create($pdo));
                $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                $selectedGradeScale = !empty($course['pupilsightScaleIDTarget'])? $course['pupilsightScaleIDTarget'] : $_SESSION[$guid]['defaultAssessmentScale'];
                $row = $form->addRow();
                    $row->addLabel('pupilsightScaleIDTarget', __('Target Scale'));
                    $row->addSelectGradeScale('pupilsightScaleIDTarget')->selected($selectedGradeScale);

                $table = $form->addRow()->addTable()->setClass('smallIntBorder fullWidth colorOddEven noMargin noPadding noBorder');

                $header = $table->addHeaderRow();
                $header->addContent(__('Student'));

                $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'today' => date('Y-m-d'));
                $sql = "SELECT title, surname, preferredName, pupilsightPerson.pupilsightPersonID, dateStart, pupilsightMarkbookTarget.pupilsightScaleGradeID as currentTarget
                        FROM pupilsightCourseClassPerson 
                        JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) 
                        LEFT JOIN pupilsightMarkbookTarget ON (pupilsightMarkbookTarget.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID 
                            AND pupilsightMarkbookTarget.pupilsightPersonIDStudent=pupilsightCourseClassPerson.pupilsightPersonID)
                        WHERE role='Student' AND pupilsightCourseClassPerson.pupilsightCourseClassID=:pupilsightCourseClassID 
                        AND status='Full' AND (dateStart IS NULL OR dateStart<=:today) AND (dateEnd IS NULL  OR dateEnd>=:today) 
                        ORDER BY surname, preferredName";
                $result = $pdo->executeQuery($data, $sql);

                if ($result->rowCount() > 0) {
                    $header->addContent(__('Attainment Target'))->setClass('w-64');

                    $sql = "SELECT pupilsightScale.pupilsightScaleID, pupilsightScaleGradeID as value, pupilsightScaleGrade.value as name 
                            FROM pupilsightScaleGrade 
                            JOIN pupilsightScale ON (pupilsightScaleGrade.pupilsightScaleID=pupilsightScale.pupilsightScaleID) 
                            WHERE pupilsightScale.active='Y' 
                            ORDER BY pupilsightScale.pupilsightScaleID, sequenceNumber";
                    $resultGrades = $pdo->executeQuery(array(), $sql);

                    $grades = ($resultGrades->rowCount() > 0)? $resultGrades->fetchAll() : array();
                    $gradesChained = array_combine(array_column($grades, 'value'), array_column($grades, 'pupilsightScaleID'));
                    $gradesOptions = array_combine(array_column($grades, 'value'), array_column($grades, 'name'));

                    $count = 0;
                    while ($student = $result->fetch()) {
                        $count++;

                        $row = $table->addRow();
                        $row->addWebLink(formatName('', $student['preferredName'], $student['surname'], 'Student', true))
                            ->setURL($_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php')
                            ->addParam('pupilsightPersonID', $student['pupilsightPersonID'])
                            ->addParam('subpage', 'Internal Assessment')
                            ->wrap('<strong>', '</strong>')
                            ->prepend($count.') ');
                        
                        $row->addSelect($count.'-pupilsightScaleGradeID')
                            ->fromArray($gradesOptions)
                            ->chainedTo('pupilsightScaleIDTarget', $gradesChained)
                            ->setClass('standardWidth')
                            ->selected($student['currentTarget'])
                            ->placeholder();

                        $form->addHiddenValue($count.'-pupilsightPersonID', $student['pupilsightPersonID']);
                    }

                    $form->addHiddenValue('count', $count);
                } else {
                    $table->addRow()->addAlert(__('There are no records to display.'), 'error');
                }

                $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit();

                echo $form->getOutput();
            }
        }
    }

    // Print the sidebar
    $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $pdo, $_SESSION[$guid]['pupilsightPersonID'], $pupilsightCourseClassID, 'markbook_edit_targets.php');
}
