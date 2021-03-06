<?php
/*
Pupilsight, Flexible & Open School System

*/

use Pupilsight\Domain\System\ModuleGateway;
use Pupilsight\Domain\DataUpdater\DataUpdaterGateway;
use Pupilsight\Domain\Students\StudentGateway;
use Pupilsight\Domain\User\UserGateway;

/**
 * BOOTSTRAP
 *
 * The bootstrapping process creates the essential variables and services for
 * Pupilsight. These are required for all scripts: page views, CLI and API.
 */
// Pupilsight system-wide include
require_once './pupilsight.php';

// Module include: Messenger has a bug where files have been relying on these
// functions because this file was included via getNotificationTray()
// TODO: Fix that :)
require_once './modules/Messenger/moduleFunctions.php';

// Setup the Page and Session objects
$page = $container->get('page');
$session = $container->get('session');

$isLoggedIn = $session->has('username') && $session->has('pupilsightRoleIDCurrent');

/**
 * MODULE BREADCRUMBS
 */
if ($isLoggedIn && $module = $page->getModule()) {
    $page->breadcrumbs->setBaseURL('index.php?q=/modules/'.$module->name.'/');
    $page->breadcrumbs->add(__($module->name), $module->entryURL);
}

/**
 * CACHE & INITIAL PAGE LOAD
 *
 * The 'pageLoads' value is used to run code when the user first logs in, and
 * also to reload cached content based on the $caching value in config.php
 *
 * TODO: When we implement routing, these can become part of the HTTP middleware.
 */
$session->set('pageLoads', !$session->exists('pageLoads') ? 0 : $session->get('pageLoads', -1)+1);

$cacheLoad = true;
$caching = $pupilsight->getConfig('caching');
if (!empty($caching) && is_numeric($caching)) {
    $cacheLoad = $session->get('pageLoads') % intval($caching) == 0;
}

/**
 * SYSTEM SETTINGS
 *
 * Checks to see if system settings are set from database. If not, tries to
 * load them in. If this fails, something horrible has gone wrong ...
 *
 * TODO: Move this to the Session creation logic.
 * TODO: Handle the exit() case with a pre-defined error template.
 */
if (!$session->has('systemSettingsSet')) {
    getSystemSettings($guid, $connection2);

    if (!$session->has('systemSettingsSet')) {
        exit(__('System Settings are not set: the system cannot be displayed'));
    }
}

/**
 * USER REDIRECTS
 *
 * TODO: When we implement routing, these can become part of the HTTP middleware.
 */

// Check for force password reset flag
if ($session->has('passwordForceReset')) {
    if ($session->get('passwordForceReset') == 'Y' and $session->get('address') != 'preferences.php') {
        $URL = $session->get('absoluteURL').'/index.php?q=preferences.php';
        $URL = $URL.'&forceReset=Y';
        header("Location: {$URL}");
        exit();
    }
}

