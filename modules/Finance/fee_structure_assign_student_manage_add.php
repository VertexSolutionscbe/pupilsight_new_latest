<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$studentids = $session->get('student_ids');

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_structure_assign_student_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Structure Assign'), 'fee_structure_assign_student_manage.php')
        ->add(__('Add Fee Structure Assign'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_structure_assign_student_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    if(isset($_REQUEST['sid'])?$id=$_REQUEST['sid']:$id="" );
    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }


    $sqla = 'SELECT * FROM  pupilsightSchoolYear';
    $resulta = $connection2->query($sqla);
    $academicyear = $resulta->fetchAll();

    echo '<h2>';
    echo __('Fee Structure Assign Student');
    echo '</h2>';

    $adata = '<select id="filterStructureOnAcademicYr" style="width:25%"><option>Academic Year</option>';
    foreach($academicyear as $ay){
        if($pupilsightSchoolYearID == $ay['pupilsightSchoolYearID']){
            $sel = 'selected';
        } else {
            $sel = '';
        }
        $adata .= '<option value='.$ay['pupilsightSchoolYearID'].' '.$sel.'>'.$ay['name'].'</option>';
    }
    $adata .= '</select>';
    echo $adata;

    

    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
    $sql = 'SELECT name FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
    $result = $pdo->executeQuery($data, $sql);

    $sqlp = 'SELECT a.id, a.name, b.name as academic_year, SUM(c.total_amount) as totalamount FROM fn_fee_structure AS a LEFT JOIN pupilsightSchoolYear AS b ON a.pupilsightSchoolYearID = b.pupilsightSchoolYearID LEFT JOIN fn_fee_structure_item AS c ON a.id=c.fn_fee_structure_id WHERE b.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' GROUP BY a.id ';
    $resultp = $connection2->query($sqlp);
    $feestructure = $resultp->fetchAll();

  
   
echo '<div style="width:40%; margin-bottom:10px; margin-top:10px;" >
    <input type="text" class="w-full" id="searchTable" placeholder="Search">
</div>';

    // $form = Form::create('assignFeeStructure', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_structure_assign_student_manage_addProcess.php');
    // $form->setFactory(DatabaseFormFactory::create($pdo));

    // $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    // $form->addHiddenValue('stu_id', $studentids);
    //$tab = '';
?>
    <!-- $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
        $col = $row->addColumn()->setClass('newdes');
        $col->addCheckbox('select')->setId('checkall')->setClass('fee_id chkAll');   

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('Fee Structure', __('Fee Structure'))->addClass('dte');

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('Amount', __('Amount'))->addClass('dte');
        
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('Academic Year', __('Academic Year'))->addClass('dte');

    foreach($feestructure as $fee){
        $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
            $col = $row->addColumn()->setClass('newdes');
            $col->addCheckbox('fee_id[]')->setValue($fee['id'])->setClass('fee_id chkChild'); 

            $col = $row->addColumn()->setClass('newdes');
            $t="<a title='".$fee['name']."'>".$fee['name']."</a>";
            $col->addLabel($fee['name'], __($t))->addClass('dte');

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('Amount', __($fee['totalamount']))->addClass('dte');
            
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel($fee['academic_year'], __($fee['academic_year']))->addClass('dte');
    } -->
<form id="assignFeeStructure" method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_structure_assign_student_manage_addProcess.php'?>">
<input type="hidden" name="stu_id" value="<?php echo $studentids?>">
<button type="submit" class="btn btn-primary" style="float:right; margin-bottom:10px;margin-top: -46px;">Submit</button>
    <table class="table" >
        <thead>
            <tr>
                <th><input type="checkbox" class="chkAll" ></th>
                <th>Academic Year</th>
                <th>Fee Structure</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
        <?php if(!empty($feestructure)){ 
                foreach($feestructure as $fee){
            ?>
            <tr>
                <td><input type="checkbox" name="fee_id[]" class="chkChild" value="<?php echo $fee['id']; ?>" ></td>
                <td><?php echo $fee['academic_year']; ?></td>
                <td><?php echo $fee['name']; ?></td>
                <td><?php echo $fee['totalamount']; ?></td>
            </tr>
            <?php } } ?>
        </tbody>
    </table>
    
</form>
<?php      
    // $row = $form->addRow();
    //     $row->addFooter();
    //     $row->addSubmit();

    // echo $form->getOutput();

}

?>


<script>
    
    $("#searchTable").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".table tbody").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $(document).on('change', '.chkAll', function () {
        if ($('.chkAll:checkbox').is(':checked')) {
            $(".chkChild:checkbox").prop("checked", true);
        } else {
            $(".chkChild:checkbox").prop("checked", false);
        }
    });

    $(document).on('change', '.chkChild', function () {
        if ($(this).is(':checked')) {
            //$(".chkChild"+id).prop("checked", true);
        } else {
            $(".chkAll:checkbox").prop("checked", false);
        }
    });

    // $(document).on('click', '#saveAssignFeeStr', function () {
    //     var sub = [];
    //     $.each($("input[name='fee_id[]']:checked"), function () {
    //         sub.push($(this).val());
    //     });
    //     var subid = sub.join(",");
    //     if (subid != '') {
    //         $.ajax({
    //             url: 'index.php?q=/modules/Finance/fee_structure_assign_student_manage_addProcess.php',
    //             type: 'post',
    //             data: $('#assignFeeStructure').serialize(),
    //             async: true,
    //             success: function (response) {
    //                 $("#TB_overlay").remove();
    //                 $("#TB_window").remove();
    //                 $("#feeSettingId-" + kid).val(response);
    //             }
    //         });
    //     } else {
    //         alert('You Have to Select Fee Group!');
    //     }
    // });

</script>    