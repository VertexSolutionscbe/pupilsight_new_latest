<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

// echo '<pre>';
// print_r($_POST);
// echo '</pre>';
// die();

$name = $_POST['name'];
$nameShort = $_POST['nameShort'];
$nameShortDisplay = $_POST['nameShortDisplay'];
$active = $_POST['active'];
$count = $_POST['count'];
// $pupilsightYearGroupIDList = (isset($_POST["pupilsightYearGroupID"]) ? implode(',', $_POST["pupilsightYearGroupID"]) : '');
$pupilsightYearGroupIDList = $_POST['pupilsightYearGroupID'];
$pupilsightRollGroupIDList = $_POST["pupilsightRollGroupID"];
$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
$pupilsightProgramID = $_POST['pupilsightProgramID'];
$pupilsightTTID = $_POST['pupilsightTTID'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/tt.php&pupilsightSchoolYearID=$pupilsightSchoolYearID";

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    if ($pupilsightSchoolYearID == '' or $name == '' or $nameShort == '' or $nameShortDisplay == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT * FROM pupilsightTT WHERE (name=:name AND pupilsightSchoolYearID=:pupilsightSchoolYearID)';
            $result = $connection2->prepare($sql);
            $result->execute($data);
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
                    //take pupilsightTTID value for insering
                $datac = array('pupilsightTTID' => $pupilsightTTID);
                $sqlc='SELECT * FROM  pupilsightTTDay WHERE pupilsightTTID =:pupilsightTTID'; 

                $resultc = $connection2->prepare($sqlc);
                $resultc->execute($datac);   
                $values = $resultc->fetchAll();
               
                        // print_r($values)  ;die();            
       
                $data = array('name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'nameShort' => $nameShort, 'nameShortDisplay' => $nameShortDisplay, 'active' => $active, 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList, 'pupilsightRollGroupIDList' => $pupilsightRollGroupIDList,'pupilsightProgramID'=>$pupilsightProgramID);
                $sql = 'INSERT INTO pupilsightTT SET pupilsightSchoolYearID=:pupilsightSchoolYearID, name=:name, nameShort=:nameShort, nameShortDisplay=:nameShortDisplay, active=:active, pupilsightProgramID=:pupilsightProgramID,pupilsightYearGroupIDList=:pupilsightYearGroupIDList, pupilsightRollGroupIDList=:pupilsightRollGroupIDList';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                //last ID
                $strId = $connection2->lastInsertID();
           
                foreach($values as $val){              
                $datar = array('pupilsightTTID'=>$strId,'pupilsightTTColumnID' =>  $val['pupilsightTTColumnID'],'name' => $val['name'], 'nameShort'=>$val['nameShort'],'color' =>$val['color'],'fontColor' => $val['fontColor']);                 
                $sqlr = 'INSERT INTO pupilsightTTDay SET pupilsightTTID=:pupilsightTTID, name=:name, nameShort=:nameShort, color=:color, fontColor=:fontColor, pupilsightTTColumnID=:pupilsightTTColumnID';              
                $resultr = $connection2->prepare($sqlr);
                $resultr->execute($datar);

                $ttc = $connection2->lastInsertID();

                $datat = array('pupilsightTTDayID' => $val['pupilsightTTDayID']);
               
                $sqlrc='SELECT * FROM  pupilsightTTDayRowClass WHERE pupilsightTTDayID =:pupilsightTTDayID'; 
                $resultrow = $connection2->prepare($sqlrc);
                $resultrow->execute($datat);   
                 $valueRow = $resultrow->fetchAll();
                 foreach ($valueRow as $value) {
                    $data = array('pupilsightTTColumnRowID' => $value['pupilsightTTColumnRowID'], 'pupilsightTTDayID' =>$ttc, 'pupilsightCourseClassID' => $value['pupilsightCourseClassID'], 'pupilsightSpaceID' => $value['pupilsightSpaceID']);
                    $sql = 'INSERT INTO pupilsightTTDayRowClass SET pupilsightTTColumnRowID=:pupilsightTTColumnRowID, pupilsightTTDayID=:pupilsightTTDayID, pupilsightCourseClassID=:pupilsightCourseClassID, pupilsightSpaceID=:pupilsightSpaceID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                   
                 }
              
            
            }

               
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 8, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