// Redirects after login
if ($session->get('pageLoads') == 0 && !$session->has('address')) { // First page load, so proceed

    if ($session->has('username')) { // Are we logged in?
        $roleCategory = getRoleCategory($session->get('pupilsightRoleIDCurrent'), $connection2);

        // Deal with attendance self-registration redirect
        // Are we a student?
        if ($roleCategory == 'Student') {
            // Can we self register?
            if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_studentSelfRegister.php')) {
                // Check to see if student is on site
                $studentSelfRegistrationIPAddresses = getSettingByScope(
                    $connection2,
                    'Attendance',
                    'studentSelfRegistrationIPAddresses'
                );
                $realIP = getIPAddress();
                if ($studentSelfRegistrationIPAddresses != '' && !is_null($studentSelfRegistrationIPAddresses)) {
                    $inRange = false ;
                    foreach (explode(',', $studentSelfRegistrationIPAddresses) as $ipAddress) {
                        if (trim($ipAddress) == $realIP) {
                            $inRange = true ;
                        }
                    }
                    if ($inRange) {
                        $currentDate = date('Y-m-d');
                        if (isSchoolOpen($guid, $currentDate, $connection2, true)) { // Is school open today
                            // Check for existence of records today
                            try {
                                $data = array('pupilsightPersonID' => $session->get('pupilsightPersonID'), 'date' => $currentDate);
                                $sql = "SELECT type FROM pupilsightAttendanceLogPerson WHERE pupilsightPersonID=:pupilsightPersonID AND date=:date ORDER BY timestampTaken DESC";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $page->addError($e->getMessage());
                            }

                            if ($result->rowCount() == 0) {
                                // No registration yet
                                // Redirect!
                                $URL = $session->get('absoluteURL').
                                    '/index.php?q=/modules/Attendance'.
                                    '/attendance_studentSelfRegister.php'.
                                    '&redirect=true';
                                $session->set('pageLoads', null);
                                header("Location: {$URL}");
                                exit;
                            }
                        }
                    }
                }
            }
        }

        // Deal with Data Updater redirect (if required updates are enabled)
        $requiredUpdates = getSettingByScope($connection2, 'Data Updater', 'requiredUpdates');
        if ($requiredUpdates == 'Y') {
            if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_updates.php')) { // Can we update data?
                $redirectByRoleCategory = getSettingByScope(
                    $connection2,
                    'Data Updater',
                    'redirectByRoleCategory'
                );
                $redirectByRoleCategory = explode(',', $redirectByRoleCategory);

                // Are we the right role category?
                if (in_array($roleCategory, $redirectByRoleCategory)) {
                    $gateway = new DataUpdaterGateway($pdo);

                    $updatesRequiredCount = $gateway->countAllRequiredUpdatesByPerson($session->get('pupilsightPersonID'));
                    
                    if ($updatesRequiredCount > 0) {
                        $URL = $session->get('absoluteURL').'/index.php?q=/modules/Data Updater/data_updates.php&redirect=true';
                        $session->set('pageLoads', null);
                        header("Location: {$URL}");
                        exit;
                    }
                }
            }
        }
    }
}

/**
 * SIDEBAR SETUP
 *
 * TODO: move all of the sidebar session variables to the $page->addSidebarExtra() method.
 */

// Set sidebar extra content values via Session.
$session->set('sidebarExtra', '');
$session->set('sidebarExtraPosition', 'top');

// Check the current Action 'entrySidebar' to see if we should display a sidebar
$showSidebar = $page->getAction()
    ? $page->getAction()['entrySidebar'] != 'N'
    : true;

// Override showSidebar if the URL 'sidebar' param is explicitly set
if (!empty($_GET['sidebar'])) {
    $showSidebar = strtolower($_GET['sidebar']) !== 'false';
}

/**
 * SESSION TIMEOUT
 *
 * Set session duration, which will be passed via JS config to setup the
 * session timeout. Ensures a minimum session duration of 1200.
 */
$sessionDuration = -1;
if ($isLoggedIn) {
    $sessionDuration = $session->get('sessionDuration');
    $sessionDuration = max(intval($sessionDuration), 1200);
}

/**
 * LOCALE
 *
 * Sets the i18n locale for jQuery UI DatePicker (if the file exists, otherwise
 * falls back to en-GB)
 */
$localeCode = str_replace('_', '-', $session->get('i18n')['code']);
$localeCodeShort = substr($session->get('i18n')['code'], 0, 2);
$localePath = $session->get('absolutePath').'/lib/jquery-ui/i18n/jquery.ui.datepicker-%1$s.js';

$datepickerLocale = 'en-GB';
if ($localeCode === 'en-US' || is_file(sprintf($localePath, $localeCode))) {
    $datepickerLocale = $localeCode;
} elseif (is_file(sprintf($localePath, $localeCodeShort))) {
    $datepickerLocale = $localeCodeShort;
}

