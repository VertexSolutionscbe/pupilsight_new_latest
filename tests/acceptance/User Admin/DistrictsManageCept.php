<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('add, edit and delete a district');
$I->loginAsAdmin();
$I->amOnModulePage('User Admin', 'district_manage.php');

// Add ------------------------------------------------
$I->clickNavigation('Add');
$I->seeBreadcrumb('Add District');

$addFormValues = array(
    'name' => 'Test District',
);

$I->submitForm('#content form', $addFormValues, 'Submit');
$I->seeSuccessMessage();

$pupilsightDistrictID = $I->grabEditIDFromURL();

// Edit ------------------------------------------------
$I->amOnModulePage('User Admin', 'district_manage_edit.php', array('pupilsightDistrictID' => $pupilsightDistrictID));
$I->seeBreadcrumb('Edit District');

$I->seeInFormFields('#content form', $addFormValues);

$editFormValues = array(
    'name' => 'Test District Too?',
);

$I->submitForm('#content form', $editFormValues, 'Submit');
$I->seeSuccessMessage();

// Delete ------------------------------------------------
$I->amOnModulePage('User Admin', 'district_manage_delete.php', array('pupilsightDistrictID' => $pupilsightDistrictID));

$I->click('Yes');
$I->seeSuccessMessage();
