<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_edit_copy.php') == false) {
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
        $pupilsightMarkbookCopyClassID = (isset($_POST['pupilsightMarkbookCopyClassID']))? $_POST['pupilsightMarkbookCopyClassID'] : null;

        if ( empty($pupilsightCourseClassID) or empty($pupilsightMarkbookCopyClassID) ) {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {

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
                echo __('Copy Columns');
                echo '</h1>';
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                $course = $result->fetch();

                //Get teacher list
                $teacherList = getTeacherList($pdo, $pupilsightCourseClassID);
                $teaching = isset($teacherList[$_SESSION[$guid]['pupilsightPersonID']]);
                $isCoordinator = isDepartmentCoordinator($pdo, $_SESSION[$guid]['pupilsightPersonID']);

                $canEditThisClass = ($teaching == true || $isCoordinator == true or $highestAction2 == 'Edit Markbook_multipleClassesAcrossSchool' or $highestAction2 == 'Edit Markbook_everything');

                if ($canEditThisClass == false) {
                    //Acess denied
                    echo "<div class='alert alert-danger'>";
                    echo __('You do not have access to this action.');
                    echo '</div>';
                } else {
                    $page->breadcrumbs
                        ->add(
                            __('Edit {courseClass} Markbook', [
                                'courseClass' => Format::courseClassName($course['course'], $course['class']),
                            ]),
                            'markbook_edit.php',
                            [
                                'pupilsightCourseClassID' => $pupilsightCourseClassID,
                            ]
                        )
                        ->add(__('Copy Columns'));

		            try {
			            $data = array('pupilsightCourseClassID' => $pupilsightMarkbookCopyClassID);
			            $sql = "SELECT * FROM pupilsightMarkbookColumn WHERE pupilsightCourseClassID=:pupilsightCourseClassID";
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
	                	try {
		                    $data2 = array('pupilsightCourseClassID' => $pupilsightMarkbookCopyClassID);
		                    $sql2 = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID';
		                    $result2 = $connection2->prepare($sql2);
		                    $result2->execute($data2);
		                } catch (PDOException $e) {
		                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
		                }

		                $courseFrom = $result2->fetch();

	                	echo '<p>';
	                	printf( __('This action will copy the following columns from %s.%s to the current class %s.%s '), $courseFrom['course'], $courseFrom['class'], $course['course'], $course['class'] );
                        echo '</p>';
                        
                        echo '<fieldset>';

                        $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/Markbook/markbook_edit_copyProcess.php?pupilsightCourseClassID='.$pupilsightCourseClassID.'&pupilsightMarkbookCopyClassID='.$pupilsightMarkbookCopyClassID);
                        $form->setClass('fullWidth');

                        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                        $table = $form->addRow()->addTable()->setClass('fullWidth colorOddEven noMargin noPadding noBorder');
                        
                        $header = $table->addHeaderRow();
                            $header->addCheckAll()->checked(true);
                            $header->addContent(__('Name'));
                            $header->addContent(__('Type'));
                            $header->addContent(__('Description'));
                            $header->addContent(__('Date Added'));

                        while ($column = $result->fetch()) {
                            $row = $table->addRow();
                                $row->addCheckbox('copyColumnID['.$column['pupilsightMarkbookColumnID'].']')->setClass('textCenter')->checked(true);
                                $row->addContent($column['name'])->wrap('<strong>', '</strong>');
                                $row->addContent($column['type']);
                                $row->addContent($column['description']);
                                $row->addContent(!empty($column['date'])? dateConvertBack($guid, $column['date']) : '');
                        }

                        $row = $form->addRow();
                            $row->addSubmit();

                        echo $form->getOutput();

                        echo '</fieldset>';
	                }
	            }
		    }
        }
    }

    // Print the sidebar
    $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $pdo, $_SESSION[$guid]['pupilsightPersonID'], $pupilsightCourseClassID, 'markbook_edit.php');
}
