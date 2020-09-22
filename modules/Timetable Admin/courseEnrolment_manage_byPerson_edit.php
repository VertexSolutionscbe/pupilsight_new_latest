<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Forms\Prefab\BulkActionForm;
use Pupilsight\Domain\Timetable\CourseGateway;
use Pupilsight\Domain\Timetable\CourseEnrolmentGateway;

//Module includes for Timetable module
include './modules/Timetable/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    //Check if school year specified
    $pupilsightPersonID = isset($_GET['pupilsightPersonID'])? $_GET['pupilsightPersonID'] : '';
    $pupilsightSchoolYearID = isset($_GET['pupilsightSchoolYearID'])? $_GET['pupilsightSchoolYearID'] : '';
    $type = isset($_GET['type'])? $_GET['type'] : '';
    $allUsers = isset($_GET['allUsers']) ? $_GET['allUsers'] : '';
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    if (empty($pupilsightPersonID) or empty($pupilsightSchoolYearID)) {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        $courseGateway = $container->get(CourseGateway::class);
        $courseEnrolmentGateway = $container->get(CourseEnrolmentGateway::class);

        try {
            if ($allUsers == 'on') {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID);
                $sql = "SELECT pupilsightPerson.pupilsightPersonID, surname, preferredName, title, NULL AS pupilsightYearGroupID, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup, NULL AS type FROM pupilsightPerson LEFT JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID) LEFT JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) LEFT JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) WHERE pupilsightPerson.status='Full' AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName";
            } else {
                if ($type == 'Student') {
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightPersonID' => $pupilsightPersonID);
                    $sql = "(SELECT pupilsightPerson.pupilsightPersonID, pupilsightStudentEnrolmentID, surname, preferredName, title, pupilsightYearGroup.pupilsightYearGroupID, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup, 'Student' AS type FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full' AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID)";
                } elseif ($type == 'Staff') {
                    $data = array('pupilsightPersonID' => $pupilsightPersonID);
                    $sql = "(SELECT pupilsightPerson.pupilsightPersonID, NULL AS pupilsightStudentEnrolmentID, surname, preferredName, title, NULL AS pupilsightYearGroupID, NULL AS yearGroup, NULL AS rollGroup, 'Staff' as type FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightPerson.pupilsightPersonID=pupilsightStaff.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightRole.pupilsightRoleID=pupilsightPerson.pupilsightRoleIDPrimary) WHERE (pupilsightStaff.type='Teaching' OR pupilsightRole.category = 'Staff') AND pupilsightPerson.status='Full' AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID) ORDER BY surname, preferredName";
                }
            }
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
                ->add(__('Course Enrolment by Person'), 'courseEnrolment_manage_byPerson.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'allUsers' => $allUsers])
                ->add(Format::name('', $values['preferredName'], $values['surname'], 'Student'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            echo "<div class='linkTop'>";
            if ($search != '') {
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Timetable Admin/courseEnrolment_manage_byPerson.php&allUsers=$allUsers&search=$search&pupilsightSchoolYearID=$pupilsightSchoolYearID'>".__('Back to Search Results').'</a> | ';
            }
            echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Timetable/tt_view.php&pupilsightPersonID=$pupilsightPersonID&allUsers=$allUsers'>".__('View')."<img style='margin: 0 0 -4px 3px' title='".__('View')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/planner.png'/></a> ";
            echo '</div>';

            //INTERFACE TO ADD NEW CLASSES
            echo '<h2>';
            echo __('Add Classes');
            echo '</h2>';
            
            $form = Form::create('manageEnrolment', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/courseEnrolment_manage_byPerson_edit_addProcess.php?type=$type&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightPersonID=$pupilsightPersonID&allUsers=$allUsers&search=$search");
                
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $classes = array();
            if ($type == 'Student') {
                $enrolableClasses = $courseEnrolmentGateway->selectEnrolableClassesByYearGroup($pupilsightSchoolYearID, $values['pupilsightYearGroupID'])->fetchAll();

                if (!empty($enrolableClasses)) {
                    $classes['--'.__('Enrolable Classes').'--'] = Format::keyValue($enrolableClasses, 'pupilsightCourseClassID', function ($item) {
                        $courseClassName = Format::courseClassName($item['course'], $item['class']);
                        $teacherName = Format::name('', $item['preferredName'], $item['surname'], 'Staff');

                        return $courseClassName .' - '. (!empty($teacherName)? $teacherName.' - ' : '') . $item['studentCount'] . ' '.__('students');
                    });
                }
            }

            $allClasses = $courseGateway->selectClassesBySchoolYear($pupilsightSchoolYearID)->fetchAll();

            if (!empty($allClasses)) {
                $classes['--'.__('All Classes').'--'] = Format::keyValue($allClasses, 'pupilsightCourseClassID', function ($item) {
                    return Format::courseClassName($item['course'], $item['class']) .' - '. $item['courseName'];
                });
            }

            $row = $form->addRow();
                $row->addLabel('Members', __('Classes'));
                $row->addSelect('Members')->fromArray($classes)->selectMultiple();

            $roles = array(
                'Student'    => __('Student'),
                'Teacher'    => __('Teacher'),
                'Assistant'  => __('Assistant'),
                'Technician' => __('Technician'),
            );
            $selectedRole = ($type == 'Staff')? 'Teacher' : $type;

            $row = $form->addRow();
                $row->addLabel('role', __('Role'));
                $row->addSelect('role')->fromArray($roles)->required()->selected($selectedRole);

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();

            
            //SHOW CURRENT ENROLMENT
            echo '<h2>';
            echo __('Current Enrolment');
            echo '</h2>';

            // QUERY
            $criteria = $courseEnrolmentGateway->newQueryCriteria()
                ->sortBy('roleSortOrder')
                ->sortBy(['course', 'class'])
                ->fromPOST();

            $enrolment = $courseEnrolmentGateway->queryCourseEnrolmentByPerson($criteria, $pupilsightSchoolYearID, $pupilsightPersonID);

            // FORM
            $form = BulkActionForm::create('bulkAction', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/courseEnrolment_manage_byPerson_editProcessBulk.php?allUsers='.$allUsers);
            $form->addHiddenValue('type', $type);
            $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);
            $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

            $linkParams = array(
                'pupilsightSchoolYearID' => $pupilsightSchoolYearID,
                'pupilsightPersonID'     => $pupilsightPersonID,
                'type'               => $type,
                'allUsers'           => $allUsers,
                'search'             => $search,
            );

            $bulkActions = array(
                'Mark as left' => __('Mark as left'),
                'Delete'       => __('Delete'),
            );

            $col = $form->createBulkActionColumn($bulkActions);
                $col->addSubmit(__('Go'));

            // DATA TABLE
            $table = $form->addRow()->addDataTable('enrolment', $criteria)->withData($enrolment);

            $table->addMetaData('bulkActions', $col);

            $table->addColumn('courseClass', __('Class Code'))
                  ->sortable(['course', 'class'])
                  ->format(Format::using('courseClassName', ['course', 'class']));
            $table->addColumn('courseName', __('Course'));
            $table->addColumn('role', __('Class Role'))->translatable();
            $table->addColumn('reportable', __('Reportable'))
                  ->format(Format::using('yesNo', 'reportable'));

            // ACTIONS
            $table->addActionColumn()
                ->addParam('pupilsightCourseClassID')
                ->addParams($linkParams)
                ->format(function ($class, $actions) {
                    $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit_edit.php');
                    $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit_delete.php');
                });

            $table->addCheckboxColumn('pupilsightCourseClassID');

            echo $form->getOutput();


            //SHOW CURRENT TIMETABLE IN EDIT VIEW
            echo "<a name='tt'></a>";
            echo '<h2>';
            echo __('Current Timetable View');
            echo '</h2>';

            $pupilsightTTID = isset($_GET['pupilsightTTID'])? $_GET['pupilsightTTID'] : null;
            $ttDate = isset($_POST['ttDate'])? dateConvertToTimestamp(dateConvert($guid, $_POST['ttDate'])) : null;

            $tt = renderTT($guid, $connection2, $pupilsightPersonID, $pupilsightTTID, false, $ttDate, '/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit.php', "&pupilsightPersonID=$pupilsightPersonID&pupilsightSchoolYearID=$pupilsightSchoolYearID&type=$type#tt", 'full', true);
            if ($tt != false) {
                echo $tt;
            } else {
                echo "<div class='alert alert-danger'>";
                echo __('There are no records to display.');
                echo '</div>';
            }

            //SHOW OLD ENROLMENT RECORDS
            echo '<h2>';
            echo __('Old Enrolment');
            echo '</h2>';

            $enrolmentLeft = $courseEnrolmentGateway->queryCourseEnrolmentByPerson($criteria, $pupilsightSchoolYearID, $pupilsightPersonID, true);

            $table = DataTable::createPaginated('enrolmentLeft', $criteria);

            $table->addColumn('courseClass', __('Class Code'))->format(Format::using('courseClassName', ['course', 'class']));
            $table->addColumn('courseName', __('Course'));
            $table->addColumn('role', __('Class Role'));

            // ACTIONS
            $table->addActionColumn()
                ->addParam('pupilsightCourseClassID')
                ->addParams($linkParams)
                ->format(function ($class, $actions) {
                    $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit_edit.php');
                    $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit_delete.php');
                });

            echo $table->render($enrolmentLeft);
        }
    }
}
