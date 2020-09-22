
<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;

$session = $container->get('session');
$tid = $session->get('fee_items');


if (isActionAccessible($guid, $connection2, '/modules/Messenger/selectRecipient.php') != false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs->add(__('Select Recipient'));

    echo '<h3>';
    echo __('Select Recipient');
    echo '</h3>';

  
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();

    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }
    //select stackhover
    
    $stakeholderList = array(''=>'Select Stakeholder','student' =>'Student',
'staff'=>'Staff','alumini'=>'Alumini');

    
    $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();

    $program = array();
    $program2 = array();
    $program1 = array('' => 'Select Program');
    foreach ($rowdataprog as $dt) {
        $program2[$dt['pupilsightProgramID']] = $dt['name'];
    }
    $program = $program1 + $program2;



    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }?>
    <table class="table display text-nowrap " id="stakeholderTab"  cellspacing="0">
    <tr class="flex flex-col sm:flex-row justify-between content-center p-0">
        <thead>
            <th class='column'>Stakeholder Type</th>           
            <th class='column'>Targer Group</th>
            <th class='column'>Name</th>
            <th class='column'>Remove</th>
        </thead>
    </tr>
    <tbody id="selected_val">  
    </tbody>
</table>
    <?php

    $form = Form::create('copytestclasssectionwise',$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/attendance_configSettings_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']); 
    $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID); 
 
    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __('Select Receipent'));
    $col->addSelect('stakeholderList')->fromArray($stakeholderList)->required()->selected($pupilsightSchoolYearID);
    
    $col = $row->addColumn()->setClass('newdes');
    
    $col->addContent('<br><a class="btncls" id="stakeholderwise">GO</a> &nbsp;<input type="checkbox" >Publish to all of the Selected Stakeholder'); 
   
    $col = $row->addColumn()->setClass('newdes');
    
    $col->addContent(' '); 
$row = $form->addRow()->setClass('ForStdentTabs');
    $col = $row->addColumn()->setClass('newdes');
    
    $col->addContent('<a id="program" class="fw-btn-fill btnStakeholder">Program</a> '); 

    $col = $row->addColumn()->setClass('newdes');
    
    $col->addContent('<a id="getclass" class="fw-btn-fill btnStakeholder">Class</a> '); 

    $col = $row->addColumn()->setClass('newdes');
    
    $col->addContent('<a id="getsection" class="fw-btn-fill btnStakeholder">Section</a> '); 

    $col = $row->addColumn()->setClass('newdes');
    
    $col->addContent('<a id="getIndiv" class="fw-btn-fill btnStakeholder">Individual</a> '); 
     echo $form->getOutput();

     ?><div class="ForStdentTabs">
     <div class="programShow">
    <table class="table display text-nowrap gradeWise" cellspacing="0">
        <tr class="flex flex-col sm:flex-row justify-between content-center p-0">
            <thead>
                <th class='column'><input type="checkbox" class="checkAll select_all "></th>           
                <th class='column'>Program</th>          
            </thead>
        </tr>
        <tbody id="programList">            
        </tbody>
    </table></div>
    <div class="classwiseShow">  
    <select name="" required  class="btn-fill-md pupilsightProgramID_cl" style="float:left ; margin:10px "  name="pupilsightProgramID" id="pupilsightProgramID_cl" >
        <option value="">--Please choose Program--</option>';
        <?php foreach($rowdataprog as $pr){
            
                echo   '<option value='.$pr['pupilsightProgramID'].'>'.$pr['name'].'</option>';                                
    
        } ?>
    </select>
    <table class="table display text-nowrap gradeWise" cellspacing="0">
        <tr class="flex flex-col sm:flex-row justify-between content-center p-0">
            <thead>
                <th class='column'><input type="checkbox" class="checkAll select_all"></th>           
                <th class='column'>Class</th>          
            </thead>
        </tr>
        <tbody id="classList">            
        </tbody>
    </table></div>
