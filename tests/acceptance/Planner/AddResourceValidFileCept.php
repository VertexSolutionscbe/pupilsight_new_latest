<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('add a new Resource with a valid filetype');
$I->loginAsAdmin();
$I->amOnModulePage('Planner', 'resources_manage_add.php');

// Add ------------------------------------------------
$I->seeBreadcrumb('Add Resource');

$I->selectOption('type', 'File');
$I->attachFile('file', 'image.png');
$I->fillField('name', 'Valid Upload');
$I->fillField('tags', 'Photo');
$I->selectOption('category', 'Photo');
$I->click('Submit');

$I->see('Your request was completed successfully.', '.success');

$pupilsightResourceID = $I->grabEditIDFromURL();

// Delete ------------------------------------------------
$filepath = $I->grabFromDatabase('pupilsightResource', 'content', ['pupilsightResourceID' => $pupilsightResourceID]);

$I->deleteFile('../'.$filepath);
$I->deleteFromDatabase('pupilsightResource', ['pupilsightResourceID' => $pupilsightResourceID]);
