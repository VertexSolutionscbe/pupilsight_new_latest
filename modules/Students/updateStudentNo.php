<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/updateStudent.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/updateStudent.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    //Proceed!
    //Validate Inputs
    $series_id = $_POST['series_id'];
    $student_id = $_POST['student_id'];
    
    
    if ($series_id == ''  or $student_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        foreach($student_id as $pupilsightPersonID){
            $sql = "SELECT id, formatval FROM fn_fee_series WHERE id = ".$series_id." ";
            $resultrec = $connection2->query($sql);
            $recptser = $resultrec->fetch();

            $seriesId = $recptser['id'];

            if (!empty($seriesId)) {
                $invformat = explode('$', $recptser['formatval']);
                $iformat = '';
                $orderwise = 0;
                foreach ($invformat as $inv) {
                    if ($inv == '{AB}') {
                        $datafort = array('fn_fee_series_id' => $seriesId, 'type' => 'numberwise');
                        $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type';
                        $resultfort = $connection2->prepare($sqlfort);
                        $resultfort->execute($datafort);
                        $formatvalues = $resultfort->fetch();

                        $str_length = $formatvalues['no_of_digit'];

                        $iformat .= str_pad($formatvalues['last_no'], $str_length, '0', STR_PAD_LEFT);

                        $lastnoadd = $formatvalues['last_no'] + 1;

                        $lastno = str_pad($lastnoadd, $str_length, '0', STR_PAD_LEFT);

                        $datafort1 = array('fn_fee_series_id' => $seriesId, 'type' => 'numberwise', 'last_no' => $lastno);
                        $sqlfort1 = 'UPDATE fn_fee_series_number_format SET last_no=:last_no WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type ';
                        $resultfort1 = $connection2->prepare($sqlfort1);
                        $resultfort1->execute($datafort1);
                    } else {
                        $iformat .= $inv;
                    }
                    $orderwise++;
                }
                $application_id = $iformat;
            } else {
                $application_id = '';
            }

            $datafort12 = array('admission_no' => $application_id, 'pupilsightPersonID' => $pupilsightPersonID);
            $sqlfort12 = 'UPDATE pupilsightPerson SET admission_no=:admission_no WHERE pupilsightPersonID=:pupilsightPersonID';
            $resultfort12 = $connection2->prepare($sqlfort12);
            $resultfort12->execute($datafort12);


        }
            
        $URL .= "&return=success0";
        header("Location: {$URL}");
       
    }
}