// Allow the URL to override system default from the i18l param
if (!empty($_GET['i18n']) && $pupilsight->locale->getLocale() != $_GET['i18n']) {
    $data = ['code' => $_GET['i18n']];
    $sql = "SELECT * FROM pupilsighti18n WHERE code=:code LIMIT 1";

    if ($result = $pdo->selectOne($sql, $data)) {
        setLanguageSession($guid, $result, false);
        $pupilsight->locale->setLocale($_GET['i18n']);
        $pupilsight->locale->setTextDomain($pdo);
        $cacheLoad = true;
    }
}

/**
 * JAVASCRIPT
 *
 * The config array defines a set of PHP values that are encoded and passed to
 * the setup.js file, which handles initialization of js libraries.
 */
$javascriptConfig = [
    'config' => [
        'datepicker' => [
            'locale' => $datepickerLocale,
        ],
        'thickbox' => [
            'pathToImage' => $session->get('absoluteURL').'/lib/thickbox/loadingAnimation.gif',
        ],
        'tinymce' => [
            'valid_elements' => getSettingByScope($connection2, 'System', 'allowableHTML'),
        ],
        'sessionTimeout' => [
            'sessionDuration' => $sessionDuration,
            'message' => __('Your session is about to expire: you will be logged out shortly.'),
        ]
    ],
];

/**
 * There are currently a handful of scripts that must be in the page <HEAD>.
 * Otherwise, the preference is to add javascript to the 'foot' at the bottom
 * of the page, which speeds up rendering by deferring their execution until
 * after all content has loaded.
 */

// Set page scripts: head
$page->scripts->addMultiple([
    'lv'             => 'lib/LiveValidation/livevalidation_standalone.compressed.js',
    'jquery'         => 'lib/jquery/jquery.js',
    'jquery-migrate' => 'lib/jquery/jquery-migrate.min.js',
    'jquery-ui'      => 'lib/jquery-ui/js/jquery-ui.min.js',
    'jquery-time'    => 'lib/jquery-timepicker/jquery.timepicker.min.js',
    'jquery-chained' => 'lib/chained/jquery.chained.min.js',
    'core'           => 'resources/assets/js/core.min.js',
], ['context' => 'head']);

// Set page scripts: foot - jquery
$page->scripts->addMultiple([
    'jquery-latex'    => 'lib/jquery-jslatex/jquery.jslatex.js',
    'jquery-form'     => 'lib/jquery-form/jquery.form.js',
    //This sets the default for en-US, or changes for none en-US
    'jquery-date'     => $datepickerLocale === 'en-US' ? '' : 'lib/jquery-ui/i18n/jquery.ui.datepicker-'.$datepickerLocale.'.js',
    'jquery-autosize' => 'lib/jquery-autosize/jquery.autosize.min.js',
    'jquery-timeout'  => 'lib/jquery-sessionTimeout/jquery.sessionTimeout.min.js',
    'jquery-token'    => 'lib/jquery-tokeninput/src/jquery.tokeninput.js',
], ['context' => 'foot']);

// Set page scripts: foot - misc
$thickboxInline = 'var tb_pathToImage="'.$session->get('absoluteURL').'/lib/thickbox/loadingAnimation.gif";';
$page->scripts->add('thickboxi', $thickboxInline, ['type' => 'inline']);
$page->scripts->addMultiple([
    'thickbox' => 'lib/thickbox/thickbox-compressed.js',
    'tinymce'  => 'lib/tinymce/tinymce.min.js',
], ['context' => 'foot']);

// Set page scripts: foot - core
$page->scripts->add('core-config', 'window.Pupilsight = '.json_encode($javascriptConfig).';', ['type' => 'inline']);
$page->scripts->add('core-setup', 'resources/assets/js/setup.js');

// Register scripts available to the core, but not included by default
$page->scripts->register('chart', 'lib/Chart.js/2.0/Chart.bundle.min.js', ['context' => 'head']);

// Set system analytics code from session cache
$page->addHeadExtra($session->get('analytics'));

/**
 * STYLESHEETS & CSS
 */
