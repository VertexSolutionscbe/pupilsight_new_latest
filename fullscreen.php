<?php
/*
Pupilsight, Flexible & Open School System
*/

// Pupilsight system-wide includes
require_once './pupilsight.php';

// Setup the Page and Session objects
$page = $container->get('page');
$session = $container->get('session');

// Check to see if system settings are set from databases
if (!$session->has('systemSettingsSet')) {
    getSystemSettings($guid, $connection2);
}

// If still false, show warning, otherwise display page
if (!$session->has('systemSettingsSet')) {
    exit(__('System Settings are not set: the system cannot be displayed'));
}

$address = $page->getAddress();

$page->addData([
    'isLoggedIn' => $session->has('username') && $session->has('pupilsightRoleIDCurrent'),
]);

if (empty($address)) {
    $page->addWarning(__('There is no content to display'));
} elseif ($page->isAddressValid($address) == false) {
    $page->addError(__('Illegal address detected: access denied.'));
} else {
    // Pass these globals into the script of the included file, for backwards compatibility.
    // These will be removed when we begin the process of ooifying action pages.
    $globals = [
        'guid'        => $guid,
        'pupilsight'      => $pupilsight,
        'version'     => $version,
        'pdo'         => $pdo,
        'connection2' => $connection2,
        'autoloader'  => $autoloader,
        'container'   => $container,
        'page'        => $page,
    ];

    if (is_file('./'.$address)) {
        $page->writeFromFile('./'.$address, $globals);
    } else {
        $page->writeFromFile('./error.php', $globals);
    }
}

echo $page->render('fullscreen.twig.html');
