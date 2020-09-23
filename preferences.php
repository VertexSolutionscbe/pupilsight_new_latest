<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (!isset($_SESSION[$guid]["username"])) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('Preferences'));

    $return = null;
    if (isset($_GET['return'])) {
        $return = $_GET['return'];
    }

    //Deal with force reset notification
    if (isset($_GET['forceReset'])) {
        $forceReset = $_GET['forceReset'];
    } else {
        $forceReset = null;
    }
    if ($forceReset == 'Y' AND $return != 'successa') {
        $forceResetReturnMessage = '<b><u>'.__('Your account has been flagged for a password reset. You cannot continue into the system until you change your password.').'</b></u>';
        echo "<div class='alert alert-danger'>";
        echo $forceResetReturnMessage;
        echo '</div>';
    }

    $returns = array();
    $returns['errora'] = sprintf(__('Your account status could not be updated, and so you cannot continue to use the system. Please contact %1$s if you have any questions.'), "<a href='mailto:".$_SESSION[$guid]['organisationAdministratorEmail']."'>".$_SESSION[$guid]['organisationAdministratorName'].'</a>');
    $returns['successa'] = __('Your account has been successfully updated. You can now continue to use the system as per normal.');
    $returns['error4'] = __('Your request failed due to non-matching passwords.');
    $returns['error3'] = __('Your request failed due to incorrect current password.');
    $returns['error6'] = __('Your request failed because your password does not meet the minimum requirements for strength.');
    $returns['error7'] = __('Your request failed because your new password is the same as your current password.');
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, $returns);
    }

    try {
        $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
        $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }
    if ($result->rowCount() == 1) {
        $values = $result->fetch();
    }

    $form = Form::create('resetPassword', $_SESSION[$guid]['absoluteURL'].'/preferencesPasswordProcess.php');

    $form->addRow()->addHeading(__('Reset Password'));

    $policy = getPasswordPolicy($guid, $connection2);
    if ($policy != false) {
        $form->addRow()->addAlert($policy, 'warning');
    }

    $row = $form->addRow();
        $row->addLabel('password', __('Current Password'));
        $row->addPassword('password')
            ->required()
            ->maxLength(30);

    $row = $form->addRow();

        $row->addLabel('passwordNew', __('New Password'));
        $row->addPassword('passwordNew')
            ->addPasswordPolicy($pdo)
            ->addGeneratePasswordButton($form)
            ->required()
            ->maxLength(30);

    $row = $form->addRow();
        $row->addLabel('passwordConfirm', __('Confirm New Password'));
        $row->addPassword('passwordConfirm')
            ->addConfirmation('passwordNew')
            ->required()
            ->maxLength(30);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();

    if ($forceReset != 'Y') {
        $staff = false;
        foreach ($_SESSION[$guid]['pupilsightRoleIDAll'] as $role) {
            $roleCategory = getRoleCategory($role[0], $connection2);
            $staff = $staff || ($roleCategory == 'Staff');
        }

        $form = Form::create('preferences', $_SESSION[$guid]['absoluteURL'].'/preferencesProcess.php');
        $form->setFactory(DatabaseFormFactory::create($pdo));

        $form->addRow()->addHeading(__('Settings'));

        $row = $form->addRow();
            $row->addLabel('calendarFeedPersonal', __('Personal Google Calendar ID'))->description(__('Google Calendar ID for your personal calendar.').'<br/>'.__('Only enables timetable integration when logging in via Google.'));
            $password = $row->addTextField('calendarFeedPersonal');

        $personalBackground = getSettingByScope($connection2, 'User Admin', 'personalBackground');
        if ($personalBackground == 'Y') {
            $row = $form->addRow();
                $row->addLabel('personalBackground', __('Personal Background'))->description(__('Set your own custom background image.').'<br/>'.__('Please provide URL to image.'));
                $password = $row->addURL('personalBackground');
        }

        $row = $form->addRow();
            $row->addLabel('pupilsightThemeIDPersonal', __('Personal Theme'))->description(__('Override the system theme.'));
            $row->addSelectTheme('pupilsightThemeIDPersonal');


        $row = $form->addRow();
            $row->addLabel('pupilsighti18nIDPersonal', __('Personal Language'))->description(__('Override the system default language.'));
            $row->addSelectI18n('pupilsighti18nIDPersonal');

        $row = $form->addRow();
            $row->addLabel('receiveNotificationEmails', __('Receive Email Notifications?'))->description(__('Notifications can always be viewed on screen.'));
            $row->addYesNo('receiveNotificationEmails');

        if ($staff) {
            $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sql = "SELECT smartWorkflowHelp FROM pupilsightStaff WHERE pupilsightPersonID=:pupilsightPersonID";
            $result = $pdo->executeQuery($data, $sql);

            if ($result && $result->rowCount() > 0) {
                $smartWorkflowHelp = $result->fetchColumn(0);

                $row = $form->addRow();
                    $row->addLabel('smartWorkflowHelp', __('Enable Smart Workflow Help?'));
                    $row->addYesNo('smartWorkflowHelp')->selected($smartWorkflowHelp);
            }
        }

        $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();

        $form->loadAllValuesFrom($values);

        echo $form->getOutput();
    }
}
?>
