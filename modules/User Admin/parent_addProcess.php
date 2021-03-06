<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/parent_add.php&search='.$_GET['search'];

$redirectURL =  $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/students.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/parent_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $insertstudentId = $_POST['student_id'];
    $title = $_POST['title'];
    $surname = trim($_POST['surname']);
    $firstName = trim($_POST['firstName']);
    $preferredName = trim($_POST['preferredName']);
    $officialName = trim($_POST['officialName']);
    $nameInCharacters = $_POST['nameInCharacters'];
    $gender = $_POST['gender'];
    if($gender == 'F'){
        $relationship = 'Mother';
    } elseif($gender == 'M') {
        $relationship = 'Father';
    } else {
        $relationship = 'Other';
    }
    
    $username = trim($_POST['username']);
    $password = $_POST['passwordNew'];
    $passwordConfirm = $_POST['passwordConfirm'];
    $status = $_POST['status'];
    $canLogin = $_POST['canLogin'];
    $passwordForceReset = $_POST['passwordForceReset'];
    $pupilsightRoleIDPrimary = $_POST['pupilsightRoleIDPrimary'];
    $dob = $_POST['dob'];
    if ($dob == '') {
        $dob = null;
    } else {
        $dob = dateConvert($guid, $dob);
    }
    $email = trim($_POST['email']);
    $emailAlternate = trim($_POST['emailAlternate']);
    $address1 = isset($_POST['address1']) ? $_POST['address1'] : '';
    $address1District = isset($_POST['address1District']) ? $_POST['address1District'] : '';
    $address1Country = isset($_POST['address1Country']) ? $_POST['address1Country'] : '';
    $address2 = isset($_POST['address2']) ? $_POST['address2'] : '';
    $address2District = isset($_POST['address2District']) ? $_POST['address2District'] : '';
    $address2Country = isset($_POST['address2Country']) ? $_POST['address2Country'] : '';
    $phone1Type = $_POST['phone1Type'];
    if ($_POST['phone1'] != '' and $phone1Type == '') {
        $phone1Type = 'Other';
    }
    $phone1CountryCode = $_POST['phone1CountryCode'];
    $phone1 = preg_replace('/[^0-9+]/', '', $_POST['phone1']);
    $phone2Type = $_POST['phone2Type'];
    if ($_POST['phone2'] != '' and $phone2Type == '') {
        $phone2Type = 'Other';
    }
    $phone2CountryCode = $_POST['phone2CountryCode'];
    $phone2 = preg_replace('/[^0-9+]/', '', $_POST['phone2']);
    $phone3Type = $_POST['phone3Type'];
    if ($_POST['phone3'] != '' and $phone3Type == '') {
        $phone3Type = 'Other';
    }
    $phone3CountryCode = $_POST['phone3CountryCode'];
    $phone3 = preg_replace('/[^0-9+]/', '', $_POST['phone3']);
    $phone4Type = $_POST['phone4Type'];
    if ($_POST['phone4'] != '' and $phone4Type == '') {
        $phone4Type = 'Other';
    }
    $phone4CountryCode = $_POST['phone4CountryCode'];
    $phone4 = preg_replace('/[^0-9+]/', '', $_POST['phone4']);
    $website = $_POST['website'];
    $languageFirst = $_POST['languageFirst'];
    $languageSecond = $_POST['languageSecond'];
    $languageThird = $_POST['languageThird'];
    $countryOfBirth = $_POST['countryOfBirth'];
    $ethnicity = $_POST['ethnicity'];
    $citizenship1 = $_POST['citizenship1'];
    $citizenship1Passport = $_POST['citizenship1Passport'];
    $citizenship2 = $_POST['citizenship2'];
    $citizenship2Passport = $_POST['citizenship2Passport'];
    $religion = $_POST['religion'];
    $nationalIDCardNumber = $_POST['nationalIDCardNumber'];
    $residencyStatus = $_POST['residencyStatus'];
    $visaExpiryDate = $_POST['visaExpiryDate'];
    if ($visaExpiryDate == '') {
        $visaExpiryDate = null;
    } else {
        $visaExpiryDate = dateConvert($guid, $visaExpiryDate);
    }
    $profession = $_POST['profession'];
    $employer = $_POST['employer'];
    $jobTitle = $_POST['jobTitle'];
    $emergency1Name = $_POST['emergency1Name'];
    $emergency1Number1 = $_POST['emergency1Number1'];
    $emergency1Number2 = $_POST['emergency1Number2'];
    $emergency1Relationship = $_POST['emergency1Relationship'];
    $emergency2Name = $_POST['emergency2Name'];
    $emergency2Number1 = $_POST['emergency2Number1'];
    $emergency2Number2 = $_POST['emergency2Number2'];
    $emergency2Relationship = $_POST['emergency2Relationship'];
    $profession = $_POST['profession'];
    $employer = $_POST['employer'];
    $jobTitle = $_POST['jobTitle'];
    $pupilsightHouseID = $_POST['pupilsightHouseID'];
    if ($pupilsightHouseID == '') {
        $pupilsightHouseID = null;
    } else {
        $pupilsightHouseID = $pupilsightHouseID;
    }
    $studentID = $_POST['studentID'];
    $dateStart = $_POST['dateStart'];
    if ($dateStart == '') {
        $dateStart = null;
    } else {
        $dateStart = dateConvert($guid, $dateStart);
    }

    $pupilsightSchoolYearIDClassOf = $_POST['pupilsightSchoolYearIDClassOf'];
    if ($pupilsightSchoolYearIDClassOf == '') {
        $pupilsightSchoolYearIDClassOf = null;
    }
    $lastSchool = $_POST['lastSchool'];
    $transport = $_POST['transport'];
    $transportNotes = $_POST['transportNotes'];
    $lockerNumber = $_POST['lockerNumber'];
    $vehicleRegistration = $_POST['vehicleRegistration'];
    $privacyOptions = null;
    if (isset($_POST['privacyOptions'])) {
        $privacyOptions = $_POST['privacyOptions'];
    }
    $privacy = '';
    if (is_array($privacyOptions)) {
        foreach ($privacyOptions as $privacyOption) {
            if ($privacyOption != '') {
                $privacy .= $privacyOption.',';
            }
        }
    }
    if ($privacy != '') {
        $privacy = substr($privacy, 0, -1);
    } else {
        $privacy = null;
    }
    $studentAgreements = null;
    $agreements = '';
    if (isset($_POST['studentAgreements'])) {
        $studentAgreements = $_POST['studentAgreements'];
        foreach ($studentAgreements as $studentAgreement) {
            if ($studentAgreement != '') {
                $agreements .= $studentAgreement.',';
            }
        }
    }
    if ($agreements != '') {
        $agreements = substr($agreements, 0, -1);
    } else {
        $agreements = null;
    }

    $dayType = null;
    if (isset($_POST['dayType'])) {
        $dayType = $_POST['dayType'];
    }

    //Validate Inputs
    if ($surname == '' or $firstName == '' or $preferredName == '' or $officialName == '' or $gender == '' or $username == '' or $password == '' or $passwordConfirm == '' or $status == '' or $pupilsightRoleIDPrimary == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('username' => $username);
            $sql = 'SELECT * FROM pupilsightPerson WHERE username=:username';
            if ($studentID != '') {
                $data = array('username' => $username, 'studentID' => $studentID);
                $sql = 'SELECT * FROM pupilsightPerson WHERE username=:username OR studentID=:studentID';
            }
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
            //Check passwords for match
            if ($password != $passwordConfirm) {
                $URL .= '&return=warning1';
                header("Location: {$URL}");
            } else {
                //Check strength of password
                $passwordMatch = doesPasswordMatchPolicy($connection2, $password);

                if ($passwordMatch == false) {
                    $URL .= '&return=error7';
                    header("Location: {$URL}");
                } else {
                    //Lock markbook column table
                    try {
                        $sql = 'LOCK TABLES pupilsightPerson WRITE, pupilsightFileExtension WRITE, pupilsightFamily WRITE, pupilsightFamilyChild WRITE, pupilsightFamilyAdult WRITE, pupilsightFamilyRelationship WRITE';
                        $result = $connection2->query($sql);

                        // $sqlf = 'LOCK TABLES pupilsightFamily WRITE, pupilsightFileExtension WRITE';
                        // $resultf = $connection2->query($sqlf);

                        // $ssql = 'LOCK TABLES pupilsightFamilyChild WRITE, pupilsightFileExtension WRITE';
                        // $sresult = $connection2->query($ssql);

                        // $fsql = 'LOCK TABLES pupilsightFamilyAdult WRITE, pupilsightFileExtension WRITE';
                        // $fresult = $connection2->query($fsql);

                        // $rsql = 'LOCK TABLES pupilsightFamilyRelationship WRITE, pupilsightFileExtension WRITE';
                        // $rresult = $connection2->query($rsql);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Get next autoincrement
                    try {
                        $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightPerson'";
                        $resultAI = $connection2->query($sqlAI);

                        $sqlAI2 = "SHOW TABLE STATUS LIKE 'pupilsightFamily'";
                        $resultAI2 = $connection2->query($sqlAI2);

                        $sqlAI3 = "SHOW TABLE STATUS LIKE 'pupilsightFamilyChild'";
                        $resultAI3 = $connection2->query($sqlAI3);

                        $sqlAI4 = "SHOW TABLE STATUS LIKE 'pupilsightFamilyAdult'";
                        $resultAI4 = $connection2->query($sqlAI4);

                        $sqlAI5 = "SHOW TABLE STATUS LIKE 'pupilsightFamilyRelationship'";
                        $resultAI5 = $connection2->query($sqlAI5);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $rowAI = $resultAI->fetch();
                    $AI = str_pad($rowAI['Auto_increment'], 10, '0', STR_PAD_LEFT);
                    $attachment1 = null;
                    $birthCertificateScan = '';
                    $nationalIDCardScan = '';
                    $citizenship1PassportScan = '';
                    $imageFail = false;
                    if (!empty($_FILES['file1']['tmp_name']) or !empty($_FILES['birthCertificateScan']['tmp_name']) or !empty($_FILES['nationalIDCardScan']['tmp_name']) or !empty($_FILES['citizenship1PassportScan']['tmp_name']))
                    {
                        $path = $_SESSION[$guid]['absolutePath'];
                        $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);
                    
                        //Move 240 attached file, if there is one
                        if (!empty($_FILES['file1']['tmp_name'])) {
                            $file = (isset($_FILES['file1']))? $_FILES['file1'] : null;

                            // Upload the file, return the /uploads relative path
                            $fileUploader->setFileSuffixType(Pupilsight\FileUploader::FILE_SUFFIX_INCREMENTAL);
                            $attachment1 = $fileUploader->uploadFromPost($file, $username.'_240');

                            if (empty($attachment1)) {
                                $imageFail = true;
                            } else {
                                //Check image sizes
                                $size1 = getimagesize($path.'/'.$attachment1);
                                $width1 = $size1[0];
                                $height1 = $size1[1];
                                $aspect1 = $height1 / $width1;
                                if ($width1 > 360 or $height1 > 480 or $aspect1 < 1.2 or $aspect1 > 1.4) {
                                    $attachment1 = '';
                                    $imageFail = true;
                                }
                            }
                        }

                        //Move birth certificate scan if there is one
                        if (!empty($_FILES['birthCertificateScan']['tmp_name'])) {
                            $file = (isset($_FILES['birthCertificateScan']))? $_FILES['birthCertificateScan'] : null;

                            // Upload the file, return the /uploads relative path
                            $fileUploader->setFileSuffixType(Pupilsight\FileUploader::FILE_SUFFIX_ALPHANUMERIC);
                            $birthCertificateScan = $fileUploader->uploadFromPost($file, $username.'_birthCertificate');

                            if (empty($birthCertificateScan)) {
                                $imageFail = true;
                            } else {
                                if (stripos($file['tmp_name'], 'pdf') === false) {
                                    //Check image sizes
                                    $size2 = getimagesize($path.'/'.$birthCertificateScan);
                                    $width2 = $size2[0];
                                    $height2 = $size2[1];
                                    if ($width2 > 1440 or $height2 > 900) {
                                        $birthCertificateScan = '';
                                        $imageFail = true;
                                    }
                                }
                            }
                        }

                        //Move ID Card scan file, if there is one
                        if (!empty($_FILES['nationalIDCardScan']['tmp_name'])) {
                            $file = (isset($_FILES['nationalIDCardScan']))? $_FILES['nationalIDCardScan'] : null;

                            // Upload the file, return the /uploads relative path
                            $fileUploader->setFileSuffixType(Pupilsight\FileUploader::FILE_SUFFIX_ALPHANUMERIC);
                            $nationalIDCardScan = $fileUploader->uploadFromPost($file, $username.'_idscan');

                            if (empty($nationalIDCardScan)) {
                                $imageFail = true;
                            } else {
                                if (stripos($file['tmp_name'], 'pdf') === false) {
                                    //Check image sizes
                                    $size3 = getimagesize($path.'/'.$nationalIDCardScan);
                                    $width3 = $size3[0];
                                    $height3 = $size3[1];
                                    if ($width3 > 1440 or $height3 > 900) {
                                        $nationalIDCardScan = '';
                                        $imageFail = true;
                                    }
                                }
                            }
                        }

                        //Move passport scan file, if there is one
                        if (!empty($_FILES['citizenship1PassportScan']['tmp_name'])) {
                            $file = (isset($_FILES['citizenship1PassportScan']))? $_FILES['citizenship1PassportScan'] : null;

                            // Upload the file, return the /uploads relative path
                            $fileUploader->setFileSuffixType(Pupilsight\FileUploader::FILE_SUFFIX_ALPHANUMERIC);
                            $citizenship1PassportScan = $fileUploader->uploadFromPost($file, $username.'_passportscan');

                            if (empty($citizenship1PassportScan)) {
                                $imageFail = true;
                            } else {
                                if (stripos($file['tmp_name'], 'pdf') === false) {
                                    //Check image sizes
                                    $size4 = getimagesize($path.'/'.$citizenship1PassportScan);
                                    $width4 = $size4[0];
                                    $height4 = $size4[1];
                                    if ($width4 > 1440 or $height4 > 900) {
                                        $citizenship1PassportScan = '';
                                        $imageFail = true;
                                    }
                                }
                            }
                        }
                    }

                    $salt = getSalt();
                    $passwordStrong = hash('sha256', $salt.$password);

                    //Write to database
                    try {
                        $data = array('title' => $title, 'surname' => $surname, 'firstName' => $firstName, 'preferredName' => $preferredName, 'officialName' => $officialName, 'nameInCharacters' => $nameInCharacters, 'gender' => $gender, 'username' => $username, 'passwordStrong' => $passwordStrong, 'passwordStrongSalt' => $salt, 'status' => $status, 'canLogin' => $canLogin, 'passwordForceReset' => $passwordForceReset, 'pupilsightRoleIDPrimary' => $pupilsightRoleIDPrimary, 'pupilsightRoleIDAll' => $pupilsightRoleIDPrimary, 'dob' => $dob, 'email' => $email, 'emailAlternate' => $emailAlternate, 'address1' => $address1, 'address1District' => $address1District, 'address1Country' => $address1Country, 'address2' => $address2, 'address2District' => $address2District, 'address2Country' => $address2Country, 'phone1Type' => $phone1Type, 'phone1CountryCode' => $phone1CountryCode, 'phone1' => $phone1, 'phone2Type' => $phone2Type, 'phone2CountryCode' => $phone2CountryCode, 'phone2' => $phone2, 'phone3Type' => $phone3Type, 'phone3CountryCode' => $phone3CountryCode, 'phone3' => $phone3, 'phone4Type' => $phone4Type, 'phone4CountryCode' => $phone4CountryCode, 'phone4' => $phone4, 'website' => $website, 'languageFirst' => $languageFirst, 'languageSecond' => $languageSecond, 'languageThird' => $languageThird, 'countryOfBirth' => $countryOfBirth, 'birthCertificateScan' => $birthCertificateScan, 'ethnicity' => $ethnicity, 'citizenship1' => $citizenship1, 'citizenship1Passport' => $citizenship1Passport, 'citizenship1PassportScan' => $citizenship1PassportScan, 'citizenship2' => $citizenship2, 'citizenship2Passport' => $citizenship2Passport, 'religion' => $religion, 'nationalIDCardNumber' => $nationalIDCardNumber, 'nationalIDCardScan' => $nationalIDCardScan, 'residencyStatus' => $residencyStatus, 'visaExpiryDate' => $visaExpiryDate, 'emergency1Name' => $emergency1Name, 'emergency1Number1' => $emergency1Number1, 'emergency1Number2' => $emergency1Number2, 'emergency1Relationship' => $emergency1Relationship, 'emergency2Name' => $emergency2Name, 'emergency2Number1' => $emergency2Number1, 'emergency2Number2' => $emergency2Number2, 'emergency2Relationship' => $emergency2Relationship, 'profession' => $profession, 'employer' => $employer, 'jobTitle' => $jobTitle, 'attachment1' => $attachment1, 'pupilsightHouseID' => $pupilsightHouseID, 'studentID' => $studentID, 'dateStart' => $dateStart, 'pupilsightSchoolYearIDClassOf' => $pupilsightSchoolYearIDClassOf, 'lastSchool' => $lastSchool, 'transport' => $transport, 'transportNotes' => $transportNotes, 'lockerNumber' => $lockerNumber, 'vehicleRegistration' => $vehicleRegistration, 'privacy' => $privacy, 'agreements' => $agreements, 'dayType' => $dayType);
                        $sql = "INSERT INTO pupilsightPerson SET title=:title, surname=:surname, firstName=:firstName, preferredName=:preferredName, officialName=:officialName, nameInCharacters=:nameInCharacters, gender=:gender, username=:username, password='', passwordStrong=:passwordStrong, passwordStrongSalt=:passwordStrongSalt, status=:status, canLogin=:canLogin, passwordForceReset=:passwordForceReset, pupilsightRoleIDPrimary=:pupilsightRoleIDPrimary, pupilsightRoleIDAll=:pupilsightRoleIDAll, dob=:dob, email=:email, emailAlternate=:emailAlternate, address1=:address1, address1District=:address1District, address1Country=:address1Country, address2=:address2, address2District=:address2District, address2Country=:address2Country, phone1Type=:phone1Type, phone1CountryCode=:phone1CountryCode, phone1=:phone1, phone2Type=:phone2Type, phone2CountryCode=:phone2CountryCode, phone2=:phone2, phone3Type=:phone3Type, phone3CountryCode=:phone3CountryCode, phone3=:phone3, phone4Type=:phone4Type, phone4CountryCode=:phone4CountryCode, phone4=:phone4, website=:website, languageFirst=:languageFirst, languageSecond=:languageSecond, languageThird=:languageThird, countryOfBirth=:countryOfBirth, birthCertificateScan=:birthCertificateScan, ethnicity=:ethnicity,  citizenship1=:citizenship1, citizenship1Passport=:citizenship1Passport, citizenship2=:citizenship2,  citizenship2Passport=:citizenship2Passport, religion=:religion, nationalIDCardNumber=:nationalIDCardNumber, nationalIDCardScan=:nationalIDCardScan, citizenship1PassportScan=:citizenship1PassportScan, residencyStatus=:residencyStatus, visaExpiryDate=:visaExpiryDate, emergency1Name=:emergency1Name, emergency1Number1=:emergency1Number1, emergency1Number2=:emergency1Number2, emergency1Relationship=:emergency1Relationship, emergency2Name=:emergency2Name, emergency2Number1=:emergency2Number1, emergency2Number2=:emergency2Number2, emergency2Relationship=:emergency2Relationship, profession=:profession, employer=:employer, jobTitle=:jobTitle, image_240=:attachment1, pupilsightHouseID=:pupilsightHouseID, studentID=:studentID, dateStart=:dateStart, pupilsightSchoolYearIDClassOf=:pupilsightSchoolYearIDClassOf, lastSchool=:lastSchool, transport=:transport, transportNotes=:transportNotes, lockerNumber=:lockerNumber, vehicleRegistration=:vehicleRegistration, privacy=:privacy, studentAgreements=:agreements, dayType=:dayType";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);

                        $parentId = str_pad($connection2->lastInsertID(), 10, '0', STR_PAD_LEFT);

                        $fname = $surname.' Family';
                        $fstatus = 'Married';
                        $flanguageHomePrimary = $languageFirst;
                        $flanguageHomeSecondary = $languageSecond;
                        $fnameAddress = $address1;
                        $fhomeAddress = $address1;
                        $fhomeAddressDistrict = $address1District;
                        $fhomeAddressCountry = $address1Country;


                        $dataf = array('name' => $fname, 'status' => $fstatus, 'languageHomePrimary' => $flanguageHomePrimary, 'languageHomeSecondary' => $flanguageHomeSecondary, 'nameAddress' => $fnameAddress, 'homeAddress' => $fhomeAddress, 'homeAddressDistrict' => $fhomeAddressDistrict, 'homeAddressCountry' => $fhomeAddressCountry);
                        $sqlf = 'INSERT INTO pupilsightFamily SET name=:name, status=:status, languageHomePrimary=:languageHomePrimary, languageHomeSecondary=:languageHomeSecondary, nameAddress=:nameAddress, homeAddress=:homeAddress, homeAddressDistrict=:homeAddressDistrict, homeAddressCountry=:homeAddressCountry';
                        $resultf = $connection2->prepare($sqlf);
                        $resultf->execute($dataf);

                        $familyId = str_pad($connection2->lastInsertID(), 10, '0', STR_PAD_LEFT);

                        
                        $sdata = array('pupilsightFamilyID' => $familyId, 'pupilsightPersonID' => $insertstudentId);
                        $ssql = 'INSERT INTO pupilsightFamilyChild SET pupilsightFamilyID=:pupilsightFamilyID, pupilsightPersonID=:pupilsightPersonID';
                        $sresult = $connection2->prepare($ssql);
                        $sresult->execute($sdata);

                        $fdata = array('pupilsightFamilyID' => $familyId, 'pupilsightPersonID' => $parentId, 'comment' => '', 'childDataAccess' => 'Y', 'contactPriority' => '1', 'contactCall' => 'Y', 'contactSMS' => 'Y', 'contactEmail' => 'Y', 'contactMail' => 'Y');

                        $fsql = 'INSERT INTO pupilsightFamilyAdult SET pupilsightFamilyID=:pupilsightFamilyID, pupilsightPersonID=:pupilsightPersonID, comment=:comment, childDataAccess=:childDataAccess, contactPriority=:contactPriority, contactCall=:contactCall, contactSMS=:contactSMS, contactEmail=:contactEmail, contactMail=:contactMail';
                        $fresult = $connection2->prepare($fsql);
                        $fresult->execute($fdata);

                        
                        $rdata = array('pupilsightFamilyID' => $familyId, 'pupilsightPersonID1' => $parentId, 'pupilsightPersonID2' => $insertstudentId, 'relationship' => $relationship);
                        $rsql = 'INSERT INTO pupilsightFamilyRelationship SET pupilsightFamilyID=:pupilsightFamilyID, pupilsightPersonID1=:pupilsightPersonID1, pupilsightPersonID2=:pupilsightPersonID2, relationship=:relationship';
                        $rresult = $connection2->prepare($rsql);
                        $rresult->execute($rdata);


                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Last insert ID
                    $AI = str_pad($connection2->lastInsertID(), 10, '0', STR_PAD_LEFT);

                    //Unlock tables
                    try {
                        $sql = 'UNLOCK TABLES';
                        $result = $connection2->query($sql);

                        $sqlf = 'UNLOCK TABLES';
                        $result = $connection2->query($sqlf);

                        $ssql = 'UNLOCK TABLES';
                        $result = $connection2->query($ssql);

                        $fsql = 'UNLOCK TABLES';
                        $result = $connection2->query($fsql);

                        $rsql = 'UNLOCK TABLES';
                        $result = $connection2->query($rsql);
                    } catch (PDOException $e) {
                    }

                    if ($imageFail) {
                        $URL .= "&return=warning1&editID=$AI";
                        header("Location: {$URL}");
                    } else {
                       
                        $redirectURL .= "&return=success0";
                        header("Location: {$redirectURL}");
                    }
                }
            }
        }
    }
}
