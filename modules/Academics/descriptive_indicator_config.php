<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Curriculum\CurriculamGateway;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Helper\HelperGateway;

if (isActionAccessible($guid, $connection2, '/modules/Academics/descriptive_indicator_config.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs->add(__('Descriptive Indicator Config'));

    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    //pupilsightYearGroupID] => 003 [pupilsightDepartmentID] => 30 [skill_id] => 3
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    
    $pupilsightYearGroupID = "";
    $pupilsightDepartmentID = "";
    $pupilsightProgramID = "";
    $skill_id = "";
    $acRemarks = NULL;
    $curriculamGateway  = $container->get(CurriculamGateway::class);
    $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();

    $program=array();  
    $program2=array();  
    $program1=array(''=>'Select Program');
    foreach ($rowdataprog as $dt) {
    $program2[$dt['pupilsightProgramID']] = $dt['name'];
    }
    $program= $program1 + $program2; 

    //grades 
    $sql_grade = 'SELECT id,name FROM examinationGradeSystem ';
    $r_grade = $connection2->query($sql_grade);
    $grade_data = $r_grade->fetchAll();
        

    //ends gredes
    $department1 = array(''=>'Select Subject');
    $department = array();
     $department2 = array();
    $sqlr = 'SELECT acRemarks.pupilsightDepartmentID as subID,pupilsightDepartment.name as subname  FROM  acRemarks LEFT JOIN pupilsightDepartment ON acRemarks.pupilsightDepartmentID = pupilsightDepartment.pupilsightDepartmentID ';
    $resultr = $connection2->query($sqlr);
    $rowdatsub= $resultr->fetchAll();
foreach($rowdatsub as $sub){
    if($sub['subname'] != ''){
        $department2[$sub['subID']] = $sub['subname'];
    }
    

}
$department = $department1+$department2;


    $classes = array();

    if ($_POST) {
        $pupilsightProgramID=$_POST['pupilsightProgramID'];
        $pupilsightYearGroupID = $_POST["pupilsightYearGroupID"];
        $pupilsightDepartmentID = $_POST["pupilsightDepartmentID"];
        $skill_id = $_POST["skill_id"];

        if(!empty($_POST['pupilsightProgramIDNew']) && empty($_POST['pupilsightProgramID'])){
            $pupilsightProgramID=$_POST['pupilsightProgramIDNew'];
            $pupilsightYearGroupID = $_POST["pupilsightYearGroupIDNew"];
            $pupilsightDepartmentID = $_POST["pupilsightDepartmentIDNew"];
            $skill_id = $_POST["skill_idNew"];
        }

        $helperGateway  = $container->get(HelperGateway::class);
        $classes = $helperGateway->getClassByProgram($connection2, $pupilsightProgramID, $pupilsightSchoolYearID);

        $rids = isset($_POST["remarkid"]) ? $_POST["remarkid"] : NULL;


        if ($rids) {

          
            $strrid = implode("",$_POST["remarkid"]);
            $ridmd5 = md5($strrid);
            //echo $ridmd5 ." || ".$_SESSION["remarkid"];
            $remarkid = isset($_SESSION["remarkid"])?$_SESSION["remarkid"]:"-1";
            if ($remarkid != $ridmd5) {
                //$rd = isset($_POST["remarkdescription"]) ? $_POST["remarkdescription"] : NULL;
                $sq = "INSERT INTO `acRemarks` (`remarkcode`, `description`, `pupilsightYearGroupID`, `pupilsightDepartmentID`, `skill`) VALUES";
                $len = count($rids);
                $i = 0;
                $sqt = "";
                while ($i < $len) {
                    if (!empty($sqt)) {
                        $sqt .= ",";
                    }
                    $rdv = "rc_".$rids[$i];
                    $rd = isset($_POST[$rdv]) ? $_POST[$rdv] : NULL;
                    //$curriculamGateway->getClassAcRemarks($pupilsightYearGroupID, $remarkcode);
                    $sqt .= "('" . $rids[$i] . "', '" . $rd . "', $pupilsightYearGroupID, $pupilsightDepartmentID, $skill_id)";
                    $i++;
                }
                $sq .= $sqt . ";";
                //echo $sq;
                
                try {
                    $connection2->query($sq);
                    $_SESSION["remarkid"] = $ridmd5;
                } catch (PDOException $e) {
                }
            }
        }
        $CurriculamGateway = $container->get(CurriculamGateway::class);
       
        if ($_POST) {      
        $subId = $_POST['departmentID'];
        $search = $_POST['search'];
        } else {
        $subId = "";
        $search = "";

        }
        // QUERY
        $criteria = $CurriculamGateway->newQueryCriteria()
            ->sortBy('id')
            ->fromPOST();
            if ($_POST) {      
           $subId = $_POST['departmentID'];
           $search = $_POST['search'];
       } else {
        $subId = "";
        $search = "";
          
       }

        $acRemarks = $curriculamGateway->getAllcurriculamRemarks($criteria,$subId,$search);
       
        $classRemarks = $curriculamGateway->getClassAcRemarks($pupilsightYearGroupID, $pupilsightDepartmentID, $skill_id);
        
    }
    $cls_sql='SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramID = "'.$pupilsightProgramID.'" GROUP BY a.pupilsightYearGroupID';
    $cls_res = $connection2->query($cls_sql);
   $cls_res1 = $cls_res->fetchAll();

   $sub_sql='select id,pupilsightDepartmentID, subject_display_name, di_mode from subjectToClassCurriculum where pupilsightProgramID ="'.$pupilsightProgramID.'" AND pupilsightYearGroupID ="'.$pupilsightYearGroupID.'" AND  di_mode NOT IN ("FREE_FORM","NO_DI") order by subject_display_name asc';
   $sub_res = $connection2->query($sub_sql);
   $sub_res1 = $sub_res->fetchAll(); 
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo '<h3>';
    echo __('Descriptive Indicator Configuration');
    echo '</h3>';

    $form = Form::create('descriptiveIndicatorConfig', "");
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $row = $form->addRow();
    $col = $row->addColumn()->setClass('');
    $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder();

     $col = $row->addColumn()->setClass('');
    $col->addSelect('pupilsightYearGroupID')->fromArray($classes)->required()->selected($pupilsightYearGroupID)->placeholder('Select Class');

    $sub[""] = "Select Subject";
    // $col = $row->addColumn()->setClass('');
    // $col->addContent('<select class="form-control"   id="pupilsightYearGroupID" name="pupilsightYearGroupID"  required>'.$option.'</select>');
    //$col->addSelectYearGroup('pupilsightYearGroupID')->required()->selected($pupilsightYearGroupID)->placeholder('Select Class');
    
    //$sub[""] = "Select Subject";
    $col = $row->addColumn()->setClass('');
    $option_s='<option value="">Select subject</option>';
    foreach ($sub_res1 as $val) {
    $slt1='';
    if($val['pupilsightDepartmentID']==$pupilsightDepartmentID){
    $slt="selected";
    }
    $option_s.="<option value='".$val['pupilsightDepartmentID']."' ".$slt1.">".$val['subject_display_name']."</option>";
    }
     $col->addContent('<select class="" style="300px"  id="pupilsightDepartmentID" name="pupilsightDepartmentID"  required>'.$option_s.'</select>');
    //$col->addSelect('pupilsightDepartmentID')->fromArray($sub);

    $skill[""] = "Select Skill";
    $col = $row->addColumn()->setClass('');
    $col->addSelect('skill_id')->fromArray($skill);

    $col = $row->addColumn()->setClass('');
    $col->addContent('<button class=" btn btn-primary" style=" font-size: 13px !important;">Go</button>'); 
    
  
    $col = $row->addColumn()->setClass('');
    $col->addLabel('dimode', __('DI MODE'))->addClass("_dimode");
    echo $form->getOutput();
?>
    <script>
        $("#pupilsightYearGroupID").change(function() {
            loadSubjects();
        });

        $("#pupilsightDepartmentID").change(function() {
            loadDescriptiveSkill();
        });

        $(function() {
            reloadData();
        });

        var reloadCall = false;
        var _pupilsightDepartmentID = "";
        var _skill_id = "";

        function reloadData() {
            <?php
            if ($pupilsightDepartmentID) {
                echo "\n_pupilsightDepartmentID = \"" . $pupilsightDepartmentID . "\";";
                echo "\n_skill_id = \"" . $skill_id . "\";";
                echo "\nreloadCall = true;";
            ?>
                loadSubjects();
            <?php
            }
            ?>
        }

        function loadSubjects() {
            var pupilsightProgramID= $("#pupilsightProgramID").val();
            var pupilsightYearGroupID = $("#pupilsightYearGroupID").val();
            if (pupilsightYearGroupID) {
                var type = "getDescriptiveSubject";
                try {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: {
                            pupilsightProgramID : pupilsightProgramID,
                            val: pupilsightYearGroupID,
                            type: type
                        },
                        async: true,
                        success: function(response) {
                            $("#pupilsightDepartmentID").html(response);
                            if (reloadCall) {
                                $("#pupilsightDepartmentID").val(_pupilsightDepartmentID);
                                loadDescriptiveSkill();
                            }
                        }
                    });
                } catch (ex) {
                    reloadCall = false;
                }
            }
        }
        $(document).ready(function(){
            //$('.gradeWise,.remarksWise').hide(); 
});
        function loadDescriptiveSkill() {
            var dimode = $("#pupilsightDepartmentID").find(':selected').attr('data-dimode');
           
            if(dimode == 'SUBJECT_GRADE_WISE' || dimode =='SUBJECT_GRADE_WISE_AUTO'){
               $('.gradeWise').show();
               $('.remarksWise').hide();
           }else if(dimode == 'SUBJECT_WISE' || dimode =='SUBJECT_WISE_NO_EDIT'){
            $('.gradeWise').hide(); 
            $('.remarksWise').show();
           }
            $("._dimode").text(dimode.replace(/_/g, ' '));
            
            var pupilsightYearGroupID = $("#pupilsightYearGroupID").val();
            var pupilsightDepartmentID = $("#pupilsightDepartmentID").val();
            if (pupilsightYearGroupID && pupilsightDepartmentID) {
                var type = "getDescriptiveSkill";
                try {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: {
                            val: pupilsightYearGroupID,
                            pupilsightYearGroupID: pupilsightYearGroupID,
                            pupilsightDepartmentID: pupilsightDepartmentID,
                            type: type,
                        },
                        async: true,
                        success: function(response) {
                            $("#skill_id").html(response);
                            if (reloadCall) {
                                $("#skill_id").val(_skill_id);
                                reloadCall = false;
                            }
                        }
                    });
                } catch (ex) {
                    console.log(ex);
                    reloadCall = false;
                }
            }
        }

