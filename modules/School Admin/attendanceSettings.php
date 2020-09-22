<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Attendance\AttendanceCodeGateway;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/attendanceSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Attendance Master'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo '<h3>';
    echo __('Attendance Codes');
    echo '</h3>';
    echo '<p>';
    echo __('These codes should not be changed during an active school year. Removing an attendace code after attendance has been recorded can result in lost information.');
    echo '</p>';

    $attendanceCodeGateway = $container->get(AttendanceCodeGateway::class);

    // QUERY
    $criteria = $attendanceCodeGateway->newQueryCriteria()
        ->sortBy(['sequenceNumber'])
        ->fromArray($_POST);

    $attendanceCodes = $attendanceCodeGateway->queryAttendanceCodes($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('attendanceCodesManage', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/School Admin/attendanceSettings_manage_add.php')
        ->displayLabel();

    $table->modifyRows(function ($values, $row) {
        if ($values['active'] == 'N') $row->addClass('error');
        return $row;
    });

    $table->addColumn('nameShort', __('Code'));
    $table->addColumn('name', __('Name'))->translatable();
    $table->addColumn('direction', __('Direction'))->translatable();
    $table->addColumn('scope', __('Scope'))->translatable();
    $table->addColumn('active', __('Active'))->format(Format::using('yesNo', 'active'));

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightAttendanceCodeID')
        ->format(function ($values, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/School Admin/attendanceSettings_manage_edit.php');

            if ($values['type'] != 'Core') {
                $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/School Admin/attendanceSettings_manage_delete.php');
            }
        });

    echo $table->render($attendanceCodes);

    echo '<h3>';
    echo __(__('Miscellaneous'));
    echo '</h3>';

    $form = Form::create('attendanceSettings', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/attendanceSettingsProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow()->addHeading(__('Reasons'));

    $setting = getSettingByScope($connection2, 'Attendance', 'attendanceReasons', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value'])->required();

    $row = $form->addRow()->addHeading(__('Context & Defaults'));

    $setting = getSettingByScope($connection2, 'Attendance', 'countClassAsSchool', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Attendance', 'crossFillClasses', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $sql = "SELECT name AS value, name FROM pupilsightAttendanceCode WHERE active='Y' ORDER BY sequenceNumber ASC, name";
    $setting = getSettingByScope($connection2, 'Attendance', 'defaultRollGroupAttendanceType', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])
            ->fromQuery($pdo, $sql)
            ->selected($setting['value'])
            ->required();

    $setting = getSettingByScope($connection2, 'Attendance', 'defaultClassAttendanceType', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])
            ->fromQuery($pdo, $sql)
            ->selected($setting['value'])
            ->required();


    $row = $form->addRow()->addHeading(__('Student Self Registration'));

    $setting = getSettingByScope($connection2, 'Attendance', 'studentSelfRegistrationIPAddresses', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $realIP = getIPAddress();
    $inRange = false;
    if ($setting['value'] != '' && $setting['value'] != null) {
        foreach (explode(',', $setting['value']) as $ipAddress) {
            if (trim($ipAddress) == $realIP) {
                $inRange = true ;
            }
        }
    }
    if ($inRange) { //Current address is in range
        $form->addRow()->addAlert(sprintf(__('Your current IP address (%1$s) is included in the saved list.'), "<b>".$realIP."</b>"), 'success')->setClass('standardWidth');
    } else { //Current address is not in range
        $form->addRow()->addAlert(sprintf(__('Your current IP address (%1$s) is not included in the saved list.'), "<b>".$realIP."</b>"), 'warning')->setClass('standardWidth');
    }

    $setting = getSettingByScope($connection2, 'Attendance', 'selfRegistrationRedirect', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();


    $row = $form->addRow()->addHeading(__('Attendance CLI'));

    $setting = getSettingByScope($connection2, 'Attendance', 'attendanceCLINotifyByRollGroup', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Attendance', 'attendanceCLINotifyByClass', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();


    $setting = getSettingByScope($connection2, 'Attendance', 'attendanceCLIAdditionalUsers', true);
    $inputs = array();
    try {
        $data=array( 'action1' => '%report_rollGroupsNotRegistered_byDate.php%', 'action2' => '%report_courseClassesNotRegistered_byDate.php%' );
        $sql = "SELECT pupilsightPerson.pupilsightPersonID, pupilsightPerson.preferredName, pupilsightPerson.surname, pupilsightRole.name as roleName
                FROM pupilsightPerson
                JOIN pupilsightPermission ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightPermission.pupilsightRoleID)
                JOIN pupilsightAction ON (pupilsightPermission.pupilsightActionID=pupilsightAction.pupilsightActionID)
                JOIN pupilsightRole ON (pupilsightRole.pupilsightRoleID=pupilsightPermission.pupilsightRoleID)
                WHERE status='Full'
                AND (pupilsightAction.URLList LIKE :action1 OR pupilsightAction.URLList LIKE :action2)
                GROUP BY pupilsightPerson.pupilsightPersonID
                ORDER BY pupilsightRole.pupilsightRoleID, surname, preferredName" ;
        $resultSelect=$connection2->prepare($sql);
        $resultSelect->execute($data);
    } catch (PDOException $e) {
    }

    $users = explode(',', $setting['value']);
    $selected = array();
    while ($rowSelect=$resultSelect->fetch()) {
        if (in_array($rowSelect['pupilsightPersonID'], $users) !== false) {
            array_push($selected, $rowSelect['pupilsightPersonID']);
        }
        $inputs[$rowSelect["roleName"]][$rowSelect['pupilsightPersonID']] = formatName("", $rowSelect["preferredName"], $rowSelect["surname"], "Staff", true, true);
    }

    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])
            ->selectMultiple()
            ->fromArray($inputs)
            ->selected($selected);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
