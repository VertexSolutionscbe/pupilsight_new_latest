<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Timetable/studentEnrolment_manage_edit_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
    $pupilsightCourseID = $_GET['pupilsightCourseID'];
    $pupilsightPersonID = $_GET['pupilsightPersonID'];
    if ($pupilsightCourseClassID == '' or $pupilsightCourseID == '' or $pupilsightPersonID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID2' => $pupilsightPersonID);
            $sql = "SELECT surname, preferredName, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourseClassPerson.role, pupilsightCourseClass.name, pupilsightCourseClass.nameShort, pupilsightCourse.pupilsightCourseID, pupilsightCourse.name AS courseName, pupilsightCourse.nameShort as courseNameShort, pupilsightCourse.description AS courseDescription, pupilsightCourse.pupilsightSchoolYearID, pupilsightSchoolYear.name as yearName, pupilsightYearGroupIDList FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE (pupilsightDepartmentStaff.role='Coordinator' OR pupilsightDepartmentStaff.role='Assistant Coordinator') AND pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID2";
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

            $page->breadcrumbs
                ->add(__('Manage Student Enrolment'), 'studentEnrolment_manage.php')
                ->add(__('Edit %1$s.%2$s Enrolment', [
                    '%1$s' => $values['courseNameShort'],
                    '%2$s' => $values['name']
                ]), 'studentEnrolment_manage_edit.php', [
                    'pupilsightCourseClassID' => $_GET['pupilsightCourseClassID'],
                    'pupilsightCourseID' => $_GET['pupilsightCourseID'],
                ])
                ->add(__('Edit Participant'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/studentEnrolment_manage_edit_editProcess.php?pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightCourseID=$pupilsightCourseID");
                
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);

            $row = $form->addRow();
                $row->addLabel('yearName', __('School Year'));
                $row->addTextField('yearName')->readonly()->setValue($values['yearName']);
            
            $row = $form->addRow();
                $row->addLabel('courseName', __('Course'));
                $row->addTextField('courseName')->readonly()->setValue($values['courseName']);

            $row = $form->addRow();
                $row->addLabel('name', __('Class'));
                $row->addTextField('name')->readonly()->setValue($values['name']);

            $row = $form->addRow();
                $row->addLabel('participant', __('Participant'));
                $row->addTextField('participant')->readonly()->setValue(Format::name('', htmlPrep($values['preferredName']), htmlPrep($values['surname']), 'Student'));

            $roles = array(
                'Student'        => __('Student'),
                'Student - Left' => __('Student - Left'),
            );

            $row = $form->addRow();
                $row->addLabel('role', __('Role'));
                $row->addSelect('role')->fromArray($roles)->required()->selected($values['role']);
            
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
