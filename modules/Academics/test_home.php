<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Curriculum\CurriculamGateway;
use Pupilsight\Domain\School\SchoolYearGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/test_home.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('Manage Test Home'));

    if(isset($_GET['m_err'])){
        $err_sql='SELECT name FROM pupilsightYearGroup WHERE pupilsightYearGroupID IN('.$_GET['m_err'].')';
        $err_res = $connection2->query($err_sql);
        $err_data= $err_res->fetchAll();
        $e_txt="";
        foreach ($err_data as $err) {
            $e_txt.=$err['name'].",";
        }
        $e_txt=substr($e_txt, 0, -1);
        echo '<div class="error">Class and Sections not configured for the academic year selected ( Not completed this '.$e_txt.').</div>';
    }
     else  if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    echo '<h3>';
    echo __('Manage Test Home');
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
        ->pageSize(5000)
        ->fromPOST();

    $general_tests = $CurriculamGateway->getAllgeneralTestMaster($criteria, $pupilsightSchoolYearID);
    $pupilsightSchoolYearIDval = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $schoolYearGateway = $container->get(SchoolYearGateway::class);
    $nextSchoolYear = $schoolYearGateway->getNextSchoolYearByID($pupilsightSchoolYearIDval);
    // DATA TABLE

    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=/modules/Academics/test_home_general_add.php' class='btn btn-primary'>Add</a>&nbsp;&nbsp;";  
   
    echo "<a style='display:none' id='showTestMasterCopyForm' href='fullscreen.php?q=/modules/Academics/test_home_copy.php&width=800'  class='thickbox '></a>";   
    
    echo "<a  id='copyTestMaster' data-hrf='fullscreen.php?q=/modules/Academics/test_home_copy.php' class='btn btn-primary'>Copy</a>&nbsp;&nbsp;";    
    echo  "</div><div class='float-none'></div></div>";

    $table = DataTable::createPaginated('testhomemanage', $criteria);

//     if (!empty($nextSchoolYear)) {
//         $table->addHeaderAction('copy', __('Copy Test To Next Year'))       
//             ->setClass('copy_test_cls')
//             ->setIcon('copy')        
//             ->displayLabel()          
//             ->append('&nbsp;|&nbsp;');
//     }
// echo "<input type='hidden' id='next_acyr' name='next_acyr' value='".$nextSchoolYear['pupilsightSchoolYearID']."'>";
//     $table->addHeaderAction('add', __('Add'))
//         ->setURL('/modules/Academics/test_home_general_add.php')
//         ->displayLabel();
      
    $table->addCheckboxColumn('id',__(''))
        ->notSortable();
    $table->addColumn('name', __('Name'));
    $table->addColumn('code', __('Test Code'))->translatable();
    $table->addColumn('academic_year', __('Academic Year')); 
 //   echo $nextSchoolYear['pupilsightSchoolYearID'];     

   
    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($general_tests, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Academics/test_home_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Academics/test_home_delete.php');
        });

    echo $table->render($general_tests);
}


