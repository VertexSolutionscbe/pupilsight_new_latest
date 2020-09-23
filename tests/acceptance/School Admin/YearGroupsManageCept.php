<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('add, edit and delete year groups');
$I->loginAsAdmin();
$I->amOnModulePage('School Admin', 'yearGroup_manage.php');

// Add ------------------------------------------------
$I->clickNavigation('Add');
$I->seeBreadcrumb('Add Year Group');

$addFormValues = array(
    'name'           => 'Test One',
    'nameShort'      => 'TY1',
    'sequenceNumber' => '900',
);

$I->submitForm('#content form', $addFormValues, 'Submit');
$I->seeSuccessMessage();

$pupilsightYearGroupID = $I->grabEditIDFromURL();

// Edit ------------------------------------------------
$I->amOnModulePage('School Admin', 'yearGroup_manage_edit.php', array('pupilsightYearGroupID' => $pupilsightYearGroupID));
$I->seeBreadcrumb('Edit Year Group');

$I->seeInFormFields('#content form', $addFormValues);

$editFormValues = array(
    'name'           => 'Test Two',
    'nameShort'      => 'TY2',
    'sequenceNumber' => '999',
);

$I->submitForm('#content form', $editFormValues, 'Submit');
$I->seeSuccessMessage();

// Delete ------------------------------------------------
$I->amOnModulePage('School Admin', 'yearGroup_manage_delete.php', array('pupilsightYearGroupID' => $pupilsightYearGroupID));

$I->click('Yes');
$I->seeSuccessMessage();
