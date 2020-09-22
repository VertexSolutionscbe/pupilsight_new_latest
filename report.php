<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;

// Pupilsight system-wide include
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

$page->addHeadExtra($session->get('analytics'));
$page->stylesheets->add('theme-dev', 'resources/assets/css/theme.min.css');
$page->stylesheets->add('core', 'resources/assets/css/core.min.css', ['weight' => 10]);

$page->addData([
    'isLoggedIn'                     => $session->has('username') && $session->has('pupilsightRoleIDCurrent'),
    'username'                       => $session->get('username'),
    'pupilsightThemeName'                => $session->get('pupilsightThemeName'),
    'organisationName'               => $session->get('organisationName'),
    'organisationNameShort'          => $session->get('organisationNameShort'),
    'organisationAdministratorName'  => $session->get('organisationAdministratorName'),
    'organisationAdministratorEmail' => $session->get('organisationAdministratorEmail'),
    'organisationLogo'               => $session->get('organisationLogo'),
    'time'                           => Format::time(date('H:i:s')),
    'date'                           => Format::date(date('Y-m-d')),
    'rightToLeft'                    => $session->get('i18n')['rtl'] == 'Y',
]);

echo $page->render('report.twig.html');
