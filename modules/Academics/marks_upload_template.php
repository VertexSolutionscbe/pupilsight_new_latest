<?php
/*
Pupilsight, Flexible & Open School System

*/

use Pupilsight\Data\ImportType;
use Pupilsight\Domain\System\CustomField;

// Increase max execution time, as this stuff gets big
ini_set('max_execution_time', 7200);
ini_set('memory_limit', '1024M');
set_time_limit(1200);

$_POST['address'] = '/modules/Academics/test_marks_upload.php';

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_POST['address'];

if (isActionAccessible($guid, $connection2, '/modules/Academics/test_marks_upload.php') == false) {
    // Access denied
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
   
    //$customField  = $container->get(CustomField::class);
    //$page->breadcrumbs->add(__('Export Student Import File'));
    if (!empty($_POST['subjectSkillId']) && !empty($_POST['testID'])) {
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';
        // die();

        foreach($_POST['testID'] as $test_id){
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="UploadMarks.csv"');

            $pupilsightRollGroupID = implode(',', $_POST['pupilsightRollGroupID']);
            $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $pupilsightProgramID = $_POST['pupilsightProgramID'];
            $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
            $sql = 'SELECT a.pupilsightPersonID, b.officialName FROM  pupilsightStudentEnrolment AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" AND a.pupilsightRollGroupID IN (' . $pupilsightRollGroupID . ') AND pupilsightRoleIDPrimary=003 GROUP BY b.pupilsightPersonID';
            $result = $connection2->query($sql);
            $students = $result->fetchAll();

            $header = array();
            if(!empty($students)){
                foreach($students as  $d){
                $stddata[] = $d['pupilsightPersonID'].','.$d['officialName'];
                }
            }
            
            $subjectSkillId = $_POST['subjectSkillId'];
            $subSklName = array();
            $maxMarks = array();
            foreach($subjectSkillId as $si){
                $sub = explode('-', $si);
                $testId = $sub[0];
                //$testId = $test_id;
                $subId = $sub[1];
                $skillId = $sub[2];
                $subName = '';
                $sklName = '';
                $ssname = '';
                $testName = '';

                $sqlsub = 'SELECT  a.pupilsightDepartmentID,a.subject_display_name, b.max_marks,b.test_id, t.name as test_name FROM subjectToClassCurriculum AS a LEFT JOIN examinationSubjectToTest AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN examinationTest AS t ON b.test_id = t.id WHERE a.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND a.pupilsightProgramID = '.$pupilsightProgramID.' AND a.pupilsightYearGroupID = '.$pupilsightYearGroupID.' AND a.pupilsightDepartmentID  = '.$subId.' AND b.test_id = '.$testId.' ';
                $resultsub = $connection2->query($sqlsub);
                $subData = $resultsub->fetch();
                $testName = $subData['test_name'];
                $subName = $subData['subject_display_name'];

                if($skillId != 0){
                    $sqlsub = 'SELECT skill_display_name FROM  subjectSkillMapping WHERE pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND pupilsightProgramID = '.$pupilsightProgramID.' AND pupilsightYearGroupID = '.$pupilsightYearGroupID.' AND pupilsightDepartmentID  = '.$subId.' AND skill_id  = '.$skillId.' ';
                    $resultsub = $connection2->query($sqlsub);
                    $skillData = $resultsub->fetch();
                    $sklName = $skillData['skill_display_name'];
                }

                if(!empty($sklName)){
                    $ssname = $testName.'/'.$subName.' # '.$sklName;
                } else {
                    $ssname = $testName.'/'.$subName;
                }

                $blank = ' ';
                $maxMarks[] = 'Marks '.$subData['max_marks'].',Remarks';
                $subSklName[] = $ssname.','.$blank;
            }
            
            // echo '<pre>';
            // print_r($subSklName);
            // echo '</pre>';
            // die();
            $fp = fopen('php://output', 'wb');
            $blank = ' ';
            $columndata1 = 'Student Details, '.$blank.'  ,'.implode(',',$subSklName).' ';
            $rowdata = array($columndata1);

            foreach ($rowdata as $linenew ) {
                $valnew = explode(",", $linenew);
                fputcsv($fp, $valnew);
            }

            $columndata = 'Student Id, Student Name ,'.implode(',',$maxMarks).' ';
            $data = array($columndata);

            foreach ($data as $line) {
                $val = explode(",", $line);
                fputcsv($fp, $val);
            }

            foreach ($stddata as $line) {
                $val = explode(",", $line);
                fputcsv($fp, $val);
            }

            fclose($fp);
            die();
        }
    }

    

}

?>