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

if (isActionAccessible($guid, $connection2, '/modules/Academics/ac_manage_remarks.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('Manage Remarks'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo '<h3>';
    echo __('Manage Remarks');
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

    $ac_remarks = $CurriculamGateway->getAllcurriculamRemarksNew($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('acRemarksmanage', $criteria);


    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/Academics/ac_manage_remarks_add.php')
        ->displayLabel();

    $table->addColumn('remarkcode', __('Code'));
    $table->addColumn('description', __('Description'))->translatable();
    $table->addColumn('subject', __('Subject'));
    $table->addColumn('skillname', __('Skill'));
      
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($ac_remarks, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Academics/ac_manage_remarks_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Academics/ac_manage_remarks_delete.php');
        });

    echo $table->render($ac_remarks);
}