<div class="sectionwiseShow">  
  <select name="" required  class="btn-fill-md pupilsightProgramIDs" style="float:left ; margin:10px "  name="pupilsightProgramID" id="pupilsightProgramID_check">
      <option value="">--Please choose Program--</option>';
      <?php foreach($rowdataprog as $pr){          
              echo   '<option value='.$pr['pupilsightProgramID'].'>'.$pr['name'].'</option>';                        
  
      } ?>
  </select>
  <select name="" required  class="btn-fill-md pupilsightYearGroupIDs" style="float:left ; margin:10px "  name="pupilsightYearGroupID" id="pupilsightYearGroupID_check">
      <option value="">--Please choose Class--</option>';
 
  </select>
  <table class="table display text-nowrap gradeWise" cellspacing="0">
      <tr class="flex flex-col sm:flex-row justify-between content-center p-0">
          <thead>
              <th class='column'><input type="checkbox" class="checkAll select_all"></th>           
              <th class='column'>Section</th>          
          </thead>
      </tr>
      <tbody id="sectionList">            
      </tbody>
  </table>
</div>
<div class="individualShow">  
<input type="text" id="studentname" class="studentname"  style="float:left; width:210px;    margin-top: 10px;
">
<select name="" required  class="btn-fill-md pupilsightProgramIDi" style="float:left ; margin:10px "  name="pupilsightProgramID" id="pupilsightProgramID">
      <option value="">--Please choose Program--</option>';
      <?php foreach($rowdataprog as $pr){          
              echo   '<option value='.$pr['pupilsightProgramID'].'>'.$pr['name'].'</option>';                        
  
      } ?>
  </select>
  <select name="" required  class="btn-fill-md pupilsightYearGroupIDi" style="float:left ; margin:10px "  name="pupilsightYearGroupID" id="pupilsightYearGroupID">
      <option value="">--Please choose Class--</option>'; 
  </select>
  <select name="" required  class="btn-fill-md pupilsightRollGroupIDi" style="float:left ; margin:10px "  name="pupilsightRollGroupID" id="pupilsightRollGroupID">
      <option value="">--Please choose Section--</option>; 
  </select>
  <br><a class="btncls getIndividual" id="getIndividual">GO</a> 

  <table class="table display text-nowrap gradeWise" cellspacing="0">
      <tr class="flex flex-col sm:flex-row justify-between content-center p-0">
          <thead>
              <th class='column'><input type="checkbox" class="checkAll select_all"></th>           
              <th class='column'>Name</th>          
          </thead>
      </tr>
      <tbody id="individualList">            
      </tbody>
  </table>
</div>
</div>
<?php

}
?>

<style>

 .mt_align 
 {
    margin-top: 17px;
 }
 .btncls{
    border: 1px solid;
    border-radius: 5px;
    padding: 5px;
 }
 .btnStakeholder{
    text-align: center;
    width: 100%;
    padding: 5px;
    height: 28px;
    border: 1px solid;
    border-radius: 5px;
 }

