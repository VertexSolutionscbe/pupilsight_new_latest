<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

$page->breadcrumbs->add(__('Password Reset'));

$step = 1;
if (isset($_GET['step'])) {
    if ($_GET['step'] == 2) {
        $step = 2;
    }
}

if ($step == 1) {
    ?>
    <p>
        <?php echo sprintf(__('Enter your %1$s username, or the email address you have listed in the system, and press submit: a unique password reset link will be emailed to you.'), $_SESSION[$guid]['systemName']); ?>
    </p>
    <?php
    $returns = array();
    $returns['error0'] = __('Email address not set.');
    $returns['error4'] = __('Your request failed due to incorrect, non-existent or non-unique email address or username.');
    $returns['error3'] = __('Failed to send update email.');
    $returns['error5'] = __('Your request failed due to non-matching passwords.');
    $returns['error6'] = __('Your request failed because your password does not meet the minimum requirements for strength.');
    $returns['error7'] = __('Your request failed because your new password is the same as your current password.');
    $returns['fail2'] = __('You do not have sufficient privileges to login.');
    $returns['fail9'] = __('Your primary role does not support the ability to log into the specified year.');
    $returns['success0'] = __('Password reset request successfully initiated, please check your email.');
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, $returns);
    }

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/passwordResetProcess.php?step=1');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
        $row->addLabel('email', __('Username/Email'));
        $row->addTextField('email')->maxLength(255)->required();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
else {
    // Sanitize the whole $_GET array
    $validator = new \Pupilsight\Data\Validator();
    $_GET = $validator->sanitize($_GET);

    //Get URL parameters
    $input = (!empty($_GET['input']) ? $_GET['input'] : null);
    $key = (!empty($_GET['key']) ? $_GET['key'] : null);
    $pupilsightPersonResetID = (!empty($_GET['pupilsightPersonResetID']) ? $_GET['pupilsightPersonResetID'] : null);

    //Verify authenticity of this request and check it is fresh (within 48 hours)
    try {
        $data = array('key' => $key, 'pupilsightPersonResetID' => $pupilsightPersonResetID);
        $sql = "SELECT * FROM pupilsightPersonReset WHERE `key`=:key AND pupilsightPersonResetID=:pupilsightPersonResetID AND (timestamp > DATE_SUB(now(), INTERVAL 2 DAY))";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    if ($result->rowCount() != 1) {
        echo "<div class='alert alert-danger'>";
        echo __('Your reset request is invalid: you may not proceed.');
        echo '</div>';
    } else {
        echo "<div class='alert alert-sucess'>";
        echo __('Your reset request is valid: you may proceed.');
        echo '</div>';

        $form = Form::create('action', $_SESSION[$guid]['absoluteURL']."/passwordResetProcess.php?input=$input&step=2&pupilsightPersonResetID=$pupilsightPersonResetID&key=$key");

        $form->setClass('smallIntBorder fullWidth');
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

        $form->addRow()->addHeading(__('Reset Password'));

        $policy = getPasswordPolicy($guid, $connection2);
        if ($policy != false) {
            $form->addRow()->addAlert($policy, 'warning');
        }

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
    }
}