$page->stylesheets->addMultiple([
    'jquery-ui'    => 'lib/jquery-ui/css/blitzer/jquery-ui.css',
    'jquery-time'  => 'lib/jquery-timepicker/jquery.timepicker.css',
    'thickbox'     => 'lib/thickbox/thickbox.css',
], ['weight' => -1]);

// Add right-to-left stylesheet
if ($session->get('i18n')['rtl'] == 'Y') {
    $page->theme->stylesheets->add('theme-rtl', '/themes/'.$session->get('pupilsightThemeName').'/css/main_rtl.css', ['weight' => 1]);
}

// Set personal, organisational or theme background     
if (getSettingByScope($connection2, 'User Admin', 'personalBackground') == 'Y' && $session->has('personalBackground')) {
    $backgroundImage = htmlPrep($session->get('personalBackground'));
    $backgroundScroll = 'repeat scroll center top';
} else if ($session->has('organisationBackground')) {
    $backgroundImage = $session->get('absoluteURL').'/'.$session->get('organisationBackground');
    $backgroundScroll = 'repeat fixed center top';
} else {
    $backgroundImage = $session->get('absoluteURL').'/themes/'.$session->get('pupilsightThemeName').'/img/backgroundPage.jpg';
    $backgroundScroll = 'repeat fixed center top';
}
$backgroundImage = "";
/*
$page->stylesheets->add(
    'personal-background',
    'body { background: url('.$backgroundImage.') '.$backgroundScroll.' #626cd3!important; }',
    ['type' => 'inline']
);
*/

$page->stylesheets->add('theme-dev', 'resources/assets/css/theme.min.css');
$page->stylesheets->add('core', 'resources/assets/css/core.min.css', ['weight' => 10]);

/**
 * USER CONFIGURATION
 *
 * This should be moved to a one-time process to run after login, which can be
 * handled by HTTP middleware.
 */

// Try to auto-set user's calendar feed if not set already
if ($session->exists('calendarFeedPersonal') && $session->exists('googleAPIAccessToken')) {
    if (!$session->has('calendarFeedPersonal') && $session->has('googleAPIAccessToken')) {
        $service = $container->get('Google_Service_Calendar');
        try {
            $calendar = $service->calendars->get('primary');
        } catch (\Google_Service_Exception $e) {}

        if (!empty($calendar['id'])) {
            $session->set('calendarFeedPersonal', $calendar['id']);
            $container->get(UserGateway::class)->update($session->get('pupilsightPersonID'), [
                'calendarFeedPersonal' => $calendar['id'],
            ]);
        }
    }
}

// Get house logo and set session variable, only on first load after login (for performance)
if ($session->get('pageLoads') == 0 and $session->has('username') and !$session->has('pupilsightHouseIDLogo')) {
    $dataHouse = array('pupilsightHouseID' => $session->get('pupilsightHouseID'));
    $sqlHouse = 'SELECT logo, name FROM pupilsightHouse
        WHERE pupilsightHouseID=:pupilsightHouseID';
    $house = $pdo->selectOne($sqlHouse, $dataHouse);

    if (!empty($house)) {
        $session->set('pupilsightHouseIDLogo', $house['logo']);
        $session->set('pupilsightHouseIDName', $house['name']);
    }
}

// Show warning if not in the current school year
// TODO: When we implement routing, these can become part of the HTTP middleware.
if ($isLoggedIn) {
    if ($session->get('pupilsightSchoolYearID') != $session->get('pupilsightSchoolYearIDCurrent')) {
        $page->addWarning('<b><u>'.sprintf(__('Warning: you are logged into the system in Academic Year %1$s, which is not the current year.'), $session->get('pupilsightSchoolYearName')).'</b></u>'.__('Your data may not look quite right (for example, students who have left the school will not appear in previous years), but you should be able to edit information from other years which is not available in the current year.'));
    }
}

