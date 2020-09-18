<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('submit and approve a family data update');
$I->loginAsParent();
$I->amOnModulePage('Data Updater', 'data_family.php');

// Select ------------------------------------------------
$I->seeBreadcrumb('Update Family Data');

$I->selectFromDropdown('pupilsightFamilyID', 2);
$I->click('Submit');

// Update ------------------------------------------------
$I->see('Update Data');

$editFormValues = array(
    'nameAddress'           => '234',
    'homeAddress'           => '234 Ficticious Ave.',
    'homeAddressDistrict'   => 'Somewhere',
    'homeAddressCountry'    => 'Antarctica',
    'languageHomePrimary'   => 'English',
    'languageHomeSecondary' => 'Latin',
);
// $I->fillField('existing', 'N');

$I->submitForm('#content form[method="post"]', $editFormValues, 'Submit');

// Confirm ------------------------------------------------
$I->seeSuccessMessage();

$pupilsightFamilyID = $I->grabValueFromURL('pupilsightFamilyID');

$I->amOnModulePage('Data Updater', 'data_family.php', ['pupilsightFamilyID' => $pupilsightFamilyID]);
$I->seeInFormFields('#content form[method="post"]', $editFormValues);

$pupilsightFamilyUpdateID = $I->grabValueFrom("input[type='hidden'][name='existing']");

$I->click('Logout', 'a');

// Accept ------------------------------------------------
$I->loginAsAdmin();
$I->amOnModulePage('Data Updater', 'data_family_manage_edit.php', array('pupilsightFamilyUpdateID' => $pupilsightFamilyUpdateID));
$I->seeBreadcrumb('Edit Request');

$I->see('234', 'td');
$I->see('234 Ficticious Ave.', 'td');
$I->see('Somewhere', 'td');
$I->see('Antarctica', 'td');
$I->see('English', 'td');
$I->see('Latin', 'td');

$I->click('Submit');
$I->seeSuccessMessage();

$pupilsightFamilyUpdateID = $I->grabValueFromURL('pupilsightFamilyUpdateID');

// Delete ------------------------------------------------
$I->amOnModulePage('Data Updater', 'data_family_manage_delete.php', array('pupilsightFamilyUpdateID' => $pupilsightFamilyUpdateID));

$I->click('Yes');
$I->seeSuccessMessage();
