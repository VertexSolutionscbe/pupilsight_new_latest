<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

// set page breadcrumb
$page->breadcrumbs->add(__('Student Self Registration'));

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_studentSelfRegister.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    if (isset($_GET['redirect']) && $_GET['redirect'] == 'true') {
        echo '<div class=\'warning\'>';
            echo __('Please self register!');
        echo '</div>';
    }

    //Check to see if IP addresses are set
    $studentSelfRegistrationIPAddresses = getSettingByScope($connection2, 'Attendance', 'studentSelfRegistrationIPAddresses');
    $realIP = getIPAddress();
    if ($studentSelfRegistrationIPAddresses == '' || is_null($studentSelfRegistrationIPAddresses)) {
        echo "<div class='alert alert-danger'>";
        echo __('You do not have access to this action.');
        echo '</div>';
    } else {
        //Check if school day
        $currentDate = date('Y-m-d');
        if (isSchoolOpen($guid, $currentDate, $connection2, true) == false) {
            print "<div class='alert alert-danger'>" ;
                print __("School is closed on the specified date, and so attendance information cannot be recorded.") ;
            print "</div>" ;
        }
        else {
            //Check for existence of records today
            try {
                $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'date' => $currentDate);
                $sql = "SELECT type FROM pupilsightAttendanceLogPerson WHERE pupilsightPersonID=:pupilsightPersonID AND date=:date ORDER BY timestampTaken DESC";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() > 0) { //Records! Output current status
                $row = $result->fetch();
                print "<div class='message'>" ;
                    print sprintf(__('Attendance has been taken for you today. Your current status is: %1$s'), "<b>".$row['type']."</b>") ;
                print "</div>" ;
            }
            else { //If no records, give option to self register
                $inRange = false ;
                foreach (explode(',', $studentSelfRegistrationIPAddresses) as $ipAddress) {
                    if (trim($ipAddress) == $realIP)
                        $inRange = true ;
                }

                $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/attendance_studentSelfRegisterProcess.php');

                $form->setFactory(DatabaseFormFactory::create($pdo));

                $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                if (!$inRange) { //Out of school, offer ability to register as absent
                    $form->addHiddenValue('status', 'Absent');

                    $row = $form->addRow();
                        $row->addLabel('submit',sprintf(__('It seems that you are out of school right now. Click the Submit button below to register yourself as %1$sAbsent%2$s today.'), '<span style=\'color: #CC0000; text-decoration: underline\'>', '</span>'));
                }
                else { //In school, offer ability to register as present
                    $form->addHiddenValue('status', 'Present');

                    $row = $form->addRow();
                        $row->addLabel('submit',sprintf(__('Welcome back to %1$s. Click the Submit button below to register yourself as %2$sPresent%3$s today.'), $_SESSION[$guid]['organisationNameShort'], '<span style=\'color: #390; text-decoration: underline\'>', '</span>'));
                }

                $row = $form->addRow();
                    $row->addFooter(false);
                    $row->addSubmit();

                echo $form->getOutput();
            }
        }

    }
}
?>