$(document).on('click', '#copydescriptive', function() {
    var pupilsightDepartmentID = $("#pupilsightDepartmentID").val();
    var val = pupilsightDepartmentID;
    var cls = $("#pupilsightYearGroupID").val();
    var prg = $("#pupilsightProgramID").val();

    var type = 'copyDescriptive';
    if (val != '') {
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: val, type: type,cls:cls,prg:prg },
            async: true,
            success: function(response) {               
                $('#clickcoptydesc').click();
            }
        });
    }

});

    </script>

    <?php
    if (!empty($acRemarks->data)) { 
        echo "<h4>remarks master</h4>";
        echo '<div class="" style="border:1px solid #b1afaf;margin-bottom: 10px;
        margin-top: 10px;">' ; 
       
        $searchform = Form::create('searchForm','');
        $searchform->setFactory(DatabaseFormFactory::create($pdo))->addClass('remarkCss');
        ?>
       
  <?php
        $row = $searchform->addRow();
        $col = $row->addColumn()->setClass('newdes advsrch');    
        $col->addLabel('search', __('Remarks code'));
        /*$col->addTextField('search')->addClass('txtfield')->setValue($search);*/
        $col->addContent('<input type="text" name="search" placeholder="Search" id="myInput" onkeyup="mysearch()" value='.$search.'>  <input type="hidden" name="pupilsightYearGroupIDNew" value='.$pupilsightYearGroupID.'>
        <input type="hidden" name="pupilsightProgramIDNew" value='.$pupilsightProgramID.'>
        <input type="hidden" name="pupilsightDepartmentIDNew" value='.$pupilsightDepartmentID.'>
        <input type="hidden" name="skill_idNew" value='.$skill_id.'>');

        $col = $row->addColumn()->setClass('newdes advsrch');
        $col->addLabel('departmentID', __('Subject')); 
        $col->addSelect('departmentID')->fromArray($department)->selected($subId)->placeholder();

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel(' ', __(' '));
        $col->addContent('<button type="submit" id="filter_remarks" class=" btn btn-primary">Search</button>'); 


 
        
        $col->addContent('<button id="submitInvoice" style="display:none;" class="transactionbtn btn-primary">Submit</button>'); 
        echo $searchform->getOutput();

    ?>
        <form id="frmAddRemarks" method="post">
            <input type="hidden" name="pupilsightYearGroupID" value="<?= $pupilsightYearGroupID; ?>">
            <input type="hidden" name="pupilsightProgramID" value="<?= $pupilsightProgramID; ?>">
             <input type="hidden" name="pupilsightDepartmentID" value="<?= $pupilsightDepartmentID; ?>">
            <input type="hidden" name="skill_id" value="<?= $skill_id; ?>">

            <?php

            $table = DataTable::create('descriptiveIndicatorRemarks');
            $table->addCheckboxColumn('remarkid', __(''))
                ->setClass('chkbox')
                ->notSortable()
                ->format(function ($acRemarks) {

                    $str = "<input type='checkbox' data_sub='".$pupilsightDepartmentID."' data-remark='" . $acRemarks['id'] . "_" . $acRemarks["description"] ."' id='" . $acRemarks['id'] . "' name='remarkid[]' value='" . $acRemarks["id"] . "'>";
                    $str .= "<input type='hidden' name='remarksCode[]'  value='" . $acRemarks["description"] . "'>";
                    
                    return $str;
                });
            $table->addColumn('remarkcode', __('Remark Code'));
            $table->addColumn('description', __('Remark Description'));
            $table->addColumn('subject', __('Subject'));
            echo "<br>" . $table->render($acRemarks);             

            ?>
            <div class='remarksWise'>
            <input type="button" id="btnRemarksSubmit" value="Add Remarks" style="display:none;">
            <div class='mt-4 mb-4 text-center'>
                <button type="button" class="btn btn-primary" onclick="checkRemarkSelected();">
                    Add Remarks
                </button>
            </div>

           </div> 
                       
           <br><form class="form-inline gradeWise" action="/action_page.php">
        <div class=' text-center gradeWise'>
        <input type=" " id="btnRemarksSubmit" value="Add Remarks" style="display:none;"><p style='color: palevioletred;font-size: 15px; '>Please Select the Grade below and Select the Remarks to Add Remarks </p>
        <table style="width: 100%">
        <tr>
            <td>
                <select name="gradeSystem" id="gradeSystem" class="w-full">
                    <option value="">Select Grade System</option>
                    <?php foreach ($grade_data as $val) { ?>
                     <option value="<?php echo $val['id'];?>"><?php echo $val['name'];?></option>
                    <?php } ?>
                </select>
            </td>
            <td>
                <select name="grade" id ="grade" class="w-full">
                    <option value="">Select grade</option>
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-primary" onclick="checkRemarkSelected();">
                    Add Remarks
                </button>
            </td>
        </tr>
</table>
            
            
        </div>
    </form><br>
</div>


    <?php
    
    echo "<div >";
        echo "<h4> selected subject/skill</h4>";
    ?>
   <div style="text-align:right;margin-bottom:10px">    <div><button class="removeConf btn btn-primary" style=" font-size: 13px !important;">Remove</button>&nbsp;&nbsp;<button id="copydescriptive"class=" btn btn-primary" style=" font-size: 13px !important;">Copy</button>&nbsp;&nbsp;<button class=" btn btn-primary" style=" font-size: 13px !important;"type="button" onclick="saveRemarks();">Save</button> <a id='clickcoptydesc' href='fullscreen.php?q=/modules/Academics/descriptive_indicator_configCopy.php&width=800' style=" font-size: 13px !important;display:none;" class='thickbox '>copty</a></div></div>
     <table class="table display text-nowrap remarksWise" cellspacing="0">
        <tr class="flex flex-col sm:flex-row justify-between content-center p-0">
            <thead>
                <th class='column'><input type='checkbox' class="checkAll"></th>          
                <th class='column'>Remark Description</th>
            </thead>
        </tr>
        <tbody id='sub_skillSelected'>        
        </tbody>
     </table>

        
     <script>
            function checkRemarkSelected() {
                var len = $('input[name="remarkid[]"]:checked').length;
                if (len > 0) {
                    $("#btnRemarksSubmit").click();
                } else {
                    alert("You have not selected any remarks item.");
                }
            }

            $(document).on('click', '#btnRemarksSubmit', function() {
                var favorite = [];
                var rdes = []; 
                $.each($('input[name="remarkid[]"]:checked'), function() {
                    favorite.push($(this).val());
                    //rcode.push($('#remarksCode').val());
                    rdes.push($(this).attr("data-remark"));
                });
                var sub = $("#pupilsightDepartmentID").val() ;
                var cls =$('#pupilsightYearGroupID').val();
                var program = $("#pupilsightProgramID").val() ;
                var dimode = $("#pupilsightDepartmentID").find(':selected').attr('data-dimode');
                // if(dimode );
                var grade_text="";
                var grade="";
              
               if(dimode == 'SUBJECT_WISE' || dimode =='SUBJECT_WISE_NO_EDIT'){
                    
               }else if(dimode == 'SUBJECT_GRADE_WISE' || dimode =='SUBJECT_GRADE_WISE_AUTO'){
                var grade_text = $( "#grade option:selected" ).text();
              var grade =$("#grade").val();
            if(grade == ""){
                alert('Please enter Grade')
                return false;
            }
              
               }
                if (favorite.length != 0) {
                    var val = favorite.join(",");
                    
                    var type = 'SubdescriptiveIndicator';
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: { val: val, type: type ,rdes:rdes,sub:sub,cls:cls,dimode : dimode,grade:grade,program:program,grade_text:grade_text},
                        async: true,
                        success: function(response) {
                            $("#gradeSystem").val('');
                            $("#grade").html('<option value="">Select grade</option>');
                            alert('Successfully Added');
                            load_remarks();                         
                        }
                    });
                } else {
                    alert('Please select only one invoice');
                }
            });
            function load_remarks(){
                var dimode = $("#pupilsightDepartmentID").find(':selected').attr('data-dimode');
              
                var sub = $("#pupilsightDepartmentID").val() ;
                var cls =$('#pupilsightYearGroupID').val();
                var program = $("#pupilsightProgramID").val() ;
                if(dimode == 'SUBJECT_WISE' || dimode =='SUBJECT_WISE_NO_EDIT'){
                    var type="load_remarks_descriptive_indicator_config"; 
                    if(sub!=""){
                    $.ajax({
                        url: 'ajaxSwitch.php',
                        type: 'post',
                        data: {  type: type , sub:sub,cls:cls,program:program},
                        async: true,
                        success: function(response) {                            
                            $("#sub_skillSelected").html();
                            $("#sub_skillSelected").html(response); 
                            $('input[name="remarkid[]"]').prop("checked", false);      
                            updatereadonly();                 
                        }
                    });
                }                   
               }else if(dimode == 'SUBJECT_GRADE_WISE' || dimode =='SUBJECT_GRADE_WISE_AUTO'){              
                var type="load_remarks_grade_descriptive_indicator_config";
                if(sub!=""){
                    $.ajax({
                        url: 'ajaxSwitch.php',
                        type: 'post',
                        data: {  type: type , sub:sub,cls:cls,program:program},
                        async: true,
                        success: function(response) {                            
                            $("#sub_skillSelectedgrd").html();
                            $("#sub_skillSelectedgrd").html(response); 
                            $('input[name="remarkid[]"]').prop("checked", false);     
                            updatereadonly();                  
                        }
                    });
                 }
               }
            }
            setTimeout(() => {
                load_remarks();
            }, 2000);
            
            function updatereadonly(){ 
                var dimode = $("#pupilsightDepartmentID").find(':selected').attr('data-dimode'); 
               
                if (dimode == 'SUBJECT_WISE_NO_EDIT'){
                $('.remarks_id').attr('readonly', 'readonly');
                } else {
                $('.remarks_id').removeAttr('readonly');
                }
            }
            $('allSelect').change(function(){   
            if($(this).is(':checked'))
                $('input:checkbox[name^=Selectone_byOne]').attr('checked','checked'); 
            else
                $('input:checkbox[name^=Selectone_byOne]').removeAttr('checked'); 
            });
            $(document).on('click','.removeConf',function(){
                var dimode = $("#pupilsightDepartmentID").find(':selected').attr('data-dimode');
                var sub = $("#pupilsightDepartmentID").val() ;
              
                var favorite = [];
                $.each($(".selectGrdCheck:checked"), function() {
                    favorite.push($(this).attr('id'));
                    var val = favorite.join(",");
                    var type="remove_config";
                   
                if(val!=""){
                    if(dimode == 'SUBJECT_WISE' || dimode =='SUBJECT_WISE_NO_EDIT'){
                        $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: {  type: type , val:val},
                        async: true,
                        success: function(response) {                            
                            $("#sub_skillSelected").html();
                            $("#sub_skillSelected").html(response); 
                            $('input[name="remarkid[]"]').prop("checked", false);   
                            load_remarks();                    
                        }
                    });
                    }
                      else if(dimode == 'SUBJECT_GRADE_WISE' || dimode =='SUBJECT_GRADE_WISE_AUTO'){
                        $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: {  type: type , val:val},
                        async: true,
                        success: function(response) {                            
                            $("#sub_skillSelectedgrd").html();
                            $("#sub_skillSelectedgrd").html(response); 
                            $('input[name="remarkid[]"]').prop("checked", false);        
                            load_remarks();               
                        }
                    });
                      }
                 
                 }

            });
          
            });
         
        
       
            function saveRemarks(){            

                var RemarkArray =[];
                $("input[name=remarksname]").each(function() {
                    RemarkArray.push($(this).val());                    
                });             
               
                var favorite = [];
                $.each($("input[name='Selectone_byOne[]']"), function() {
                favorite.push($(this).val());
                  });
                $remarksID =favorite.join(",");
               var descid = $remarksID;
                var val = RemarkArray;
               
                try {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: {
                            val: val,
                            descid:descid,
                            type: "saveDescriptive"
                        },
                        async: true,
                        success: function(response) {
                            
                         alert('updated successfully');
                         load_remarks();
                        }
                    });
                } catch (ex) {
                    console.log(ex);
                }
            }

        </script>


<table class="table display text-nowrap gradeWise" cellspacing="0">
    <tr class="flex flex-col sm:flex-row justify-between content-center p-0">
        <thead>
            <th class='column' style='width:80px;'>Grade</th>           
            <th class='column'>Remark Description</th>
            <th class='column'>Count</th>
            <th class='column'><input type='checkbox' class="checkAll"></th>
        </thead>
    </tr>
    <tbody id="sub_skillSelectedgrd">  
    </tbody>
</table>
<?php
 echo "</div>" ;  
}
}
?>


<style>
.remarkCss{
border:0px;

}
</style>
<script type="text/javascript">
    $(document).on('change','#gradeSystem',function(){
      var syid=$(this).val();
      var type = "systemByidloadGrades";
        $.ajax({
            url: 'ajaxSwitchExcel.php',
            type: 'post',
            data: { type: type,syid:syid },
            async: true,
            success: function(response) {
                 $("#grade").html(response);
            }
        });
    });
</script>