<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;



if (isActionAccessible($guid, $connection2, '/modules/Academics/test_create.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
if(!empty($_GET['tid'])){
   

    $testId = $_GET['tid'];
    $sqlt = 'SELECT a.name as tmname,a.code,a.pupilsightSchoolYearID AS academicId,a.id,b.* FROM examinationTestMaster AS a LEFT JOIN  examinationTest AS b ON a.id = b.test_master_id WHERE a.id = '.$testId.' GROUP BY b.test_master_id';
    $resultt = $connection2->query($sqlt);
    $testdata = $resultt->fetch();
    $testName = $testdata['tmname'];
    // echo '<pre>';
    // print_r($testdata);
    // echo '</pre>'; 
    // die();

    $sqlterm = 'SELECT * FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearID = '.$testdata['academicId'].' ORDER BY pupilsightSchoolYearTermID ASC';
    $resultterm = $connection2->query($sqlterm);
    $termdata = $resultterm->fetchAll();

    $term = array();
    $term1 = array(''=>'Please Select');
    $term2 = array();
    foreach($termdata as $trm){
        $term2[$trm['pupilsightSchoolYearTermID']] = $trm['name'];
    }
    if(!empty($term2)){
        $term = $term1 + $term2;
    }

    $sqlgrade = 'SELECT * FROM examinationGradeSystem ORDER BY id ASC';
    $resultgrade = $connection2->query($sqlgrade);
    $gradedata = $resultgrade->fetchAll();

    $grade = array();
    $grade1 = array(''=>'Please Select');
    $grade2 = array();
    foreach($gradedata as $grd){
        $grade2[$grd['id']] = $grd['name'];
    }
    if(!empty($grade2)){
        $grade = $grade1 + $grade2;
    }
    

    $page->breadcrumbs->add(__('Create Test'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $sqlq = 'SELECT * FROM pupilsightProgram ORDER BY pupilsightProgramID ASC';
    $resultval = $connection2->query($sqlq);
    $allprogclass = $resultval->fetchAll();
    if(!empty($allprogclass)){
    foreach($allprogclass as $k=>$rd){
        $sql = 'SELECT a.pupilsightMappingID, a.pupilsightProgramID, b.* FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightSchoolYearID = '.$testdata['academicId'].' AND a.pupilsightProgramID = '.$rd['pupilsightProgramID'].' GROUP BY a.pupilsightYearGroupID ';
        $result = $connection2->query($sql);
        $classes = $result->fetchAll();
        $n=0;
        foreach($classes as $kcl=>$cl){
           $sqlchk = 'SELECT id FROM examinationTestAssignClass WHERE test_master_id = "'.$testId.'" AND pupilsightProgramID = "'.$cl['pupilsightProgramID'].'" AND pupilsightYearGroupID = "'.$cl['pupilsightYearGroupID'].'" ';
            $resultchk = $connection2->query($sqlchk);
            $testclasschk = $resultchk->fetch();
            if(!empty($testclasschk['id'])){
                $allprogclass[$k]['class'][$kcl]['check'] = 'checked';
                $n++;
            } else {
                $allprogclass[$k]['class'][$kcl]['check'] = '';
            }

            $allprogclass[$k]['class'][$kcl]['pupilsightMappingID'] = $cl['pupilsightMappingID'];
            $allprogclass[$k]['class'][$kcl]['pupilsightYearGroupID'] = $cl['pupilsightYearGroupID'];
            $allprogclass[$k]['class'][$kcl]['className'] = $cl['name'];
        }
        $allprogclass[$k]['countclass'] = count($classes);
        $allprogclass[$k]['countchkclass'] = $n;
    }
    // echo '<pre>';
    // print_r($allprogclass);
    // echo '</pre>';
    // die();
?>

<h2>Create Test</h2>
<div>
 <a href="<?php echo $_SESSION[$guid]['absoluteURL']?>/index.php?q=/modules/Academics/test_home_edit.php&id=<?php echo $testId; ?>" class=" btn btn-primary ">General</a>
<a href="<?php echo $_SESSION[$guid]['absoluteURL']?>/index.php?q=/modules/Academics/test_create.php&tid=<?php echo $testId; ?>" class=" btn btn-primary active">Create Tests</a>
    <table class="smallIntBorder fullWidth standardForm relative" cellspacing="0">

        <tbody>
            <tr class="flex flex-col sm:flex-row justify-between content-center p-0">
                <td colspan="3" class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                    <table class="tablewidth" border="0">
                        <tr>
                            <th><input type="checkbox" class="chkAll" style="margin: 0 4px 0 8px;">Program</th>
                            <th>Test Status</th>
                        <tr>
                        <?php foreach($allprogclass as $pc) { ?>
                        <tr class="col_header_new">
                            <?php /* if($pc['countclass'] == $pc['countchkclass']){
                                $pchk = 'checked';
                             }  else { 
                                $pchk = '';
                             } */ ?>
                            <td> <i class="col_header fa fa-chevron-circle-down rotate padding" style="cursor:pointer;"></i>&nbsp;<input type="checkbox" class="parentChkBox chkChild" id="chkParent<?php echo $pc['pupilsightProgramID']?> " value="<?php echo $pc['pupilsightProgramID']?>" >&nbsp;<?php echo $pc['name']?>
                            </td>
                            <td>
                            <?php if($pc['countclass'] == $pc['countchkclass']){ ?>
                            <i class="mdi mdi-checkbox-marked-circle mdi-24px" style="color:limegreen"></i>
                            <?php }  else { ?>
                            <i class="mdi mdi-close-circle mdi-24px"></i>
                            <?php } ?>
                            </td>
                        </tr>
                        <?php foreach($pc['class'] as $cl) { ?>
                        <tr>
                        <?php if($cl['check'] == 'checked') { $cls = 'assignCls'; } else { $cls = ''; } ?>
                            <td><span class="childrow">&nbsp;<input name="class_id" type="checkbox" class="childChkBox chkChild<?php echo $pc['pupilsightProgramID']?>  <?php echo $cls;?>  chkChild" data-par="<?php echo $pc['pupilsightProgramID']?>" data-tid="<?php echo $testId; ?>" data-cls="<?php echo $cl['pupilsightYearGroupID']?>" value="<?php echo $cl['pupilsightMappingID']?>" >&nbsp;<?php echo $cl['className']?></span></td>
                            <td>
                            <?php if($cl['check'] == 'checked'){ ?>
                            <i class="mdi mdi-checkbox-marked-circle mdi-24px" style="color:limegreen"></i>
                            <?php }  else { ?>
                            <i class="mdi mdi-close-circle mdi-24px"></i>
                            <?php } ?>
                            </td>
                        </tr>
                        
                        <?php } } ?>    

                    </table>
                </td>


                <td colspan='2' class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">

                    <?php $form = Form::create('testCreate', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/test_create_addProcess.php'); 
                    $form->setFactory(DatabaseFormFactory::create($pdo));
                    echo "<a style='display:none' id='seletcategories' href='fullscreen.php?q=/modules/Academics/select_sub_categories.php&tid=".$testId."&width=1000'  class='thickbox '> unassign staff</a>"; 
                    
                    $form->addHiddenValue('address', $_SESSION[$guid]['address']); 
                    $form->addHiddenValue('pupilsightMappingID', ''); 
                    $form->addHiddenValue('test_id', $testId); 

                    $row = $form->addRow();
                    $row->addLabel('name', __('Name'));
                    $row->addTextField('name')->setValue($testName)->maxLength(40)->required();
                    $row = $form->addRow();
                    $row->addLabel('pupilsightSchoolYearTermID', __('Test Type'));
                    $row->addSelect('pupilsightSchoolYearTermID')->fromArray($term);


                    $row = $form->addRow();
                    // $row->addLabel('Academic Year', __('Academic Year'));
                    if(!empty($testdata['subject_type'])){
                        $chkd = $testdata['subject_type'];
                    } else {
                        $chkd = '1';
                    }
                    $row->addRadio('subject_type')->setID('select_sub')->fromArray(array('1' => __('Include All Subject'), '2' => __('Select Subject Categories')))->checked('1')->inline();

                    $row = $form->addRow();
                    $method = array(__('Basic') => array ('Marks' => __('Marks'), 'Grade' => __('Grade')));
                    $row->addLabel('assesment_method', __('Assesment Method'));
                    $row->addSelect('assesment_method')->addClass('enb_dis')->setID('changeByMethod')->fromArray($method);
                    $row = $form->addRow();
                    $option = array(__('Basic') => array ('Radio Button' => __('Radio Button'), 'Dropdown' => __('Dropdown')));
                    $row->addLabel('assesment_option', __('Assesment Option'));
                    $row->addSelect('assesment_option')->addClass('enb_dis')->fromArray($option);
                    $row = $form->addRow();
                    $row->addLabel('max_marks', __('Max Marks'));
                    $row->addTextField('max_marks')->addClass('enb_dis')->maxLength(40);
                    $row = $form->addRow();
                    $row->addLabel('min_marks', __('Min Marks'));
                    $row->addTextField('min_marks')->addClass('enb_dis')->maxLength(40);
                    $row = $form->addRow();
                    $row->addLabel('gradeSystemId', __('Grading System'));
                    $row->addSelect('gradeSystemId')->addClass('enb_dis')->fromArray($grade);
                    
                    $row = $form->addRow();                  
                    $row->addCheckbox('enable_remarks')->description(__('Enable Remarks'))->addClass(' dte')->setValue('1');
                    $row->addCheckbox('enable_schedule')->description(__('Enable Schedule the Test'))->addClass(' dte')->setValue('1');

                    $row = $form->addRow();
                    $row->addLabel('start_date', __('Start Date'));
                    if($testdata['start_date'] != '0000-00-00'){
                        $startdate = date('d/m/Y', strtotime($testdata['start_date']));
                    } else {
                        $startdate = '';
                    }
                    $row->addDate('start_date');
               
                    $row = $form->addRow();
                    $row->addLabel('start_time', __('Start Time'));
                    $row->addTextField('start_time');

                    $row = $form->addRow();
                    $row->addLabel('end_date', __('End Date'));
                    if($testdata['end_date'] != '0000-00-00'){
                        $enddate = date('d/m/Y', strtotime($testdata['end_date']));
                    } else {
                        $enddate = '';
                    }
                    $row->addDate('end_date');
               
                    $row = $form->addRow();
                    $row->addLabel('end_time', __('End Time'));
                    $row->addTextField('end_time');


                    $row = $form->addRow();
                    $row->addFooter();
                    $row->addLabel('', __(''));
                    $row->addContent(' ');  
                    //$row->addSubmit(__('Save'));
                    $row->addContent('<a  id="saveTestCreate" class=" btn btn-primary" style=" font-size: 14px !important;">Save</a>');  
                    $row->addContent('<a  id="deleteTestAssignClass" class=" btn btn-primary" style=" font-size: 14px !important;">Delete</a>');  
                   
            
                echo $form->getOutput();?>
                </td>
            </tr>

        </tbody>
    </table>
    </div>



<?php
    }
}
}
?>
<script type="text/javascript">
    $(document).on('change','#enable_schedule',function(){
        if($(this). prop("checked") == true){
          $("#start_date").prop("disabled", false);
          $("#end_date").prop("disabled", false);
          $("#start_time").prop("disabled", false);
          $("#end_time").prop("disabled", false);
        } else if($(this). prop("disabled") == false){
            $("#start_date").val('');
            $("#end_date").val('');
            $("#start_time").val('');
            $("#end_time").val('');
          $("#start_date").prop("disabled", true);
          $("#end_date").prop("disabled", true);
          $("#start_time").prop("disabled", true);
          $("#end_time").prop("disabled", true);

        }
    });
    function check(){
        if($("#enable_schedule"). prop("checked") == true){
        $("#start_date").prop("disabled", false);
          $("#end_date").prop("disabled", false);
          $("#start_time").prop("disabled", false);
          $("#end_time").prop("disabled", false);
        } else if($("#enable_schedule"). prop("disabled") == false){
          $("#start_date").prop("disabled", true);
          $("#end_date").prop("disabled", true);
          $("#start_time").prop("disabled", true);
          $("#end_time").prop("disabled", true);
        }
    }
    check();
</script>