<?php
/*
Pupilsight, Flexible & Open School System
 */
use Pupilsight\Domain\Curriculum\CurriculamGateway;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Helper\HelperGateway;

ini_set('max_execution_time', 7200);
if (isActionAccessible($guid, $connection2, '/modules/Academics/manage_marks_entry_by_subject.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $HelperGateway = $container->get(HelperGateway::class);

    $page->breadcrumbs->add(__('Marks Entry By Subject'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    echo '<h3>';
    echo __('Enter Marks By Subject');
    echo '</h3>';
    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    $roleId = $_SESSION[$guid]['pupilsightRoleIDPrimary'];
    $uid = $_SESSION[$guid]['pupilsightPersonID'];

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

    $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resultval = $connection2->query($sqlq);
    $rowdata = $resultval->fetchAll();
    $academic = array();
    $ayear = '';
    if (!empty($rowdata)) {
        $ayear = $rowdata[0]['name'];
        foreach ($rowdata as $dt) {
            $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
        }
    }   
    /*
    echo "<pre>";
    print_r($_POST);
    */
    if ($_POST) {

          
        //    $pupilsightSchoolYearIDpost = $_POST['pupilsightSchoolYearID'];
        $pupilsightProgramID = $_POST['pupilsightProgramID'];
        $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];
        $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
        $skill_id = $_POST['skill_id'];
        $test_id  =  $_POST['test_id'];
        $test_id1  =  implode(',',$_POST['test_id']);
        $test_type  =  $_POST['test_type'];
        $searchbyPost = '';
      
        //  $search =  $_POST['search'];
        $stuId = $_POST['studentId'];

        $sqlskl = 'SELECT * FROM subjectSkillMapping WHERE pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" AND pupilsightProgramID = "'.$pupilsightProgramID.'" AND pupilsightYearGroupID = "'.$pupilsightYearGroupID.'" AND pupilsightDepartmentID = "'.$pupilsightDepartmentID.'"  ';
        $resultskl = $connection2->query($sqlskl);
        $skillData = $resultskl->fetchAll();
        

        $skillsdata = array();
        $skillsdata2 = array();
        $skillsdata1 = array('' => 'Select Skill');
        foreach ($skillData as $dt) {
            $skillsdata2[$dt['skill_id']] = $dt['skill_display_name'];
        }
        $skillsdata = $skillsdata1 + $skillsdata2;
       // print_r($skillsdata);

       //$sql_tst = 'SELECT b.id, b.name FROM examinationTestAssignClass AS a LEFT JOIN examinationTest AS b ON a.test_id = b.id  WHERE a.pupilsightSchoolYearID= "'.$pupilsightSchoolYearID.'" AND a.pupilsightProgramID = "'.$pupilsightProgramID.'" AND a.pupilsightYearGroupID = "'.$pupilsightYearGroupID.'"  AND a.pupilsightRollGroupID = "'.$pupilsightRollGroupID.'"';

        $sql_tst = 'SELECT b.id, b.name FROM examinationTestAssignClass AS a LEFT JOIN examinationTest AS b ON a.test_id = b.id LEFT JOIN examinationSubjectToTest AS c ON a.test_id = c.test_id  WHERE a.pupilsightSchoolYearID= ' . $pupilsightSchoolYearID . ' AND a.pupilsightProgramID = ' . $pupilsightProgramID . ' AND a.pupilsightYearGroupID = ' . $pupilsightYearGroupID . ' AND a.pupilsightRollGroupID = ' . $pupilsightRollGroupID . ' AND c.pupilsightDepartmentID = ' . $pupilsightDepartmentID . ' ' ;
        if(!empty($skill_id)){
            $sql_tst .= 'AND c.skill_id = ' . $skill_id . ' ';
        } 
        $sql_tst .=' AND c.is_tested = "1" GROUP BY a.test_id ';
        $result_test = $connection2->query($sql_tst);
        $tests = $result_test->fetchAll();
        $testarr=array ('' => __('Select'));  
        $test2=array();  

        foreach ($tests as $ts) {
            $test2[$ts['id']] = $ts['name'];
        }
        $testarr+=  $test2; 
        $subjects=array();  
        if ($roleId == '2' || $roleId == '34') {
            $classes =  $HelperGateway->getClassByProgramForAcademic($connection2, $pupilsightProgramID, $uid, $pupilsightSchoolYearID);
            $sections =  $HelperGateway->getSectionByProgramForAcademic($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $uid);

            $sq = "select DISTINCT subjectToClassCurriculum.pupilsightDepartmentID, subjectToClassCurriculum.subject_display_name from subjectToClassCurriculum  LEFT JOIN assignstaff_tosubject ON subjectToClassCurriculum.pupilsightDepartmentID = assignstaff_tosubject.pupilsightDepartmentID  LEFT JOIN pupilsightStaff ON assignstaff_tosubject.pupilsightStaffID = pupilsightStaff.pupilsightStaffID  where subjectToClassCurriculum.pupilsightSchoolYearID = '" . $pupilsightSchoolYearID . "' AND subjectToClassCurriculum.pupilsightProgramID = '" . $pupilsightProgramID . "' AND subjectToClassCurriculum.pupilsightYearGroupID ='" . $pupilsightYearGroupID . "' AND assignstaff_tosubject.pupilsightRollGroupID = '" . $pupilsightRollGroupID . "'  AND pupilsightStaff.pupilsightPersonID='" . $uid . "' order by subjectToClassCurriculum.subject_display_name asc";
            $resultd = $connection2->query($sq);
            $rowdatadept = $resultd->fetchAll();
            
            $subject2=array();  
            // $subject1=array(''=>'Select Subjects');
            foreach ($rowdatadept as $dt) {
                $subject2[$dt['pupilsightDepartmentID']] = $dt['subject_display_name'];
            }
            $subjects=  $subject2; 
        } else {
            $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID, $pupilsightSchoolYearID);
            $sections =  $HelperGateway->getSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $pupilsightSchoolYearID);

            $sq = "select pupilsightDepartmentID, subject_display_name, di_mode from subjectToClassCurriculum where pupilsightSchoolYearID = '" . $pupilsightSchoolYearID . "' AND pupilsightProgramID = '" . $pupilsightProgramID . "' AND pupilsightYearGroupID ='" . $pupilsightYearGroupID . "' order by subject_display_name asc";
            $resultd = $connection2->query($sq);
            $rowdatadept = $resultd->fetchAll();
            
            $subject2=array();  
            foreach ($rowdatadept as $dt) {
                $subject2[$dt['pupilsightDepartmentID']] = $dt['subject_display_name'];
            }
            $subjects=  $subject2; 
        }

    } else {
        $classes = array('' => 'Select Class');
        $sections = array('' => 'Select Section');
        $subjects=array();  
        $pupilsightProgramID =  '';
        //   $pupilsightSchoolYearIDpost = $pupilsightSchoolYearID;
        $pupilsightYearGroupID = '';
        $pupilsightRollGroupID = '';
        $pupilsightDepartmentID='';
        $skill_id = '';
        $test_id  = '';
        $test_id1  = '';
        $test_type  = '';
        $searchbyPost = '';
        $search = '';
        $stuId = '0';
       
        $skillsdata = array();
        $testarr = array();
    }
    $sql_rmk = 'SELECT id, description FROM acRemarks ';
    $result_rmk = $connection2->query($sql_rmk);
    $rowdata_rmk = $result_rmk->fetchAll();
    $remarks=array();  
    $remark2=array();  
    // $subject1=array(''=>'Select Subjects');
    foreach ($rowdata_rmk as $dr) {
        $subject2[$dr['id']] = $dr['description'];
    }
    $remarks=  $remark2;  
    
    
    //$test_types=array('' =>'Select test type','Term1'=>'Term 1','Term2');
    //$test_types = array(__('Select') => array ('Term1' => __('Term 1'), 'Term2' => __('Term 2')));

    $sqlterm = 'SELECT * FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' ORDER BY pupilsightSchoolYearTermID ASC';
    $resultterm = $connection2->query($sqlterm);
    $termdata = $resultterm->fetchAll();

    $test_types = array();
    $term1 = array(''=>'Please Select');
    $term2 = array();
    foreach($termdata as $trm){
        $term2[$trm['pupilsightSchoolYearTermID']] = $trm['name'];
    }
    if(!empty($term2)){
        $test_types = $term1 + $term2;
    }

    

    $sqlsk = 'SELECT id, name FROM ac_manage_skill ';
    $resultsk = $connection2->query($sqlsk);
    $skilldata = $resultsk->fetchAll();
    $skills=array ('' => __('Select'));  
    $skills2=array();  

    foreach ($skilldata as $sk) {
        $skills2[$sk['id']] = $sk['name'];
    }
    $skills+=  $skills2;     


    $searchform = Form::create('searchForm', '');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->setClass('noIntBorder fullWidth');
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->setId('pupilsightProgramIDbyPPbyMarks')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder('Select Program');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
     $col->addSelect('pupilsightYearGroupID')->setId('pupilsightYearGroupIDbyPPbyMarks')->fromArray($classes)->selected($pupilsightYearGroupID)->required()->placeholder('Select Class');

     
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelect('pupilsightRollGroupID')->setId('pupilsightRollGroupIDbyPPbyMarks')->fromArray($sections)->required()->selected($pupilsightRollGroupID)->placeholder('Select Section');


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightDepartmentID', __('Subjects'));
    $col->addSelect('pupilsightDepartmentID')->setId('pupilsightDepartmentIDbyPPbyMarks')->fromArray($subjects)->required()->selected($pupilsightDepartmentID)->placeholder()->required();
      $col = $row->addColumn()->setClass('newdes');

    $col->addLabel('skill_id', 'Skill');
    //$col->addSelect('skill_id')->fromArray($skillsdata)->selected($skill_id)->required();
    $col->addSelect('skill_id')->fromArray($skillsdata)->selected($skill_id);

    $col = $row->addColumn()->setClass('newdes ');
    $col->addLabel('test_type', __('Test Type'));
    $col->addSelect('test_type')->fromArray($test_types)->setId('termId');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('test_id', __('Select Test'));
    $col->addSelect('test_id')->setId('testId')->selectMultiple()->fromArray($testarr)->selected($test_id)->required()->setClass('test_lngth');

    //$col->addSelect('test_id')->fromArray($testarr)->setId('testId')->selected($test_id);   
  
    $col = $row->addColumn()->setClass('newdes');   
    $col->addContent('<div style="width:90px;margin-top: 30px;"><a id="searchSubmit" class=" btn btn-primary">Go</a>&nbsp;&nbsp;
   </div>');  
    
    echo $searchform->getOutput();
    echo  "<div style='height:20px'></div>";
    $CurriculamGateway = $container->get(CurriculamGateway::class);
    $criteria = $CurriculamGateway->newQueryCriteria()
    //->sortBy(['id'])
        ->pageSize(1000)
        ->fromPOST();
        //print_r($criteria);

    $chkelectSubsql = 'SELECT a.id FROM ac_elective_group AS a LEFT JOIN ac_elective_group_subjects AS b ON a.id = b.ac_elective_group_id WHERE a.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" AND a.pupilsightProgramID = "'.$pupilsightProgramID.'" AND a.pupilsightYearGroupID = "'.$pupilsightYearGroupID.'" AND b.pupilsightDepartmentID = "'.$pupilsightDepartmentID.'" ';
    $resChk= $connection2->query($chkelectSubsql);
    $chkEleSubData = $resChk->fetch();
    if(!empty($chkEleSubData)){
       $chkEleSub = $chkEleSubData['id'];
    } else {
       $chkEleSub = '';
    }


    $students = $CurriculamGateway->getstudent_subject_skill_test_mappingdata($criteria, $pupilsightSchoolYearID, $pupilsightProgramID,$pupilsightYearGroupID, $pupilsightRollGroupID,$pupilsightDepartmentID,$skill_id,$test_id1,$test_type, $chkEleSub);

    $subject_wise_tests =  $CurriculamGateway->getStudentTestSubjectClassWise($criteria,$pupilsightSchoolYearID,$pupilsightDepartmentID,$pupilsightYearGroupID,$pupilsightRollGroupID,$test_id1,$skill_id);

    if(count($subject_wise_tests)<1){
        $subject_wise_tests_all =  $CurriculamGateway->getStudentTestSubjectClassWiseAll($criteria,$pupilsightSchoolYearID,$pupilsightDepartmentID,$pupilsightYearGroupID,$pupilsightRollGroupID,$test_id1);
        
        if(count($subject_wise_tests_all)<1){
            $subject_wise_tests = $subject_wise_tests;
        } else {
            $subject_wise_tests = $subject_wise_tests_all;
        }
    } else {
        $subject_wise_tests = $subject_wise_tests;
    }

    // echo '<pre>';
    // print_r($subject_wise_tests);
    // echo '<pre>';
     
     $sql_check='SELECT di_mode  FROM `subjectToClassCurriculum` WHERE `pupilsightSchoolYearID` = "'.$pupilsightSchoolYearID.'" AND `pupilsightProgramID` = "'.$pupilsightProgramID.'" AND `pupilsightYearGroupID` = "'.$pupilsightYearGroupID.'" AND `pupilsightDepartmentID` = "'.$pupilsightDepartmentID.'" ';
            $re_mode_check= $connection2->query($sql_check);
            $re_mode_data_check = $re_mode_check->fetch();
            $mode="";
            if(!empty($re_mode_data_check['di_mode'])){
              $mode=$re_mode_data_check['di_mode'];
            }
    if (count($students) < 1 || count($subject_wise_tests)<1 ) {
        echo "<div class='error'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {
        foreach($subject_wise_tests as $st){
            $subjectName = $st['subject_display_name'];
            if($st['lock_marks_entry'] == '1'){
                $disabledButt = 'disabled';
            } else {
                $disabledButt = '';
            }

            $gradeId = $st['gradeSystemId'];
        }
         if($mode=="SUBJECT_GRADE_WISE_AUTO"){
           $sql_mode='SELECT * FROM `subject_skill_descriptive_indicator_config`  WHERE pupilsightDepartmentID ="'.$pupilsightDepartmentID.'" AND pupilsightProgramID ="'.$pupilsightProgramID.'" AND pupilsightYearGroupID="'.$pupilsightYearGroupID.'" ORDER BY remark_description ASC';
            $re_mode = $connection2->query($sql_mode);
            $re_mode_data = $re_mode->fetchAll();
         } 
        //$subjectName = $subject_wise_tests->dataSet['data'][0]['subject_display_name'];
        echo '<h2>Subject : '.$subjectName.'</h2>';
        echo '<form method="POST" id="marksbysubject" action="'.$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/manage_marks_entry_by_subject_addProcess.php">
        <input type="hidden" name="address" value="'.$_SESSION[$guid]['address'].'">
        <input type="hidden" name="pupilsightSchoolYearID" value="'.$pupilsightSchoolYearID.'">      
        <input type="hidden" name="pupilsightYearGroupID" value="'.$pupilsightYearGroupID.'">
        <input type="hidden" name="pupilsightDepartmentID" value="'.$pupilsightDepartmentID.'">
        <input type="hidden" name="pupilsightRollGroupID" value="'.$pupilsightRollGroupID.'">       
        <input type="hidden" name="skill_id" value="'.$skill_id.'">
        <input type="hidden" id="gradeId" value="'.$gradeId.'">
        ';       
        ?>  
        <a  id='exportExcelNew' data-type='student' class='btn btn-primary' style='float: right;'>Export To Excel</a>
        <a style='display:none' id='showMarkHistory' href='fullscreen.php?q=/modules/Academics/show_subject_mark_history.php&width=800'  class='thickbox '></a>      
         <a id='save_marks_by_subject' data-type='test' class='btn btn-primary' <?php echo $disabledButt; ?>>Save</a>     
         <div style="overflow-x:auto;">
         <div style='display:none' id='marks_subjectExcel'></div>
         <div class="large-table-fake-top-scroll-container-3">
            <div>&nbsp;</div>
        </div>
        <div class="double-scroll table-wrapper-scroll-y">
         <table  class ='table text-nowrap data-table' id="expore_tbl" cellspacing='0' style='width: 100%;margin-top: 20px;'>
            <thead>
                <tr class='head'>
                <!--th style="width:80px" rowspan="2">
                    <input type="checkbox" name="checkall" id="checkall" value="on" class="floatNone checkall">
                    </th!-->
                    <th rowspan="2" style="width:50px"> Sl No </th>
                    <th rowspan="2" style="width:80px"> Student Name </th>
                    <th rowspan="2" style="width:80px" class="bdr_right"> Admission No </th>
                    <?php 
                    //echo count($subject_wise_tests);
                    $i1 = 1;
                    foreach($subject_wise_tests as $s_test)
                    { 
                        if($i1 > 1){ 
                            $colspan = '7';
                        } else {
                            $colspan = '6';
                        }
                        ?>
                    <th colspan="<?php echo $colspan;?>" style="text-align:center; border:1px solid #dee2e6"><?php echo $s_test['name']?></th>
                    <?php $i1++; } ?>               
                </tr>
                <tr>
                <?php 
                $i = 1;
                foreach($subject_wise_tests as $s_test){ 
                    if($i > 1){
                ?> 
                
                    <th rowspan="2" style="width:80px"> Student Name <?php echo 'dd'.$s_test['enable_remarks'];?> </th>
                <?php } ?>
                        <th>Marks history</th>            
                        <th colspan='2'> Marks <br/>Obtained(<?php echo str_replace(".00", "", $s_test['max_marks']);?>)</th>
                        <th>Grade</th>
                        <th>Grade Status</th>
                        <?php if($s_test['enable_remarks'] == '1') { ?>
                            <th class="bdr_right" data-orderable="false">Remark all <input type="checkbox" data-id="<?php echo $s_test['test_id'];?>" class="remark_all"></th>
                        <?php } ?>
                        
                        <?php $i++; } ?>
                </tr>    
            </thead>       
        <tbody>
        <?php                           
            
            $count = 0;
            $rowNum = 'odd';
            $f = 1;
            foreach ($students as $row) {

                if ($count % 2 == 0) {
                    $rowNum = 'even';
                } else {
                    $rowNum = 'odd';
                }
                ++$count;
                echo "<tr class='".$rowNum."'>";
                echo ' <input type="hidden" name="student_id['.$row['stuid'].']" value="'.$row['stuid'].'">';
            /*  echo '<td>';
                echo  '<input type="checkbox" name="student_id[]" id="student_id[' . $row['pupilsightPersonID'] . ']" value="'.$row['pupilsightPersonID'].'" >';
                echo '</td>';
                */       
                echo '<td >'; echo '<center>'; echo $count;echo '</center>';echo '</td>';
                echo '<td>'; echo $row['student_name']; echo '</td>';
                echo '<td>'; echo $row['admission_no']; echo '</td>'; 
                ?>                 
                <?php           
                //echo count($subject_wise_tests);
                $km = 1;
                foreach($subject_wise_tests as $k => $s_test)
                {
                    $sql = "SELECT lock_marks_entry, enable_pdf, enable_html, enable_test_report FROM examinationTestStudentConfig WHERE pupilsightSchoolYearID = ".$pupilsightSchoolYearID." AND pupilsightProgramID = ".$pupilsightProgramID." AND pupilsightYearGroupID = ".$pupilsightYearGroupID." AND pupilsightRollGroupID = ".$pupilsightRollGroupID." AND test_id = ".$s_test['test_id']." AND pupilsightPersonID = ".$row['stuid']."";
                    $result = $connection2->query($sql);
                    $chkData = $result->fetch();

                    if(!empty($chkData) && ($chkData['enable_test_report'] == '2' || $chkData['lock_marks_entry'] == '2')){
                        $lock_clss = 'disable_input';
                    }

                    $skill_configure = $s_test['skill_configure'];
                    
                    if($skill_configure != 'None' && $s_test['skill_id'] == 0){
                        // if($s_test['lock_marks_entry'] == '1'){
                        //     $disabled = 'disabled';
                        // } else {
                        //     $disabled = '';
                        // }

                        $disabled = 'disabled';
                       
                        // $data1 = array('test_id' => $s_test['test_id'], 'pupilsightYearGroupID' => $pupilsightYearGroupID,'pupilsightRollGroupID' => $pupilsightRollGroupID,'pupilsightDepartmentID' => $pupilsightDepartmentID,'pupilsightPersonID' => $row['stuid'],'skill_id' => $skill_id);                    
                        // $sql1 = 'SELECT * FROM examinationMarksEntrybySubject WHERE test_id=:test_id  AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightPersonID=:pupilsightPersonID AND  skill_id=:skill_id ';
                        // $result = $connection2->prepare($sql1);
                        // $result->execute($data1);
                        // $prevdata =  $result->fetch();
                        
                        $skill_configure = strtoupper($skill_configure);
                        $sql1 = 'SELECT *, '.$skill_configure.'(marks_obtained) as total_marks  FROM examinationMarksEntrybySubject WHERE test_id='.$s_test['test_id'].'  AND pupilsightYearGroupID='.$pupilsightYearGroupID.' AND pupilsightRollGroupID='.$pupilsightRollGroupID.' AND pupilsightDepartmentID='.$pupilsightDepartmentID.' AND pupilsightPersonID='.$row['stuid'].' ';
                        $result = $connection2->query($sql1);
                        $prevdata = $result->fetch();
                        // echo '<pre>';
                        // print_r($prevdata);
                        // echo '</pre>';
                        // die();
    
                        
                        $data_sel = '<option value="">Select Remark</option>';
                        if($mode=="SUBJECT_GRADE_WISE_AUTO"){
                        foreach ($re_mode_data as  $val) {
                            if($prevdata['remark_type'] == 'list' && $prevdata['remarks'] == $val['remark_description']) {
                                $sel = 'selected';
                            } else {
                                $sel = '';
                            }
                            $data_sel .= '<option value="' . $val['grade_id']. '" '.$sel.'>' . $val['remark_description'] . '</option>';
                        }
                        } else {
                        if (!empty($rowdata_rmk)) {
                                foreach ($rowdata_rmk as $k => $rk) {
                                    if($prevdata['remark_type'] == 'list' && $prevdata['remarks'] == $rk['description']) {
                                        $sel = 'selected';
                                    } else {
                                        $sel = '';
                                    }
                                    $data_sel .= '<option value="' . $rk['description'] . '" '.$sel.'>' . $rk['description'] . '</option>';
                                }
                            }  
                        }
                        
                        if($prevdata['status']==1 && $prevdata['entrytype'] == 2)
                        {
                            $locked = 'locked';
                            
                        }
                        else
                        {
                            $locked = '';
                        }
                        //echo $km;
    
                        if($km > 1){
                            echo '<td>'; echo $row['student_name']; echo '</td>';
                        }

                        
    
    
                        // $sqlMarks = 'SELECT * FROM examinationMarksEntrybySubject WHERE test_id='..' AND pupilsightYearGroupID='..' AND pupilsightRollGroupID='..' AND pupilsightDepartmentID='..' AND test_id='..' AND test_id='..' AND  '
                         ?>
                          <td><center>
                          <?php /*?>
                          <a href="javascript:void(0)" class="getMaxHistroy" title="Max history" data-stid="<?php echo $row['stuid'];?>" data-tid="<?php echo $s_test['test_id'];?>" data-dep="<?php echo $pupilsightDepartmentID;?>">
                          <?php */?>
    
                          <a href="fullscreen.php?q=/modules/Academics/show_history.php&pid=<?php echo $pupilsightProgramID;?>&cid=<?php echo $pupilsightYearGroupID;?>&sid=<?php echo $pupilsightRollGroupID;?>&did=<?php echo $pupilsightDepartmentID;?>&skid=<?php echo $skill_id;?>&tid=<?php echo $s_test['test_id'];?>&stid=<?php echo $row['stuid'];?>" class="thickbox" title="Max history" data-stid="<?php echo $row['stuid'];?>" data-tid="<?php echo $s_test['test_id'];?>" data-skil="<?php echo $skill_id;?>">
                          <i class="mdi mdi-history mdi-24px" aria-hidden="true"></i></a></center></td> 
                         <?php
                        echo ' <input type="hidden" name="test_id['.$s_test['test_id'].']" value="'.$s_test['test_id'].'">';
                        echo ' <input type="hidden" name="lock_status['.$s_test['test_id'].']['. $row['stuid'].']" value="'.$prevdata['status'].'">';
                        echo '<td class="td_texfield">';
                        //if marks is enabled
                        // $en_dis_clss=($s_test['assesment_method']=='Marks')? '' : 'disable_input';
                        // $en_dis_grd_clss=($s_test['assesment_method']=='Grade')? '' : 'disable_input';   

                        $en_dis_clss='disable_input';
                        $en_dis_grd_clss='disable_input';   
                        
                        if(!empty($prevdata['marks_abex'])){
                            $marksobt = '';
                        } else {
                            $marksobt = str_replace(".00","",$prevdata['total_marks']);
                            //$marksobt = rtrim($marksobt,'0');
                        }
                        
    
                        echo '<input type="text" data-mark="'.$s_test['max_marks'].'" data-cnt="'.$row['stuid'].'" data-lock="'.$locked.'" data-tid="'.$s_test['test_id'].'" name="mark_obtained['.$s_test['test_id'].']['. $row['stuid'].']" data-gid="'.$s_test['gradeSystemId'].'" data-fid="'.$f.'"  class="numMarksfield chkData tabfocus enable_input mark_obtn textfield_wdth abexClsDis'.$s_test['test_id'].$row['stuid'].'  '.$en_dis_clss.' '.$lock_clss.' " id="focustab-'.$s_test['test_id'].'-'.$f.'" data-nid="'.$s_test['test_id'].$row['stuid'].'" value="'.$marksobt.'"  '.$disabled.'>';
                        echo '</td>';  
                        
                        $seab = array("-", "AB", "EX");
                                $slen = count($seab);
                                $s = 0;
    
                                echo '<td><select class="chkData mr-2 abex width60px '.$lock_clss.' " data-id="'.$s_test['test_id'].$row['stuid'].'" name="marks_abex[' . $s_test['test_id'] . '][' . $row['stuid'] . ']" disabled>';
                                while ($s < $slen) {
                                    if($prevdata['marks_abex'] == $seab[$s]){
                                        $selected = 'selected';
                                    } else {
                                        $selected = '';
                                    }
                                    echo '<option value="' . $seab[$s] . '" '.$selected.'>' . $seab[$s] . '</option>';
                                    $s++;
                                }
                                echo '</select></td>';
    
                        //if grade is enable 
                        echo '<td class="'.$en_dis_grd_clss.' '.$lock_clss.' ">'; 
                        

                        if(!empty($marksobt) && !empty($s_test['gradeSystemId'])){
                            $obtMark = $s_test['max_marks'];
                            $mrks = ($marksobt / $obtMark) * 100;
                            $sql = 'SELECT grade_name,id, subject_status FROM examinationGradeSystemConfiguration  WHERE gradeSystemId="' . $s_test['gradeSystemId'] . '" AND  (' . $mrks . ' BETWEEN `lower_limit` AND `upper_limit`)';
                            $result = $connection2->query($sql);
                            $grade = $result->fetch();
    
                            $gstatus = $grade['subject_status'];
                            $grID = $grade['id'];
                        } else {
                            $gstatus = '';
                            $grID = 0;
                        }
                        //echo $row['stuid'].$grID.'<br>';
                        $grade_arr=array();
                        $test_grades = explode(',', $s_test['grade_names']);
                        $grade_ids = explode(',', $s_test['grade_ids']);
                        $grade_arr = array_combine($grade_ids, $test_grades);
                        //     echo "<pre>";print_r($grade_arr);
                        foreach ($grade_arr as $grdid => $tgrade) {
                            // if($prevdata['gradeId'] == $grdid){
                            if($grID == $grdid){
                                $selected = 'checked';
                            } else {
                                $selected = '';
                            }

                            echo ' <input type="radio" class="chkData abexClsDis'.$s_test['test_id'].$row['stuid'].'" id="grade_val'.$s_test['test_id'].'row'.$row['stuid'].'grade'.trim($grdid).'"   name="grade_val['.$s_test['test_id'].']['. $row['stuid'].']" value="'.$grdid.'"    '.$selected.'>'.$tgrade.'';
                    
                        }
                        echo '</fieldset></td>';
                        //echo $prevdata['marks_obtained'].' -- '.$prevdata['gradeId'];
                        
                        
                        echo '<td id="grade_status'.$s_test['test_id'].'row'.$row['stuid'].'">'.$gstatus.'</td>';
                        if($s_test['enable_remarks'] == '1') { 
                            echo '<td> ';
                            if(!empty($prevdata['remarks'])){
                                $colrCls = 'grnRemark';
                            } else {
                                $colrCls = '';
                            }
                            echo '<i class="mdi mdi-book-open-outline mdi-24px px-4 remark_enter_type icon_re_'.$s_test['test_id'].' '.$colrCls.' " id="rmk'.$s_test['test_id'].'stu'.$row['stuid'].'" data-id="'.$s_test['test_id'].'stu'.$row['stuid'].'"></i>';
                        
                            ?>
    
                            <div class="show_remark_div remark_div_<?php echo $s_test['test_id'];?>"  id="show_remark_div<?php echo $s_test['test_id'].'stu'.$row['stuid']; ?>" style="display:none;">
                                <div class="show_remark_div remark_div_<?php echo $s_test['test_id'];?> <?php echo $lock_clss;?>" style="width:190px"  id="show_remark_div<?php echo $s_test['test_id'].'stu'.$row['stuid']; ?>">
                                    <p><label>From List </label>
                                    <input type="radio" id="fromlist" class="rm_type" tid="<?php echo $s_test['test_id'].'stu'.$row['stuid']; ?>" name="remark_type[<?php echo $s_test['test_id'] ?>][<?php echo $row['stuid']; ?>]" value="fromlist" style="margin-left: 2px;"  <?php if($prevdata['remark_type'] == 'list'){ ?> checked <?php } ?>>
                                    &nbsp;&nbsp;
                                    <label> Your Own </label>
                                    <input type="radio" id="enter_own"  class="rm_type rm_type_<?php echo $s_test['test_id'];?>" tid="<?php echo $s_test['test_id'].'stu'.$row['stuid']; ?>" name="remark_type[<?php echo $s_test['test_id'] ?>][<?php echo $row['stuid']; ?>]" value="enter_own" style="margin-left: 2px;"   <?php if($prevdata['remark_type'] == 'own' && !empty($prevdata['remarks'])){ ?> checked <?php } ?> >
                                    </p>
                                </div>
                                <?php 
                                    if($prevdata['remark_type'] == 'list'){ 
                                        $showlist = '';
                                    } else {
                                        $showlist = 'display:none;';
                                    }
                                ?>
                                <select id="remarklist<?php echo $s_test['test_id'].'stu'.$row['stuid']; ?>" name="" class="w-full remarklist rmk_width" style="<?php echo $showlist;?>" data-id="<?php echo $s_test['test_id'].'stu'.$row['stuid']; ?>">
                                <?php echo $data_sel;?>
                                </select>
    
                                <?php 
                                    if($prevdata['remark_type'] == 'list'){
                                        $remList = $prevdata['remarks'];
                                    } else {
                                        $remList = '';
                                    }
                                ?>
                            
                                <input id="remarklistval<?php echo $s_test['test_id'].'stu'.$row['stuid']; ?>" type="hidden" name="remark_frmlst[<?php echo $s_test['test_id'] ?>][<?php echo $row['stuid']; ?>]" value="<?php echo $remList;?>">
                                <?php 
                                    if($prevdata['remark_type'] == 'own'){
                                        $remOwnData = $prevdata['remarks'];
                                    } else {
                                        $remOwnData = '';
                                    }
                                ?>
    
                                <?php 
                                    if($prevdata['remark_type'] == 'own' && !empty($prevdata['remarks'])){ 
                                        $showown = '';
                                    } else {
                                        $showown = 'display:none;';
                                    }
                                ?>
                                <textarea data-tid="<?php echo $s_test['test_id'];?>"  data-fid="<?php echo $f;?>"  style="<?php echo $showown;?> margin: 0 0px 0px -8px;" name="remark_own[<?php echo $s_test['test_id'] ?>][<?php echo $row['stuid']; ?>]" id="remark_textarea<?php echo $s_test['test_id'].'stu'.$row['stuid']; ?>" rows="2" cols="10" class="tabfocusRemark remark_textarea text_remark_<?php echo $s_test['test_id']; ?>  focustabRemark-<?php echo $s_test['test_id'];?>-<?php echo $f; ?>" ><?php echo $remOwnData; ?></textarea> 
                                <br/> 
                                <span class="rcount_<?php echo $s_test['test_id'];?>"></span>        
                            </div>
                            <?php
                            echo '</td>';
                        }
                        echo '</fieldset>';
                       $km++;
                    } else {
                        //echo 'working';
                        if($s_test['lock_marks_entry'] == '1'){
                            $disabled = 'disabled';
                        } else {
                            $disabled = '';
                        }
    
                        if(!empty($skill_id)){
                            $data1 = array('test_id' => $s_test['test_id'], 'pupilsightYearGroupID' => $pupilsightYearGroupID,'pupilsightRollGroupID' => $pupilsightRollGroupID,'pupilsightDepartmentID' => $pupilsightDepartmentID,'pupilsightPersonID' => $row['stuid'],'skill_id' => $skill_id);                    
                            $sql1 = 'SELECT * FROM examinationMarksEntrybySubject WHERE test_id=:test_id  AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightPersonID=:pupilsightPersonID AND  skill_id=:skill_id ';
                            $result = $connection2->prepare($sql1);
                            $result->execute($data1);
                            $prevdata =  $result->fetch();
                        } else {
                            $data1 = array('test_id' => $s_test['test_id'], 'pupilsightYearGroupID' => $pupilsightYearGroupID,'pupilsightRollGroupID' => $pupilsightRollGroupID,'pupilsightDepartmentID' => $pupilsightDepartmentID,'pupilsightPersonID' => $row['stuid']);                    
                            $sql1 = 'SELECT * FROM examinationMarksEntrybySubject WHERE test_id=:test_id  AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightPersonID=:pupilsightPersonID';
                            $result = $connection2->prepare($sql1);
                            $result->execute($data1);
                            $prevdata =  $result->fetch();
                        }
                        
                        // echo '<pre>';
                        // print_r($prevdata);
                        // echo '</pre>';
    
                        
                        $data_sel = '<option value="">Select Remark</option>';
                        if($mode=="SUBJECT_GRADE_WISE_AUTO"){
                        foreach ($re_mode_data as  $val) {
                            if($prevdata['remark_type'] == 'list' && $prevdata['remarks'] == $val['remark_description']) {
                                $sel = 'selected';
                            } else {
                                $sel = '';
                            }
                            $data_sel .= '<option value="' . $val['grade_id']. '" '.$sel.'>' . $val['remark_description'] . '</option>';
                        }
                        } else {
                        if (!empty($rowdata_rmk)) {
                                foreach ($rowdata_rmk as $k => $rk) {
                                    if($prevdata['remark_type'] == 'list' && $prevdata['remarks'] == $rk['description']) {
                                        $sel = 'selected';
                                    } else {
                                        $sel = '';
                                    }
                                    $data_sel .= '<option value="' . $rk['description'] . '" '.$sel.'>' . $rk['description'] . '</option>';
                                }
                            }  
                        }
                        
                        if($prevdata['status']==1 && $prevdata['entrytype'] == 2)
                        {
                            $locked = 'locked';
                            
                        }
                        else
                        {
                            $locked = '';
                        }
                        //echo $km;
    
                        if($km > 1){
                            echo '<td>'; echo $row['student_name']; echo '</td>';
                        }
    
    
                        // $sqlMarks = 'SELECT * FROM examinationMarksEntrybySubject WHERE test_id='..' AND pupilsightYearGroupID='..' AND pupilsightRollGroupID='..' AND pupilsightDepartmentID='..' AND test_id='..' AND test_id='..' AND  '
                         ?>
                          <td><center>
                          <?php /*?>
                          <a href="javascript:void(0)" class="getMaxHistroy" title="Max history" data-stid="<?php echo $row['stuid'];?>" data-tid="<?php echo $s_test['test_id'];?>" data-dep="<?php echo $pupilsightDepartmentID;?>">
                          <?php */?>
    
                          <a href="fullscreen.php?q=/modules/Academics/show_history.php&pid=<?php echo $pupilsightProgramID;?>&cid=<?php echo $pupilsightYearGroupID;?>&sid=<?php echo $pupilsightRollGroupID;?>&did=<?php echo $pupilsightDepartmentID;?>&skid=<?php echo $skill_id;?>&tid=<?php echo $s_test['test_id'];?>&stid=<?php echo $row['stuid'];?>" class="thickbox" title="Max history" data-stid="<?php echo $row['stuid'];?>" data-tid="<?php echo $s_test['test_id'];?>" data-skil="<?php echo $skill_id;?>">
                          <i class="mdi mdi-history mdi-24px" aria-hidden="true"></i></a></center></td> 
                         <?php
                        echo ' <input type="hidden" name="test_id['.$s_test['test_id'].']" value="'.$s_test['test_id'].'">';
                        echo ' <input type="hidden" name="lock_status['.$s_test['test_id'].']['. $row['stuid'].']" value="'.$prevdata['status'].'">';
                        echo '<td class="td_texfield">';
                        //if marks is enabled
                        $en_dis_clss=($s_test['assesment_method']=='Marks')? '' : 'disable_input';
                        $en_dis_grd_clss=($s_test['assesment_method']=='Grade')? '' : 'disable_input';   
                        
                        if(!empty($prevdata['marks_abex'])){
                            $marksobt = '';
                        } else {
                            $marksobt = str_replace(".00","",$prevdata['marks_obtained']);
                            //$marksobt = rtrim($marksobt,'0');
                        }
                        
    
                        echo '<input type="text" data-mark="'.$s_test['max_marks'].'" data-cnt="'.$row['stuid'].'" data-lock="'.$locked.'" data-tid="'.$s_test['test_id'].'" name="mark_obtained['.$s_test['test_id'].']['. $row['stuid'].']" data-gid="'.$s_test['gradeSystemId'].'" data-fid="'.$f.'"  class="numMarksfield chkData tabfocus enable_input mark_obtn textfield_wdth abexClsDis'.$s_test['test_id'].$row['stuid'].'  '.$en_dis_clss.' '.$lock_clss.' " id="focustab-'.$s_test['test_id'].'-'.$f.'" data-nid="'.$s_test['test_id'].$row['stuid'].'" value="'.$marksobt.'"  '.$disabled.'>';
                        echo '</td>';  
                        
                        $seab = array("-", "AB", "EX");
                                $slen = count($seab);
                                $s = 0;
    
                                echo '<td><select class="chkData mr-2 abex width60px '.$lock_clss.'" data-id="'.$s_test['test_id'].$row['stuid'].'" name="marks_abex[' . $s_test['test_id'] . '][' . $row['stuid'] . ']">';
                                while ($s < $slen) {
                                    if($prevdata['marks_abex'] == $seab[$s]){
                                        $selected = 'selected';
                                    } else {
                                        $selected = '';
                                    }
                                    echo '<option value="' . $seab[$s] . '" '.$selected.'>' . $seab[$s] . '</option>';
                                    $s++;
                                }
                                echo '</select></td>';
    
                        //if grade is enable 
                        echo '<td class="'.$en_dis_grd_clss.' '.$lock_clss.'">'; 
                        $grade_arr=array();
                        $test_grades = explode(',', $s_test['grade_names']);
                        $grade_ids = explode(',', $s_test['grade_ids']);
                        $grade_arr = array_combine($grade_ids, $test_grades);
                        //     echo "<pre>";print_r($grade_arr);
                        foreach ($grade_arr as $grdid => $tgrade) {
                            if($prevdata['gradeId'] == $grdid){
                                $selected = 'checked';
                            } else {
                                $selected = '';
                            }
                        echo ' <input type="radio" class="chkData abexClsDis'.$s_test['test_id'].$row['stuid'].'" id="grade_val'.$s_test['test_id'].'row'.$row['stuid'].'grade'.trim($grdid).'"   name="grade_val['.$s_test['test_id'].']['. $row['stuid'].']" value="'.$grdid.'"  '.$disabled.'  '.$selected.'>'.$tgrade.'';
                    
                        }
                        echo '</fieldset></td>';
                        //echo $prevdata['marks_obtained'].' -- '.$prevdata['gradeId'];
                        
                        if(!empty($marksobt) && !empty($s_test['gradeSystemId'])){
                            $obtMark = $s_test['max_marks'];
                            $mrks = ($marksobt / $obtMark) * 100;
                            $sql = 'SELECT grade_name,id, subject_status FROM examinationGradeSystemConfiguration  WHERE gradeSystemId="' . $s_test['gradeSystemId'] . '" AND  (' . $mrks . ' BETWEEN `lower_limit` AND `upper_limit`)';
                            $result = $connection2->query($sql);
                            $grade = $result->fetch();
    
                            $gstatus = $grade['subject_status'];
                        } else {
                            $gstatus = '';
                        }
                        echo '<td class="'.$lock_clss.'" id="grade_status'.$s_test['test_id'].'row'.$row['stuid'].'">'.$gstatus.'</td>';
                        if($s_test['enable_remarks'] == '1') { 
                            echo '<td> ';
                            if(!empty($prevdata['remarks'])){
                                $colrCls = 'grnRemark';
                            } else {
                                $colrCls = '';
                            }
                            echo '<i class="mdi mdi-book-open-outline mdi-24px px-4 remark_enter_type icon_re_'.$s_test['test_id'].' '.$colrCls.' " id="rmk'.$s_test['test_id'].'stu'.$row['stuid'].'" data-id="'.$s_test['test_id'].'stu'.$row['stuid'].'"></i>';
                        
                            ?>
    
                            <div class="show_remark_div remark_div_<?php echo $s_test['test_id'];?>"  id="show_remark_div<?php echo $s_test['test_id'].'stu'.$row['stuid']; ?>" style="display:none;">
                                <div class="show_remark_div remark_div_<?php echo $s_test['test_id'];?>" style="width:190px"  id="show_remark_div<?php echo $s_test['test_id'].'stu'.$row['stuid']; ?>">
                                    <p><label>From List </label>
                                    <input type="radio" id="fromlist" class="rm_type" tid="<?php echo $s_test['test_id'].'stu'.$row['stuid']; ?>" name="remark_type[<?php echo $s_test['test_id'] ?>][<?php echo $row['stuid']; ?>]" value="fromlist" style="margin-left: 2px;"  <?php if($prevdata['remark_type'] == 'list'){ ?> checked <?php } ?>>
                                    &nbsp;&nbsp;
                                    <label> Your Own </label>
                                    <input type="radio" id="enter_own"  class="rm_type rm_type_<?php echo $s_test['test_id'];?>" tid="<?php echo $s_test['test_id'].'stu'.$row['stuid']; ?>" name="remark_type[<?php echo $s_test['test_id'] ?>][<?php echo $row['stuid']; ?>]" value="enter_own" style="margin-left: 2px;"   <?php if($prevdata['remark_type'] == 'own' && !empty($prevdata['remarks'])){ ?> checked <?php } ?> >
                                    </p>
                                </div>
                                <?php 
                                    if($prevdata['remark_type'] == 'list'){ 
                                        $showlist = '';
                                    } else {
                                        $showlist = 'display:none;';
                                    }
                                ?>
                                <select id="remarklist<?php echo $s_test['test_id'].'stu'.$row['stuid']; ?>" name="" class="w-full remarklist rmk_width" style="<?php echo $showlist;?>" data-id="<?php echo $s_test['test_id'].'stu'.$row['stuid']; ?>">
                                <?php echo $data_sel;?>
                                </select>
    
                                <?php 
                                    if($prevdata['remark_type'] == 'list'){
                                        $remList = $prevdata['remarks'];
                                    } else {
                                        $remList = '';
                                    }
                                ?>
                            
                                <input id="remarklistval<?php echo $s_test['test_id'].'stu'.$row['stuid']; ?>" type="hidden" name="remark_frmlst[<?php echo $s_test['test_id'] ?>][<?php echo $row['stuid']; ?>]" value="<?php echo $remList;?>">
                                <?php 
                                    if($prevdata['remark_type'] == 'own'){
                                        $remOwnData = $prevdata['remarks'];
                                    } else {
                                        $remOwnData = '';
                                    }
                                ?>
    
                                <?php 
                                    if($prevdata['remark_type'] == 'own' && !empty($prevdata['remarks'])){ 
                                        $showown = '';
                                    } else {
                                        $showown = 'display:none;';
                                    }
                                ?>
                                <textarea data-tid="<?php echo $s_test['test_id'];?>"  data-fid="<?php echo $f;?>"  style="<?php echo $showown;?> margin: 0 0px 0px -8px;" name="remark_own[<?php echo $s_test['test_id'] ?>][<?php echo $row['stuid']; ?>]" id="remark_textarea<?php echo $s_test['test_id'].'stu'.$row['stuid']; ?>" rows="2" cols="10" class="tabfocusRemark remark_textarea text_remark_<?php echo $s_test['test_id']; ?>  focustabRemark-<?php echo $s_test['test_id'];?>-<?php echo $f; ?>" ><?php echo $remOwnData; ?></textarea> 
                                <br/> 
                                <span class="rcount_<?php echo $s_test['test_id'];?>"></span>        
                            </div>
                            <?php
                            echo '</td>';
                        }
                        echo '</fieldset>';
                       $km++;
                    }
                    
                }       
                echo '</tr>';
                $f++;
             }

        echo "</tbody>";
        echo '</table> </div></div></form>';
        echo '<input type="hidden" id="chkMarksSaveData" value="0">';
    }
}
?>
<style>
    .text_cntr 
    {
        text-align: center;
    }   
    .bdr_right 
    {
        border-right: 2px solid #dee2e6;
    }
    .textfield_wdth 
    {
        width:75px;
    }
    .td_texfield 
    {
        width: 9%;
    }
    .rmk_width 
    {
        /* width: 137px; */
        margin : 0 8px 8px 0;
    }
    .enable_input 
    {
        background-color: #fff !important;
        border: 1px solid gray!important;


    }
    .disable_input 
    {
        background-color: #d2d2d2  !important;
        pointer-events:none;
        opacity:0.5;
    }
    span {
        cursor: pointer;
    }
    .text-xxs {
        display: none;
    }

    .grnRemark {
        color:green;
    }

    .txtColor {
        background-color: lightblue !important;
    }

    .table-wrapper-scroll-y {
        display: block;
        /* max-height: 200px; */
        overflow-y: auto;
        -ms-overflow-style: -ms-autohiding-scrollbar;
    }
    
</style>

<script>

    $(document).ready(function () {
      	$('#testId').selectize({
      		plugins: ['remove_button'],
      	});
    });

    $(document).on('click','.remark_all',function(){
      var id = $(this).attr('data-id');
        if($(this). prop("checked") == true){
            $(".remark_div_"+id).show();
            $(".text_remark_"+id).show();
            $(".rcount_"+id).show();
            //$(".rm_type_"+id).prop("checked", true);
        }
        else if($(this). prop("checked") == false){
            $(".remark_div_"+id).hide();
            $(".text_remark_"+id).hide();
            $(".rcount_"+id).hide();
            //$(".rm_type_"+id).prop("checked", false);
        }
    });
    $(document).on('keyup','.remark_textarea',function(){
       var txt=$(this).val();
       var count=txt.length;
       var dis=txt.replace(/\"/g, "");
       $(this).nextAll('span:first').html('Character length (<i class="fa fa-eye" aria-hidden="true"></i> ): '+count);
       $(this).nextAll('span:first').attr("title",dis);
    });
    $(document).on('click', '.remark_enter_type', function() {
    var divid= $(this).attr('data-id');
        $('#show_remark_div'+divid).toggle('show');
    });
    $(document).on('click', '.rm_type', function() {
        var tid= $(this).attr('tid');
        var rm_type = $(this).val();
         // alert(rm_type);
        if(rm_type=='fromlist')
        {
         //  alert("#remarklist"+tid);
            $('#remarklist'+tid).show();  
            $('#remark_textarea'+tid).hide();  
        }
        else if(rm_type=='enter_own')
        {
            $('#remark_textarea'+tid).show();  
            $('#remarklist'+tid).hide();  
        }
        else
        {
            $('#remarklist'+tid).hide(); 
            $('#remark_textarea'+tid).hide();  
        }
    });

    $(document).on('change', '.remarklist', function() {
        var id = $(this).attr('data-id');
        var val = $(this).val();
        $("#remarklistval"+id).val(val);
    });    

    $(document).on('change', '.mark_obtn', function() {
        var obtain_mark= Number($(this).attr('data-mark'));
        var enterd_mrk = Number($(this).val()); 
        var subject_mode="<?php echo $mode;?>";
        var nid = $(this).attr('data-nid');
        if(Number(obtain_mark) < Number(enterd_mrk)){
           alert('You cannot enter marks greater than max marks defined');
           $(this).val(""); 
           return;
        }  

        if($(this).val() == ''){
            $(".abexClsDis"+nid).prop("checked",false);
            return;
        }

        var grad_val = percentage(obtain_mark, enterd_mrk);
        //var grad_val = enterd_mrk;
        var lock_sts=   $(this).attr('data-lock');
        var tid= $(this).attr('data-tid');    
        var data_cnt=  $(this).attr('data-cnt');  
        var gradeid = $(this).attr('data-gid');
        var type = 'getGradeConfigDataSubject';
        if(lock_sts !='locked')
        {
        if (grad_val != ''  || grad_val == 0) {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: grad_val, type: type, gsid:gradeid },
                dataType: "json",
                async: true,
                success: function(response) {
                
                 //  alert('#grade_val'+tid+'row'+data_cnt+'grade'+response);
                // alert(response);
                if(response != '')
                {
                    var gid = response.id;
                    if(subject_mode=="SUBJECT_GRADE_WISE_AUTO"){
                        $("#remarklist"+tid+"stu"+data_cnt).val(gid);
                        $("#remarklistval"+tid+"stu"+data_cnt).val(gid);
                        setTimeout(function(){
                            if ($("#remarklist"+tid+"stu"+data_cnt)[0].selectedIndex <= 0) {
                                $("#remarklist"+tid+"stu"+data_cnt).prop('disabled', false);
                            } else {
                                $("#remarklist"+tid+"stu"+data_cnt).prop('disabled', true);
                            }
                        },10);
                        
                    }
                    var gstatus = response.status;
                    $('#grade_val'+tid+'row'+data_cnt+'grade'+gid).prop("checked",true);
                    $('#grade_status'+tid+'row'+data_cnt).html(gstatus);
                 }
                 else
                 {
                   var rdname= 'grade_val'+'['+tid+']'+'['+data_cnt+']';
                 //  alert(rdname);               
                   $('input[type="radio"][name='+rdname+']').prop("checked", false);
                 }
                    
                  
                }
            });
        }
     }
     else
     {
         alert("Marks entry is Locked for this");
         $(this).prop("value", "");
         $(this).prop("disabled", true);
         $('#rmk'+tid+'stu'+data_cnt).hide();
        
     }

      //  alert(grad_val);

    });
    function percentage(obtain_mark, enterd_mrk)
    {
        //return (enterd_mrk/obtain_mark)*99.99;
        return (enterd_mrk/obtain_mark)*100;
    }

    var reloadCall = false;
        var _pupilsightDepartmentID = "";
        var roleid= $("#roleid").val();
        var pupilsightPersonID = $("#roleid").attr('data-pid');
        
        // $("#pupilsightYearGroupIDbyPPbyMarks").change(function() {
        //     loadSubjects();
        // });

        $("#pupilsightRollGroupIDbyPPbyMarks").change(function() {
            loadSubjects();
        });
        

        function loadSubjects() {
            var pupilsightProgramID = $("#pupilsightProgramIDbyPPbyMarks").val();
            var pupilsightYearGroupID = $("#pupilsightYearGroupIDbyPPbyMarks").val();
            var pupilsightRollGroupID = $("#pupilsightRollGroupIDbyPPbyMarks").val();
            //var roleid= $("#roleid").val();
            if (pupilsightYearGroupID) {
                var type = "getSubjectbasedonclassNew";
                try {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: {
                            val: pupilsightYearGroupID,
                            type: type,
                            pupilsightProgramID:pupilsightProgramID,
                            pupilsightRollGroupID:pupilsightRollGroupID

                        },
                        async: true,
                        success: function(response) {
                            $("#pupilsightDepartmentIDbyPPbyMarks").html(response);
                            if (reloadCall) {
                                $("#pupilsightDepartmentIDbyPPbyMarks").val(_pupilsightDepartmentID);
                              
                            }
                        }
                    });
                } catch (ex) {
                    reloadCall = false;
                }
            }
        }

        $(document).on('keydown', '.tabfocus', function(e) {
            $(".numMarksfield").removeClass('txtColor');
            var id = $(this).attr('data-fid');
            var tid = $(this).attr('data-tid');
            var newid = parseInt(id) + 1;
            var keycode = (window.event) ? event.keyCode : e.keyCode;
            if (keycode == 9){
                window.setTimeout(function() {
                    $("#focustab-"+tid+'-'+newid).focus().addClass('txtColor');
                }, 10);
            }
        }); 

        $(document).on('keydown', '.tabfocusRemark', function(e) {
            
            //$(".tabfocusRemark").removeClass('txtColor');
            var id = $(this).attr('data-fid');
            var tid = $(this).attr('data-tid');
            var newid = parseInt(id) + 1;
            //alert(id);
            var keycode = (window.event) ? event.keyCode : e.keyCode;
            if (keycode == 9){
                window.setTimeout(function() {
                    // $(".focustabRemark-"+tid+'-'+newid).focus().addClass('txtColor');
                    $(".focustabRemark-"+tid+'-'+newid).focus();
                }, 10);
            }
        });       

       
    $(document).on('click','.getMaxHistroy',function(){
    var stid = $(this).attr('data-stid');
    var tid = $(this).attr('data-tid');
    var skil_id = $(this).attr('data-dep');
    var type ="setMarkHistoryVal";
    $.ajax({
    url: 'ajaxSwitch.php',
    type: 'post',
    data: { stid:stid,tid:tid,skil_id:skil_id,type:type},
    async: true,
    success: function(response) {
    $("#showMarkHistory").click();
    }
    });

    });


    $(document).on('change', '.abex', function(e) {
        var val = $(this).val();
        var id = $(this).attr('data-id');
        if(val == 'AB' || val == 'EX'){
            $(".abexClsDis"+id).prop('readonly', true);
            $(".abexClsDis"+id).addClass('disabled');
        } else {
            $(".abexClsDis"+id).prop('readonly', false);
            $(".abexClsDis"+id).removeClass('disabled');
        }
    }); 

    
    $(document).on('click', '#save_marks_by_subject', function(e) {
        $("#preloader").show();
        $.ajax({
            url: 'modules/Academics/manage_marks_entry_by_subject_addProcess.php',
            type: 'post',
            data: $('#marksbysubject').serialize(),
            async: true,
            success: function(response) {
                alert('Marks Saved!');
                $("#preloader").hide();
                $("#searchForm").submit();
                //$('#marksbysubject')[0].reset();
            }
        });
    });

    $(document).on('click', '#searchSubmit', function(e) {
        $("#preloader").show();
        var favorite = [];
        $(".chkData").each(function() {
            favorite.push($(this).val());
        });
        var getdata = favorite.join(",");
        var chkChng = $("#chkMarksSaveData").val();
        if(chkChng == 1){
            if(getdata != ''){
                if (confirm("Do you want to Save the Data")) {
                    $("#preloader").hide();
                    $("#save_marks_by_subject")[0].click();
                } else {
                    $("#searchForm").submit();
                }
            } else {
                $("#preloader").hide();
                $("#searchForm").submit();
            }
        } else {
            $("#preloader").hide();
            $("#searchForm").submit();
        }
        
    });

    $(document).on('change', '.chkData', function(e) {
        $("#chkMarksSaveData").val(1);
    }); 

    $(document).ready(function(){
        $('.double-scroll').doubleScroll();
    });

    
     
</script>
