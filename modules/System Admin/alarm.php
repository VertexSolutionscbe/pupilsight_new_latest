<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\FileUploader;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/alarm.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Sound Alarm'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Get list of acceptable file extensions
    $fileUploader = new FileUploader($pdo, $pupilsight->session);
    $fileUploader->getFileExtensions('Audio');

    // Alram Types
    $alarmTypes = array(
        'None'     => __('None'),
        'General'  => __('General'),
        'Lockdown' => __('Lockdown'),
        'Custom'   => __('Custom'),
    );

    $form = Form::create('alarmSettings', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/alarmProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $setting = getSettingByScope($connection2, 'System Admin', 'customAlarmSound', true);

    $row = $form->addRow();
        $label = $row->addLabel('file', __($setting['nameDisplay']))->description(__($setting['description']));
        if (!empty($setting['value'])) $label->append(__('Will overwrite existing attachment.'));

        $file = $row->addFileUpload('file')
                    ->accepts($fileUploader->getFileExtensionsCSV())
                    ->setAttachment('attachmentCurrent', $_SESSION[$guid]['absoluteURL'], $setting['value']);

    $setting = getSettingByScope($connection2, 'System', 'alarm', true);
    $form->addHiddenValue('alarmCurrent', $setting['value']);

    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])->fromArray($alarmTypes)->selected($setting['value'])->required();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
