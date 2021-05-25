<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
include $_SERVER["DOCUMENT_ROOT"] . '/db.php';

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=%2Fmodules%2FCampaign%2FtransitionsList.php';

if (isActionAccessible($guid, $connection2, '/modules/Campaign/transitionImportProcess.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $subid = explode(',', $_POST['subid']);
    $campaign_id = $_POST["campaign_id"];
    if (empty($campaign_id)) {
        echo "fail";
        die();
    }
    
    $stateid = $_POST['sid'];
    $statename = $_POST['sname'];
    $formId = $_POST['fid'];
    $crtd =  date('Y-m-d H:i:s');
    $cdt = date('Y-m-d H:i:s');
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];

    

    try {
        $sql = "SELECT a.*, b.form_id, b.academic_id, b.admission_series_id FROM campaign_transitions_form_map AS a LEFT JOIN campaign AS b ON a.campaign_id = b.id where b.id= " . $campaign_id . " GROUP BY table_name";
        $result = $connection2->query($sql);
        $tablelist = $result->fetchAll();
    } catch (Exception $ex) {
        print_r($ex);
    }

    // echo '<pre>';
    // print_r($tablelist);
    // echo '</pre>';
    // die();
    if (empty($tablelist)) {
        header("Location: {$URL}");
    }
    $tabledata = array();
    try {
        foreach ($tablelist as $k => $tbl) {

            $sqlc = 'SELECT column_name, fluent_form_column_name FROM campaign_transitions_form_map WHERE table_name = "' . $tbl['table_name'] . '" AND campaign_id = ' . $campaign_id . ' ';
            $resultc = $connection2->query($sqlc);
            $tablecol = $resultc->fetchAll();


            // $sqls = "SELECT submission_id FROM wp_fluentform_entry_details WHERE form_id = '".$tbl['form_id']."' GROUP BY submission_id ";
            // $results = $connection2->query($sqls);
            // $tablesubdata = $results->fetchAll();

            $tabledata[$k]['tablename'] = $tbl['table_name'];
            $tabledata[$k]['campaign_id'] = $tbl['campaign_id'];
            $tabledata[$k]['pupilsightSchoolYearID'] = $tbl['academic_id'];
            $tabledata[$k]['form_id'] = $tbl['form_id'];
            $tabledata[$k]['tablecol'] = $tablecol;
            $tabledata[$k]['tablesubdata'] = $subid;
            $tabledata[$k]['admission_series_id'] = $tbl['admission_series_id'];
        }
    } catch (Exception $ex) {
        print_r($ex);
    }

    // echo '<pre>';
    // print_r($tabledata);
    // echo '</pre>';


    $tdata = array();
    $tdataft = array();
    $tdatamt = array();
    $cname = array();
    if (!empty($tabledata)) {
        foreach ($tabledata as $k => $td) {
            $fid = $td['form_id'];
            $pupilsightSchoolYearID = $td['pupilsightSchoolYearID'];
            $cid = $td['campaign_id'];

            try {
                foreach ($td['tablesubdata'] as $ts => $tsub) {
                    foreach ($td['tablecol'] as $t => $tcol) {

                        $sqlf = "SELECT field_value FROM wp_fluentform_entry_details WHERE submission_id = " . $tsub . " AND field_name = '" . $tcol['fluent_form_column_name'] . "' AND status = '0' ";
                        $resultf = $connection2->query($sqlf);
                        $coldata = $resultf->fetch();

                        $sqll = 'SELECT id FROM workflow_transition WHERE campaign_id =' . $cid . ' ORDER BY id DESC LIMIT 0,1 ';
                        $resultl = $connection2->query($sqll);
                        $lastData = $resultl->fetch();
                        $lastId = $lastData['id'];

                        $sql1 = 'SELECT state_id FROM campaign_form_status WHERE submission_id =' . $tsub . ' AND state_id = ' . $lastId . ' ';
                        $result1 = $connection2->query($sql1);
                        $chkSts = $result1->fetch();

                        $sqlcp = "SELECT pupilsightProgramID, pupilsightYearGroupID FROM wp_fluentform_submissions WHERE id = " . $tsub . "  ";
                        $resultcp = $connection2->query($sqlcp);
                        $clspro = $resultcp->fetch();
                        if (!empty($clspro)) {
                            $prog = $clspro['pupilsightProgramID'];
                            $cls = $clspro['pupilsightYearGroupID'];
                        } else {
                            $prog = '';
                            $cls = '';
                        }

                        $sqlchks = 'SELECT seats, used_seats FROM seatmatrix WHERE campaignid = ' . $cid . ' AND pupilsightProgramID = ' . $prog . ' AND pupilsightYearGroupID = ' . $cls . ' ';
                        $resultchks = $connection2->query($sqlchks);
                        $seatdata = $resultchks->fetch();
                        if (!empty($seatdata)) {
                            if ($seatdata['seats'] == $seatdata['used_seats']) {
                                $chkseats = 1;
                            } else {
                                $chkseats = 2;
                            }
                        } else {
                            $chkseats = 2;
                        }
                        print_r($coldata);

                        //echo $coldata->rowCount();
                        if ($chkseats == 2) {
                            // if (!empty($chkSts) && !empty($coldata)) {
                            if (!empty($coldata)) {
                                if (strpos($tcol['fluent_form_column_name'], 'father_') !== false) {
                                    $colname = 'ft_' . $tcol['column_name'];
                                    $val = $coldata['field_value'];
                                    $tdata[$ts][$colname] = $val;
                                    $tdata[$ts]['ft_pupilsightRoleIDPrimary'] = '004';
                                    $tdata[$ts]['ft_pupilsightRoleIDAll'] = '004';
                                    $cname[$t] = $colname . '=:' . $colname;
                                } else if (strpos($tcol['fluent_form_column_name'], 'mother_') !== false) {
                                    $colname = 'mt_' . $tcol['column_name'];
                                    $val = $coldata['field_value'];
                                    $tdata[$ts][$colname] = $val;
                                    $tdata[$ts]['mt_pupilsightRoleIDPrimary'] = '004';
                                    $tdata[$ts]['mt_pupilsightRoleIDAll'] = '004';
                                    $cname[$t] = $colname . '=:' . $colname;
                                } else {
                                    $colname = 'st_' . $tcol['column_name'];
                                    $val = $coldata['field_value'];
                                    $fid = $td['form_id'];
                                    $tablename = $td['tablename'];
                                    $tdata[$ts][$colname] = $val;
                                    $tdata[$ts]['st_pupilsightRoleIDPrimary'] = '003';
                                    $tdata[$ts]['st_pupilsightRoleIDAll'] = '003';
                                    $tdata[$ts]['admission_series_id'] = $td['admission_series_id'];
                                    $tdata[$ts]['sid'] = $tsub;
                                    $cname[$t] = $colname . '=:' . $colname;
                                }

                                $chk = '1';
                            } else {
                                $chk = '2';
                            }
                        } else {
                            echo 'fail';
                            die();
                        }
                    }
                }
            } catch (Exception $ex) {
                print_r($ex);
            }

            // echo $chk;
            // echo '<pre> tdata';
            // print_r($tdata);
            // echo '</pre>';
            // die();


            if (!empty($tdata)) {
                try {
                    //print_r($tdata);
                    foreach ($tdata as $td) {
                        //print_r($td);
                        if (!empty($td['admission_series_id'])) {
                            $seriesId = $td['admission_series_id'];
                            $sqlrec = 'SELECT id, formatval FROM fn_fee_series WHERE id = "' . $seriesId . '" ';
                            $resultrec = $connection2->query($sqlrec);
                            $recptser = $resultrec->fetch();

                            $invformat = explode('$', $recptser['formatval']);
                            $iformat = '';
                            $orderwise = 0;
                            foreach ($invformat as $inv) {
                                if ($inv == '{AB}') {
                                    $datafort = array('fn_fee_series_id' => $seriesId, 'order_wise' => $orderwise, 'type' => 'numberwise');
                                    $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=:fn_fee_series_id AND order_wise=:order_wise AND type=:type';
                                    $resultfort = $connection2->prepare($sqlfort);
                                    $resultfort->execute($datafort);
                                    $formatvalues = $resultfort->fetch();
                                    $str_length = $formatvalues['no_of_digit'];

                                    $iformat .= str_pad($formatvalues['last_no'], $str_length, '0', STR_PAD_LEFT);

                                    $lastnoadd = $formatvalues['last_no'] + 1;

                                    //$lastno = substr("0000000{$lastnoadd}", -$str_length);
                                    $lastno = str_pad($lastnoadd, $str_length, '0', STR_PAD_LEFT);

                                    $datafort1 = array('fn_fee_series_id' => $seriesId, 'order_wise' => $orderwise, 'type' => 'numberwise', 'last_no' => $lastno);
                                    $sqlfort1 = 'UPDATE fn_fee_series_number_format SET last_no=:last_no WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type AND order_wise=:order_wise';
                                    $resultfort1 = $connection2->prepare($sqlfort1);
                                    $resultfort1->execute($datafort1);
                                } else {
                                    //$iformat .= $inv.'/';
                                    $iformat .= $inv;
                                }
                                $orderwise++;
                            }
                            $admission_no = $iformat;
                        } else {
                            $admission_no = '';
                        }
                        $sql = "INSERT INTO pupilsightPerson (";
                        foreach ($td as $key => $ar) {
                            if (strpos($key, 'st_') !== false && !empty($ar)) {
                                //$clname = ltrim($key, 'st_'); 
                                $clname = substr($key, 3, strlen($key));
                                $sql .= $clname . ',';
                            }
                        }
                        $sql .= 'admission_no';
                        //$sql = rtrim($sql, ", ");
                        $sql .= ") VALUES (";
                        foreach ($td as $k => $value) {
                            if ($k == "st_dob" && !empty($value)) {
                                $sdate = str_replace('/', '-', $value);
                                $value =  date('Y-m-d', strtotime($sdate));
                            }
                            if (strpos($k, 'st_') !== false && !empty($value)) {
                                $val1 = str_replace("'", "", $value);
                                $val = str_replace('"', "", $val1);
                                $sql .= '"' . $val . '",';

                                if (!empty($value['st_officialName'])) {
                                    $fmname = $value['st_officialName'] . ' Family';
                                }
                            }
                        }
                        $sql .= '"' . $admission_no . '"';
                        //$sql = rtrim($sql, ", ");
                        $sql .= ")";
                        $sql = rtrim($sql, ", ");

                        //  echo $sql;
                        //  die();
                        $conn->query($sql);
                        $stu_id = $conn->insert_id;

                        // Father Entry

                        $sqlf = "INSERT INTO pupilsightPerson (";
                        foreach ($td as $key => $ar) {
                            if (strpos($key, 'ft_') !== false  && !empty($ar)) {
                                //$clname = ltrim($key, 'ft_'); 
                                $clname = substr($key, 3, strlen($key));
                                $sqlf .= $clname . ',';
                            }
                        }
                        $sqlf = rtrim($sqlf, ", ");
                        $sqlf .= ") VALUES (";
                        foreach ($td as $k => $value) {
                            if ($k == "ft_dob" && !empty($value)) {
                                $sdate = str_replace('/', '-', $value);
                                $value =  date('Y-m-d', strtotime($sdate));
                            }
                            if (strpos($k, 'ft_') !== false  && !empty($value)) {
                                $val1 = str_replace("'", "", $value);
                                $val = str_replace('"', "", $val1);
                                $sqlf .= '"' . $val . '",';
                            }
                        }
                        $sqlf = rtrim($sqlf, ", ");
                        $sqlf .= ")";
                        $sqlf = rtrim($sqlf, ", ");
                        $conn->query($sqlf);
                        $fat_id = $conn->insert_id;

                        // Mother Entry

                        $sqlm = "INSERT INTO pupilsightPerson (";
                        foreach ($td as $key => $ar) {
                            if (strpos($key, 'mt_') !== false  && !empty($ar)) {
                                //$clname = ltrim($key, 'mt_'); 
                                $clname = substr($key, 3, strlen($key));
                                $sqlm .= $clname . ',';
                            }
                        }
                        $sqlm = rtrim($sqlm, ", ");
                        $sqlm .= ") VALUES (";
                        foreach ($td as $k => $value) {
                            if ($k == "mt_dob" && !empty($value)) {
                                $sdate = str_replace('/', '-', $value);
                                $value =  date('Y-m-d', strtotime($sdate));
                            }
                            if (strpos($k, 'mt_') !== false  && !empty($value)) {
                                $val1 = str_replace("'", "", $value);
                                $val = str_replace('"', "", $val1);
                                $sqlm .= '"' . $val . '",';
                            }
                        }
                        $sqlm = rtrim($sqlm, ", ");
                        $sqlm .= ")";
                        $sqlm = rtrim($sqlm, ", ");

                        //echo "\n<br/>mother ".$sqlm;
                        $conn->query($sqlm);
                        $mot_id = $conn->insert_id;

                        if (!empty($stu_id) && !empty($pupilsightSchoolYearID)) {
                            $sqle = "INSERT INTO pupilsightStudentEnrolment (pupilsightPersonID,pupilsightSchoolYearID,pupilsightProgramID,pupilsightYearGroupID) VALUES (" . $stu_id . "," . $pupilsightSchoolYearID . "," . $prog . "," . $cls . ")";
                            $enrol = $conn->query($sqle);

                            //echo "\n<br/>pupilsightStudentEnrolment: ".$sqle;
                        }


                        if (!empty($fat_id) || !empty($mot_id)) {
                            $sqlfamily = 'INSERT INTO pupilsightFamily (name) VALUES ("' . $fmname . '")';
                            //echo "\n<br/>family: ".$sqlfamily;
                            $conn->query($sqlfamily);
                            $family_id = $conn->insert_id;
                            if (!empty($family_id)) {
                                if (!empty($fat_id)) {
                                    $sqlf1 = "INSERT INTO pupilsightFamilyAdult (pupilsightFamilyID,pupilsightPersonID,childDataAccess,contactPriority,contactCall,contactSMS,contactEmail,contactMail) VALUES (" . $family_id . "," . $fat_id . ",'Y','1','N','N','N','N')";
                                    $conn->query($sqlf1);
                                    //echo "\n<br/>pupilsightFamilyAdult: ".$sqlf1;

                                    $sqlf4 = "INSERT INTO pupilsightFamilyRelationship (pupilsightFamilyID,pupilsightPersonID1,pupilsightPersonID2,relationship) VALUES (" . $family_id . "," . $fat_id . "," . $stu_id . ",'Father')";
                                    $conn->query($sqlf4);

                                    //echo "\n<br/>pupilsightFamilyRelationshipFther: ".$sqlf4;
                                }

                                if (!empty($mot_id)) {
                                    $sqlf2 = "INSERT INTO pupilsightFamilyAdult (pupilsightFamilyID,pupilsightPersonID,childDataAccess,contactPriority,contactCall,contactSMS,contactEmail,contactMail) VALUES (" . $family_id . "," . $mot_id . ",'Y','2','N','N','N','N')";
                                    $conn->query($sqlf2);

                                    //echo "\n<br/>pupilsightFamilyAdult: ".$sqlf2;

                                    $sqlf5 = "INSERT INTO pupilsightFamilyRelationship (pupilsightFamilyID,pupilsightPersonID1,pupilsightPersonID2,relationship) VALUES (" . $family_id . "," . $mot_id . "," . $stu_id . ",'Mother')";

                                    //echo "\n<br/>pupilsightFamilyRelationshipMother: ".$sqlf5;
                                    $conn->query($sqlf5);
                                }

                                $sqlf3 = "INSERT INTO pupilsightFamilyChild (pupilsightFamilyID,pupilsightPersonID) VALUES (" . $family_id . "," . $stu_id . ")";
                                //echo "\n<br/>familyChild: ".$sqlf3;
                                $conn->query($sqlf3);
                            }
                        }
                        $wdata = array('status' => '1', 'submission_id' => $td['sid']);
                        $sqlupd = "UPDATE wp_fluentform_entry_details SET status=:status WHERE submission_id=:submission_id";
                        $resultupd = $connection2->prepare($sqlupd);
                        $resultupd->execute($wdata);

                        $data = array('campaign_id' => $campaign_id,'form_id' => $formId, 'submission_id' => $td['sid'], 'state' => $statename,  'state_id' => $stateid, 'status' => '1', 'pupilsightPersonID' => $cuid, 'cdt' => $cdt);
                
                        $sql = "INSERT INTO campaign_form_status SET campaign_id=:campaign_id,form_id=:form_id, submission_id=:submission_id,state=:state,state_id=:state_id, status=:status, pupilsightPersonID=:pupilsightPersonID, cdt=:cdt";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);

                        $sqlchks = 'SELECT seats, used_seats FROM seatmatrix WHERE campaignid = ' . $cid . ' AND pupilsightProgramID = ' . $prog . ' AND pupilsightYearGroupID = ' . $cls . ' ';
                        $resultchks = $connection2->query($sqlchks);
                        $seatdata = $resultchks->fetch();

                        if (!empty($seatdata)) {
                            $totalSeats = $seatdata['seats'];
                            if (!empty($seatdata['used_seats'])) {
                                $used_seats = $seatdata['used_seats'] + 1;
                            } else {
                                $used_seats = 1;
                            }

                            $remaining_seats = $totalSeats - $used_seats;
                            $wdata = array('used_seats' => $used_seats, 'remaining_seats' => $remaining_seats, 'campaignid' => $cid, 'pupilsightProgramID' => $prog, 'pupilsightYearGroupID' => $cls);
                            $sqlupd = "UPDATE seatmatrix SET used_seats=:used_seats, remaining_seats=:remaining_seats WHERE campaignid=:campaignid AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID";
                            $resultupd = $connection2->prepare($sqlupd);
                            $resultupd->execute($wdata);
                        }
                    }
                } catch (Exception $ex) {
                    print_r($ex);
                    die();
                }
            }
        }
        echo 'success';
    }
    die();

    //$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/transitionsList.php';

}
