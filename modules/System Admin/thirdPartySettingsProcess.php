<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/thirdPartySettings.php';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/thirdPartySettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $pupilsightPersonID=$_SESSION[$guid]["pupilsightPersonID"];
    $enablePayments = (isset($_POST['enablePayments']))? $_POST['enablePayments'] : '';
    $paypalAPIUsername = (isset($_POST['paypalAPIUsername']))? $_POST['paypalAPIUsername'] : '';
    $paypalAPIPassword = (isset($_POST['paypalAPIPassword']))? $_POST['paypalAPIPassword'] : '';
    $paypalAPISignature = (isset($_POST['paypalAPISignature']))? $_POST['paypalAPISignature'] : '';
    $googleOAuth = (isset($_POST['googleOAuth']))? $_POST['googleOAuth'] : '';
    $googleClientName = (isset($_POST['googleClientName']))? $_POST['googleClientName'] : '';
    $googleClientID = (isset($_POST['googleClientID']))? $_POST['googleClientID'] : '';
    $googleClientSecret = (isset($_POST['googleClientSecret']))? $_POST['googleClientSecret'] : '';
    $googleRedirectUri = (isset($_POST['googleRedirectUri']))? $_POST['googleRedirectUri'] : '';
    $googleDeveloperKey = (isset($_POST['googleDeveloperKey']))? $_POST['googleDeveloperKey'] : '';
    $calendarFeed = (isset($_POST['calendarFeed']))? $_POST['calendarFeed'] : '';
    $smsGateway = $_POST['smsGateway'] ?? '';
    $smsSenderID = $_POST['smsSenderID'] ?? '';
    $smsUsername = (isset($_POST['smsUsername']))? $_POST['smsUsername'] : '';
    $smsPassword = (isset($_POST['smsPassword']))? $_POST['smsPassword'] : '';
    $smsURL = (isset($_POST['smsURL']))? $_POST['smsURL'] : '';
    $smsURLCredit = (isset($_POST['smsURLCredit']))? $_POST['smsURLCredit'] : '';
    $send_sms_to = (isset($_POST['send_sms_to']))? $_POST['send_sms_to'] : '';
    
    // SMTP Mail Settings
    $enableMailerSMTP = (isset($_POST['enableMailerSMTP']))? $_POST['enableMailerSMTP'] : '';
    $mailerSMTPHost = (isset($_POST['mailerSMTPHost']))? $_POST['mailerSMTPHost'] : '';
    $mailerSMTPPort = (isset($_POST['mailerSMTPPort']))? $_POST['mailerSMTPPort'] : '';
    $mailerSMTPSecure = (isset($_POST['mailerSMTPSecure']))? $_POST['mailerSMTPSecure'] : '';
    $mailerSMTPUsername = (isset($_POST['mailerSMTPUsername']))? $_POST['mailerSMTPUsername'] : '';
    $mailerSMTPPassword = (isset($_POST['mailerSMTPPassword']))? $_POST['mailerSMTPPassword'] : '';
    $emailSignature = (isset($_POST['emailSignature']))? $_POST['emailSignature'] : '';
    $smsSignature = (isset($_POST['smsSignature']))? $_POST['smsSignature'] : '';
    try {
    $data = array('emailSignature' => $emailSignature,'smsSignature' => $smsSignature,'pupilsightPersonID'=>$pupilsightPersonID);
    $sql = "UPDATE pupilsightPerson SET emailSignature=:emailSignature,smsSignature=:smsSignature WHERE pupilsightPersonID=:pupilsightPersonID";
    $result = $connection2->prepare($sql);
    $result->execute($data);
    } catch (PDOException $e) {
    $fail = true;
    }

    //Validate Inputs
    if ($enablePayments == '' or $googleOAuth == '') {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Write to database
        $fail = false;

       try {
            $data = array('value' => $smsGateway);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Messenger' AND name='smsGateway'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $send_sms_to);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Messenger' AND name='send_sms_to'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        if ($googleOAuth == 'Y') {
            try {
                $data = array('value' => $googleClientName);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='System' AND name='googleClientName'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }

            try {
                $data = array('value' => $googleClientID);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='System' AND name='googleClientID'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }

            try {
                $data = array('value' => $googleClientSecret);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='System' AND name='googleClientSecret'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }

            try {
                $data = array('value' => $googleRedirectUri);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='System' AND name='googleRedirectUri'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }

            try {
                $data = array('value' => $googleDeveloperKey);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='System' AND name='googleDeveloperKey'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }

            try {
                $data = array('calendarFeed' => $calendarFeed);
                $sql = "UPDATE pupilsightSetting SET value=:calendarFeed WHERE scope='System' AND name='calendarFeed'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }
        }

        try {
            $data = array('enablePayments' => $enablePayments);
            $sql = "UPDATE pupilsightSetting SET value=:enablePayments WHERE scope='System' AND name='enablePayments'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        if ($enablePayments == 'Y') {
            try {
                $data = array('paypalAPIUsername' => $paypalAPIUsername);
                $sql = "UPDATE pupilsightSetting SET value=:paypalAPIUsername WHERE scope='System' AND name='paypalAPIUsername'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }

            try {
                $data = array('paypalAPIPassword' => $paypalAPIPassword);
                $sql = "UPDATE pupilsightSetting SET value=:paypalAPIPassword WHERE scope='System' AND name='paypalAPIPassword'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }

            try {
                $data = array('paypalAPISignature' => $paypalAPISignature);
                $sql = "UPDATE pupilsightSetting SET value=:paypalAPISignature WHERE scope='System' AND name='paypalAPISignature'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }
        }

        try {
            $data = array('value' => $smsGateway);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Messenger' AND name='smsGateway'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        if (!empty($smsGateway)) {
            try {
                $data = array('value' => $smsSenderID);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Messenger' AND name='smsSenderID'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }

            try {
                $data = array('value' => $smsUsername);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Messenger' AND name='smsUsername'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }

            try {
                $data = array('value' => $smsPassword);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Messenger' AND name='smsPassword'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }

            try {
                $data = array('value' => $smsURL);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Messenger' AND name='smsURL'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }

            try {
                $data = array('value' => $smsURLCredit);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Messenger' AND name='smsURLCredit'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }
        }

        // SMTP Mailer
        try {
            $data = array('value' => $enableMailerSMTP);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='System' AND name='enableMailerSMTP'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        if ($enableMailerSMTP == 'Y') {
            try {
                $data = array('value' => $mailerSMTPHost);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='System' AND name='mailerSMTPHost'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }

            try {
                $data = array('value' => $mailerSMTPPort);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='System' AND name='mailerSMTPPort'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }

            try {
                $data = array('value' => $mailerSMTPSecure);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='System' AND name='mailerSMTPSecure'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }

            try {
                $data = array('value' => $mailerSMTPUsername);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='System' AND name='mailerSMTPUsername'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }

            try {
                $data = array('value' => $mailerSMTPPassword);
                $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='System' AND name='mailerSMTPPassword'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }
        }

        if ($fail == true) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            getSystemSettings($guid, $connection2);
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}
