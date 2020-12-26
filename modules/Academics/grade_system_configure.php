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

if (isActionAccessible($guid, $connection2, '/modules/Academics/grade_system_configure.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs
    ->add(__('Manage Grading System'), 'grade_system_manage.php')
    ->add(__('Manage Grading Configure'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    echo '<h3>';
    echo __('Grade System Configure');
    echo '</h3>';

    echo '<h5>';
    echo __('Subject Grades');
    echo '</h5>';

    $id = $_GET['id'];
    
    $CurriculamGateway = $container->get(CurriculamGateway::class);

    // QUERY
    $criteria = $CurriculamGateway->newQueryCriteria()
        ->sortBy('id')
        ->fromPOST();

    $configure = $CurriculamGateway->getAllGradeConfigure($criteria, $id);

    $data = array('id' => $id);
    $sql = 'SELECT pass_fail_condition FROM examinationGradeSystem WHERE id=:id';
    $result = $connection2->prepare($sql);
    $result->execute($data);
    $values = $result->fetch();
    $chkmarks = '';
    $chkgrade = '';
    if(!empty($values)){
        if($values['pass_fail_condition'] == 'Marks'){
            $chkmarks = 'checked';
            $chkgrade = '';
        } else if($values['pass_fail_condition'] == 'Grade'){
            $chkmarks = '';
            $chkgrade = 'checked';
        } else {
            $chkmarks = 'checked';
            $chkgrade = '';
        }  
    }
    
    echo'<div  style="  margin-left: 10px; float: right;" >&nbsp;&nbsp;<a href="fullscreen.php?q=/modules/Academics/grade_system_configure_add.php&sid='.$id.'&width=650" class= "btn btn-primary thickbox" style="height: 34px;  margin-left: 10px; float: left;"class=" btn btn-primary">Add</a>
    <label style="margin-top:8px;font-weight:bold; font-size:14px; float:right;">
    &nbsp;&nbsp;Pass / Fail Condition &nbsp;&nbsp;
    <input type="radio" class="changeGradeSystemCondition" data-id='.$id.' value="Marks" name="pass_fail_condition" '.$chkmarks.'> Marks &nbsp; 
        <input type="radio" class="changeGradeSystemCondition" data-id='.$id.' value="Grade" name="pass_fail_condition" '.$chkgrade.'> Grade  </label>
    <div style="height:20px"></div>
    </div>';

    // DATA TABLE
    $table = DataTable::createPaginated('gradesystemsubmanage', $criteria);
   /* $table->addHeaderAction('add', __('Add'))
    ->setURL('/modules/Academics/subject_grade_add.php')
    ->displayLabel();*/
    //$table->addColumn('serial_number',__('Sl No'))->notSortable();
    $table->addColumn('grade_name', __('Grade Name'));
    $table->addColumn('grade_point', __('Grade Point'))->translatable();
    $table->addColumn('lower_limit', __('Lower Limit'));
    $table->addColumn('upper_limit', __('Upper Limit'));
    $table->addColumn('rank', __('Rank'));
    $table->addColumn('class_obtained', __('Class Obtained'));
    $table->addColumn('subject_status', __('Status'));
    $table->addColumn('description', __('Description'));

    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->addParam('gradeSystemId')
        ->format(function ($general_tests, $actions) {
            $actions->addAction('editnew', __('Edit'))
                    ->setURL('/modules/Academics/grade_system_configure_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Academics/grade_system_configure_delete.php');
        
        });

    echo $table->render($configure);
}

?>
<script type="text/javascript">
    $(document).on('click','#checkformVal',function(){
        var lower_limit = $("#lower_limit").val();
        var upper_limit = $("#upper_limit").val();
        var err=0;
        if(lower_limit.trim()!="" && upper_limit.trim()!=""){
            if(Number(upper_limit) > Number(lower_limit)){
                $(".error_cls").hide();
            } else {
                err++;
                $(".error_cls").show();
            }
        } else {
            $(".error_cls").hide();
        }
        if(err==0){
           $("#formStnBtn").click();
        }
       
    });
</script>