<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('create a lesson with a linked markbook column');
$I->loginAsAdmin();
$I->amOnModulePage('Planner', 'planner_add.php');

// Add Lesson ------------------------------------------------
$I->seeBreadcrumb('Add Lesson Plan');

$date = $I->grabAttributeFrom('#date', 'value');

$I->selectFromDropdown('pupilsightCourseClassID', 2);
$I->fillField('name', 'Testing Markbook');
$I->fillField('timeStart', '09:00');
$I->fillField('timeEnd', '10:00');
$I->selectOption('markbook', 'Y');
$I->click('Submit');

// Verify Linked Lesson ---------------------------------------

$I->see('Planner was successfully added', '.success');
$I->seeBreadcrumb('Add Column');

$pupilsightPlannerEntryID = $I->grabValueFromURL('pupilsightPlannerEntryID');
$I->seeInField('pupilsightPlannerEntryID', $pupilsightPlannerEntryID);
$I->seeInField('name', 'Testing Markbook');

// Add Column ------------------------------------------------

$I->fillField('description', 'Linked to Planner Lesson');
$I->selectFromDropdown('type', 2);
$I->seeInField('date', $date);
$I->selectOption('attainment', 'Y');
$I->selectOption('effort', 'N');
$I->selectOption('viewableStudents', 'N');
$I->selectOption('viewableParents', 'N');
$I->click('Submit');

// Verify Column ------------------------------------------------

$I->see('Your request was completed successfully.', '.success');
$I->click('here', 'a');
$I->seeBreadcrumb('Edit Column');

$pupilsightMarkbookColumnID = $I->grabValueFromURL('pupilsightMarkbookColumnID');
$pupilsightCourseClassID = $I->grabValueFromURL('pupilsightCourseClassID');

$I->seeInField('name', 'Testing Markbook');
$I->seeInField('description', 'Linked to Planner Lesson');
$I->seeInField('attainment', 'Y');
$I->seeInField('effort', 'N');
$I->seeInField('viewableStudents', 'N');
$I->seeInField('viewableParents', 'N');

// Delete Markbook -----------------------------------------------

$urlParams = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightMarkbookColumnID' => $pupilsightMarkbookColumnID);
$I->amOnModulePage('Markbook', 'markbook_edit_delete.php', $urlParams );

$I->click('Yes');
$I->see('Your request was completed successfully.', '.success');

// Delete Planner ------------------------------------------------

$urlParams = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'viewBy' => 'class');
$I->amOnModulePage('Planner', 'planner_delete.php', $urlParams );

$I->click('Yes');
$I->see('Your request was completed successfully.', '.success');

// Force Cleanup (for failed tests) ------------------------------

$I->deleteFromDatabase('pupilsightMarkbookColumn', ['pupilsightMarkbookColumnID' => $pupilsightMarkbookColumnID]);
$I->deleteFromDatabase('pupilsightPlannerEntry', ['pupilsightPlannerEntryID' => $pupilsightPlannerEntryID]);
