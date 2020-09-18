<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('add, edit and delete courses and classes');
$I->loginAsAdmin();
$I->amOnModulePage('Timetable Admin', 'course_manage.php');

// Add ------------------------------------------------
$I->clickNavigation('Add');
$I->seeBreadcrumb('Add Course');

$addFormValues = array(
    'name'               => 'Test Course',
    'nameShort'          => 'TEST01',
    'orderBy'            => '1',
    'description'        => 'This is a test.',
    'map'                => 'Y',
);

$I->selectFromDropdown('pupilsightDepartmentID', 2);

$I->submitForm('#content form', $addFormValues, 'Submit');
$I->see('Your request was completed successfully.', '.success');

$pupilsightCourseID = $I->grabEditIDFromURL();
$pupilsightSchoolYearID = $I->grabValueFromURL('pupilsightSchoolYearID');

// Edit ------------------------------------------------
$I->amOnModulePage('Timetable Admin', 'course_manage_edit.php', array('pupilsightCourseID' => $pupilsightCourseID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID));
$I->seeBreadcrumb('Edit Course');

$I->seeInFormFields('#content form', $addFormValues);

$editFormValues = array(
    'name'               => 'Test Course Too',
    'nameShort'          => 'TEST02',
    'orderBy'            => '2',
    'description'        => 'This is also a test.',
    'map'                => 'N',
);

$I->selectFromDropdown('pupilsightDepartmentID', 3);

$I->submitForm('#content form', $editFormValues, 'Submit');
$I->see('Your request was completed successfully.', '.success');

// Add Class ---------------------------------------------
$I->clickNavigation('Add');
$I->seeBreadcrumb('Add Class');

$addFormValues = array(
    'name'       => 'C-1',
    'nameShort'  => 'C-1',
    'reportable' => 'Y',
    'attendance' => 'Y',
);

$I->submitForm('#content form', $addFormValues, 'Submit');
$I->see('Your request was completed successfully.', '.success');

$pupilsightCourseClassID = $I->grabEditIDFromURL();

// Edit Class ---------------------------------------------
$I->amOnModulePage('Timetable Admin', 'course_manage_class_edit.php', array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightCourseID' => $pupilsightCourseID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID));
$I->seeBreadcrumb('Edit Class');

$I->seeInFormFields('#content form', $addFormValues);

$editFormValues = array(
    'name'       => 'C-2',
    'nameShort'  => 'C-2',
    'reportable' => 'N',
    'attendance' => 'N',
);

$I->submitForm('#content form', $editFormValues, 'Submit');
$I->see('Your request was completed successfully.', '.success');

// Delete Class -------------------------------------------
$I->amOnModulePage('Timetable Admin', 'course_manage_class_delete.php', array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightCourseID' => $pupilsightCourseID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID));

$I->click('Yes');
$I->see('Your request was completed successfully.', '.success');

// Delete ------------------------------------------------
$I->amOnModulePage('Timetable Admin', 'course_manage_delete.php', array('pupilsightCourseID' => $pupilsightCourseID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'search' => ''));

$I->click('Yes');
$I->see('Your request was completed successfully.', '.success');

