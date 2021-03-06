<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('add, edit and delete a school year');
$I->loginAsAdmin();
$I->amOnModulePage('School Admin', 'schoolYear_manage.php');

// Add ------------------------------------------------
$I->clickNavigation('Add');
$I->seeBreadcrumb('Add School Year');

$I->fillField('name', '2050-2051');
$I->selectOption('status', 'Upcoming');
$I->fillField('sequenceNumber', 100);
$I->fillField('firstDay', '01/01/2050');
$I->fillField('lastDay', '31/12/2050');
$I->click('Submit');

$I->see('Your request was completed successfully.', '.success');

$pupilsightSchoolYearID = $I->grabEditIDFromURL();

// Edit ------------------------------------------------
$I->amOnModulePage('School Admin', 'schoolYear_manage_edit.php', array('pupilsightSchoolYearID' => $pupilsightSchoolYearID));
$I->seeBreadcrumb('Edit School Year');

$I->fillField('name', '1920-1921');
$I->selectOption('status', 'Past');
$I->fillField('sequenceNumber', 90);
$I->fillField('firstDay', '01/01/1920');
$I->fillField('lastDay', '31/12/1920');
$I->click('Submit');

$I->seeInField('name', '1920-1921');
$I->seeOptionIsSelected('status', 'Past');
$I->seeInField('sequenceNumber', 90);
$I->seeInField('firstDay', '01/01/1920');
$I->seeInField('lastDay', '31/12/1920');

// Delete ------------------------------------------------
$I->amOnModulePage('School Admin', 'schoolYear_manage_delete.php', array('pupilsightSchoolYearID' => $pupilsightSchoolYearID));

$I->click('Yes');
$I->see('Your request was completed successfully.', '.success');
