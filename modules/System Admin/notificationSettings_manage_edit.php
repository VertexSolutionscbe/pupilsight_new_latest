<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\System\NotificationGateway;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/System Admin/notificationSettings_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Notification Settings'), 'notificationSettings.php')
        ->add(__('Edit Notification Event'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightNotificationEventID = (isset($_GET['pupilsightNotificationEventID']))? $_GET['pupilsightNotificationEventID'] : null;

    if (empty($pupilsightNotificationEventID)) {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        $gateway = new NotificationGateway($pdo);
        $result = $gateway->selectNotificationEventByID($pupilsightNotificationEventID);

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            $event = $result->fetch();

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/notificationSettings_manage_editProcess.php');

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightNotificationEventID', $pupilsightNotificationEventID);

            $row = $form->addRow();
                $row->addLabel('event', __('Event'));
                $row->addTextField('event')->setValue(__($event['moduleName']).': '.__($event['event']))->readOnly();

            $row = $form->addRow();
                $row->addLabel('permission', __('Permission Required'));
                $row->addTextField('permission')->setValue(__($event['actionName']))->readOnly();

            $row = $form->addRow();
                $row->addLabel('active', __('Active'));
                $row->addYesNo('active')->selected($event['active']);

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();

            echo '<h3>';
            echo __('Edit Subscribers');
            echo '</h3>';

            if ($event['active'] == 'N') {
                echo "<div class='alert alert-warning'>";
                echo __('This notification event is not active. The following subscribers will not receive any notifications until the event is set to active.');
                echo '</div>';
            }

            if ($event['type'] == 'CLI') {
                echo "<div class='message'>";
                echo __('This is a CLI notification event. It will only run if the corresponding CLI script has been setup on the server.');
                echo '</div>';
            }

            $gateway = new NotificationGateway($pdo);
            $result = $gateway->selectAllNotificationListeners($pupilsightNotificationEventID, false);

            if ($result->rowCount() == 0) {
                echo "<div class='alert alert-danger'>";
                echo __('There are no records to display.');
                echo '</div>';
            } else {
                echo '<table class="colorOddEven fullWidth" cellspacing="0">';
                echo '<tr class="head">';
                echo '<th>';
                echo __('Name');
                echo '</th>';
                echo '<th style="width: 120px;" title="'.__('Notifications can always be viewed on screen.').'">';
                echo __('Receive Email Notifications?');
                echo '</th>';
                echo '<th>';
                echo __('Scope');
                echo '</th>';
                echo '<th style="width: 80px;">';
                echo __('Actions');
                echo '</th>';
                echo '</tr>';

                while ($listener = $result->fetch()) {
                    echo '<tr class="'.(($listener['receiveNotificationEmails'] == 'N')? 'warning' : '').'">';
                    echo '<td>';
                    echo Format::name($listener['title'], $listener['preferredName'], $listener['surname'], 'Staff', false, true);
                    echo '</td>';
                    echo '<td>';
                    echo ynExpander($guid, $listener['receiveNotificationEmails']);
                    echo '</td>';
                    echo '<td>';

                    if ($listener['scopeType'] == 'All') {
                        echo __('All');
                    } else {
                        switch($listener['scopeType']) {
                            case 'pupilsightPersonIDStudent':   $data = array('pupilsightPersonID' => $listener['scopeID']);
                                                            $sql = "SELECT 'Student' as scopeTypeName, CONCAT(surname, ' ', preferredName) as scopeIDName FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID";
                                                            break;

                            case 'pupilsightYearGroupID':       $data = array('pupilsightYearGroupID' => $listener['scopeID']);
                                                            $sql = "SELECT 'Year Group' as scopeTypeName, name as scopeIDName FROM pupilsightYearGroup WHERE pupilsightYearGroupID=:pupilsightYearGroupID";
                                                            break;

                            default:                        $data = array();
                                                            $sql = "SELECT 'Scope' as scopeTypeName, 'Unknown' as scopeIDName";
                        }

                        $resultScope = $pdo->executeQuery($data, $sql);
                        if ($resultScope && $resultScope->rowCount() > 0) {
                            $scopeDetails = $resultScope->fetch();
                            echo __($scopeDetails['scopeTypeName']).' - '.$scopeDetails['scopeIDName'];
                        }
                    }

                    echo '</td>';
                    echo '<td>';
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/notificationSettings_manage_listener_deleteProcess.php?pupilsightNotificationEventID=".$listener['pupilsightNotificationEventID']."&pupilsightNotificationListenerID=".$listener['pupilsightNotificationListenerID']."&address=".$_SESSION[$guid]['address']."'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a>";
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }

            // Filter users who can have permissions for the notification event action
            $staffMembers = array();
            $data=array( 'action' => $event['actionName']);
            $sql = "SELECT pupilsightPerson.pupilsightPersonID, pupilsightPerson.preferredName, pupilsightPerson.surname, pupilsightRole.name as roleName
                    FROM pupilsightPerson
                    JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID OR FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll))
                    JOIN pupilsightPermission ON (pupilsightRole.pupilsightRoleID=pupilsightPermission.pupilsightRoleID)
                    JOIN pupilsightAction ON (pupilsightPermission.pupilsightActionID=pupilsightAction.pupilsightActionID)
                    WHERE pupilsightPerson.status='Full'
                    AND (pupilsightAction.name=:action)
                    GROUP BY pupilsightPerson.pupilsightPersonID
                    ORDER BY pupilsightRole.pupilsightRoleID, surname, preferredName" ;
            $resultSelect=$pdo->executeQuery($data, $sql);

            if ($resultSelect && $resultSelect->rowCount() > 0) {
                $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/notificationSettings_manage_listener_addProcess.php');
                $form->setFactory(DatabaseFormFactory::create($pdo));

                $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                $form->addHiddenValue('pupilsightNotificationEventID', $pupilsightNotificationEventID);

                while ($rowSelect = $resultSelect->fetch()) {
                    $staffMembers[$rowSelect['roleName']][$rowSelect['pupilsightPersonID']] = Format::name("", $rowSelect["preferredName"], $rowSelect["surname"], "Staff", true, true);
                }

                $row = $form->addRow();
                    $row->addLabel('pupilsightPersonID', __('Person'))->description(__('Available only to users with the required permission.'));
                    $row->addSelect('pupilsightPersonID')->fromArray($staffMembers)->placeholder(__('Please select...'))->required();

                if ($event['scopes'] == 'All') {
                    $form->addHiddenValue('scopeType', 'All');
                } else {
                    $allScopes = array(
                        'All'                   => __('All'),
                        'pupilsightPersonIDStudent' => __('Student'),
                        'pupilsightPersonIDStaff'   => __('Staff'),
                        'pupilsightYearGroupID'     => __('Year Group'),
                    );

                    $eventScopes = array_combine(explode(',', $event['scopes']), explode(',', trim($event['scopes'])));
                    $availableScopes = array_intersect_key($allScopes, $eventScopes);

                    $row = $form->addRow();
                        $row->addLabel('scopeType', __('Scope'))->description(__('Apply an optional filter to notifications received.'));
                        $row->addSelect('scopeType')->fromArray($availableScopes);

                    $form->toggleVisibilityByClass('scopeTypeStudent')->onSelect('scopeType')->when('pupilsightPersonIDStudent');
                    $row = $form->addRow()->addClass('scopeTypeStudent');
                        $row->addLabel('pupilsightPersonIDStudent', __('Student'));
                        $row->addSelectStudent('pupilsightPersonIDStudent', $_SESSION[$guid]['pupilsightSchoolYearID'])->required()->placeholder();

                    $form->toggleVisibilityByClass('scopeTypeStaff')->onSelect('scopeType')->when('pupilsightPersonIDStaff');
                    $row = $form->addRow()->addClass('scopeTypeStaff');
                        $row->addLabel('pupilsightPersonIDStaff', __('Student'));
                        $row->addSelectStaff('pupilsightPersonIDStaff')->required()->placeholder();

                    $form->toggleVisibilityByClass('scopeTypeYearGroup')->onSelect('scopeType')->when('pupilsightYearGroupID');
                    $row = $form->addRow()->addClass('scopeTypeYearGroup');
                        $row->addLabel('pupilsightYearGroupID', __('Year Group'));
                        $row->addSelectYearGroup('pupilsightYearGroupID')->required()->placeholder();
                }

                $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit('Add');

                echo $form->getOutput();
            }
        }
    }
}
