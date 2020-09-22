<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Contracts\Comms\Mailer;

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';
$search = $_GET['search'];
$pupilsightMessengerID = $_GET['pupilsightMessengerID'];

if ($pupilsightMessengerID == '' or $action != 'resend') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Messenger/messenger_manage_report.php&search=$search&pupilsightMessengerID=$pupilsightMessengerID&sidebar=true";

    if (isActionAccessible($guid, $connection2, '/modules/Messenger/messenger_manage_report.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
        exit;
    } else {
        $highestAction=getHighestGroupedAction($guid, '/modules/Messenger/messenger_manage_report.php', $connection2) ;
        if ($highestAction==FALSE) {
            $URL.="&updateReturn=error0" ;
            header("Location: {$URL}");
            exit;
        }

        $pupilsightMessengerReceiptIDs = array();
        if (isset($_POST['pupilsightMessengerReceiptIDs'])) {
            $pupilsightMessengerReceiptIDs = $_POST['pupilsightMessengerReceiptIDs'];
        }

        if (count($pupilsightMessengerReceiptIDs) < 1) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
            exit;
        } else {
            $partialFail = false;

            //Check message exists
            try {
                $data = array("pupilsightMessengerID" => $pupilsightMessengerID);
                $sql = "SELECT * FROM pupilsightMessenger WHERE pupilsightMessengerID=:pupilsightMessengerID";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) { }

            if ($result->rowCount() != 1) {
                $URL .= '&return=error0';
                header("Location: {$URL}");
                exit;
            } else {
                $row = $result->fetch();

                if ($row['pupilsightPersonID'] != $_SESSION[$guid]['pupilsightPersonID'] && $highestAction != 'Manage Messages_all') {
                    $URL .= '&return=error0';
                    header("Location: {$URL}");
                    exit;
                }
                else {
                    //Prep message
                    $emailCount = 0;
                    $bodyReminder = "<p style='font-style: italic; font-weight: bold'>" . __('This is a reminder for an email that requires your action. Please look for the link in the email, and click it to confirm receipt and reading of this email.') ."</p>" ;
                    $bodyFin = "<p style='font-style: italic'>" . sprintf(__('Email sent via %1$s at %2$s.'), $_SESSION[$guid]["systemName"], $_SESSION[$guid]["organisationName"]) ."</p>" ;
                    $mail = $container->get(Mailer::class);
    				$mail->SetFrom($_SESSION[$guid]["email"], $_SESSION[$guid]["preferredName"] . " " . $_SESSION[$guid]["surname"]);
    				$mail->CharSet="UTF-8";
    				$mail->Encoding="base64" ;
    				$mail->IsHTML(true);
    				$mail->Subject=__('REMINDER:').' '.$row['subject'] ;

                    //Scan through receipients
                    foreach ($pupilsightMessengerReceiptIDs as $pupilsightMessengerReceiptID) {
                        //Check recipient status
                        try {
                            $dataRecipt = array("pupilsightMessengerID" => $pupilsightMessengerID, "pupilsightMessengerReceiptID" => $pupilsightMessengerReceiptID);
                            $sqlRecipt = "SELECT * FROM pupilsightMessengerReceipt WHERE pupilsightMessengerID=:pupilsightMessengerID AND pupilsightMessengerReceiptID=:pupilsightMessengerReceiptID";
                            $resultRecipt = $connection2->prepare($sqlRecipt);
                            $resultRecipt->execute($dataRecipt);
                        } catch (PDOException $e) { }

                        if ($resultRecipt->rowCount() != 1) {
                            $partialFail = true;
                        } else {
                            $rowRecipt = $resultRecipt->fetch();

                            //Resend message
                            $emailCount ++;
                            $mail->ClearAddresses();
    						$mail->AddAddress($rowRecipt['contactDetail']);
    						//Deal with email receipt and body finalisation
    						if ($row['emailReceipt'] == 'Y') {
    							$bodyReadReceipt = "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Messenger/messenger_emailReceiptConfirm.php&pupilsightMessengerID=$pupilsightMessengerID&pupilsightPersonID=".$rowRecipt['pupilsightPersonID']."&key=".$rowRecipt['key']."'>".$row['emailReceiptText']."</a>";
    							if (is_numeric(strpos($row['body'], '[confirmLink]'))) {
    								$bodyOut = $bodyReminder.str_replace('[confirmLink]', $bodyReadReceipt, $row['body']).$bodyFin;
    							}
    							else {
    								$bodyOut = $bodyReminder.$row['body'].$bodyReadReceipt.$bodyFin;
    							}
    						}
    						else {
    							$bodyOut = $bodyReminder.$row['body'].$bodyFin;
    						}
    						$mail->Body = $bodyOut ;
    						$mail->AltBody = emailBodyConvert($bodyOut);
                            if(!$mail->Send()) {
    							$partialFail = TRUE ;
    						}
                        }
                    }
                }
            }

            if ($partialFail == true) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
