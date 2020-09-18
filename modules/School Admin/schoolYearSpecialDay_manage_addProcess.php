<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$fdate = $_POST['f_date'];
if($fdate == 'single'){
    $date = $_POST['date'];
    $type = $_POST['type'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $dateStamp = $_POST['dateStamp'];
    $pupilsightSchoolYearTermID = $_POST['pupilsightSchoolYearTermID'];
    $firstDay = $_POST['firstDay'];
    $lastDay = $_POST['lastDay'];
    $schoolOpen = null;
    if (!empty($_POST['schoolOpenH']) && is_numeric($_POST['schoolOpenH']) && is_numeric($_POST['schoolOpenM'])) {
        $schoolOpen = $_POST['schoolOpenH'].':'.$_POST['schoolOpenM'].':00';
    }
    $schoolStart = null;
    if (!empty($_POST['schoolStartH']) && is_numeric($_POST['schoolStartH']) && is_numeric($_POST['schoolStartM'])) {
        $schoolStart = $_POST['schoolStartH'].':'.$_POST['schoolStartM'].':00';
    }
    $schoolEnd = null;
    if (!empty($_POST['schoolEndH']) && is_numeric($_POST['schoolEndH']) && is_numeric($_POST['schoolEndM'])) {
        $schoolEnd = $_POST['schoolEndH'].':'.$_POST['schoolEndM'].':00';
    }
    $schoolClose = null;
    if (!empty($_POST['schoolCloseH']) && is_numeric($_POST['schoolCloseH']) && is_numeric($_POST['schoolCloseM'])) {
        $schoolClose = $_POST['schoolCloseH'].':'.$_POST['schoolCloseM'].':00';
    }

    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/schoolYearSpecialDay_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearID";

    if (isActionAccessible($guid, $connection2, '/modules/School Admin/schoolYearSpecialDay_manage_add.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Validate Inputs
        if ($date == '' or $type == '' or $name == '' or $pupilsightSchoolYearID == '' or $dateStamp == '' or $pupilsightSchoolYearTermID == '' or $firstDay == '' or $lastDay == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //Lock table
            try {
                $sql = 'LOCK TABLE pupilsightSchoolYearSpecialDay WRITE';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Check unique inputs for uniquness
            try {
                $data = array('date' => dateConvert($guid, $date));
                $sql = 'SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date=:date';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            if ($dateStamp < $firstDay or $dateStamp > $lastDay) {
                $URL .= '&return=warning1';
                header("Location: {$URL}");
            } else {
                if ($result->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightSchoolYearTermID' => $pupilsightSchoolYearTermID, 'date' => dateConvert($guid, $date), 'type' => $type, 'name' => $name, 'description' => $description, 'schoolOpen' => $schoolOpen, 'schoolStart' => $schoolStart, 'schoolEnd' => $schoolEnd, 'schoolClose' => $schoolClose);
                        $sql = 'INSERT INTO pupilsightSchoolYearSpecialDay SET pupilsightSchoolYearTermID=:pupilsightSchoolYearTermID, date=:date, type=:type, name=:name, description=:description,schoolOpen=:schoolOpen, schoolStart=:schoolStart, schoolEnd=:schoolEnd, schoolClose=:schoolClose';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Unlock locked database tables
                    try {
                        $sql = 'UNLOCK TABLES';
                        $result = $connection2->query($sql);
                    } catch (PDOException $e) {
                    }

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }

} else {
    
    function getDatesFromRange($start, $end, $format = 'Y-m-d') { 
        $array = array(); 
        $interval = new DateInterval('P1D'); 
    
        $realEnd = new DateTime($end); 
        $realEnd->add($interval); 
    
        $period = new DatePeriod(new DateTime($start), $interval, $realEnd); 
    
        foreach($period as $date) {                  
            $array[] = $date->format($format);  
        } 
    
        return $array; 
    } 


   // $date = $_POST['date'];
    $type = $_POST['type'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $dateStamp = $_POST['dateStamp'];
    $pupilsightSchoolYearTermID = $_POST['pupilsightSchoolYearTermID'];
    $firstDay = $_POST['firstDay'];
    $lastDay = $_POST['lastDay'];
    $fdate = str_replace('/', '-', $_POST['f_date']);
    $ldate = str_replace('/', '-', $_POST['l_date']);
    $sdate = date('Y-m-d', strtotime($fdate));
    $edate = date('Y-m-d', strtotime($ldate));
    $alldates = getDatesFromRange($sdate, $edate); 
    
    $schoolOpen = null;
    if (!empty($_POST['schoolOpenH']) && is_numeric($_POST['schoolOpenH']) && is_numeric($_POST['schoolOpenM'])) {
        $schoolOpen = $_POST['schoolOpenH'].':'.$_POST['schoolOpenM'].':00';
    }
    $schoolStart = null;
    if (!empty($_POST['schoolStartH']) && is_numeric($_POST['schoolStartH']) && is_numeric($_POST['schoolStartM'])) {
        $schoolStart = $_POST['schoolStartH'].':'.$_POST['schoolStartM'].':00';
    }
    $schoolEnd = null;
    if (!empty($_POST['schoolEndH']) && is_numeric($_POST['schoolEndH']) && is_numeric($_POST['schoolEndM'])) {
        $schoolEnd = $_POST['schoolEndH'].':'.$_POST['schoolEndM'].':00';
    }
    $schoolClose = null;
    if (!empty($_POST['schoolCloseH']) && is_numeric($_POST['schoolCloseH']) && is_numeric($_POST['schoolCloseM'])) {
        $schoolClose = $_POST['schoolCloseH'].':'.$_POST['schoolCloseM'].':00';
    }

    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/schoolYearSpecialDay_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearID";

    if (isActionAccessible($guid, $connection2, '/modules/School Admin/schoolYearSpecialDay_manage_add.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Validate Inputs
        if ($sdate == '' or $edate == '' or $type == '' or $name == '' or $pupilsightSchoolYearID == ''  or $pupilsightSchoolYearTermID == '' ) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //Lock table
            try {
                $sql = 'LOCK TABLE pupilsightSchoolYearSpecialDay WRITE';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }
            foreach($alldates as $date){
                //Check unique inputs for uniquness
                $data = array('date' => $date);
                $sql = 'SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date=:date';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            
                if ($result->rowCount() > 0) {
                    // $URL .= '&return=error3';
                    // header("Location: {$URL}");
                } else {
                    //Write to database
                    
                    $data = array('pupilsightSchoolYearTermID' => $pupilsightSchoolYearTermID, 'date' => $date, 'type' => $type, 'name' => $name, 'description' => $description, 'schoolOpen' => $schoolOpen, 'schoolStart' => $schoolStart, 'schoolEnd' => $schoolEnd, 'schoolClose' => $schoolClose);
                    $sql = 'INSERT INTO pupilsightSchoolYearSpecialDay SET pupilsightSchoolYearTermID=:pupilsightSchoolYearTermID, date=:date, type=:type, name=:name, description=:description,schoolOpen=:schoolOpen, schoolStart=:schoolStart, schoolEnd=:schoolEnd, schoolClose=:schoolClose';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                }
            }
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }

}