</style>
<script>
var check = [];

    $(document).ready(function(){
         $('input[name="selectItems[]"]').prop("checked", true)
        $('.programShow,.sectionwiseShow,.individualShow').hide();
        $('.programShow,.classwiseShow,.sectionwiseShow,.individualShow,.ForStdentTabs').hide();
   
    });
    $(document).on('click','#stakeholderwise',function(){
   
   var val = $('#stakeholderList').val();
  
   if(val == 'student'){
       $('.ForStdentTabs').show();
   }
});
    //get program
    $(document).on('click','#program',function(){
        $('.programShow').show();
        //selectcheckbox();
       
        $('.sectionwiseShow,.classwiseShow,.individualShow,.stakeholderwise').hide();
       var type="getPrograms";
       var val =  $("input[name=pupilsightSchoolYearID]").val();
     
       $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: val, type: type },
        async: true,
        success: function(response) {           
            $("#programList").html();
            $("#programList").html(response);
           
        }
    });
    });
    //close get program
       //get Class
       $(document).on('click','#getclass',function(){
        $('.classwiseShow').show();
        $('.programShow,.sectionwiseShow,.individualShow').hide();
        getallcheck();
    });
    $(document).on('change','.pupilsightProgramID_cl',function(){
       // $('input[name="selectItems[]"]').prop("checked", false)
        $('.programShow,.sectionwiseShow,.individualShow').hide();
        $('.classwiseShow').show();
        var val =  $("#pupilsightProgramID_cl").val(); 
        var type="getClass_interaction";
        var status = $(this).attr('checked');
               
        $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: val, type: type },
        async: true,
        success: function(response) {           
            $("#classList").html();
            $("#classList").html(response);
            $(".selectItems").attr('checked',status);
        }
    });
    });
    
    //close get Class

     //get section
     $(document).on('click','#getsection',function(){
        $('.sectionwiseShow').show();
        $('.programShow,.classwiseShow,.individualShow').hide();

    });
    $(document).on('change','.pupilsightYearGroupIDs',function(){
       // $('input[name="selectItems[]"]').prop("checked", false)
        $('.programShow,.classwiseShow,.individualShow').hide();
        $('.sectionwiseShow').show();
        var val =  $(".pupilsightProgramIDs").val(); 
        var cls =  $(".pupilsightYearGroupIDs").val();
        var type="getSection_interaction";
        $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: val, type: type,cls:cls },
        async: true,
        success: function(response) {           
            $("#sectionList").html();
            $("#sectionList").html(response);
        }
    });
    });
    
    //close  section
    
     //get Individual
     $(document).on('click','#getIndiv',function(){
        $('.individualShow').show();
        $('.programShow,.classwiseShow,.sectionwiseShow').hide();

    });
    $(document).on('click','.getIndividual',function(){
      //  $('input[name="selectItems[]"]').prop("checked", false)
        $('.programShow,.classwiseShow,.sectionwiseShow').hide();
        $('.individualShow').show();
       
        var val =  $("input[name=pupilsightSchoolYearID]").val();
        
        var pid =  $(".pupilsightProgramIDi").val(); 
        var cls =  $(".pupilsightYearGroupIDi").val();
        var sec = $(".pupilsightRollGroupIDi").val();
        var name = $(".studentname").val();
        var type="getIndi_interaction";
     
        $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: val, type: type, pid:pid,cls:cls ,sec:sec, name:name},
        async: true,
        success: function(response) {           
            $("#individualList").html();
            $("#individualList").html(response);
        }
    });
    });    
    //close  individual
//checkbox
    $('.select_all').on('click',function(){
        if(this.checked){
            $('.selectItems').each(function(){
                this.checked = true;
            });
        }else{
             $('.selectItems').each(function(){
                this.checked = false;
            });
        }
    });
    
    $('.selectItems').on('click',function(){
        if($('.selectItems:checked').length == $('.selectItems').length){
            $('.select_all').prop('checked',true);
        }else{
            $('.select_all').prop('checked',false);
        }
    });
   //append to selected value 
    $(document).on('click','.selectItems',function(){
       // selectcheckbox();     
        var favorite = [];
        $.each($("input[name='selectItems[]']:checked"), function() {
            favorite.push($(this).attr('data_pro'));
        });
         var val = favorite.join(",");
        var type="getSelect_interaction"
        $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: val, type: type},
        async: true,
        success: function(response) {   
            // alert(response);        
            $("#selected_val").empty();
            $("#selected_val").append(response);
        }
    });           
   })
//    getallcheck();

   function getallcheck(){
    $("#stakeholderTab tr").each(function() {  

        if($('.selectItems:checked').length == $('.selectItems').length){
            $('.select_all').prop('checked',true);
        }else{
            $('.select_all').prop('checked',false);
        }  
      
    alert($(this).attr('id'));
    
  });
   }
 
   
</script>
