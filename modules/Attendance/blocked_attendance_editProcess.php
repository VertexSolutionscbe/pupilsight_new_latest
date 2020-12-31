<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

//Search & Filters


$pupilsightAttendanceBlockID = $_POST['pupilsightAttendanceBlockID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/blocked_attendance.php";

if (isActionAccessible($guid, $connection2, '/modules/Attendance/add_blocked_attendance.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    
            //Proceed!
          
            if ($pupilsightAttendanceBlockID == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                try {
                  
                        $data = array('pupilsightAttendanceBlockID' => $pupilsightAttendanceBlockID);
                        $sql = 'SELECT * FROM pupilsightAttendanceBlocked WHERE pupilsightAttendanceBlockID=:pupilsightAttendanceBlockID';
                   
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() != 1) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                } else {
                    //Proceed!
                    $name=$_POST['name'] ;
                    $pupilsightYearGroupID=$_POST['pupilsightYearGroupID_check'];
                    if(isset($_POST['pupilsightRollGroupID'] ))
                    {
                        $pupilsightRollGroupIDs=$_POST['pupilsightRollGroupID'] ;
                    }
                    else
                    {
                        $pupilsightRollGroupIDs=array('0'=>"");
                    }
                   // $pupilsightYearGroupID=$_POST['pupilsightYearGroupID'];
                  //  $pupilsightRollGroupID=$_POST['pupilsightRollGroupID'] ;
                    $type=$_POST['type'] ;
                    $start_date=$_POST['start_date'];
                    $end_date=$_POST['end_date'] ;
                    $remark=$_POST['remark'] ;

                    if ( $name == ''  or $type == '' or $start_date == '' or $end_date=='') {
                        $URL .= '&return=error1';
                        header("Location: {$URL}");
                    } else {
                        //Write to database

                        $dateStart = '';
                        if ($_POST['start_date'] != '') {
                             $dateStart = dateConvert($guid, $_POST['start_date']);
                        }
                        $dateEnd = $dateStart;
                        if ($_POST['end_date'] != '') {
                             $dateEnd = dateConvert($guid, $_POST['end_date']);
                        }
                        $today = date('Y-m-d');
            
                        //Check to see if date is in the future and is a school day.
                        if ($dateStart == '' or ($dateEnd != '' and $dateEnd < $dateStart)  ) {
                            $URL .= '&return=error8';
                            header("Location: {$URL}");
                        } else {        //this was previously commented and delete query try block was enabled. if this creates any problem comment this and enable below commented code.

                                try {
                                    $dataUpdate = array('pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'],'pupilsightRollGroupID' => $pupilsightRollGroupIDs, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'name' => $name, 'type' => $type, 'start_date' => $dateStart, 'end_date' => $dateEnd, 'remark'=> $remark,'pupilsightAttendanceBlockID'=> $pupilsightAttendanceBlockID ,'timestampTaken' => date('Y-m-d H:i:s'));
                                
                                    $sql = 'UPDATE pupilsightAttendanceBlocked SET pupilsightPersonIDTaker=:pupilsightPersonIDTaker, pupilsightRollGroupID=:pupilsightRollGroupID, pupilsightYearGroupID=:pupilsightYearGroupID, name=:name, type=:type, start_date=:start_date, end_date=:end_date, remark=:remark ,timestampTaken=:timestampTaken WHERE pupilsightAttendanceBlockID=:pupilsightAttendanceBlockID';
                                    //print_r($dataUpdate);print_r($sql);die();
                                    $result = $connection2->prepare($sql);
                                    $result->execute($dataUpdate);
                                } catch (PDOException $e) {
                                    $URL .= '&return=error2';
                                    header("Location: {$URL}");
                                    exit();
                                }

                                $URL .= '&return=success0';
                                header("Location: {$URL}");
                        
                            }


                      /*else {
                        $data1 = array('pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'],'type'=>$type,'start_date' => $dateStart, 'end_date' => $dateEnd);
                        $sql1 = 'DELETE FROM pupilsightAttendanceBlocked WHERE pupilsightPersonIDTaker=:pupilsightPersonIDTaker  AND type=:type AND  start_date=:start_date AND end_date=:end_date ';
                        print_r($data1);print_r($sql1);die();
                        $result1 = $connection2->prepare($sql1);
                        $result1->execute($data1);
               
                                foreach($pupilsightRollGroupIDs as $pupilsightRollGroupID)
                                { 


                                    try {
                                        $dataUpdate = array('pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'],'pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'name' => $name, 'type' => $type, 'start_date' => $dateStart, 'end_date' => $dateEnd, 'remark'=> $remark, 'timestampTaken' => date('Y-m-d H:i:s'));
                                        $sqlUpdate = 'INSERT INTO pupilsightAttendanceBlocked SET  pupilsightPersonIDTaker=:pupilsightPersonIDTaker, pupilsightRollGroupID=:pupilsightRollGroupID, pupilsightYearGroupID=:pupilsightYearGroupID, name=:name, type=:type, start_date=:start_date, end_date=:end_date, remark=:remark, timestampTaken=:timestampTaken';
                                        $resultUpdate = $connection2->prepare($sqlUpdate);
                                        $resultUpdate->execute($dataUpdate);
                                    } catch (PDOException $e) {
                                        $URL .= '&return=error2';
                                        header("Location: {$URL}");
                                        exit();
                                    }
                                
                                //  $URL .= '&return=error7';            
                                // header("Location: {$URL}");                             
                
                                }*/
                        //$URL .= '&return=success0';
                        //header("Location: {$URL}");
                    //}



                    }
                }
            }
        
    
}
