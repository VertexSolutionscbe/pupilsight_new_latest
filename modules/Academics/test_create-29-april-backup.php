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
    $sqlt = 'SELECT a.name as tmname,a.code,a.pupilsightSchoolYearID,a.id,b.* FROM examinationTestMaster AS a LEFT JOIN  examinationTest AS b ON a.id = b.test_master_id WHERE a.id = '.$testId.' GROUP BY b.test_master_id';
    $resultt = $connection2->query($sqlt);
    $testdata = $resultt->fetch();
    $testName = $testdata['tmname'];
    // echo '<pre>';
    // print_r($testdata);
    // echo '</pre>'; 

    $sqlterm = 'SELECT * FROM pupilsightSchoolYearTerm ORDER BY pupilsightSchoolYearTermID ASC';
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
        $sql = 'SELECT a.pupilsightMappingID, a.pupilsightProgramID, b.* FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramID = '.$rd['pupilsightProgramID'].' GROUP BY a.pupilsightYearGroupID ';
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

<h2>Crate Test</h2>
<div>
 <a href="<?php echo $_SESSION[$guid]['absoluteURL']?>/index.php?q=/modules/Academics/test_home_edit.php&id=<?php echo $testId; ?>" class=" btn btn-primary ">General</a>
<a href="<?php echo $_SESSION[$guid]['absoluteURL']?>/index.php?q=/modules/Academics/test_create.php&tid=<?php echo $testId; ?>" class=" btn btn-primary active">Create Tests</a>
    <table class="smallIntBorder fullWidth standardForm relative" cellspacing="0">

        <tbody>
            <tr class="flex flex-col sm:flex-row justify-between content-center p-0">
                <td colspan="3" class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                    <table class="tablewidth" border="0">
                        <tr>
                            <th>Program</th>
                            <th>Test Status</th>
                        <tr>
                        <?php foreach($allprogclass as $pc) { ?>
                        <tr class="col_header_new">
                            <?php /* if($pc['countclass'] == $pc['countchkclass']){
                                $pchk = 'checked';
                             }  else { 
                                $pchk = '';
                             } */ ?>
                            <td> <i class="col_header fa fa-chevron-circle-down rotate padding"></i><input type="checkbox" class="parentChkBox" id="chkParent<?php echo $pc['pupilsightProgramID']?>" value="<?php echo $pc['pupilsightProgramID']?>" ><?php echo $pc['name']?>
                            </td>
                            <td>
                            <?php if($pc['countclass'] == $pc['countchkclass']){ ?>
                            <i class="fas fa-check fa-2x" style="color:limegreen"></i>
                            <?php }  else { ?>
                            <i class="fas fa-times fa-2x"></i>
                            <?php } ?>
                            </td>
                        </tr>
                        <?php foreach($pc['class'] as $cl) { ?>
                        <tr>
                        <?php if($cl['check'] == 'checked') { $cls = 'assignCls'; } else { $cls = ''; } ?>
                            <td><span class="childrow"><input name="class_id" type="checkbox" class="childChkBox chkChild<?php echo $pc['pupilsightProgramID']?>  <?php echo $cls;?>" data-par="<?php echo $pc['pupilsightProgramID']?>" data-tid="<?php echo $testId; ?>" data-cls="<?php echo $cl['pupilsightYearGroupID']?>" value="<?php echo $cl['pupilsightMappingID']?>" ><?php echo $cl['className']?></span></td>
                            <td>
                            <?php if($cl['check'] == 'checked'){ ?>
                            <i class="fas fa-check fa-2x" style="color:limegreen"></i>
                            <?php }  else { ?>
                            <i class="fas fa-times fa-2x"></i>
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
                    $row->addLabel('pupilsightSchoolYearTermID', __('Test type'));
                    $row->addSelect('pupilsightSchoolYearTermID')->fromArray($term)->selected($testdata['pupilsightSchoolYearTermID'])->required();


                    $row = $form->addRow();
                    // $row->addLabel('Academic Year', __('Academic Year'));
                    if(!empty($testdata['subject_type'])){
                        $chkd = $testdata['subject_type'];
                    } else {
                        $chkd = '1';
                    }
                    $row->addRadio('subject_type')->setID('select_sub')->fromArray(array('1' => __('Include All Subject'), '2' => __('Select Subject Categories')))->checked($chkd)->inline();

                    $row = $form->addRow();
                    $method = array(__('Basic') => array ('Marks' => __('Marks'), 'Grade' => __('Grade')));
                    $row->addLabel('assesment_method', __('Assesment Method'));
                    $row->addSelect('assesment_method')->fromArray($method)->selected($testdata['assesment_method'])->required();
                    $row = $form->addRow();
                    $option = array(__('Basic') => array ('Radio Button' => __('Radio Button'), 'Dropdown' => __('Dropdown')));
                    $row->addLabel('assesment_option', __('Assesment Option'));
                    $row->addSelect('assesment_option')->fromArray($option)->selected($testdata['assesment_option'])->required();
                    $row = $form->addRow();
                    $row->addLabel('max_marks', __('Max Marks'));
                    $row->addTextField('max_marks')->maxLength(40)->required()->setValue($testdata['max_marks']);
                    $row = $form->addRow();
                    $row->addLabel('min_marks', __('Min Marks'));
                    $row->addTextField('min_marks')->maxLength(40)->required()->setValue($testdata['min_marks']);
                    $row = $form->addRow();
                    $row->addLabel('gradeSystemId', __('Grading System'));
                    $row->addSelect('gradeSystemId')->fromArray($grade)->required()->selected($testdata['gradeSystemId']);
                    
                    $row = $form->addRow();                  
                    $row->addCheckbox('enable_remarks')->description(__('Enable Remarks'))->addClass(' dte')->setValue('1')->checked($testdata['enable_remarks']);
                    $row->addCheckbox('enable_schedule')->description(__('Enable Schedule the Test'))->addClass(' dte')->setValue('1')->checked($testdata['enable_schedule']);

                    $row = $form->addRow();
                    $row->addLabel('start_date', __('Start Date'));
                    if($testdata['start_date'] != '0000-00-00'){
                        $startdate = date('d/m/Y', strtotime($testdata['start_date']));
                    } else {
                        $startdate = '';
                    }
                    $row->addDate('start_date')->setValue($startdate);
               
                    $row = $form->addRow();
                    $row->addLabel('start_time', __('Start Time'));
                    $row->addTextField('start_time')->setValue($testdata['start_time']);

                    $row = $form->addRow();
                    $row->addLabel('end_date', __('End Date'));
                    if($testdata['end_date'] != '0000-00-00'){
                        $enddate = date('d/m/Y', strtotime($testdata['end_date']));
                    } else {
                        $enddate = '';
                    }
                    $row->addDate('end_date')->setValue($enddate);
               
                    $row = $form->addRow();
                    $row->addLabel('end_time', __('End Time'));
                    $row->addTextField('end_time')->setValue($testdata['end_time']);


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