/**
 * RETURN PROCESS
 *
 * Adds an alert to the index based on the URL 'return' parameter.
 *
 * TODO: Remove all returnProcess() from pages. We could add a method to the
 * Page class to allow them to register custom messages, or use Session flash
 * to add the message directly from the Process pages.
 */
if (!$session->has('address') && !empty($_GET['return'])) {
    $customReturns = [
        'success1' => __('Password reset was successful: you may now log in.')
    ];

    if ($alert = returnProcessGetAlert($_GET['return'], '', $customReturns)) {
        $page->addAlert($alert['context'], $alert['text']);
    }
}

/**
 * MENU ITEMS & FAST FINDER
 *
 * TODO: Move this somewhere more sensible.
 */
if ($isLoggedIn) {
    if ($cacheLoad || !$session->has('fastFinder')) {
        $templateData = getFastFinder($connection2, $guid);
        $templateData['enrolmentCount'] = $container->get(StudentGateway::class)->getStudentEnrolmentCount($session->get('pupilsightSchoolYearID'));

        $fastFinder = $page->fetchFromTemplate('finder.twig.html', $templateData);
        $session->set('fastFinder', $fastFinder);
    }

    $moduleGateway = $container->get(ModuleGateway::class);

    if ($cacheLoad || !$session->has('menuMainItems')) {
        $menuMainItems = $moduleGateway->selectModulesByRole($session->get('pupilsightRoleIDCurrent'))->fetchGrouped();

        foreach ($menuMainItems as $category => &$items) {
            foreach ($items as &$item) {
                $modulePath = '/modules/'.$item['name'];
                $entryURL = isActionAccessible($guid, $connection2, $modulePath.'/'.$item['entryURL'])
                    ? $item['entryURL']
                    : $item['alternateEntryURL'];

                $item['url'] = $session->get('absoluteURL').'/index.php?q='.$modulePath.'/'.$entryURL;
            }
        }

        $session->set('menuMainItems', $menuMainItems);
    }

    if ($page->getModule()) {
        $currentModule = $page->getModule()->getName();
        $menuModule = $session->get('menuModuleName');
        
        if ($cacheLoad || !$session->has('menuModuleItems') || $currentModule != $menuModule) {
            $menuModuleItems = $moduleGateway->selectModuleActionsByRole($page->getModule()->getID(), $session->get('pupilsightRoleIDCurrent'))->fetchGrouped();
        } else {
            $menuModuleItems = $session->get('menuModuleItems');
        }
        
        // Update the menu items to indicate the current active action
        foreach ($menuModuleItems as $category => &$items) {
            foreach ($items as &$item) {
                $urlList = array_map('trim', explode(',', $item['URLList']));
                $item['active'] = in_array($session->get('action'), $urlList);
                $item['url'] = $session->get('absoluteURL').'/index.php?q=/modules/'
                        .$item['moduleName'].'/'.$item['entryURL'];
            }
        }

        $session->set('menuModuleItems', $menuModuleItems);
        $session->set('menuModuleName', $currentModule);
    } else {
        $session->forget(['menuModuleItems', 'menuModuleName']);
    }
}

/**
 * TEMPLATE DATA
 *
 * These values are merged with the Page class settings & content, then passed
 * into the template engine for rendering. They're a work in progress, but once
 * they're more finalized we can document them for theme developers.
 */
$header = $container->get(Pupilsight\UI\Components\Header::class);

$page->addData([
    'isLoggedIn'        => $isLoggedIn,
    'pupilsightThemeName'   => $session->get('pupilsightThemeName'),
    'pupilsightHouseIDLogo' => $session->get('pupilsightHouseIDLogo'),
    'organisationLogo'  => $session->get('organisationLogo'),
    'minorLinks'        => $header->getMinorLinks($cacheLoad),
    'notificationTray'  => $header->getNotificationTray($cacheLoad),
    'sidebar'           => $showSidebar,
    'version'           => $pupilsight->getVersion(),
    'versionName'       => 'v'.$pupilsight->getVersion().($session->get('cuttingEdgeCode') == 'Y'? 'dev' : ''),
    'rightToLeft'       => $session->get('i18n')['rtl'] == 'Y',
]);

