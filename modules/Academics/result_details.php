<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Curriculum\CurriculamGateway;

if (isActionAccessible($guid, $connection2, '/modules/Academics/result.php') == false) {
    //Access denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Academic Result'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $callbacklink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
        "https" : "http") . "://" . $_SERVER['HTTP_HOST'] .
        $_SERVER['REQUEST_URI'];

    //$baseurl = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];     

    $baseurl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
    $baseurl .= "://" . $_SERVER['HTTP_HOST'];
    $baseurl .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);

    $CurriculamGateway = $container->get(CurriculamGateway::class);

    $stuId = $_GET['cid'];
    $test_id = $_GET['tid'];
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    

    if(!empty($stuId) && !empty($test_id)){
        $chkchilds = 'SELECT a.*, b.officialName FROM pupilsightStudentEnrolment AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightPersonID = ' . $stuId . ' AND a.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' ';
        $resultachk = $connection2->query($chkchilds);
        $stuData = $resultachk->fetch();


        $sq = 'SELECT a.pupilsightDepartmentID, b.name as test_name, c.subject_display_name FROM examinationSubjectToTest AS a 
        LEFT JOIN examinationTest AS b ON a.test_id = b.id
        LEFT JOIN subjectToClassCurriculum AS c ON a.pupilsightDepartmentID = c.pupilsightDepartmentID
        WHERE a.test_id = '.$test_id.' AND a.skill_id =  "0" AND c.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND c.pupilsightProgramID = '.$stuData['pupilsightProgramID'].' AND c.pupilsightYearGroupID = '.$stuData['pupilsightYearGroupID'].' GROUP BY a.pupilsightDepartmentID ORDER BY c.pos ASC ';
        $resultsub = $connection2->query($sq);
        $subData = $resultsub->fetchAll();

        if(!empty($subData)){
            foreach($subData as $k => $sb){
                $ksql = 'SELECT count(a.id) as kount  
                        FROM examinationSubjectToTest AS a 
                        LEFT JOIN examinationTest AS b ON a.test_id = b.id
                        LEFT JOIN subjectToClassCurriculum AS c ON a.pupilsightDepartmentID = c.pupilsightDepartmentID
                        LEFT JOIN ac_manage_skill AS d ON a.skill_id = d.id
                        WHERE a.test_id = '.$test_id.' AND a.is_tested =  "1" AND a.skill_id != "0" AND a.pupilsightDepartmentID = '.$sb['pupilsightDepartmentID'].' AND c.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND c.pupilsightProgramID = '.$stuData['pupilsightProgramID'].' AND c.pupilsightYearGroupID = '.$stuData['pupilsightYearGroupID'].'  ';
                $kresult = $connection2->query($ksql);
                $kountData = $kresult->fetch();

                $sql = 'SELECT a.*, c.subject_display_name, d.name as skill_name 
                        FROM examinationSubjectToTest AS a 
                        
                        LEFT JOIN subjectToClassCurriculum AS c ON a.pupilsightDepartmentID = c.pupilsightDepartmentID
                        LEFT JOIN ac_manage_skill AS d ON a.skill_id = d.id
                        WHERE a.test_id = '.$test_id.' AND a.is_tested =  "1" AND a.pupilsightDepartmentID = '.$sb['pupilsightDepartmentID'].' AND c.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND c.pupilsightProgramID = '.$stuData['pupilsightProgramID'].' AND c.pupilsightYearGroupID = '.$stuData['pupilsightYearGroupID'].'  ';
                $result = $connection2->query($sql);
                $subjectData = $result->fetchAll();
                $subData[$k]['kountData'] = $kountData['kount'];
                $subData[$k]['subjectData'] = $subjectData;
                
            }
        }

    }

    // echo '<pre>';
    // print_r($subData);
    // echo '</pre>';
    // echo $stuId;
    // die();
    if(!empty($subData) && !empty($stuData)){
    ?>
    <div class="mt-5">
        <h2>Test Name - <?php echo $subData[0]['test_name'];?></h2>
        <h2>Student Name - <?php echo $stuData['officialName'];?></h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Subject Name</th>
                    <th>Marks Obtained</th>
                    <th>Grade Obtained</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
    <?php       
                foreach($subData as $sbd){
                    if($sbd['kountData'] >= 1){
    ?>
                    <tr>
                        <td colspan="4"><h3><?php echo $sbd['subject_display_name'];?></h3></td>
                    </tr>
    <?php       }
                foreach($sbd['subjectData'] as $subdata){
                    $pupilsightDepartmentID = $subdata['pupilsightDepartmentID'];
                    $skill_id = $subdata['skill_id'];
                    $msql = 'SELECT * FROM examinationMarksEntrybySubject WHERE pupilsightPersonID = ' . $stuId . ' AND pupilsightYearGroupID = '.$stuData['pupilsightYearGroupID'].' AND pupilsightRollGroupID = '.$stuData['pupilsightRollGroupID'].' AND test_id = '.$test_id.' AND pupilsightDepartmentID = '.$pupilsightDepartmentID.' ';
                    if(!empty($skill_id)){
                        $msql .= ' AND skill_id = '.$skill_id.' ';
                    }
                    $resultm = $connection2->query($msql);
                    $mksData = $resultm->fetch();

                    $marks_enter = $mksData['marks_obtained'];
                    $gradeSystemId = $subdata['gradeSystemId'];
                    $gradeId = $mksData['gradeId'];
                    if(!empty($marks_enter)){
                        $sql = 'SELECT grade_name FROM examinationGradeSystemConfiguration  WHERE id= '.$gradeId.' AND gradeSystemId="' . $gradeSystemId . '"';
                        $result = $connection2->query($sql);
                        $grade = $result->fetch();
                        $grade_name = $grade['grade_name'];
                    } else {
                        $grade_name = '';
                    }
                    // echo '<pre>';
                    // print_r($mksData);
                    // echo '</pre>';
                    if(!empty($subdata['skill_name'])){
                        $subName = $subdata['skill_name'];
                        $style = 'style="padding-left: 30px;"';
                    } else {
                        $subName = $subdata['subject_display_name'];
                        $style = '';
                    }

                    if(!empty($mksData['marks_obtained']) && $mksData['marks_obtained'] != '0.00'){
                        $marksObtained = floatval($mksData['marks_obtained']);
                    } else {
                        $marksObtained = '';
                    }
    ?>
                    <tr>
                    <?php if($sbd['kountData'] >= 1){?>
                        <td <?php echo $style;?>><?php echo $subName;?></td>
                    <?php } else { ?>
                        <td <?php echo $style;?>><h3><?php echo $subName;?></h3></td>
                    <?php } ?>
                        <td><?php echo $marksObtained;?></td>
                        <td><?php echo $grade_name;?></td>
                        <td><?php echo $mksData['remarks'];?></td>
                    </tr>
    <?php } } ?>
            </tbody>
        </table>
    </div>
    <?php
    } else {
        echo '<h1>No Test</h1>';
    }
}

?>

<script>

    $(document).on('change', '#childSel', function() {
        var id = $(this).val();
        var hrf = 'index.php?q=/modules/Academics/result.php&cid=' + id;
        window.location.href = hrf;
    });

</script>