<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\Prefab\BulkActionForm;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Timetable/studentEnrolment_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
    $pupilsightCourseID = $_GET['pupilsightCourseID'];
    if ($pupilsightCourseClassID == '' or $pupilsightCourseID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sql = "SELECT pupilsightCourseClassID, pupilsightCourseClass.name, pupilsightCourseClass.nameShort, pupilsightCourse.pupilsightCourseID, pupilsightCourse.name AS courseName, pupilsightCourse.nameShort as courseNameShort, pupilsightCourse.description AS courseDescription, pupilsightCourse.pupilsightSchoolYearID, pupilsightSchoolYear.name as yearName, pupilsightYearGroupIDList FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE (role='Coordinator' OR role='Assistant Coordinator') AND pupilsightPersonID=:pupilsightPersonID AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClassID=:pupilsightCourseClassID";
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
                ]));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            echo '<h2>';
            echo __('Add Participants');
            echo '</h2>';

            $form = Form::create('manageEnrolment', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/studentEnrolment_manage_edit_addProcess.php?pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightCourseID=$pupilsightCourseID");

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $people = array();

            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightYearGroupIDList' => $values['pupilsightYearGroupIDList']);
            $sql = "SELECT pupilsightPerson.pupilsightPersonID, preferredName, surname, username, pupilsightRollGroup.name AS rollGroupName
                    FROM pupilsightPerson
                    JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                    JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                    WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full'
                    AND FIND_IN_SET(pupilsightStudentEnrolment.pupilsightYearGroupID, :pupilsightYearGroupIDList)
                    ORDER BY rollGroupName, surname, preferredName";
            $result = $pdo->executeQuery($data, $sql);

            if ($result->rowCount() > 0) {
                $people['--'.__('Enrolable Students').'--'] = array_reduce($result->fetchAll(), function ($group, $item) {
                    $group[$item['pupilsightPersonID']] = $item['rollGroupName'].' - '.Format::name('', htmlPrep($item['preferredName']), htmlPrep($item['surname']), 'Student', true).' ('.$item['username'].')';
                    return $group;
                }, array());
            }

            $sql = "SELECT pupilsightPerson.pupilsightPersonID, surname, preferredName, status, username FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) WHERE status='Full' OR status='Expected' ORDER BY surname, preferredName";
            $result = $pdo->executeQuery(array(), $sql);

            if ($result->rowCount() > 0) {
                $people['--'.__('All Students').'--'] = array_reduce($result->fetchAll(), function($group, $item) {
                    $expected = ($item['status'] == 'Expected')? '('.__('Expected').')' : '';
                    $group[$item['pupilsightPersonID']] = Format::name('', htmlPrep($item['preferredName']), htmlPrep($item['surname']), 'Student', true).' ('.$item['username'].')'.$expected;
                    return $group;
                }, array());
            }

            $row = $form->addRow();
                $row->addLabel('Members', __('Participants'));
                $row->addSelect('Members')->fromArray($people)->selectMultiple();

            $roles = array(
                'Student'    => __('Student'),
            );

            $row = $form->addRow();
                $row->addLabel('role', __('Role'));
                $row->addSelect('role')->fromArray($roles)->required();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();

            echo '<h2>';
            echo __('Current Participants');
            echo '</h2>';

            try {
                $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                $sql = "SELECT * FROM pupilsightPerson, pupilsightCourseClassPerson WHERE (pupilsightPerson.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) AND pupilsightCourseClassID=:pupilsightCourseClassID AND (status='Full' OR status='Expected') AND (role='Student' OR role='Teacher') ORDER BY role DESC, surname, preferredName";
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
                $form = BulkActionForm::create('bulkAction', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/studentEnrolment_manage_editProcessBulk.php');
                $form->addHiddenValue('pupilsightCourseID', $pupilsightCourseID);
                $form->addHiddenValue('pupilsightCourseClassID', $pupilsightCourseClassID);

                $bulkActions = array('Mark as left'  => __('Mark as left'));

                $row = $form->addBulkActionRow($bulkActions);
                $row->addSubmit(__('Go'));

                $table = $form->addRow()->addTable()->setClass('colorOddEven fullWidth');

                $header = $table->addHeaderRow();
                    $header->addContent(__('Name'));
                    $header->addContent(__('Email'));
                    $header->addContent(__('Role'));
                    $header->addContent(__('Actions'));
                    $header->addCheckAll();

                while ($student = $result->fetch()) {
                    $row = $table->addRow();
                    $name = Format::name('', htmlPrep($student['preferredName']), htmlPrep($student['surname']), 'Student', true);
                    if ($student['role'] == 'Student') {
                        $row->addWebLink($name)
                            ->setURL($_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='. $student['pupilsightPersonID'].'&subpage=Timetable');
                    } else {
                        $row->addContent($name);
                    }

                    $row->addContent($student['email']);
                    $row->addContent($student['role']);
                    $col = $row->addColumn()->addClass('inline');
                    if ($student['role'] == 'Student') {
                        $col->addWebLink('<img title="' . __('Edit') . '" src="./themes/' . $_SESSION[$guid]['pupilsightThemeName'] . '/img/config.png"/>')
                            ->setURL($_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . '/studentEnrolment_manage_edit_edit.php')
                            ->addParam('pupilsightCourseID', $pupilsightCourseID)
                            ->addParam('pupilsightCourseClassID', $pupilsightCourseClassID)
                            ->addParam('pupilsightPersonID', $student['pupilsightPersonID']);
                        $row->addCheckbox('pupilsightPersonID[]')->setValue($student['pupilsightPersonID'])->setClass('textCenter');
                    }
                    else {
                        $row->addContent();
                    }

                }

                echo $form->getOutput();
            }

            echo '<h2>';
            echo __('Former Students');
            echo '</h2>';

            try {
                $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                $sql = "SELECT * FROM pupilsightPerson, pupilsightCourseClassPerson WHERE (pupilsightPerson.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) AND pupilsightCourseClassID=:pupilsightCourseClassID AND (status='Full' OR status='Expected') AND role='Student - Left' ORDER BY role DESC, surname, preferredName";
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
                echo __('Name');
                echo '</th>';
                echo '<th>';
                echo __('Email');
                echo '</th>';
                echo '<th>';
                echo __('Class Role');
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
                    ++$count;

                            //COLOR ROW BY STATUS!
                            echo "<tr class=$rowNum>";
                    echo '<td>';
                    if ($row['role'] == 'Student - Left') {
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='.$row['pupilsightPersonID']."&subpage=Timetable'>".Format::name('', htmlPrep($row['preferredName']), htmlPrep($row['surname']), 'Student', true).'</a>';
                    } else {
                        echo Format::name('', htmlPrep($row['preferredName']), htmlPrep($row['surname']), 'Student', true);
                    }
                    echo '</td>';
                    echo '<td>';
                    echo $row['email'];
                    echo '</td>';
                    echo '<td>';
                    echo $row['role'];
                    echo '</td>';
                    echo '<td>';
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/studentEnrolment_manage_edit_edit.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightCourseID=$pupilsightCourseID&pupilsightPersonID=".$row['pupilsightPersonID']."'><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        }
    }
}
