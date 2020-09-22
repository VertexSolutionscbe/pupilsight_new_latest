<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Curriculum\CurriculamGateway;

if (isActionAccessible($guid, $connection2, '/modules/Academics/manage_elective_group.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $pupilsightYearGroupID = $_GET['cid'];
    $pupilsightProgramID = $_GET['pid'];

    $eid = '';
    if(!empty($_GET['eid'])){
        $eid = $_GET['eid'];
    } 
    
    //Proceed!
    $page->breadcrumbs->add(__('Manage School Years'));
    
    echo '<h2>';
    echo __('Add Section');
    echo '</h2>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $CurriculamGateway  = $container->get(CurriculamGateway ::class);

    // QUERY
    $criteria = $CurriculamGateway ->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    $section = $CurriculamGateway->getSelect($criteria, $pupilsightProgramID, $pupilsightYearGroupID, $eid);

    // DATA TABLE
    $table = DataTable::createPaginated('schoolYearManage', $criteria);

    
 
    $table->addCheckboxColumn('id',__(''))
    ->setClass('chkbox')
    ->notSortable()
    ->format(function ($section) {
        if($section['checked'] == '1'){
            return "<input type='checkbox' name='id[]' value='".$section['id']."' checked>";
        } else {    
            return "<input type='checkbox' name='id[]' value='".$section['id']."' >";
        }
    });
    $table->addColumn('rollGroup', __('Section Name'));
  
     
  
    echo $table->render($section);

    echo "<div style='height:50px;'><div class='float-right mb-2'><a id='electiveSection' class=' btn btn-primary' >Save</a><div class='float-none'></div></div></div>"; 
}
