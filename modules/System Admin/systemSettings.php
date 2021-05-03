<?php
/*
Pupilsight, Flexible & Open School System

*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/systemSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $sqlq = 'SELECT * FROM pupilsightSchoolYear ORDER BY sequenceNumber';
        $resultval = $connection2->query($sqlq);
        $rowdata = $resultval->fetchAll();
        $academic = array();
        $ayear = '';
        if (!empty($rowdata)) {
            $ayear = $rowdata[0]['name'];
            foreach ($rowdata as $dt) {
                $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
            }
        }
    //Prepare and submit stats if that is what the system calls for
    if ($_SESSION[$guid]['statsCollection'] == 'Y') {
        $absolutePathProtocol = '';
        $absolutePath = '';
        if (substr($_SESSION[$guid]['absoluteURL'], 0, 7) == 'http://') {
            $absolutePathProtocol = 'http';
            $absolutePath = substr($_SESSION[$guid]['absoluteURL'], 7);
        } elseif (substr($_SESSION[$guid]['absoluteURL'], 0, 8) == 'https://') {
            $absolutePathProtocol = 'https';
            $absolutePath = substr($_SESSION[$guid]['absoluteURL'], 8);
        }
        try {
            $data = array();
            $sql = 'SELECT * FROM pupilsightPerson';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
        }
        $usersTotal = $result->rowCount();
        try {
            $data = array();
            $sql = "SELECT * FROM pupilsightPerson WHERE status='Full'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
        }
        $usersFull = $result->rowCount();
        echo "<iframe style='display: none; height: 10px; width: 10px' src='http://pupilsight.in/services/tracker/tracker.php?absolutePathProtocol=".urlencode($absolutePathProtocol).'&absolutePath='.urlencode($absolutePath).'&organisationName='.urlencode($_SESSION[$guid]['organisationName']).'&type='.urlencode($_SESSION[$guid]['installType']).'&version='.urlencode($version).'&country='.$_SESSION[$guid]['country']."&usersTotal=$usersTotal&usersFull=$usersFull'></iframe>";
    }

    //Proceed!
    $page->breadcrumbs->add(__('System Settings'));

    //Check for new version of Pupilsight
    echo getCurrentVersion($guid, $connection2, $version);

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $form = Form::create('systemSettings', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/systemSettingsProcess.php');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    // SYSTEM SETTINGS
    $form->addRow()->addHeading(__('System Settings'));

    $setting = getSettingByScope($connection2, 'System', 'absoluteURL', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addURL($setting['name'])->setValue($setting['value'])->maxLength(100)->required();

    $setting = getSettingByScope($connection2, 'System', 'absolutePath', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
    $row->addTextField($setting['name'])->setValue($setting['value'])->maxLength(100)->required();
    $setting = getSettingByScope($connection2, 'System', 'systemName', true);
    $row = $form->addRow();
    $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
    $row->addTextField($setting['name'])->setValue($setting['value'])->maxLength(50)->required();
    $setting = getSettingByScope($connection2, 'System', 'pupilsightSchoolYearID', true);
    $row = $form->addRow();
    $row->addLabel('pupilsightSchoolYearID', __('Academic Year'))->description(__($setting['description']));;
    $row->addSelect('pupilsightSchoolYearID')->fromArray($academic)->required()->selected($setting['value']);
    $setting = getSettingByScope($connection2, 'System', 'smsCredits', true);
    $row = $form->addRow();
    $row->addLabel('Total SMS Credits', __('Total SMS Credits'));
    $row->addTextField('totalSmsCredits')->setValue($setting['value'])->readonly();
    $row = $form->addRow();
    $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
    $row->addTextField($setting['name'])->setValue('0')->maxLength(11);
    $setting = getSettingByScope($connection2, 'System', 'indexText', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value'])->setRows(8)->required();
    $installTypes = array(
        'Production' => __("Production"),
        'Testing' =>  __("Testing"),
        'Development' =>  __("Development")
    );        
    $setting = getSettingByScope($connection2, 'System', 'installType', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])->fromArray($installTypes)->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'System', 'cuttingEdgeCode', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextField($setting['name'])->setValue(ynExpander($guid, $setting['value']))->readonly();

    $setting = getSettingByScope($connection2, 'System', 'statsCollection', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    // ORGANISATION
    $form->addRow()->addHeading(__('Organisation Settings'));

    $setting = getSettingByScope($connection2, 'System', 'organisationName', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextField($setting['name'])->setValue($setting['value'])->maxLength(50)->required();

    $setting = getSettingByScope($connection2, 'System', 'organisationNameShort', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextField($setting['name'])->setValue($setting['value'])->maxLength(50)->required();

    $setting = getSettingByScope($connection2, 'System', 'organisationEmail', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addEmail($setting['name'])->setValue($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'System', 'organisationLogo', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextField($setting['name'])->setValue($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'System', 'organisationBackground', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextField($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'System', 'organisationAdministrator', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelectStaff($setting['name'])->selected($setting['value'])->placeholder()->required();

    $setting = getSettingByScope($connection2, 'System', 'organisationDBA', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelectStaff($setting['name'])->selected($setting['value'])->placeholder()->required();

    $setting = getSettingByScope($connection2, 'System', 'organisationAdmissions', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelectStaff($setting['name'])->selected($setting['value'])->placeholder()->required();

    $setting = getSettingByScope($connection2, 'System', 'organisationHR', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelectStaff($setting['name'])->selected($setting['value'])->placeholder()->required();

    // SECURITY SETTINGS
    $form->addRow()->addHeading(__('Security Settings'));
    $form->addRow()->addSubheading(__('Password Policy'));

    $setting = getSettingByScope($connection2, 'System', 'passwordPolicyMinLength', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])->fromArray(range(4, 12))->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'System', 'passwordPolicyAlpha', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'System', 'passwordPolicyNumeric', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'System', 'passwordPolicyNonAlphaNumeric', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $form->addRow()->addSubheading(__('Miscellaneous'));

    $setting = getSettingByScope($connection2, 'System', 'sessionDuration', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addNumber($setting['name'])->setValue($setting['value'])->minimum(1200)->maxLength(50)->required();

    // VALUE ADDED
    $form->addRow()->addHeading(__('pupilsightedu.com Value Added Services'));

    $setting = getSettingByScope($connection2, 'System', 'pupilsighteduComOrganisationName', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextField($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'System', 'pupilsighteduComOrganisationKey', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextField($setting['name'])->setValue($setting['value']);

    // LOCALISATION
    $form->addRow()->addHeading(__('Localisation'));

    $setting = getSettingByScope($connection2, 'System', 'country', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelectCountry($setting['name'])->selected($setting['value']);

    $firstDayOfTheWeekOptions = array(
        'Monday' => __("Monday"),
        'Sunday' => __("Sunday")
    );
    
    $setting = getSettingByScope($connection2, 'System', 'firstDayOfTheWeek', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])->fromArray($firstDayOfTheWeekOptions)->selected($setting['value'])->required();

    $tzlist = array_reduce(DateTimeZone::listIdentifiers(DateTimeZone::ALL), function($group, $item) {
        $group[$item] = __($item);
        return $group;
    }, array());
    $setting = getSettingByScope($connection2, 'System', 'timezone', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])->fromArray($tzlist)->selected($setting['value'])->placeholder()->required();

    $setting = getSettingByScope($connection2, 'System', 'currency', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelectCurrency($setting['name'])->selected($setting['value'])->required();

    // MISCELLANEOUS
    $form->addRow()->addHeading(__('Miscellaneous'));

    $setting = getSettingByScope($connection2, 'System', 'emailLink', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addURL($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'System', 'webLink', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addURL($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'System', 'pagination', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addNumber($setting['name'])->setValue($setting['value'])->minimum(5)->maxLength(50)->required();

    $setting = getSettingByScope($connection2, 'System', 'analytics', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value'])->setRows(8);

    $sql = "SELECT pupilsightScaleID as value, name FROM pupilsightScale WHERE active='Y' ORDER BY name";

    $setting = getSettingByScope($connection2, 'System', 'defaultAssessmentScale', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])->fromQuery($pdo, $sql)->selected($setting['value']);


    $setting = getSettingByScope($connection2, 'Finance', 'due_date_payment_validation', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']));
        $row->addCheckbox($setting['name'])->setValue('1')->checked($setting['value']);;

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
?>
<script type="text/javascript">
    $("#smsCredits").bind("keypress", function (e) {
          var keyCode = e.which ? e.which : e.keyCode
               
          if (!(keyCode >= 48 && keyCode <= 57)) {
            return false;
          }
      });
</script>