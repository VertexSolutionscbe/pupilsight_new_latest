<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Tables\View\GridView;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$makeDepartmentsPublic = getSettingByScope($connection2, 'Departments', 'makeDepartmentsPublic');
if (isActionAccessible($guid, $connection2, '/modules/Departments/department.php') == false and $makeDepartmentsPublic != 'Y') {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $pupilsightDepartmentID = $_GET['pupilsightDepartmentID'];
    if ($pupilsightDepartmentID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightDepartmentID' => $pupilsightDepartmentID);
            $sql = 'SELECT * FROM pupilsightDepartment WHERE pupilsightDepartment.pupilsightDepartmentID=:pupilsightDepartmentID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            $row = $result->fetch();

            //Get role within learning area
            $role = null;
            if (isset($_SESSION[$guid]['username'])) {
                $role = getRole($_SESSION[$guid]['pupilsightPersonID'], $pupilsightDepartmentID, $connection2);
            }

            $urlParams = ['pupilsightDepartmentID' => $pupilsightDepartmentID];
            
            $page->breadcrumbs
                ->add(__('View All'), 'departments.php')
                ->add($row['name'], 'departments.php', $urlParams);

            //Print overview
            if ($row['blurb'] != '' or $role == 'Coordinator' or $role == 'Assistant Coordinator' or $role == 'Teacher (Curriculum)' or $role == 'Director' or $role == 'Manager') {
                echo '<h2>';
                echo __('Overview');
                if ($role == 'Coordinator' or $role == 'Assistant Coordinator' or $role == 'Teacher (Curriculum)' or $role == 'Director' or $role == 'Manager') {
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/department_edit.php&pupilsightDepartmentID=$pupilsightDepartmentID'><img style='margin-left: 5px' title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                }
                echo '</h2>';
                echo '<p>';
                echo $row['blurb'];
                echo '</p>';
            }

            //Print staff
            $dataStaff = array('pupilsightDepartmentID' => $pupilsightDepartmentID);
            $sqlStaff = "SELECT pupilsightPerson.pupilsightPersonID, pupilsightDepartmentStaff.role, title, surname, preferredName, image_240, pupilsightStaff.jobTitle FROM pupilsightDepartmentStaff JOIN pupilsightPerson ON (pupilsightDepartmentStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND pupilsightDepartmentStaff.pupilsightDepartmentID=:pupilsightDepartmentID ORDER BY pupilsightDepartmentStaff.role,pupilsightPerson.surname, pupilsightPerson.preferredName";
            // echo $sqlStaff;
            // die();
            $staff = $pdo->select($sqlStaff, $dataStaff)->toDataSet();

            // Data Table
            $gridRenderer = new GridView($container->get('twig'));
            $table = $container->get(DataTable::class)->setRenderer($gridRenderer);
            $table->setTitle(__('Staff'));
            $table->addMetaData('gridClass', 'rounded-sm bg-blue-100 border py-2');
            $table->addMetaData('gridItemClass', 'w-1/2 sm:w-1/4 md:w-1/5 my-2 text-center');

            $table->addColumn('image_240')
                ->format(function ($person) {
                    return Format::userPhoto($person['image_240'], 'sm', '');
                });

            $canViewProfile = isActionAccessible($guid, $connection2, '/modules/Staff/staff_view_details.php');
            $table->addColumn('name')
                ->setClass('text-xs font-bold mt-1')
                ->format(function ($person) use ($canViewProfile) {
                    $name = Format::name($person['title'], $person['preferredName'], $person['surname'], 'Staff');
                    $url = "./index.php?q=/modules/Staff/staff_view_details.php&pupilsightPersonID=".$person['pupilsightPersonID'];
                    return $canViewProfile
                        ? Format::link($url, $name)
                        : $name;
                });

            $table->addColumn('jobTitle')
                ->setClass('text-xs text-gray italic leading-snug')
                ->format(function ($person) {
                    return !empty($person['jobTitle']) ? $person['jobTitle'] : __($person['role']);
                });

            echo $table->render($staff);


            //Print sidebar
            $sidebarExtra = '';

            //Print subject list
            if ($row['subjectListing'] != '') {
                $sidebarExtra .= '<div class="column-no-break">';
                $sidebarExtra .= '<h4>';
                $sidebarExtra .= __('Subject List');
                $sidebarExtra .= '</h4>';

                $sidebarExtra .= '<ul>';
                $subjects = explode(',', $row['subjectListing']);
                for ($i = 0;$i < count($subjects);++$i) {
                    $sidebarExtra .= '<li>'.$subjects[$i].'</li>';
                }
                $sidebarExtra .= '</ul>';
                $sidebarExtra .= '</div>';
            }

            //Print current course list
            try {
                $dataCourse = array('pupilsightDepartmentID' => $pupilsightDepartmentID);
                $sqlCourse = "SELECT pupilsightCourse.* FROM pupilsightCourse 
                    JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) 
                    WHERE pupilsightDepartmentID=:pupilsightDepartmentID 
                    AND pupilsightYearGroupIDList <> '' 
                    AND pupilsightSchoolYearID=(SELECT pupilsightSchoolYearID FROM pupilsightSchoolYear WHERE status='Current') 
                    GROUP BY pupilsightCourse.pupilsightCourseID 
                    ORDER BY nameShort, name";
                $resultCourse = $connection2->prepare($sqlCourse);
                $resultCourse->execute($dataCourse);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($resultCourse->rowCount() > 0) {
                $sidebarExtra .= '<div class="column-no-break">';
                if ($role == 'Coordinator' or $role == 'Assistant Coordinator' or $role == 'Teacher (Curriculum)') {
                    $sidebarExtra .= '<h4>';
                    $sidebarExtra .= __('Current Courses');
                    $sidebarExtra .= '</h4>';
                } else {
                    $sidebarExtra .= '<h4>';
                    $sidebarExtra .= __('Course List');
                    $sidebarExtra .= '</h4>';
                }

                $sidebarExtra .= '<ul>';
                while ($rowCourse = $resultCourse->fetch()) {
                    $sidebarExtra .= "<li><a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Departments/department_course.php&pupilsightDepartmentID=$pupilsightDepartmentID&pupilsightCourseID=".$rowCourse['pupilsightCourseID']."'>".$rowCourse['nameShort']."</a> <span style='font-size: 85%; font-style: italic'>".$rowCourse['name'].'</span></li>';
                }
                $sidebarExtra .= '</ul>';
                $sidebarExtra .= '</div>';
            }

            //Print other courses
            if ($role == 'Coordinator' or $role == 'Assistant Coordinator' or $role == 'Teacher (Curriculum)' or $role == 'Teacher') {
                $data = array('pupilsightDepartmentID' => $pupilsightDepartmentID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sql = "SELECT pupilsightSchoolYear.name AS year, pupilsightCourse.pupilsightCourseID as value, pupilsightCourse.name AS name
                        FROM pupilsightCourse
                        JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID)
                        WHERE pupilsightDepartmentID=:pupilsightDepartmentID
                        AND NOT pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID
                        ORDER BY sequenceNumber, pupilsightCourse.nameShort, name";
                $result = $pdo->executeQuery($data, $sql);

                $courses = ($result->rowCount() > 0)? $result->fetchAll() : array();
                $courses = array_reduce($courses, function($carry, $item) {
                    $carry[$item['year']][$item['value']] = $item['name'];
                    return $carry;
                }, array());

                if (!empty($courses)) {
                    $form = Form::create('courseSelect', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
                    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/department_course.php');
                    $form->addHiddenValue('pupilsightDepartmentID', $pupilsightDepartmentID);

                    $row = $form->addRow()->addClass('items-center');
                        $row->addSelect('pupilsightCourseID')
                            ->fromArray($courses)
                            ->placeholder()
                            ->setClass('w-48 float-none');
                    $row->addSubmit(__('Go'));

                    $sidebarExtra .= '<div class="column-no-break">';
                    $sidebarExtra .= '<h4>';
                    $sidebarExtra .= __('Non-Current Courses');
                    $sidebarExtra .= '</h4>';
                    
                    $sidebarExtra .= $form->getOutput();
                    $sidebarExtra .= '</div>';
                }
            }

            //Print useful reading
            try {
                $dataReading = array('pupilsightDepartmentID' => $pupilsightDepartmentID);
                $sqlReading = 'SELECT * FROM pupilsightDepartmentResource WHERE pupilsightDepartmentID=:pupilsightDepartmentID ORDER BY name';
                $resultReading = $connection2->prepare($sqlReading);
                $resultReading->execute($dataReading);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($resultReading->rowCount() > 0 or $role == 'Coordinator' or $role == 'Assistant Coordinator' or $role == 'Teacher (Curriculum)' or $role == 'Director' or $role == 'Manager') {
                $sidebarExtra .= '<div class="column-no-break">';
                $sidebarExtra .= '<h4>';
                $sidebarExtra .= __('Useful Reading');
                if ($role == 'Coordinator' or $role == 'Assistant Coordinator' or $role == 'Teacher (Curriculum)' or $role == 'Director' or $role == 'Manager') {
                    $sidebarExtra .= "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/department_edit.php&pupilsightDepartmentID=$pupilsightDepartmentID'><img style='margin-left: 5px' title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                }
                $sidebarExtra .= '</h4>';

                $sidebarExtra .= '<ul>';
                while ($rowReading = $resultReading->fetch()) {
                    if ($rowReading['type'] == 'Link') {
                        $sidebarExtra .= "<li><a target='_blank' href='".$rowReading['url']."'>".$rowReading['name'].'</a></li>';
                    } else {
                        $sidebarExtra .= "<li><a href='".$_SESSION[$guid]['absoluteURL'].'/'.$rowReading['url']."'>".$rowReading['name'].'</a></li>';
                    }
                }
                $sidebarExtra .= '</ul>';
                $sidebarExtra .= '</div>';
            }

            $_SESSION[$guid]['sidebarExtra'] = $sidebarExtra;
        }
    }
}
