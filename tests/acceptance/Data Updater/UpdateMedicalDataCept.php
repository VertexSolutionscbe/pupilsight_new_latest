<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('submit and approve a medical data update');
$I->loginAsAdmin();
$I->amOnModulePage('Data Updater', 'data_medical.php');

// Select ------------------------------------------------
$I->seeBreadcrumb('Update Medical Data');

$I->selectFromDropdown('pupilsightPersonID', 2);
$I->click('Submit');

// Update ------------------------------------------------
$I->see('Update Data');

$editFormValues = array(
    'bloodType'                 => 'AB+',
    'longTermMedication'        => 'Y',
    'longTermMedicationDetails' => 'Test',
    'tetanusWithin10Years'      => 'Y',
);

$I->submitForm('#content form[method="post"]', $editFormValues, 'Submit');

// Confirm ------------------------------------------------
$I->seeSuccessMessage();

$pupilsightPersonID = $I->grabValueFromURL('pupilsightPersonID');

$I->amOnModulePage('Data Updater', 'data_medical.php', ['pupilsightPersonID' => $pupilsightPersonID]);
$I->seeInFormFields('#content form[method="post"]', $editFormValues);

$pupilsightPersonMedicalUpdateID = $I->grabValueFrom("input[type='hidden'][name='existing']");

// Accept ------------------------------------------------
$I->amOnModulePage('Data Updater', 'data_medical_manage_edit.php', array('pupilsightPersonMedicalUpdateID' => $pupilsightPersonMedicalUpdateID));
$I->seeBreadcrumb('Edit Request');

$I->see('AB+', 'td');
$I->see('Y', 'td');
$I->see('Test', 'td');
$I->see('Y', 'td');

$I->click('Submit');
$I->seeSuccessMessage();

$pupilsightPersonMedicalUpdateID = $I->grabValueFromURL('pupilsightPersonMedicalUpdateID');

// Delete ------------------------------------------------
$I->amOnModulePage('Data Updater', 'data_medical_manage_delete.php', array('pupilsightPersonMedicalUpdateID' => $pupilsightPersonMedicalUpdateID));

$I->click('Yes');
$I->seeSuccessMessage();
