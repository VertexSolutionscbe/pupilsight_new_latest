<?php 

$I = new AcceptanceTester($scenario);
$I->wantTo('login to Pupilsight as a parent');
$I->loginAsParent();

// Logged In
$I->see('Logout', 'a');
