<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Academics/subject_to_test.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!  
    $test_id = $_GET['tid'];
    $sqltest = 'SELECT * FROM examinationTest WHERE id = '.$test_id.' ';
    $resulttest = $connection2->query($sqltest);
    $testdata = $resulttest->fetch();

    $remarkChecked="";
    $testedChecked="";
    if($testdata['enable_remarks']==1){
        $remarkChecked="checked";
    }
    // if(!empty($testdata['subject_type'] == '1')){
    //     $testedChecked="checked";
    // }
    
    $testedChecked="checked";

    
    
    // $sqlcls = 'SELECT GROUP_CONCAT(DISTINCT pupilsightYearGroupID) AS classIds FROM examinationTestAssignClass WHERE test_id = '.$test_id.' ';
    // $resultcls = $connection2->query($sqlcls);
    // $clsdata = $resultcls->fetch();
    $pupilsightSchoolYearID =  $_GET['aid'];
    $pupilsightProgramID =  $_GET['pid'];
    $classes =  $_GET['cid'];

    if(!empty($classes)){

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $sqlg = 'SELECT id, name FROM examinationGradeSystem ';
    $resultg = $connection2->query($sqlg);
    $gradedata = $resultg->fetchAll();

    
    $sqlr = 'SELECT id, name FROM examinationRoomMaster ';
    $resultr = $connection2->query($sqlr);
    $rooms = $resultr->fetchAll();

    $sqls = 'SELECT pupilsightPersonID, officialName FROM pupilsightPerson WHERE pupilsightRoleIDPrimary = "002" ';
    $results = $connection2->query($sqls);
    $staffdata = $results->fetchAll();

    
    if($testdata['subject_type'] == '1'){
        $sql = 'SELECT * FROM subjectToClassCurriculum WHERE pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND pupilsightProgramID = '.$pupilsightProgramID.' AND pupilsightYearGroupID IN ('.$classes.') GROUP BY pupilsightDepartmentID ORDER BY pos ASC';
    } else {
        $testMasterId = $testdata['test_master_id'];
        $subsql =  "SELECT GROUP_CONCAT(DISTINCT CONCAT('''', subject_type, '''' )) AS subcattype FROM examinationTestSubjectCategory WHERE test_master_id = ".$testMasterId." ";
        $resultsub = $connection2->query($subsql);
        $subCategory = $resultsub->fetch();


       $sql = 'SELECT a.*, b.type FROM subjectToClassCurriculum AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID WHERE a.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND a.pupilsightProgramID = '.$pupilsightProgramID.' AND a.pupilsightYearGroupID IN ('.$classes.') AND b.type IN ('.$subCategory['subcattype'].') GROUP BY a.pupilsightDepartmentID  ORDER BY a.pos ASC';
       
    }
    
    $result = $connection2->query($sql);
    $subjects = $result->fetchAll();

?>

    <h2> Subject To Test</h2>
    <form name="" method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/subject_to_testProcess.php' ?>">
    
        <div class='table-responsive dataTables_wrapper '>
            <a id="copyAllData" class='btn btn-primary'>Copy Selected Row To all Selected Subjects</a>
            <a href="<?php echo $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/manage_edit_test.php' ?>" class='btn btn-primary'>Back</a>
        <table class="table" cellspacing="0" style='overflow-x: scroll !important;margin-top: 10px;
    width: 162%;'>
            <thead  style="font-size:14px;">
            
            <tr >
                    <th >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Select</lable>
                            </div>
                        </div>
                    </th>
                    <th >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Test</lable>
                            </div>
                        </div>
                    </th>
                    <th >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Subject</lable>
                            </div>
                        </div>
                    </th>
                    <th >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Subject Skill</lable>
                            </div>
                        </div>
                    </th>
                    <th >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Is tested</lable>
                            </div>
                        </div>
                    </th>

                    <th >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Assignment Method</lable>
                            </div>
                        </div>
                    </th>
                    <th >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Assignment Option</lable>
                            </div>
                        </div>
                    </th>
                    <th >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Max Mark</lable>
                            </div>
                        </div>
                    </th>
                    <th >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Min Marks</lable>
                            </div>
                        </div>
                    </th>
                    <th >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Remark</lable>
                            </div>
                        </div>
                    </th>
                    <th >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Grading System</lable>
                            </div>
                        </div>
                    </th>
                    <th >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Date</lable>
                            </div>
                        </div>
                    </th>
                    <th >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>From</lable>
                            </div>
                        </div>
                    </th>
                    <th >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>To</lable>
                            </div>
                        </div>
                    </th>
                    <th >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Location</lable>
                            </div>
                        </div>
                    </th>
                    <th >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>Invigilator</lable>
                            </div>
                        </div>
                    </th>
                    <th >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <lable>A.A.T</lable>
                            </div>
                        </div>
                    </th>
            </tr>
            </thead>
            <tbody>
            <?php $k=1; foreach($subjects as $sub) { 
                $i = $sub['pupilsightDepartmentID'];

                $sqlsubdata = 'SELECT * FROM examinationSubjectToTest WHERE test_id = '.$test_id.' AND pupilsightDepartmentID = '.$sub['pupilsightDepartmentID'].' ';
                $resultsd = $connection2->query($sqlsubdata);
                $fetchSubData = $resultsd->fetch();

                $aatChecked="";
                if($fetchSubData['aat'] == 1){
                    $aatChecked="checked";
                }

                $isTested = '';
                if($fetchSubData['is_tested'] == 1){
                    $isTested="checked";
                }

                $enableRem = '';
                if($fetchSubData['enable_remarks'] == 1){
                    $enableRem="checked";
                }

                if($testdata['subject_type'] == '2'){
                    $subcatsql = 'SELECT * FROM examinationTestSubjectCategory WHERE subject_type = "'.$sub['type'].'" AND test_master_id = '.$testMasterId.' ';
                    $resultsubcat = $connection2->query($subcatsql);
                    $fetchSubCatData = $resultsubcat->fetch();
                    $scAssesment_method = $fetchSubCatData['assesment_method'];
                    $scAssesment_option = $fetchSubCatData['assesment_option'];
                    $scmaxMarks = $fetchSubCatData['max_marks'];
                    $scminMarks = $fetchSubCatData['min_marks'];
                    $scgradeSystemId = $fetchSubCatData['gradeSystemId'];
                }  else {
                    $scAssesment_method = '';
                    $scAssesment_option = '';
                    $scmaxMarks = '';
                    $scminMarks = '';
                    $scgradeSystemId = '';
                }  

                if(!empty($fetchSubData['assesment_method'])){
                    $assesment_method = $fetchSubData['assesment_method'];
                } else {
                    if(!empty($scAssesment_method)){
                        $assesment_method = $scAssesment_method;
                    } else {
                        $assesment_method = $testdata['assesment_method'];
                    }
                }

                if(!empty($fetchSubData['assesment_option'])){
                    $assesment_option = $fetchSubData['assesment_option'];
                } else {
                    if(!empty($scAssesment_option)){
                        $assesment_option = $scAssesment_option;
                    } else {
                        $assesment_option = $testdata['assesment_option'];
                    }
                }

                if(!empty($fetchSubData['max_marks'])){
                    if($fetchSubData['max_marks'] != '0.00'){
                        $max_marks = $fetchSubData['max_marks'];
                    } else {
                        $max_marks = '';
                    }
                    
                } else {
                    if(!empty($scmaxMarks)){
                        $max_marks = $scmaxMarks;
                    } else {
                        $max_marks = $testdata['max_marks'];
                    }
                }

                if(!empty($fetchSubData['min_marks'])){
                    if($fetchSubData['min_marks'] != '0.00'){
                        $min_marks = $fetchSubData['min_marks'];
                    } else {
                        $min_marks = '';
                    }
                } else {
                    if(!empty($scminMarks)){
                        $min_marks = $scminMarks;
                    } else {
                        $min_marks = $testdata['min_marks'];
                    }
                }

                if(!empty($fetchSubData['gradeSystemId'])){
                    $gradeSystemId = $fetchSubData['gradeSystemId'];
                } else {
                    if(!empty($scgradeSystemId)){
                        $gradeSystemId = $scgradeSystemId;
                    } else {
                        $gradeSystemId = $testdata['gradeSystemId'];
                    }
                }

                if($fetchSubData['exam_date'] != '0000-00-00'){
                    if($fetchSubData['exam_date'] != '1970-01-01'){
                        $startdate = date('d/m/Y', strtotime($fetchSubData['exam_date']));
                    } else {
                        $startdate = '';
                    }  
                   
                } else {
                    if($testdata['start_date'] != '1970-01-01'){
                        if($testdata['start_date'] != '0000-00-00'){
                            $startdate = date('d/m/Y', strtotime($testdata['start_date']));
                        } else {
                            $startdate = '';
                        }
                        
                    } else {
                        $startdate = '';
                    }  
                }

                if($assesment_method == 'Grade') {
                    $readonly = 'readonly';
                } else {
                    $readonly = '';
                }

                

                $sqlskl = 'SELECT * FROM subjectSkillMapping WHERE
                pupilsightSchoolYearID = '.$sub['pupilsightSchoolYearID'].' AND pupilsightProgramID = '.$sub['pupilsightProgramID'].' AND pupilsightYearGroupID = '.$sub['pupilsightYearGroupID'].'  AND pupilsightDepartmentID = '.$sub['pupilsightDepartmentID'].' ';
                $resultskl = $connection2->query($sqlskl);
                $getSkills = $resultskl->fetchAll();

                $allid = '&aid='.$sub['pupilsightSchoolYearID'].'&pid='.$sub['pupilsightProgramID'].'&cid='.$sub['pupilsightYearGroupID'].'&did='.$sub['pupilsightDepartmentID'];
                
                // echo '<pre>';
                // print_r($getSkills);
                // echo '</pre>';

            ?>
            <input type="hidden" name="test_id" value="<?php echo $test_id;?>">
            <input type="hidden" name="pupilsightDepartmentID[<?php echo $k;?>]" value="<?php echo $sub['pupilsightDepartmentID']?>">
                <tr >
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <input type="checkbox" class="copyAll" value="<?php echo $i; ?>">
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <p> <strong><?php echo $testdata['name']; ?></strong></p>
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <p> <strong><?php echo $sub['subject_display_name']?></strong></p>                            
                            </div>
                        </div>
                    </td>

                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <span id="configureName-<?php echo $k;?>">None</span>
                                <i class="fa fa-cog configure setSkillConfigure" style="cursor:pointer" data-id="<?php echo $k;?>" aria-hidden="true"></i>
                                <input type="hidden"  name="skill_id[<?php echo $k;?>]" value="m">
                                <input type="hidden"  name="skill_configure[<?php echo $k;?>]" id="skill_configure<?php echo $k;?>" value="None">
                                <a href="fullscreen.php?q=/modules/Academics/subject_to_test_configure.php<?php echo $allid;?>&kid=<?php echo $k;?>" class='thickbox ' id="clickSkillConfigure-<?php echo $k;?>" style="display:none;">Add</a>
                                
                                <input type="hidden" id="formData<?php echo $k;?>" name="skill_configure_form[<?php echo $k;?>]" value="">
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <input id="cpytsted<?php echo $i; ?>" type='checkbox' name="is_tested[<?php echo $k;?>]" class=" cpytsted" <?php echo $isTested;?> value="1">                            
                            </div>
                        </div>
                    </td>

                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <select id="cpyassmethod<?php echo $i; ?>" name="assesment_method[<?php echo $k;?>]" class="w-full cpyassmethod">
                                    <option value="">Select Method</option>
                                    <option value="Marks" <?php if($assesment_method == 'Marks') { ?> selected <?php } ?>>Marks</option>
                                    <option value="Grade" <?php if($assesment_method == 'Grade') { ?> selected <?php } ?>>Grade</option>
                                </select>
                            </div>
                        </div>
                    </td>

                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <select id="cpyassoption<?php echo $i; ?>" name="assesment_option[<?php echo $k;?>]" class=" cpyassoption">
                                    <option value="">Select Option</option>
                                    <<option value="Radio Button" <?php if($assesment_option == 'Radio Button' ) { ?> selected <?php } ?>>Radio Button</option>
                                    <option value="Dropdown" <?php if($assesment_option == 'Dropdown') { ?> selected <?php } ?>>Drop Down</option>
                                </select>
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                            <input id="cpymaxmrks<?php echo $i; ?>" type='text' name="max_marks[<?php echo $k;?>]" class="w-full numfield cpymaxmrks" value="<?php echo $max_marks;?>" <?php echo $readonly; ?>>
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <input id="cpyminmrks<?php echo $i; ?>" type='text' name="min_marks[<?php echo $k;?>]" class="w-full numfield cpyminmrks" value="<?php echo $min_marks;?>" <?php echo $readonly; ?>>
                                <!-- <select id="pupilsightProgramID" name="pupilsightProgramID" class="w-full">
                                    <option value="">Select Skill</option>
                                </select> -->
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <input id="cpyenbrms<?php echo $i; ?>" type='checkbox' name="enable_remarks[<?php echo $k;?>]" class="w-full cpyenbrms" <?php echo $enableRem;?> value="1">                            
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <select id="cpygrdsys<?php echo $i; ?>" name="gradeSystemId[<?php echo $k;?>]" class=" cpygrdsys">
                                    <option value="">Select grading System</option>
                                    <?php if(!empty($gradedata)) { 
                                        foreach($gradedata as $gd) { ?>
                                            <option value="<?php echo $gd['id']?>" <?php if($gradeSystemId == $gd['id']) { ?> selected <?php } ?>><?php echo $gd['name']?></option>
                                    <?php } }?>    
                                </select>
                            </div>
                        </div>
                    </td>
                    <td >
                        
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <input id="cpyexdte<?php echo $i; ?>" type='text' name="exam_date[<?php echo $k;?>]" class="fdate w-full cpyexdte" value="<?php echo $startdate;?>">
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                            <input id="cpyexstme<?php echo $i; ?>" type='text' name="exam_start_time[<?php echo $k;?>]" class="w-full cpyexstme" value="<?php echo $testdata['start_time'];?>">
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <input id="cpyexetme<?php echo $i; ?>" type='text' name="exam_end_time[<?php echo $k;?>]" class="w-full cpyexetme" value="<?php echo $testdata['end_time'];?>">
                             
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <select id="cpyrmid<?php echo $i; ?>" name="room_id[<?php echo $k;?>]" class="w-full cpyrmid">
                                    <option value="">Select Room Location</option>
                                    <?php if(!empty($rooms)) { 
                                        foreach($rooms as $rm) { ?>
                                            <option value="<?php echo $rm['id']?>" <?php if($fetchSubData['room_id'] == $rm['id']) { ?> selected <?php } ?>><?php echo $rm['name']?></option>
                                    <?php } }?>    
                                </select>
                               
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                        
                                <select id="cpystid<?php echo $i; ?>" name="invigilator_id[<?php echo $k;?>]" class="w-full cpystid">
                                    <option value="">Select Invigilator</option>
                                    <?php if(!empty($staffdata)) { 
                                        foreach($staffdata as $st) { ?>
                                            <option value="<?php echo $st['pupilsightPersonID']?>" <?php if($fetchSubData['invigilator_id'] == $st['pupilsightPersonID']) { ?> selected <?php } ?>><?php echo $st['officialName']?></option>
                                    <?php } }?>  
                                </select>
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative" style="display:inline-flex;">
                               <input id="cpyaat<?php echo $i; ?>" type='checkbox' name="aat[<?php echo $k;?>]" class="w-full cpyaat selaat" data-id="<?php echo $k;?>" value="1" <?php echo $aatChecked;?> style="width:20px !important;margin: 10px 5px 0 0px;"> 
                               
                                    <select name="aat_calcutaion_by[<?php echo $k;?>]" id="showSelAatOpt<?php echo $k; ?>" <?php if($fetchSubData['aat'] != 1) { ?>style="display:none;" <?php } ?> >
                                    <option >Select Option</option>
                                    <option value="Sum" <?php if($fetchSubData['aat_calcutaion_by'] == 'Sum') { ?> selected <?php } ?>>Sum</option>
                                    <option value="Average" <?php if($fetchSubData['aat_calcutaion_by'] == 'Average') { ?> selected <?php } ?>>Average</option>
                               </select>                         
                            </div>
                        </div>
                    </td>
                
                </tr>
            <?php  $j = $k; if(!empty($getSkills)){
                $j = $k + 1;
                foreach($getSkills as $skls){    
                    $sqlsubskldata = 'SELECT * FROM examinationSubjectToTest WHERE test_id = '.$test_id.' AND pupilsightDepartmentID = '.$sub['pupilsightDepartmentID'].' AND skill_id = '.$skls['skill_id'].' ';
                    $resultsklsd = $connection2->query($sqlsubskldata);
                    $fetchSubSklData = $resultsklsd->fetch();

                    $aatSklChecked = "";
                    
                    if($fetchSubSklData['aat'] == 1){
                        $aatSklChecked="checked";
                    }

                    $isSkillTested = '';
                    if($fetchSubSklData['is_tested'] == 1){
                        $isSkillTested="checked";
                    }

                    $enableRemSkl = '';
                    if($fetchSubSklData['enable_remarks'] == 1){
                        $enableRemSkl="checked";
                    }

                    if(!empty($fetchSubSklData['assesment_method'])){
                        $chassesment_method = $fetchSubSklData['assesment_method'];
                    } else {
                        if(!empty($scAssesment_method)){
                            $chassesment_method = $scAssesment_method;
                        } else {
                            $chassesment_method = $testdata['assesment_method'];
                        }
                    }
    
                    if(!empty($fetchSubSklData['assesment_option'])){
                        $chassesment_option = $fetchSubSklData['assesment_option'];
                    } else {
                        if(!empty($scAssesment_option)){
                            $chassesment_option = $scAssesment_option;
                        } else {
                            $chassesment_option = $testdata['assesment_option'];
                        }
                    }

                    if(!empty($fetchSubSklData['max_marks'])){
                        if($fetchSubSklData['max_marks'] != '0.00'){
                            $chmax_marks = $fetchSubSklData['max_marks'];
                        } else {
                            $chmax_marks = '';
                        }
                        
                    } else {
                        if(!empty($scmaxMarks)){
                            $chmax_marks = $scmaxMarks;
                        } else {
                            $chmax_marks = $testdata['max_marks'];
                        }
                    }
    
                    if(!empty($fetchSubSklData['min_marks'])){
                        if($fetchSubSklData['min_marks'] != '0.00'){
                            $chmin_marks = $fetchSubSklData['min_marks'];
                        } else {
                            $chmin_marks = '';
                        }
                    } else {
                        if(!empty($scminMarks)){
                            $chmin_marks = $scminMarks;
                        } else {
                            $chmin_marks = $testdata['min_marks'];
                        }
                    }
    
                    if(!empty($fetchSubSklData['gradeSystemId'])){
                        $chgradeSystemId = $fetchSubSklData['gradeSystemId'];
                    } else {
                        if(!empty($scgradeSystemId)){
                            $chgradeSystemId = $scgradeSystemId;
                        } else {
                            $chgradeSystemId = $testdata['gradeSystemId'];
                        }
                    }

                    
                    if($fetchSubSklData['exam_date'] != '0000-00-00'){
                        if($fetchSubSklData['exam_date'] != '1970-01-01'){
                            $sstartdate = date('d/m/Y', strtotime($fetchSubSklData['exam_date']));
                        } else {
                            $sstartdate = '';
                        }  
                       
                    } else {
                        if($testdata['start_date'] != '1970-01-01'){
                            if($testdata['start_date'] != '0000-00-00'){
                                $sstartdate = date('d/m/Y', strtotime($testdata['start_date']));
                            } else {
                                $sstartdate = '';
                            }
                            
                        } else {
                            $sstartdate = '';
                        }  
                    }

                    if($chassesment_method == 'Grade') {
                        $chreadonly = 'readonly';
                    } else {
                        $chreadonly = '';
                    }
            ?>
                <input type="hidden" name="test_id" value="<?php echo $test_id;?>">
                <input type="hidden" name="pupilsightDepartmentID[<?php echo $j;?>]" value="<?php echo $sub['pupilsightDepartmentID']?>">
                <tr >
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <input type="checkbox" class="copyAll" value="<?php echo $i; ?>">
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <p> <strong><?php echo $testdata['name']; ?></strong></p>
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <p> <strong><?php echo $sub['subject_display_name']?></strong></p>                            
                            </div>
                        </div>
                    </td>

                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <?php echo $skls['skill_display_name'];?>
                                <input type="hidden"  name="skill_id[<?php echo $j;?>]" value="<?php echo $skls['skill_id']; ?>">

                                <input type="hidden"  name="skill_configure[<?php echo $j;?>]" id="skill_configure<?php echo $k;?>" value="">
                                <input type="hidden" id="formData<?php echo $j;?>" name="skill_configure_form[<?php echo $j;?>]" value="">
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <input id="cpytsted<?php echo $i; ?>" type='checkbox' name="is_tested[<?php echo $j;?>]" class=" cpytsted" <?php echo $isSkillTested;?> value="1">                            
                            </div>
                        </div>
                    </td>

                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <select id="cpyassmethod<?php echo $i; ?>" name="assesment_method[<?php echo $j;?>]" class="w-full cpyassmethod">
                                    <option value="">Select Method</option>
                                    <option value="Marks" <?php if($chassesment_method == 'Marks') { ?> selected <?php } ?>>Marks</option>
                                    <option value="Grade" <?php if($chassesment_method == 'Grade') { ?> selected <?php } ?>>Grade</option>
                                </select>
                            </div>
                        </div>
                    </td>

                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <select id="cpyassoption<?php echo $i; ?>" name="assesment_option[<?php echo $j;?>]" class=" cpyassoption">
                                    <option value="">Select Option</option>
                                    <option value="Radio Button" <?php if($chassesment_option == 'Radio Button') { ?> selected <?php } ?>>Radio Button</option>
                                    <option value="Dropdown" <?php if($chassesment_option == 'Dropdown') { ?> selected <?php } ?>>Drop Down</option>
                                </select>
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                            <input id="cpymaxmrks<?php echo $i; ?>" type='text' name="max_marks[<?php echo $j;?>]" class="w-full numfield cpymaxmrks" value="<?php echo $chmax_marks;?>" <?php echo $chreadonly; ?>>
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <input id="cpyminmrks<?php echo $i; ?>" type='text' name="min_marks[<?php echo $j;?>]" class="w-full numfield cpyminmrks" value="<?php echo $chmin_marks;?>" <?php echo $chreadonly; ?>>
                                <!-- <select id="pupilsightProgramID" name="pupilsightProgramID" class="w-full">
                                    <option value="">Select Skill</option>
                                </select> -->
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               <input id="cpyenbrms<?php echo $i; ?>" type='checkbox' name="enable_remarks[<?php echo $j;?>]" class="w-full cpyenbrms" <?php echo $enableRemSkl;?> value="1">                            
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <select id="cpygrdsys<?php echo $i; ?>" name="gradeSystemId[<?php echo $j;?>]" class=" cpygrdsys">
                                    <option value="">Select grading System</option>
                                    <?php if(!empty($gradedata)) { 
                                        foreach($gradedata as $gd) { ?>
                                            <option value="<?php echo $gd['id']?>" <?php if($chgradeSystemId == $gd['id']) { ?> selected <?php } ?>><?php echo $gd['name']?></option>
                                    <?php } }?>    
                                </select>
                            </div>
                        </div>
                    </td>
                    <td >
                        
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <input id="cpyexdte<?php echo $i; ?>" type='text' name="exam_date[<?php echo $j;?>]" class="fdate w-full cpyexdte" value="<?php echo $sstartdate;?>">
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                            <input id="cpyexstme<?php echo $i; ?>" type='text' name="exam_start_time[<?php echo $j;?>]" class="w-full cpyexstme" value="<?php echo $testdata['start_time'];?>">
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <input id="cpyexetme<?php echo $i; ?>" type='text' name="exam_end_time[<?php echo $j;?>]" class="w-full cpyexetme" value="<?php echo $testdata['end_time'];?>">
                             
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                                <select id="cpyrmid<?php echo $i; ?>" name="room_id[<?php echo $j;?>]" class="w-full cpyrmid">
                                    <option value="">Select Room Location</option>
                                    <?php if(!empty($rooms)) { 
                                        foreach($rooms as $rm) { ?>
                                            <option value="<?php echo $rm['id']?>" <?php if($fetchSubSklData['room_id'] == $rm['id']) { ?> selected <?php } ?>><?php echo $rm['name']?></option>
                                    <?php } }?>    
                                </select>
                               
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                        
                                <select id="cpystid<?php echo $i; ?>" name="invigilator_id[<?php echo $j;?>]" class="w-full cpystid">
                                    <option value="">Select Invigilator</option>
                                    <?php if(!empty($staffdata)) { 
                                        foreach($staffdata as $st) { ?>
                                            <option value="<?php echo $st['pupilsightPersonID']?>" <?php if($fetchSubSklData['invigilator_id'] == $st['pupilsightPersonID']) { ?> selected <?php } ?>><?php echo $st['officialName']?></option>
                                    <?php } }?>  
                                </select>
                            </div>
                        </div>
                    </td>
                    <td >
                        <div class="input-group stylish-input-group">
                            <div class="flex-1 relative">
                               
                               <input id="cpyaat<?php echo $i; ?>" type='checkbox' name="aat[<?php echo $j;?>]" class="w-full cpyaat selaat" data-id="<?php echo $j;?>" value="1" <?php echo $aatSklChecked;?> style="width:20px !important;margin: 10px 5px 0 0px;"> 
                               
                                                   
                               <select name="aat_calcutaion_by[<?php echo $j;?>]" id="showSelAatOpt<?php echo $j; ?>" <?php if($fetchSubSklData['aat'] != 1) { ?>style="display:none;" <?php } ?> >
                                    <option >Select Option</option>
                                    <option value="Sum" <?php if($fetchSubSklData['aat_calcutaion_by'] == 'Sum') { ?> selected <?php } ?>>Sum</option>
                                    <option value="Average" <?php if($fetchSubSklData['aat_calcutaion_by'] == 'Average') { ?> selected <?php } ?>>Average</option>
                            </div>
                        </div>
                    </td>
                
                </tr>
            <?php $j++; } } $k = $j;  $k++; } ?>
               
            </tbody>
        </table>
        </br>
        <button type="submit" class="btn btn-primary" id="">&nbsp;Save&nbsp;</button>


        </div>
    </form>
    
<style>
        /* th{padding: 0px ;
            width: 90% !important;
         } */
    select {
        margin-right: 8px;
    }
    
    .configure {
        border: 1px solid grey;
        border-radius: 3px;
        padding: 2px 2px;
        margin: 0 0 0 4px;
    }
</style>

<?php
    } else {
        echo 'There is No Subject TO This Class';
    }

} ?>

<script>
    $(document).on('change', '.selaat', function() {
        var id = $(this).attr('data-id');
        if ($(this).is(':checked')) {
            $("#showSelAatOpt"+id).show();
        }
    });

    
    $(document).on('click', '.setSkillConfigure', function() {
        var id = $(this).attr('data-id');
       
        window.setTimeout(function() {
            $("#clickSkillConfigure-"+id)[0].click();
        }, 10);
    });

    $(document).on('click', '#configureSet', function() {
        var id = $(this).attr('data-id');
        var chkval = $(".configureVal:checked").val();
        var checked = $(".configureVal:checked").length;
        if (checked == 1) {
            $("#configureName-"+id).text('');
            $("#configureName-"+id).text(chkval);
            $("#skill_configure"+id).val(chkval);
            if(chkval == 'Percentage'){
                var formData = $("#skillConfigureForm").serialize();
                $("#formData"+id).val(formData);
            }
            $("#TB_overlay").remove();
            $("#TB_window").remove();
        } else {
            alert('You Have Select Configure');
        }
        
    });
</script>
