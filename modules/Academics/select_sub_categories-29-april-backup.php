<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;



if (isActionAccessible($guid, $connection2, '/modules/Academics/select_sub_categories.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

   

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $sql = 'SELECT id,name FROM pupilsightDepartmentType ORDER BY id ASC';
    $result = $connection2->query($sql);
    $subjectType = $result->fetchAll();

    foreach($subjectType as $k=>$sbt){
        $sql1 = "SELECT b.subject_type,b.subject_type_id,b.assesment_method,b.assesment_option,b.max_marks,b.min_marks,b.gradeSystemId FROM examinationTestSubjectCategory AS b WHERE b.test_master_id = ".$_GET['tid']." AND b.subject_type_id = ".$sbt['id']." ";
        $result1 = $connection2->query($sql1);
        $chkSub = $result1->fetch();
        if(!empty($chkSub['subject_type_id'])){
            $subjectType[$k]['subject_type'] = $chkSub['subject_type'];
            $subjectType[$k]['subject_type_id'] = $chkSub['subject_type_id'];
            $subjectType[$k]['assesment_method'] = $chkSub['assesment_method'];
            $subjectType[$k]['assesment_option'] = $chkSub['assesment_option'];
            $subjectType[$k]['max_marks'] = $chkSub['max_marks'];
            $subjectType[$k]['min_marks'] = $chkSub['min_marks'];
            $subjectType[$k]['gradeSystemId'] = $chkSub['gradeSystemId'];
        } else {
            $subjectType[$k]['subject_type'] = '';
            $subjectType[$k]['subject_type_id'] = '';
            $subjectType[$k]['assesment_method'] = '';
            $subjectType[$k]['assesment_option'] = '';
            $subjectType[$k]['max_marks'] = '';
            $subjectType[$k]['min_marks'] = '';
            $subjectType[$k]['gradeSystemId'] = '';
        }
    }

    $sqlgrade = 'SELECT * FROM examinationGradeSystem ORDER BY id ASC';
    $resultgrade = $connection2->query($sqlgrade);
    $gradedata = $resultgrade->fetchAll();

    //  echo '<pre>';
    // print_r($subjectType);
    // echo '</pre>';
    // die();
?>

    <h2>Select Subject Categories</h2>
    <form id="testSubjectCategoryForm" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/'?>select_sub_categories_addProcess.php" method="post" >
    <a  id="saveTestSubjectCategory" class=" btn btn-primary" style=" font-size: 14px !important;">Save</a>
        <table class="smallIntBorder fullWidth standardForm relative" cellspacing="0">

            <tbody>
            <tr class="flex flex-col sm:flex-row justify-between content-center p-0">
                    <th class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Select</lable>
                            </div>
                        </div>
                    </th>
                    <th class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Category</lable>
                            </div>
                        </div>
                    </th>

                    <th class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Assesment Method</lable>
                            </div>
                        </div>
                    </th>
                    <th class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Assesment Option</lable>
                            </div>
                        </div>
                    </th>
                    <th class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Max Mark</lable>
                            </div>
                        </div>
                    </th>
                    <th class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Min Marks</lable>
                            </div>
                        </div>
                    </th>
                    <th class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Grading System</lable>
                            </div>
                        </div>
                    </th>
            </tr>
            <?php if(!empty($subjectType)){ 
                foreach($subjectType as $st) {
                ?>
                <input type="hidden" name="test_master_id" value="<?php echo $_GET['tid'];?>">
                <input type="hidden" name="subject_type[<?php echo $st['id']?>]" value="<?php echo $st['name']?>">
                
                <tr class="flex flex-col sm:flex-row justify-between content-center p-0">
                    <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                <input type="checkbox" name="subject_type_id[<?php echo $st['id']?>]" value="1" <?php if($st['id'] == $st['subject_type_id']) { ?> checked <?php } ?>>
                            </div>
                        </div>
                    </td>
                    <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <p> <strong><?php echo $st['name']?></strong></p>
                            </div>
                        </div>
                    </td>

                    <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <select id="subCatGrade" data-id="<?php echo $st['id']?>" name="assesment_method[<?php echo $st['id']?>]" class="w-full">
                                    <option value="">Select Method</option>
                                    <option value="Marks" <?php if($st['assesment_method'] == 'Marks') { ?> selected <?php } ?>>Marks</option>
                                    <option value="Grade" <?php if($st['assesment_method'] == 'Grade') { ?> selected <?php } ?>>Grade</option>
                                </select>
                            </div>
                        </div>
                    </td>

                    <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <select id="" name="assesment_option[<?php echo $st['id']?>]" class="w-full">
                                    <option value="">Select Option</option>
                                    <option value="Radio Button" <?php if($st['assesment_option'] == 'Radio Button') { ?> selected <?php } ?>>Radio Button</option>
                                    <option value="Dropdown" <?php if($st['assesment_option'] == 'Dropdown') { ?> selected <?php } ?>>Dropdown</option>
                                </select>
                            </div>
                        </div>
                    </td>
                    <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                            <input type='number' class="marks<?php echo $st['id']?>" name="max_marks[<?php echo $st['id']?>]" style='width:50%' value="<?php echo $st['max_marks']?>" <?php if($st['assesment_method'] == 'Grade') { ?> disabled <?php } ?>>
                            </div>
                        </div>
                    </td>
                    <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <input type='number' class="marks<?php echo $st['id']?>" name="min_marks[<?php echo $st['id']?>]" style='width:50%' value="<?php echo $st['min_marks']?>" <?php if($st['assesment_method'] == 'Grade') { ?> disabled <?php } ?>>
                            </div>
                        </div>
                    </td>
                    <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <select id="" name="gradeSystemId[<?php echo $st['id']?>]" class="w-full">
                                    <option value="">Select Grading System</option>
                                    <?php if(!empty($gradedata)) { 
                                        foreach($gradedata as $gd){ ?>
                                            <option value="<?php echo $gd['id']; ?>" <?php if($st['gradeSystemId'] == $gd['id']) { ?> selected <?php } ?>><?php echo $gd['name']; ?></option>
                                    <?php  } } ?>
                                </select>
                            </div>
                        </div>
                    </td>                  
                </tr>   
            <?php } } ?>                 
            </tbody>
        </table>
        </div>
    </form>
    <script>
        
    </script>

<?php
    

}
