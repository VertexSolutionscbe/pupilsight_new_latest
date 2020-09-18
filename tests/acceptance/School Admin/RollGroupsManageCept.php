<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('add, edit and delete roll groups');
$I->loginAsAdmin();
$I->amOnModulePage('School Admin', 'rollGroup_manage.php');

// Add ------------------------------------------------
$I->clickNavigation('Add');
$I->seeBreadcrumb('Add');

$addFormValues = array(
    'name'       => 'Test 1',
    'nameShort'  => 'TR1',
    'attendance' => 'Y',
    'website'    => 'http://testing.test',
);

$I->selectFromDropdown('pupilsightPersonIDTutor', 2);
$I->selectFromDropdown('pupilsightPersonIDTutor2', 3);
$I->selectFromDropdown('pupilsightPersonIDTutor3', 4);

$I->selectFromDropdown('pupilsightPersonIDEA', -2);
$I->selectFromDropdown('pupilsightPersonIDEA2', -3);
$I->selectFromDropdown('pupilsightPersonIDEA3', -4);

$I->selectFromDropdown('pupilsightSpaceID', 2);

$I->submitForm('#content form', $addFormValues, 'Submit');
$I->seeSuccessMessage();

$pupilsightSchoolYearID = $I->grabValueFromURL('pupilsightSchoolYearID');
$pupilsightRollGroupID = $I->grabEditIDFromURL();

// Edit ------------------------------------------------
$I->amOnModulePage('School Admin', 'rollGroup_manage_edit.php', array('pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID));
$I->seeBreadcrumb('Edit');

$I->seeInFormFields('#content form', $addFormValues);

$editFormValues = array(
    'name'       => 'Test 2',
    'nameShort'  => 'TR2',
    'attendance' => 'N',
    'website'    => '',
);

$I->submitForm('#content form', $editFormValues, 'Submit');
$I->seeSuccessMessage();

// Delete ------------------------------------------------
$I->amOnModulePage('School Admin', 'rollGroup_manage_delete.php', array('pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID));

$I->click('Yes');
$I->seeSuccessMessage();
