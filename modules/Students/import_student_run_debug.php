<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\DataSet;
use Pupilsight\Services\Format;


include $_SERVER["DOCUMENT_ROOT"] . '/db.php';


require __DIR__ . '/moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Students/import_student_run.php';

if (isActionAccessible($guid, $connection2, "/modules/Students/import_student_run.php") == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $page->breadcrumbs->add(__('Student Import'));
    $form = Form::create('uploadData', '');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);


    $row = $form->addRow();
    $row->addLabel('file', __('File'))->description(__('See Notes below for specification.'));
    $row->addFileUpload('file')->required()->accepts('.csv');

    $row = $form->addRow();
    $row->addFooter();
    $row->addSubmit();

    echo $form->getOutput();


    if ($_POST) {
        //print_r($_FILES);
        //die();
        $exception_count = 0;
        $exception_result = array();

        if (!empty($_FILES['file']['name'])) {
            $handle = fopen($_FILES['file']['tmp_name'], "r");
            $headers = fgetcsv($handle, 10000, ",");
            $hders = array();
            // echo '<pre>';
            // print_r($headers);
            // echo '</pre>';
            $chkHeaderKey = array();
            foreach ($headers as $key => $hd) {
                if ($hd == 'Academic Year') {
                    $headers[$key] = 'at_pupilsightSchoolYearID';
                } else if ($hd == 'Program') {
                    $headers[$key] = 'at_pupilsightProgramID';
                } else if ($hd == 'Class') {
                    $headers[$key] = 'at_pupilsightYearGroupID';
                } else if ($hd == 'Section') {
                    $headers[$key] = 'at_pupilsightRollGroupID';
                } else if ($hd == 'Official Name') {
                    $headers[$key] = '##_officialName';
                } else if ($hd == 'Gender') {
                    $headers[$key] = '##_gender';
                } else if ($hd == 'Date of Birth') {
                    $headers[$key] = '##_dob';
                } else if ($hd == 'Username') {
                    $headers[$key] = '##_username';
                } else if ($hd == 'Can Login') {
                    $headers[$key] = '##_canLogin';
                } else if ($hd == 'Email') {
                    $headers[$key] = '##_email';
                } else if ($hd == 'Address') {
                    $headers[$key] = '##_address1';
                } else if ($hd == 'District') {
                    $headers[$key] = '##_address1District';
                } else if ($hd == 'Country') {
                    $headers[$key] = '##_address1Country';
                } else if ($hd == 'First Language') {
                    $headers[$key] = '##_languageFirst';
                } else if ($hd == 'Second Language') {
                    $headers[$key] = '##_languageSecond';
                } else if ($hd == 'Third Language') {
                    $headers[$key] = '##_languageThird';
                } else if ($hd == 'Country of Birth') {
                    $headers[$key] = '##_countryOfBirth';
                } else if ($hd == 'Ethnicity') {
                    $headers[$key] = '##_ethnicity';
                } else if ($hd == 'Religion') {
                    $headers[$key] = '##_religion';
                } else if ($hd == 'National ID Card Number') {
                    $headers[$key] = '##_nationalIDCardNumber';
                } else if ($hd == 'Fee Category') {
                    $headers[$key] = '##_fee_category_id';
                } else if ($hd == 'Father Official Name') {
                    $headers[$key] = '&&_officialName';
                } else if ($hd == 'Father Date of Birth') {
                    $headers[$key] = '&&_dob';
                } else if ($hd == 'Father Username') {
                    $headers[$key] = '&&_username';
                } else if ($hd == 'Father Can Login') {
                    $headers[$key] = '&&_canLogin';
                } else if ($hd == 'Father Email') {
                    $headers[$key] = '&&_email';
                } else if ($hd == 'Father Mobile (Country Code)') {
                    $headers[$key] = '&&_phone1CountryCode';
                } else if ($hd == 'Father Mobile No') {
                    $headers[$key] = '&&_phone1';
                } else if ($hd == 'Father LandLine (Country Code)') {
                    $headers[$key] = '&&_phone2CountryCode';
                } else if ($hd == 'Father Landline No') {
                    $headers[$key] = '&&_phone2';
                } else if ($hd == 'Mother Official Name') {
                    $headers[$key] = '$$_officialName';
                } else if ($hd == 'Mother Date of Birth') {
                    $headers[$key] = '$$_dob';
                } else if ($hd == 'Mother Username') {
                    $headers[$key] = '$$_username';
                } else if ($hd == 'Mother Can Login') {
                    $headers[$key] = '$$_canLogin';
                } else if ($hd == 'Mother Email') {
                    $headers[$key] = '$$_email';
                } else if ($hd == 'Mother Mobile (Country Code)') {
                    $headers[$key] = '$$_phone1CountryCode';
                } else if ($hd == 'Mother Mobile No') {
                    $headers[$key] = '$$_phone1';
                } else if ($hd == 'Mother LandLine (Country Code)') {
                    $headers[$key] = '$$_phone2CountryCode';
                } else if ($hd == 'Mother Landline No') {
                    $headers[$key] = '$$_phone2';
                } else {

                    //$sqlchk = 'SELECT field_name, modules FROM custom_field WHERE field_title = "' . $hd . '"';
                    $sqlchk = "SELECT field_name, modules FROM custom_field WHERE field_title = '" . $hd . "' and not  find_in_set('staff',modules)";
                    $resultchk = $connection2->query($sqlchk);
                    $cd = $resultchk->fetch();
                    $modules = explode(',', $cd['modules']);

                    //if(!in_array('##_'.$cd['field_name'], $chkHeaderKey)){
                    if (in_array('student', $modules)) {
                        $headers[$key] = '##_' . $cd['field_name'];
                        $chkHeaderKey[] = '##_' . $cd['field_name'];
                    }
                    //}
                    //else if(!in_array('&&_'.$cd['field_name'], $chkHeaderKey)){
                    if (in_array('father', $modules)) {
                        $headers[$key] = '&&_' . $cd['field_name'];
                        $chkHeaderKey[] = '&&_' . $cd['field_name'];
                    }
                    //}
                    //else if(!in_array('$$_'.$cd['field_name'], $chkHeaderKey)){
                    if (in_array('mother', $modules)) {
                        $headers[$key] = '$$_' . $cd['field_name'];
                        $chkHeaderKey[] = '$$_' . $cd['field_name'];
                    }
                    //}
                }
            }

            $hders = $headers;

            $all_rows = array();
            while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                $all_rows[] = array_combine($hders, $data);
            }



            if (!empty($all_rows)) {

                function getSaltNew()
                {
                    $c = explode(' ', '. / a A b B c C d D e E f F g G h H i I j J k K l L m M n N o O p P q Q r R s S t T u U v V w W x X y Y z Z 0 1 2 3 4 5 6 7 8 9');
                    $ks = array_rand($c, 22);
                    $s = '';
                    foreach ($ks as $k) {
                        $s .= $c[$k];
                    }
                    return $s;
                }

                $salt = getSaltNew();
                $pass = 'Admin@123456';
                $password = hash('sha256', $salt . $pass);
                /*
                echo '<pre>';
                print_r($all_rows);
                echo '</pre>';
                die();
                */
                try {
                    $cnt = 0;
                    $dcnt = 0;
                    $username_stock = array();
                    $dusername_stock = array();
                    $dtable_str = "";
                    foreach ($all_rows as  $alrow) {

                        $pupilsightSchoolYearID = '0';
                        $pupilsightProgramID = '0';
                        $pupilsightYearGroupID = '0';
                        $pupilsightRollGroupID = '0';
                        $homeAddress = 'Null';
                        $homeDistrict = 'Null';
                        $homeCountry = 'Null';
                        $stu_id = '';
                        $fat_id = '0';
                        $mot_id = '0';
                        if (!empty($alrow['##_address1'])) {
                            $addr = str_replace('"', "", $alrow['##_address1']);
                            $homeAddress = $addr;
                        }
                        if (!empty($alrow['##_address1District'])) {
                            $homeDistrict = $alrow['##_address1District'];
                        }
                        if (!empty($alrow['##_address1Country'])) {
                            $homeCountry = $alrow['##_address1Country'];
                        }

                        if (!empty($alrow['at_pupilsightSchoolYearID'])) {
                            $sqlaca = 'SELECT pupilsightSchoolYearID FROM pupilsightSchoolYear WHERE name = "' . $alrow['at_pupilsightSchoolYearID'] . '"';
                            $resultaca = $connection2->query($sqlaca);
                            $acaData = $resultaca->fetch();
                            $pupilsightSchoolYearID =  $acaData['pupilsightSchoolYearID'];
                        }
                        if (!empty($alrow['at_pupilsightProgramID'])) {
                            $sqlaca = 'SELECT pupilsightProgramID FROM pupilsightProgram WHERE name = "' . $alrow['at_pupilsightProgramID'] . '"';
                            $resultaca = $connection2->query($sqlaca);
                            $acaData = $resultaca->fetch();
                            $pupilsightProgramID =  $acaData['pupilsightProgramID'];
                        }
                        $pupilsightYearGroupID =  '0';
                        if (!empty($alrow['at_pupilsightYearGroupID'])) {
                            $sqlaca = 'SELECT pupilsightYearGroupID FROM pupilsightYearGroup WHERE name = "' . $alrow['at_pupilsightYearGroupID'] . '"';
                            $resultaca = $connection2->query($sqlaca);
                            $acaData = $resultaca->fetch();
                            if ($acaData['pupilsightYearGroupID']) {
                                $pupilsightYearGroupID =  $acaData['pupilsightYearGroupID'];
                            } else {
                                $pupilsightYearGroupID =  '0';
                            }
                        }

                        $pupilsightRollGroupID = '0';
                        if (!empty($alrow['at_pupilsightRollGroupID'])) {
                            $sqlaca = 'SELECT pupilsightRollGroupID FROM pupilsightRollGroup WHERE name = "' . $alrow['at_pupilsightRollGroupID'] . '"';
                            $resultaca = $connection2->query($sqlaca);
                            $acaData = $resultaca->fetch();
                            if ($acaData['pupilsightRollGroupID']) {
                                $pupilsightRollGroupID =  $acaData['pupilsightRollGroupID'];
                            } else {
                                $pupilsightRollGroupID =  '0';
                            }
                        }

                        try {
                            // Student Entry

                            $sql = "INSERT INTO pupilsightPerson (";
                            foreach ($alrow as $key => $ar) {
                                if (strpos($key, '##_') !== false && !empty($ar)) {
                                    //$clname = ltrim($key, '##_'); 
                                    $clname = substr($key, 3, strlen($key));
                                    $sql .= $clname . ',';
                                }
                            }
                            $sql .= 'preferredName,pupilsightRoleIDPrimary,pupilsightRoleIDAll';
                            //$sql = rtrim($sql, ", ");
                            $sql .= ") VALUES (";
                            $offical_name = "";
                            $username = "";
                            foreach ($alrow as $k => $value) {

                                if ($k == "##_dob" && !empty($value)) {
                                    $value = date('Y-m-d', strtotime($value));
                                }

                                if ($k == "##_officialName" && !empty($value)) {
                                    $offical_name = $conn->real_escape_string($value);
                                }

                                if ($k == "##_username" && !empty($value)) {
                                    $username = $value;
                                }

                                if ($k == "##_fee_category_id" && !empty($value)) {
                                    $sqlfc = 'SELECT id FROM fee_category WHERE name = "' . $value . '"';
                                    $resultfc = $connection2->query($sqlfc);
                                    $fcData = $resultfc->fetch();
                                    $value = $fcData['id'];
                                }

                                if (strpos($k, '##_') !== false && !empty($value)) {
                                    $val = str_replace('"', "", $value);
                                    $sql .= '"' . $conn->real_escape_string($val) . '",';
                                }
                            }
                            $sql .= '"' . $alrow['##_officialName'] . '","003","003"';
                            //$sql = rtrim($sql, ", ");
                            $sql .= ")";
                            $sql = rtrim($sql, ", ");
                            //echo "\n<br>" . $cnt . " " . $offical_name;

                            echo "\n" . $sql . ";";
                            //mysqli_query($conn, $sql);

                            if (in_array($username, $username_stock)) {
                                //echo "Match found";
                                $tmp = array($username, $offical_name);
                                $dusername_stock[$dcnt] = $tmp;
                                $dcnt++;
                                $dtable_str .= "\n<tr>";
                                $dtable_str .= "\n<td>" . $dcnt . "</td>";
                                $dtable_str .= "\n<td>" . $username . "</td>";
                                $dtable_str .= "\n<td>" . $offical_name . "</td>";
                                $dtable_str .= "\n</tr>";
                            } else {
                                //username unique
                                $username_stock[$cnt] = $username;
                                $cnt++;
                                //echo $sql . ";";

                                $conn->autocommit(FALSE);
                                $conn->query($sql);
                                $stu_id = $conn->insert_id;

                                // Father Entry
                                $chkFamily = 0;
                                if (!empty($alrow['&&_officialName'])) {

                                    if (!empty($alrow['&&_email'])) {
                                        $sqlchk = 'SELECT a.pupilsightPersonID, b.pupilsightFamilyID FROM pupilsightPerson AS a LEFT JOIN pupilsightFamilyRelationship AS b ON a.pupilsightPersonID = b.pupilsightPersonID1 WHERE a.email = "' . $alrow['&&_email'] . '" AND a.pupilsightRoleIDPrimary="004" ';
                                        $resultchk = $connection2->query($sqlchk);
                                        $pd = $resultchk->fetch();
                                        if (!empty($pd)) {
                                            $fat_id = $pd['pupilsightPersonID'];
                                            $family_id = $pd['pupilsightFamilyID'];
                                            $chkFamily = 1;

                                            $sqlf4 = "INSERT INTO pupilsightFamilyRelationship (pupilsightFamilyID,pupilsightPersonID1,pupilsightPersonID2,relationship) VALUES (" . $family_id . "," . $fat_id . "," . $stu_id . ",'Father')";
                                            $conn->query($sqlf4);
                                        }
                                    }

                                    if ($chkFamily == '0') {
                                        $sqlf = "INSERT INTO pupilsightPerson (";
                                        foreach ($alrow as $key => $ar) {
                                            if (strpos($key, '&&_') !== false  && !empty($ar)) {
                                                //$clname = ltrim($key, '&&_'); 
                                                $clname = substr($key, 3, strlen($key));
                                                $sqlf .= $clname . ',';
                                            }
                                        }
                                        $sqlf .= 'preferredName,gender,passwordStrong,passwordStrongSalt,pupilsightRoleIDPrimary,pupilsightRoleIDAll';
                                        //$sqlf = rtrim($sqlf, ", ");
                                        $sqlf .= ") VALUES (";
                                        foreach ($alrow as $k => $value) {
                                            if ($k == "&&_dob" && !empty($value)) {
                                                $value = date('Y-m-d', strtotime($value));
                                            }

                                            if (strpos($k, '&&_') !== false  && !empty($value)) {
                                                $val = str_replace('"', "", $value);
                                                $sqlf .= '"' . $conn->real_escape_string($val) . '",';
                                            }
                                        }
                                        $sqlf .= '"' . $alrow['&&_officialName'] . '","M","' . $password . '","' . $salt . '","004","004"';
                                        //$sqlf = rtrim($sqlf, ", ");
                                        $sqlf .= ")";
                                        $sqlf = rtrim($sqlf, ", ");

                                        //echo "\n<br/>father " . $sqlf;
                                        echo "\n" . $sqlf . ";";
                                        $conn->query($sqlf);
                                        $fat_id = $conn->insert_id;
                                    }
                                }

                                // Mother Entry
                                if ($chkFamily == '0') {
                                    if (!empty($alrow['$$_officialName'])) {
                                        $sqlm = "INSERT INTO pupilsightPerson (";
                                        foreach ($alrow as $key => $ar) {
                                            if (strpos($key, '$$_') !== false  && !empty($ar)) {
                                                //$clname = ltrim($key, '$$_'); 
                                                $clname = substr($key, 3, strlen($key));
                                                $sqlm .= $clname . ',';
                                            }
                                        }
                                        $sqlm .= 'preferredName,gender,passwordStrong,passwordStrongSalt,pupilsightRoleIDPrimary,pupilsightRoleIDAll';
                                        //$sqlm = rtrim($sqlm, ", ");
                                        $sqlm .= ") VALUES (";
                                        foreach ($alrow as $k => $value) {
                                            if ($k == "$$" . "_dob" && !empty($value)) {
                                                $value = date('Y-m-d', strtotime($value));
                                            }

                                            if (strpos($k, '$$_') !== false  && !empty($value)) {
                                                $val = str_replace('"', "", $value);
                                                $sqlm .= '"' . $conn->real_escape_string($val) . '",';
                                            }
                                        }
                                        $sqlm .= '"' . $alrow['$$_officialName'] . '","F","' . $password . '","' . $salt . '","004","004"';
                                        //$sqlm = rtrim($sqlm, ", ");
                                        $sqlm .= ")";
                                        $sqlm = rtrim($sqlm, ", ");
                                        //echo "\n<br/>mother " . $sqlm;
                                        echo "\n" . $sqlm . ";";
                                        $conn->query($sqlm);
                                        $mot_id = $conn->insert_id;
                                    }
                                } else {
                                    if ($family_id) {
                                        $sqlchk = 'SELECT a.pupilsightPersonID, b.pupilsightFamilyID FROM pupilsightPerson AS a LEFT JOIN pupilsightFamilyRelationship AS b ON a.pupilsightPersonID = b.pupilsightPersonID1 WHERE b.pupilsightFamilyID = ' . $family_id . ' AND b.relationship = "Mother" ';
                                        $resultchk = $connection2->query($sqlchk);
                                        $pd = $resultchk->fetch();
                                        if (!empty($pd)) {
                                            $mot_id = $pd['pupilsightPersonID'];
                                            $family_id = $pd['pupilsightFamilyID'];
                                            //$chkFamily = 1;
                                            $sqlf4 = "INSERT INTO pupilsightFamilyRelationship (pupilsightFamilyID,pupilsightPersonID1,pupilsightPersonID2,relationship) VALUES (" . $family_id . "," . $mot_id . "," . $stu_id . ",'Mother')";
                                            $conn->query($sqlf4);
                                        }
                                    }
                                }

                                if (!empty($stu_id) && !empty($pupilsightSchoolYearID)) {
                                    $sqlche = 'SELECT pupilsightStudentEnrolmentID FROM pupilsightStudentEnrolment WHERE pupilsightPersonID = "' . $stu_id . '"';
                                    $resultche = $connection2->query($sqlche);
                                    $enrData = $resultche->fetch();

                                    if (empty($enrData)) {
                                        $sqle = "INSERT INTO pupilsightStudentEnrolment (pupilsightPersonID,pupilsightSchoolYearID,pupilsightProgramID,pupilsightYearGroupID,pupilsightRollGroupID) VALUES (" . $stu_id . "," . $pupilsightSchoolYearID . "," . $pupilsightProgramID . "," . $pupilsightYearGroupID . "," . $pupilsightRollGroupID . ")";
                                        $enrol = $conn->query($sqle);
                                        echo "\n" . $sqle . ";";
                                    }

                                    //echo "\n<br/>pupilsightStudentEnrolment: " . $sqle;
                                }

                                if ($chkFamily == '0') {
                                    if (!empty($fat_id) || !empty($mot_id)) {

                                        if (!empty($alrow['&&_officialName']) && !empty($alrow['$$_officialName'])) {
                                            $name = $alrow['&&_officialName'] . ' & ' . $alrow['$$_officialName'] . ' Family';
                                        } elseif (!empty($alrow['&&_officialName']) && empty($alrow['$$_officialName'])) {
                                            $name = $alrow['&&_officialName'] . ' Family';
                                        } elseif (empty($alrow['&&_officialName']) && !empty($alrow['$$_officialName'])) {
                                            $name = $alrow['$$_officialName'] . ' Family';
                                        } else {
                                            $name = 'Family';
                                        }

                                        $sqlfamily = 'INSERT INTO pupilsightFamily (name,homeAddress,homeAddressDistrict,homeAddressCountry) VALUES ("' . $conn->real_escape_string($name) . '","' . $conn->real_escape_string($homeAddress) . '","' . $conn->real_escape_string($homeDistrict) . '","' . $conn->real_escape_string($homeCountry) . '")';
                                        //echo "\n<br/>family: " . $sqlfamily;
                                        $conn->query($sqlfamily);

                                        $family_id = $conn->insert_id;

                                        if (!empty($family_id)) {

                                            if (!empty($fat_id)) {
                                                $sqlf1 = "INSERT INTO pupilsightFamilyAdult (pupilsightFamilyID,pupilsightPersonID,childDataAccess,contactPriority,contactCall,contactSMS,contactEmail,contactMail) VALUES (" . $family_id . "," . $fat_id . ",'Y','1','N','N','N','N')";
                                                $conn->query($sqlf1);
                                                //echo "\n<br/>pupilsightFamilyAdult: " . $sqlf1;

                                                $sqlf4 = "INSERT INTO pupilsightFamilyRelationship (pupilsightFamilyID,pupilsightPersonID1,pupilsightPersonID2,relationship) VALUES (" . $family_id . "," . $fat_id . "," . $stu_id . ",'Father')";
                                                $conn->query($sqlf4);

                                                //echo "\n<br/>pupilsightFamilyRelationship: " . $sqlf4;
                                            }

                                            if (!empty($mot_id)) {
                                                $sqlf2 = "INSERT INTO pupilsightFamilyAdult (pupilsightFamilyID,pupilsightPersonID,childDataAccess,contactPriority,contactCall,contactSMS,contactEmail,contactMail) VALUES (" . $family_id . "," . $mot_id . ",'Y','2','N','N','N','N')";
                                                $conn->query($sqlf2);

                                                //echo "\n<br/>pupilsightFamilyAdult: ".$sqlf2;

                                                $sqlf5 = "INSERT INTO pupilsightFamilyRelationship (pupilsightFamilyID,pupilsightPersonID1,pupilsightPersonID2,relationship) VALUES (" . $family_id . "," . $mot_id . "," . $stu_id . ",'Mother')";

                                                //echo "\n<br/>pupilsightFamilyRelationship: " . $sqlf5;
                                                $conn->query($sqlf5);
                                            }
                                        }

                                        $sqlf3 = "INSERT INTO pupilsightFamilyChild (pupilsightFamilyID,pupilsightPersonID) VALUES (" . $family_id . "," . $stu_id . ")";
                                        $conn->query($sqlf3);
                                        //echo "\n<br/>pupilsightFamilyChild: " . $sqlf3;
                                    }
                                } else {
                                    $sqlf3 = "INSERT INTO pupilsightFamilyChild (pupilsightFamilyID,pupilsightPersonID) VALUES (" . $family_id . "," . $stu_id . ")";
                                    $conn->query($sqlf3);
                                }
                                $conn->commit();
                            }
                        } catch (PDOException $ex) {
                            $conn->rollback();
                            $exception_result[$exception_count] = $e->getMessage();
                            $exception_count++;
                        }
                    }
                } catch (Exception $ex) {
                    $exception_result[$exception_count] = $e->getMessage();
                    $exception_count++;
                }
            }
            die();
            fclose($handle);
            echo "\n<a href='" . $URL . "'>Back</a>\n<br>";
            if ($exception_result) {
                echo json_encode($exception_result);
                die();
            }
            if ($dusername_stock) {
                echo "\n<p style='color:red'><b>Duplicate Usernames " . count($dusername_stock) . "</b>. <br/>Please correct these username and upload once again.</p>";
                echo "<table border='1'><tr><th>Srno</th><th>Username</th><th>OfficalName</th>";
                echo $dtable_str;
                echo "</table>";
                echo "\nDebug";
                echo json_encode($dusername_stock);
                die();
            }
            $URL .= '&return=success0';
            header("Location: {$URL}");
        } else {
            //die();
            $URL .= '&return=error0';
            header("Location: {$URL}");
        }
    }
}
