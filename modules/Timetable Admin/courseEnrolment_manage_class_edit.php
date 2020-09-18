<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Forms\Prefab\BulkActionForm;
use Pupilsight\Domain\Timetable\CourseEnrolmentGateway;
use Pupilsight\Domain\Timetable\CourseGateway;
use Pupilsight\Domain\User\UserGateway;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_manage_class_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightCourseClassID = isset($_GET['pupilsightCourseClassID'])? $_GET['pupilsightCourseClassID'] : '';
    $pupilsightCourseID = isset($_GET['pupilsightCourseID'])? $_GET['pupilsightCourseID'] : '';
    $pupilsightSchoolYearID = isset($_GET['pupilsightSchoolYearID'])? $_GET['pupilsightSchoolYearID'] : '';

    if (empty($pupilsightCourseID) or empty($pupilsightSchoolYearID) or empty($pupilsightCourseClassID)) {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        $userGateway = $container->get(UserGateway::class);
        $courseGateway = $container->get(CourseGateway::class);
        $courseEnrolmentGateway = $container->get(CourseEnrolmentGateway::class);

        $values = $courseGateway->getCourseClassByID($pupilsightCourseClassID);

        if (empty($values)) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $page->breadcrumbs
                ->add(__('Course Enrolment by Class'), 'courseEnrolment_manage.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
                ->add(__('Edit %1$s.%2$s Enrolment', ['%1$s' => $values['courseNameShort'], '%2$s' => $values['name']]));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            echo '<h2>';
            echo __('Add Participants');
            echo '</h2>';

            $form = Form::create('manageEnrolment', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/courseEnrolment_manage_class_edit_addProcess.php?pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightCourseID=$pupilsightCourseID&pupilsightSchoolYearID=$pupilsightSchoolYearID");
                
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $people = array();

            $enrolableStudents = $courseEnrolmentGateway->selectEnrolableStudentsByYearGroup($pupilsightSchoolYearID, $values['pupilsightYearGroupIDList'])->fetchAll();
            if (!empty($enrolableStudents)) {
                $people['--'.__('Enrolable Students').'--'] = Format::keyValue($enrolableStudents, 'pupilsightPersonID', function ($item) {
                    return $item['rollGroupName'].' - '.Format::name('', $item['preferredName'], $item['surname'], 'Student', true).' ('.$item['username'].')';
                });
            }

            $allUsers = $userGateway->selectUserNamesByStatus(['Full', 'Expected'])->fetchAll();
            if (!empty($allUsers)) {
                $people['--'.__('All Users').'--'] = Format::keyValue($allUsers, 'pupilsightPersonID', function ($item) {
                    $expected = ($item['status'] == 'Expected')? '('.__('Expected').')' : '';
                    return Format::name('', $item['preferredName'], $item['surname'], 'Student', true).' ('.$item['username'].', '.$item['roleCategory'].')'.$expected;
                });
            }

            $row = $form->addRow();
                $row->addLabel('Members', __('Participants'));
                $row->addSelect('Members')->fromArray($people)->selectMultiple();

            $roles = array(
                'Student'    => __('Student'),
                'Teacher'    => __('Teacher'),
                'Assistant'  => __('Assistant'),
                'Technician' => __('Technician'),
                'Parent'     => __('Parent'),
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

            $linkedName = function ($person) use ($guid) {
                $isStudent = stripos($person['role'], 'Student') !== false;
                $name = Format::name('', $person['preferredName'], $person['surname'], $isStudent ? 'Student' : 'Staff', true, true);
                return $isStudent
                    ? Format::link('./index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='.$person['pupilsightPersonID'].'&subpage=Timetable', $name).'<br/>'.Format::userStatusInfo($person)
                    : $name;
            };

            // QUERY
            $criteria = $courseEnrolmentGateway->newQueryCriteria()
                ->sortBy('roleSortOrder')
                ->sortBy(['pupilsightPerson.surname', 'pupilsightPerson.preferredName'])
                ->fromPOST();

            $enrolment = $courseEnrolmentGateway->queryCourseEnrolmentByClass($criteria, $pupilsightSchoolYearID, $pupilsightCourseClassID, false, true);

            // FORM
            $form = BulkActionForm::create('bulkAction', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/courseEnrolment_manage_class_editProcessBulk.php');
            $form->addHiddenValue('pupilsightCourseID', $pupilsightCourseID);
            $form->addHiddenValue('pupilsightCourseClassID', $pupilsightCourseClassID);
            $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

            $linkParams = array(
                'pupilsightCourseID'      => $pupilsightCourseID,
                'pupilsightCourseClassID' => $pupilsightCourseClassID,
                'pupilsightSchoolYearID'  => $pupilsightSchoolYearID,
            );

            $bulkActions = array(
                'Copy to class' => __('Copy to class'),
                'Mark as left'  => __('Mark as left'),
                'Delete'        => __('Delete'),
            );

            $col = $form->createBulkActionColumn($bulkActions);
                $classesBySchoolYear = $courseGateway->selectClassesBySchoolYear($pupilsightSchoolYearID)->fetchAll();
                $classesBySchoolYear = Format::keyValue($classesBySchoolYear, 'pupilsightCourseClassID', 'courseClassName', ['course', 'class']);
                $col->addSelect('pupilsightCourseClassIDCopyTo')->fromArray($classesBySchoolYear)->setClass('shortWidth copyTo');
                $col->addSubmit(__('Go'));

            $form->toggleVisibilityByClass('copyTo')->onSelect('action')->when('Copy to class');

            // DATA TABLE
            $table = $form->addRow()->addDataTable('enrolment', $criteria)->withData($enrolment);

            $table->modifyRows(function ($person, $row) {
                if (!(empty($person['dateStart']) || $person['dateStart'] <= date('Y-m-d'))) $row->addClass('error');
                return $row;
            });
            $table->addMetaData('bulkActions', $col);

            $table->addColumn('name', __('Name'))
                  ->sortable(['pupilsightPerson.surname', 'pupilsightPerson.preferredName'])
                  ->format($linkedName);
            $table->addColumn('email', __('Email'));
            $table->addColumn('role', __('Class Role'));
            $table->addColumn('reportable', __('Reportable'))
                  ->format(Format::using('yesNo', 'reportable'));

            // ACTIONS
            $table->addActionColumn()
                ->addParam('pupilsightPersonID')
                ->addParams($linkParams)
                ->format(function ($person, $actions) {
                    $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/Timetable Admin/courseEnrolment_manage_class_edit_edit.php');
                    $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Timetable Admin/courseEnrolment_manage_class_edit_delete.php');
                });

            $table->addCheckboxColumn('pupilsightPersonID');

            echo $form->getOutput();


            echo '<h2>';
            echo __('Former Participants');
            echo '</h2>';

            $enrolmentLeft = $courseEnrolmentGateway->queryCourseEnrolmentByClass($criteria, $pupilsightSchoolYearID, $pupilsightCourseClassID, true, true);

            $table = DataTable::createPaginated('enrolmentLeft', $criteria);

            $table->modifyRows(function ($person, $row) {
                if (!(empty($person['dateStart']) || $person['dateStart'] <= date('Y-m-d'))) $row->addClass('error');
                return $row;
            });
            
            $table->addColumn('name', __('Name'))
                ->sortable(['surname', 'preferredName'])
                ->format($linkedName);
            $table->addColumn('email', __('Email'));
            $table->addColumn('role', __('Class Role'));

            // ACTIONS
            $table->addActionColumn()
                ->addParam('pupilsightPersonID')
                ->addParams($linkParams)
                ->format(function ($person, $actions) {
                    $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/Timetable Admin/courseEnrolment_manage_class_edit_edit.php');
                    $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Timetable Admin/courseEnrolment_manage_class_edit_delete.php');
                });

            echo $table->render($enrolmentLeft);
        }
    }
}
