<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/series_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/series_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    //Proceed!
    //Validate Inputs
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $pupilsightProgramID = $_POST['pupilsightProgramID'];
    if(!empty($_POST['pupilsightYearGroupID'])){
        $classIds = implode(',', $_POST['pupilsightYearGroupID']);
    } else {
        $classIds = '';
    }
    $type = $_POST['type'];
    $series_name = $_POST['series_name'];
    $description = $_POST['description'];
    $format = $_POST['format'];
    $formatval = $_POST['formatval'];
    $start_number = $_POST['st_number'];
    $no_of_digit = $_POST['no_ofdigit'];
    $start_char = $_POST['startchar'];
    
    if ($series_name == ''  or $format == '' or $formatval == '' ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('series_name' => $series_name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT * FROM fn_fee_series WHERE series_name=:series_name AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
            // echo $result->rowCount();
            // die();
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() > 0) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('type' => $type, 'series_name' => $series_name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'format' => $format, 'formatval' => $formatval, 'description' => $description, 'pupilsightProgramID' => $pupilsightProgramID, 'classIds' => $classIds);
                $sql = 'INSERT INTO fn_fee_series SET type=:type, series_name=:series_name, format=:format, formatval=:formatval, pupilsightSchoolYearID=:pupilsightSchoolYearID, description=:description, pupilsightProgramID=:pupilsightProgramID, classIds=:classIds';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                $seriesId = $connection2->lastInsertID();


                if(!empty($start_number)){
                    $order = 1;
                    foreach($start_number as $k=>$sn){
                        $snum = $sn;
                        $sdigit = $no_of_digit[$k];

                        $data1 = array('fn_fee_series_id' => $seriesId, 'order_wise' => $order, 'start_number' => $snum, 'no_of_digit' => $sdigit, 'last_no' => $snum, 'type' => 'numberwise');
                    
                        $sql1 = 'INSERT INTO fn_fee_series_number_format SET fn_fee_series_id=:fn_fee_series_id, order_wise=:order_wise, start_number=:start_number, no_of_digit=:no_of_digit, last_no=:last_no, type=:type';
                        $result1 = $connection2->prepare($sql1);
                        $result1->execute($data1);
                        $order++;
                    }
                }

                if(!empty($start_char)){
                    $order1 = 1;
                    foreach($start_char as $c=>$sc){
                        $char = $sc;
                        
                        $data2 = array('fn_fee_series_id' => $seriesId, 'order_wise' => $order1, 'start_char' => $char, 'type' => 'charwise');
                        
                        $sql2 = 'INSERT INTO fn_fee_series_number_format SET fn_fee_series_id=:fn_fee_series_id, order_wise=:order_wise, start_char=:start_char, type=:type';
                        $result2 = $connection2->prepare($sql2);
                        $result2->execute($data2);
                        $order1++;
                    }
                }
              
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