if ($isLoggedIn) {
    $page->addData([
        'menuMain'   => $session->get('menuMainItems', []),
        'menuModule' => $session->get('menuModuleItems', []),
        'fastFinder' => $session->get('fastFinder'),
    ]);
}

/**
 * GET PAGE CONTENT
 *
 * TODO: move queries into Gateway classes.
 * TODO: rewrite dashboards as template files.
 */
if (!$session->has('address')) {
    // Welcome message
    if (!$isLoggedIn) {
        // Create auto timeout message
        if (isset($_GET['timeout']) && $_GET['timeout'] == 'true') {
            $page->addWarning(__('Your session expired, so you were automatically logged out of the system.'));
        }

        $templateData = [
            'indexText'                 => $session->get('indexText'),
            'organisationName'          => $session->get('organisationName'),
            'publicStudentApplications' => getSettingByScope($connection2, 'Application Form', 'publicApplications') == 'Y',
            'publicStaffApplications'   => getSettingByScope($connection2, 'Staff Application Form', 'staffApplicationFormPublicApplications') == 'Y',
            'makeDepartmentsPublic'     => getSettingByScope($connection2, 'Departments', 'makeDepartmentsPublic') == 'Y',
            'makeUnitsPublic'           => getSettingByScope($connection2, 'Planner', 'makeUnitsPublic') == 'Y',
        ];

        // Get any elements hooked into public home page, checking if they are turned on
        $sql = "SELECT * FROM pupilsightHook WHERE type='Public Home Page' ORDER BY name";
        $hooks = $pdo->select($sql)->fetchAll();

        foreach ($hooks as $hook) {
            $options = unserialize(str_replace("'", "\'", $hook['options']));
            $check = getSettingByScope($connection2, $options['toggleSettingScope'], $options['toggleSettingName']);
            if ($check == $options['toggleSettingValue']) { // If its turned on, display it
                $options['text'] = stripslashes($options['text']);
                $templateData['indexHooks'][] = $options;
            }
        }

        $page->writeFromTemplate('welcome.twig.html', $templateData);
        
    } else {
        // Custom content loader
        if (!$session->exists('index_custom.php')) {
            $globals = [
                'guid'        => $guid,
                'connection2' => $connection2,
            ];

            $session->set('index_custom.php', $page->fetchFromFile('./index_custom.php', $globals));
        }
        
        if ($session->has('index_custom.php')) {
            $page->write($session->get('index_custom.php'));
        }

        // DASHBOARDS!
        $category = getRoleCategory($session->get('pupilsightRoleIDCurrent'), $connection2);
        
        switch ($category) {
            case 'Parent':
                $page->write($container->get(Pupilsight\UI\Dashboard\ParentDashboard::class)->getOutput());
                break;
            case 'Student':
                $page->write($container->get(Pupilsight\UI\Dashboard\StudentDashboard::class)->getOutput());
                break;
            case 'Staff':
                $page->write($container->get(Pupilsight\UI\Dashboard\StaffDashboard::class)->getOutput());
                break;
            default:
                $page->write('<div class="alert alert-danger">'.__('Your current role type cannot be determined.').'</div>');
        }
    }
} else {
    $address = trim($page->getAddress(), ' /');

    if ($page->isAddressValid($address) == false) {
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
}

/**
 * GET SIDEBAR CONTENT
 *
 * TODO: rewrite the Sidebar class as a template file.
 */
$sidebarContents = '';
if ($showSidebar) {
    $page->addSidebarExtra($session->get('sidebarExtra'));
    $session->set('sidebarExtra', '');

    $page->addData([
        'sidebarContents' => $container->get(Pupilsight\UI\Components\Sidebar::class)->getOutput(),
        'sidebarPosition' => $session->get('sidebarExtraPosition'),
    ]);
}


/**
 * DONE!!
 */
echo $page->render('index.twig.html');
