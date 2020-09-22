<?php
/*
Pupilsight, Flexible & Open School System
*/


use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/externalAssessment.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('View All Assessments'));

    echo '<h2>';
    echo __('Search');
    echo '</h2>';

    $search = isset($_GET['search'])? $_GET['search'] : '';
    $allStudents = isset($_GET['allStudents'])? $_GET['allStudents'] : '';

    $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setClass('noIntBorder fullWidth standardForm');
    
    $form->addHiddenValue('q', '/modules/Formal Assessment/externalAssessment.php');

    $row = $form->addRow();
        $row->addLabel('search', __('Search For'))->description(__('Preferred, surname, username.'));
        $row->addTextField('search')->setValue($search);

    $row = $form->addRow();
    
        $row->addLabel('allStudents', __('All Students'))->description(__('Include all students, regardless of status and current enrolment. Some data may not display.'));
        $row->addCheckbox('allStudents')->checked($allStudents);
        
    $row = $form->addRow()->addClass('right_align');
        $row->addSearchSubmit($pupilsight->session, __('Clear Search'));
        
    echo $form->getOutput();

    echo '<h2>';
    echo __('Choose A Student');
    echo '</h2>';

    //Set pagination variable
    $page = 1;
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    }
    if ((!is_numeric($page)) or $page < 1) {
        $page = 1;
    }

    try {
        if ($allStudents != 'on') {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            $sql = "SELECT pupilsightPerson.pupilsightPersonID, pupilsightStudentEnrolmentID, surname, preferredName, title, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup FROM pupilsightPerson, pupilsightStudentEnrolment, pupilsightYearGroup, pupilsightRollGroup WHERE (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) AND (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) AND (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full' ORDER BY surname, preferredName";
            if ($search != '') {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'search1' => "%$search%", 'search2' => "%$search%", 'search3' => "%$search%");
                $and = ' AND ((preferredName LIKE :search1) OR (surname LIKE :search2) OR (username LIKE :search3))';
                $sql = "SELECT pupilsightPerson.pupilsightPersonID, pupilsightStudentEnrolmentID, surname, preferredName, title, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup FROM pupilsightPerson, pupilsightStudentEnrolment, pupilsightYearGroup, pupilsightRollGroup WHERE (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) AND (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) AND (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.status='Full' $and ORDER BY surname, preferredName";
            }
        } else {
            $data = array();
            $sql = 'SELECT DISTINCT pupilsightPerson.pupilsightPersonID, surname, preferredName, title, NULL AS yearGroup, NULL AS rollGroup FROM pupilsightPerson, pupilsightStudentEnrolment WHERE (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) ORDER BY surname, preferredName';
            if ($search != '') {
                $data = array('search1' => "%$search%", 'search2' => "%$search%", 'search3' => "%$search%");
                $and = ' AND ((preferredName LIKE :search1) OR (surname LIKE :search2) OR (username LIKE :search3))';
                $sql = "SELECT DISTINCT pupilsightPerson.pupilsightPersonID, surname, preferredName, title, NULL AS yearGroup, NULL AS rollGroup FROM pupilsightPerson, pupilsightStudentEnrolment WHERE (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) $and ORDER BY surname, preferredName";
            }
        }
        $sqlPage = $sql.' LIMIT '.$_SESSION[$guid]['pagination'].' OFFSET '.(($page - 1) * $_SESSION[$guid]['pagination']);
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
        if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
            printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'top', "search=$search&allStudents=$allStudents");
        }
        echo '<div class="table-responsive">';
        
        echo "<table cellspacing='0'  class='table display data-table text-nowrap dataTable no-footer' style='width: 100%'>";
        echo "<thead>";
        echo "<tr class='head'>";
        echo '<th>';
        echo __('Name');
        echo '</th>';
        echo '<th>';
        echo __('Year Group');
        echo '</th>';
        echo '<th>';
        echo __('Roll Group');
        echo '</th>';
        echo '<th>';
        echo __('Actions');
        echo '</th>';
        echo '</tr>';
        echo '</thead>';

        $count = 0;
        $rowNum = 'odd';
        try {
            $resultPage = $connection2->prepare($sqlPage);
            $resultPage->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }
        while ($row = $resultPage->fetch()) {
            if ($count % 2 == 0) {
                $rowNum = 'even';
            } else {
                $rowNum = 'odd';
            }
            ++$count;

            //COLOR ROW BY STATUS!
            echo "<tr class=$rowNum>";
            echo '<td>';
            echo Format::name('', $row['preferredName'], $row['surname'], 'Student', true);
            echo '</td>';
            echo '<td>';
            if ($row['yearGroup'] != '') {
                echo __($row['yearGroup']);
            }
            echo '</td>';
            echo '<td>';
            echo $row['rollGroup'];
            echo '</td>';
            echo '<td>';
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/externalAssessment_details.php&pupilsightPersonID='.$row['pupilsightPersonID']."&search=$search&allStudents=$allStudents'><i class='mdi mdi-eye-outline mdi-24px' style='font-size:20px;'></i></a> ";
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';

        if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
            printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'bottom', "search=$search&allStudents=$allStudents");
        }
    }
}
?>
