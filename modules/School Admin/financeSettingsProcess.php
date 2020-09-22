<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/financeSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/financeSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $email = $_POST['email'];
    $financeOnlinePaymentEnabled = $_POST['financeOnlinePaymentEnabled'];
    $financeOnlinePaymentThreshold = (isset($_POST['financeOnlinePaymentThreshold'])) ? $_POST['financeOnlinePaymentThreshold'] : null;
    $invoiceeNameStyle = $_POST['invoiceeNameStyle'];
    $invoiceText = $_POST['invoiceText'];
    $invoiceNotes = $_POST['invoiceNotes'];
    $invoiceNumber = $_POST['invoiceNumber'];
    $receiptText = $_POST['receiptText'];
    $receiptNotes = $_POST['receiptNotes'];
    $hideItemisation = $_POST['hideItemisation'];
    $reminder1Text = $_POST['reminder1Text'];
    $reminder2Text = $_POST['reminder2Text'];
    $reminder3Text = $_POST['reminder3Text'];
    $budgetCategories = $_POST['budgetCategories'];
    $expenseApprovalType = $_POST['expenseApprovalType'];
    $budgetLevelExpenseApproval = $_POST['budgetLevelExpenseApproval'];
    $expenseRequestTemplate = $_POST['expenseRequestTemplate'];
    $allowExpenseAdd = $_POST['allowExpenseAdd'];
    $purchasingOfficer = $_POST['purchasingOfficer'];
    $reimbursementOfficer = $_POST['reimbursementOfficer'];

    if ($email == '' or $financeOnlinePaymentEnabled == '' or $invoiceeNameStyle == '' or $invoiceNumber == '' or $hideItemisation == '' or $budgetCategories == '' or $expenseApprovalType == '' or $budgetLevelExpenseApproval == '' or $allowExpenseAdd == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Write to database
        $fail = false;

        try {
            $data = array('value' => $email);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='email'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $financeOnlinePaymentEnabled);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='financeOnlinePaymentEnabled'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $financeOnlinePaymentThreshold);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='financeOnlinePaymentThreshold'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $invoiceeNameStyle);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='invoiceeNameStyle'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $invoiceText);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='invoiceText'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $invoiceNotes);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='invoiceNotes'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $invoiceNumber);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='invoiceNumber'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $receiptText);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='receiptText'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $receiptNotes);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='receiptNotes'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $hideItemisation);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='hideItemisation'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $reminder1Text);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='reminder1Text'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $reminder2Text);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='reminder2Text'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $reminder3Text);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='reminder3Text'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $budgetCategories);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='budgetCategories'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $expenseApprovalType);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='expenseApprovalType'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $budgetLevelExpenseApproval);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='budgetLevelExpenseApproval'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $expenseRequestTemplate);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='expenseRequestTemplate'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $allowExpenseAdd);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='allowExpenseAdd'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $purchasingOfficer);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='purchasingOfficer'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $reimbursementOfficer);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Finance' AND name='reimbursementOfficer'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
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
