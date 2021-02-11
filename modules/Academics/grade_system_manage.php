<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Curriculum\CurriculamGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/grade_system_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('Manage Grading System'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    echo '<h3>';
    echo __('Manage Grade System');
    echo '</h3>';
    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    if ($pupilsightSchoolYearID != $_SESSION[$guid]['pupilsightSchoolYearID']) {
        try {
            $data = array('pupilsightSchoolYearID' => $_GET['pupilsightSchoolYearID']);
            $sql = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }
        if ($result->rowcount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            $row = $result->fetch();
            $pupilsightSchoolYearID = $row['pupilsightSchoolYearID'];
            $pupilsightSchoolYearName = $row['name'];
        }
    }
    $CurriculamGateway = $container->get(CurriculamGateway::class);

    // QUERY
    $criteria = $CurriculamGateway->newQueryCriteria()
        ->sortBy('id')
        ->fromPOST();

    $gradedata = $CurriculamGateway->getAllGradeSystem($criteria);

    echo'<div  >&nbsp;&nbsp;<a href="fullscreen.php?q=/modules/Academics/grade_system_add.php&width=650" class= "btn btn-primary thickbox" style="height: 34px;  margin-left: 10px; float: right;"class=" btn btn-primary">Add</a>
    <div style="height:20px"></div>
    </div>';

    // DATA TABLE
    $table = DataTable::createPaginated('gradesystemmanage', $criteria);
    $table->addColumn('serial_number',__('Sl No'));
    $table->addColumn('name', __('Grade System Name'));
    $table->addColumn('code', __('Grade System  Code'))->translatable();
   
    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($general_tests, $actions) {
            $actions->addAction('editnew', __('Edit'))
                    ->setURL('/modules/Academics/grade_system_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Academics/grade_system_delete.php');

            $actions->addAction('Grade Configure', __('Grade Configure'))
                    ->setURL('/modules/Academics/grade_system_configure.php')
                    ->setIcon('cog');        
        });

    echo $table->render($gradedata);
}


