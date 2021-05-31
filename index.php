<?php
/*
Pupilsight, Flexible & Open School System

*/

//echo  "http://localhost/pupilsight/wp/wp-login.php?user=".urlencode('admin')."&pass=".urlencode('Admin@123456');
error_reporting(E_ERROR | E_PARSE);
//error_reporting(0);

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

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
require_once "./pupilsight.php";

// Module include: Messenger has a bug where files have been relying on these
// functions because this file was included via getNotificationTray()
// TODO: Fix that :)
require_once "./modules/Messenger/moduleFunctions.php";

// Setup the Page and Session objects
$page = $container->get("page");
$session = $container->get("session");

$isLoggedIn =
    $session->has("username") && $session->has("pupilsightRoleIDCurrent");

/**
 * MODULE BREADCRUMBS
 */

if ($isLoggedIn && ($module = $page->getModule())) {
    $mid = $module->pupilsightModuleID;
    $sql =
        'SELECT p.category FROM pupilsightModule AS p WHERE p.pupilsightModuleID = "' .
        $mid .
        '"';
    $result = $connection2->query($sql);
    $moduledata = $result->fetch();
    $moduleName = $moduledata["category"];

    $page->breadcrumbs->setBaseURL(
        "index.php?q=/modules/" . $module->name . "/"
    );
    $page->breadcrumbs->add(__($moduleName), $module->entryURL);
    $page->breadcrumbs->setBaseURL(
        "index.php?q=/modules/" . $module->name . "/"
    );
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
$session->set(
    "pageLoads",
    !$session->exists("pageLoads") ? 0 : $session->get("pageLoads", -1) + 1
);

$cacheLoad = true;
$caching = $pupilsight->getConfig("caching");
if (!empty($caching) && is_numeric($caching)) {
    $cacheLoad = $session->get("pageLoads") % intval($caching) == 0;
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

if (!$session->has("systemSettingsSet")) {
    getSystemSettings($guid, $connection2);

    if (!$session->has("systemSettingsSet")) {
        exit(__("System Settings are not set: the system cannot be displayed"));
    }
}

/**
 * USER REDIRECTS
 *
 * TODO: When we implement routing, these can become part of the HTTP middleware.
 */

// Check for force password reset flag
if ($session->has("passwordForceReset")) {
    if (
        $session->get("passwordForceReset") == "Y" and
        $session->get("address") != "preferences.php"
    ) {
        $URL = $session->get("absoluteURL") . "/index.php?q=preferences.php";
        $URL = $URL . "&forceReset=Y";
        header("Location: {$URL}");
        exit();
    }
}

$roleid = "";

// Redirects after login
if ($session->get("pageLoads") == 0 && !$session->has("address")) {
    // First page load, so proceed

    if ($session->has("username")) {
        // Are we logged in?
        $roleid = $_SESSION[$guid]["pupilsightRoleIDPrimary"];

        $roleCategory = getRoleCategory(
            $session->get("pupilsightRoleIDCurrent"),
            $connection2
        );

        // Deal with attendance self-registration redirect
        // Are we a student?
        if ($roleCategory == "Student") {
            // Can we self register?
            if (
                isActionAccessible(
                    $guid,
                    $connection2,
                    "/modules/Attendance/attendance_studentSelfRegister.php"
                )
            ) {
                // Check to see if student is on site
                $studentSelfRegistrationIPAddresses = getSettingByScope(
                    $connection2,
                    "Attendance",
                    "studentSelfRegistrationIPAddresses"
                );
                $realIP = getIPAddress();
                if (
                    $studentSelfRegistrationIPAddresses != "" &&
                    !is_null($studentSelfRegistrationIPAddresses)
                ) {
                    $inRange = false;
                    foreach (
                        explode(",", $studentSelfRegistrationIPAddresses)
                        as $ipAddress
                    ) {
                        if (trim($ipAddress) == $realIP) {
                            $inRange = true;
                        }
                    }
                    if ($inRange) {
                        $currentDate = date("Y-m-d");
                        if (
                            isSchoolOpen(
                                $guid,
                                $currentDate,
                                $connection2,
                                true
                            )
                        ) {
                            // Is school open today
                            // Check for existence of records today
                            try {
                                $data = [
                                    "pupilsightPersonID" => $session->get(
                                        "pupilsightPersonID"
                                    ),
                                    "date" => $currentDate,
                                ];
                                $sql =
                                    "SELECT type FROM pupilsightAttendanceLogPerson WHERE pupilsightPersonID=:pupilsightPersonID AND date=:date ORDER BY timestampTaken DESC";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $page->addError($e->getMessage());
                            }

                            if ($result->rowCount() == 0) {
                                // No registration yet
                                // Redirect!
                                $URL =
                                    $session->get("absoluteURL") .
                                    "/index.php?q=/modules/Attendance" .
                                    "/attendance_studentSelfRegister.php" .
                                    "&redirect=true";
                                $session->set("pageLoads", null);
                                header("Location: {$URL}");
                                exit();
                            }
                        }
                    }
                }
            }
        }

        // Deal with Data Updater redirect (if required updates are enabled)
        $requiredUpdates = getSettingByScope(
            $connection2,
            "Data Updater",
            "requiredUpdates"
        );
        if ($requiredUpdates == "Y") {
            if (
                isActionAccessible(
                    $guid,
                    $connection2,
                    "/modules/Data Updater/data_updates.php"
                )
            ) {
                // Can we update data?
                $redirectByRoleCategory = getSettingByScope(
                    $connection2,
                    "Data Updater",
                    "redirectByRoleCategory"
                );
                $redirectByRoleCategory = explode(",", $redirectByRoleCategory);

                // Are we the right role category?
                if (in_array($roleCategory, $redirectByRoleCategory)) {
                    $gateway = new DataUpdaterGateway($pdo);

                    $updatesRequiredCount = $gateway->countAllRequiredUpdatesByPerson(
                        $session->get("pupilsightPersonID")
                    );

                    if ($updatesRequiredCount > 0) {
                        $URL =
                            $session->get("absoluteURL") .
                            "/index.php?q=/modules/Data Updater/data_updates.php&redirect=true";
                        $session->set("pageLoads", null);
                        header("Location: {$URL}");
                        exit();
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
$session->set("sidebarExtra", "");
$session->set("sidebarExtraPosition", "top");

// Check the current Action 'entrySidebar' to see if we should display a sidebar
$showSidebar = $page->getAction()
    ? $page->getAction()["entrySidebar"] != "N"
    : true;

// Override showSidebar if the URL 'sidebar' param is explicitly set
if (!empty($_GET["sidebar"])) {
    $showSidebar = strtolower($_GET["sidebar"]) !== "false";
}

/**
 * SESSION TIMEOUT
 *
 * Set session duration, which will be passed via JS config to setup the
 * session timeout. Ensures a minimum session duration of 1200.
 */
$sessionDuration = -1;
if ($isLoggedIn) {
    $sessionDuration = $session->get("sessionDuration");
    $sessionDuration = max(intval($sessionDuration), 1200);
}

/**
 * LOCALE
 *
 * Sets the i18n locale for jQuery UI DatePicker (if the file exists, otherwise
 * falls back to en-GB)
 */
$localeCode = str_replace("_", "-", $session->get("i18n")["code"]);
$localeCodeShort = substr($session->get("i18n")["code"], 0, 2);
$localePath =
    $session->get("absolutePath") .
    '/lib/jquery-ui/i18n/jquery.ui.datepicker-%1$s.js';

$datepickerLocale = "en-GB";
if ($localeCode === "en-US" || is_file(sprintf($localePath, $localeCode))) {
    $datepickerLocale = $localeCode;
} elseif (is_file(sprintf($localePath, $localeCodeShort))) {
    $datepickerLocale = $localeCodeShort;
}

// Allow the URL to override system default from the i18l param
if (
    !empty($_GET["i18n"]) &&
    $pupilsight->locale->getLocale() != $_GET["i18n"]
) {
    $data = ["code" => $_GET["i18n"]];
    $sql = "SELECT * FROM pupilsighti18n WHERE code=:code LIMIT 1";

    if ($result = $pdo->selectOne($sql, $data)) {
        setLanguageSession($guid, $result, false);
        $pupilsight->locale->setLocale($_GET["i18n"]);
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
    "config" => [
        "datepicker" => [
            "locale" => $datepickerLocale,
        ],
        "thickbox" => [
            "pathToImage" =>
                $session->get("absoluteURL") .
                "/lib/thickbox/loadingAnimation.gif",
        ],
        "tinymce" => [
            "valid_elements" => getSettingByScope(
                $connection2,
                "System",
                "allowableHTML"
            ),
        ],
        "sessionTimeout" => [
            "sessionDuration" => $sessionDuration,
            "message" => __(
                "Your session is about to expire: you will be logged out shortly."
            ),
        ],
    ],
];

/**
 * USER CONFIGURATION
 *
 * This should be moved to a one-time process to run after login, which can be
 * handled by HTTP middleware.
 */

// Try to auto-set user's calendar feed if not set already
if (
    $session->exists("calendarFeedPersonal") &&
    $session->exists("googleAPIAccessToken")
) {
    if (
        !$session->has("calendarFeedPersonal") &&
        $session->has("googleAPIAccessToken")
    ) {
        $service = $container->get("Google_Service_Calendar");
        try {
            $calendar = $service->calendars->get("primary");
        } catch (\Google_Service_Exception $e) {
        }

        if (!empty($calendar["id"])) {
            $session->set("calendarFeedPersonal", $calendar["id"]);
            $container
                ->get(UserGateway::class)
                ->update($session->get("pupilsightPersonID"), [
                    "calendarFeedPersonal" => $calendar["id"],
                ]);
        }
    }
}

// Get house logo and set session variable, only on first load after login (for performance)
if (
    $session->get("pageLoads") == 0 and
    $session->has("username") and
    !$session->has("pupilsightHouseIDLogo")
) {
    $dataHouse = ["pupilsightHouseID" => $session->get("pupilsightHouseID")];
    $sqlHouse = 'SELECT logo, name FROM pupilsightHouse
        WHERE pupilsightHouseID=:pupilsightHouseID';
    $house = $pdo->selectOne($sqlHouse, $dataHouse);

    if (!empty($house)) {
        $session->set("pupilsightHouseIDLogo", $house["logo"]);
        $session->set("pupilsightHouseIDName", $house["name"]);
    }
}

// Show warning if not in the current school year
// TODO: When we implement routing, these can become part of the HTTP middleware.
if ($isLoggedIn) {
    if (
        $session->get("pupilsightSchoolYearID") !=
        $session->get("pupilsightSchoolYearIDCurrent")
    ) {
        $page->addWarning(
            "<b><u>" .
                sprintf(
                    __(
                        'Warning: you are logged into the system in Academic Year %1$s, which is not the current year.'
                    ),
                    $session->get("pupilsightSchoolYearName")
                ) .
                "</b></u>" .
                __(
                    "Your data may not look quite right (for example, students who have left the school will not appear in previous years), but you should be able to edit information from other years which is not available in the current year."
                )
        );
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

if (!$session->has("address") && !empty($_GET["return"])) {
    $customReturns = [
        "success1" => __("Password reset was successful: you may now log in."),
    ];

    if ($alert = returnProcessGetAlert($_GET["return"], "", $customReturns)) {
        $page->addAlert($alert["context"], $alert["text"]);
    }
}

/**
 * MENU ITEMS & FAST FINDER
 *
 * TODO: Move this somewhere more sensible.
 */
$isAllMenu = false;
$changeyear = "";

if ($isLoggedIn) {
    $roleid = trim($_SESSION[$guid]["pupilsightRoleIDPrimary"]);
    if ($roleid == "001") {
        $isAllMenu = true;
    }

    if ($cacheLoad || !$session->has("fastFinder")) {
        $templateData = getFastFinder($connection2, $guid);
        $templateData["enrolmentCount"] = $container
            ->get(StudentGateway::class)
            ->getStudentEnrolmentCount($session->get("pupilsightSchoolYearID"));

        $fastFinder = $page->fetchFromTemplate(
            "finder.twig.html",
            $templateData
        );
        $session->set("fastFinder", $fastFinder);
    }

    $moduleGateway = $container->get(ModuleGateway::class);

    //if ($cacheLoad || !$session->has('menuMainItems')) {
    $menuMainItems = $moduleGateway
        ->selectModulesByRole($session->get("pupilsightRoleIDCurrent"))
        ->fetchGrouped();
    foreach ($menuMainItems as $category => &$items) {
        foreach ($items as &$item) {
            $modulePath = "/modules/" . $item["name"];
            $entryURL = isActionAccessible(
                $guid,
                $connection2,
                $modulePath . "/" . $item["entryURL"]
            )
                ? $item["entryURL"]
                : $item["alternateEntryURL"];

            $item["url"] =
                $session->get("absoluteURL") .
                "/index.php?q=" .
                $modulePath .
                "/" .
                $entryURL;
        }
    }

    $countersql = "SELECT id FROM fn_fees_counter";
    $resultcounter = $connection2->query($countersql);
    $counterData = $resultcounter->fetch();

    $masterList[0] = [
        "name" => "Fee Category",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Finance/fee_category_manage.php",
    ];
    $masterList[1] = [
        "name" => "Fee Series",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Finance/fee_series_manage.php",
    ];
    $masterList[2] = [
        "name" => "Fee Head",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Finance/fee_head_manage.php",
    ];
    $masterList[3] = [
        "name" => "Fine Rule",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Finance/fee_fine_rule_manage.php",
    ];
    $masterList[4] = [
        "name" => "Fee Item Type",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Finance/fee_item_type_manage.php",
    ];
    $masterList[5] = [
        "name" => "Receipts Template",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Finance/fee_receipts_manage.php",
    ];
    $masterList[6] = [
        "name" => "Fee Item",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Finance/fee_item_manage.php",
    ];
    $masterList[7] = [
        "name" => "Deposit Account",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Finance/deposit_account_manage.php",
    ];
    $masterList[8] = [
        "name" => "Discount Rule",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Finance/fee_discount_rule_manage.php",
    ];
    $masterList[9] = [
        "name" => "Fee Counter",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Finance/fee_counter_manage.php",
    ];
    $masterList[10] = [
        "name" => "Banks & Payment Mode",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Finance/fee_master_manage.php",
    ];
    $masterList[11] = [
        "name" => "Fee Payment Gateway",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Finance/fee_payment_gateway_manage.php",
    ];

    $paymentList[0] = [
        "name" => "Manage Invoice",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Finance/invoice_manage.php",
    ];
    $paymentList[1] = [
        "name" => "Bulk Discount",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Finance/invoice_discount_manage.php",
    ];
    //echo $session->get('counterid');
    if (!empty($counterData)) {
        if ($session->get("counterid") == "") {
            $paymentList[2] = [
                "name" => "Collection",
                "class" => "thickbox",
                "url" =>
                    $session->get("absoluteURL") .
                    "/index.php?q=/modules/Finance/fee_counter_check_add.php",
            ];
        } else {
            $paymentList[2] = [
                "name" => "Collection",
                "url" =>
                    $session->get("absoluteURL") .
                    "/index.php?q=/modules/Finance/fee_collection_manage.php",
            ];
        }
    } else {
        $paymentList[2] = [
            "name" => "Collection",
            "url" =>
                $session->get("absoluteURL") .
                "/index.php?q=/modules/Finance/fee_collection_manage.php",
        ];
    }
    $paymentList[3] = [
        "name" => "Transaction",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Finance/fee_transaction_manage.php",
    ];
    $paymentList[4] = [
        "name" => "Cancel Transaction",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Finance/fee_transaction_cancel_manage.php",
    ];
    $paymentList[5] = [
        "name" => "Refund Transaction",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Finance/fee_transaction_refund_manage.php",
    ];

    //$structureList[0] = array('name'=>'Structure','url'=>$session->get('absoluteURL') .'/index.php?q=/modules/Finance/fee_structure_manage.php');

    $masterMenu = [
        "name" => "Master",
        "list" => $masterList,
        "col" => "dropdown-menu-columns  dropdown-menu-columns-2",
    ];
    //$structureMenu = array('name' => "Structure",'list'=>$structureList, 'col'=>'dropdown-menu-columns  dropdown-menu-columns-3');
    $paymentMenu = [
        "name" => "Payment",
        "list" => $paymentList,
        "col" => "dropdown-menu-columns  dropdown-menu-columns-2",
    ];
    //echo "role id " . $roleid;
    //die();

    if (isset($menuMainItems["Finance"])) {
        if ($roleid == "001") {
            $menuMainItems["Finance"][0] = $masterMenu;
            $menuMainItems["Finance"][1] = [
                "name" => "Fee Structure",
                "url" =>
                    $session->get("absoluteURL") .
                    "/index.php?q=/modules/Finance/fee_structure_manage.php",
                "col" => "dropdown-menu-columns  dropdown-menu-columns-2",
            ];
            $menuMainItems["Finance"][2] = $paymentMenu;
        } elseif ($roleid == "003" || $roleid == "004") {
            unset($menuMainItems["Finance"]);
            $menuMainItems["Finance"][0] = [
                "name" => "Invoices",
                "url" =>
                    $session->get("absoluteURL") .
                    "/index.php?q=/modules/Finance/invoice_child_view.php",
                "col" => "dropdown-menu-columns  dropdown-menu-columns-2",
            ];
        } else {
            unset($menuMainItems["Finance"]);
            $menuMainItems["Finance"][0] = [
                "name" => "Fee Structure",
                "url" =>
                    $session->get("absoluteURL") .
                    "/index.php?q=/modules/Finance/fee_structure_manage.php",
                "col" => "dropdown-menu-columns  dropdown-menu-columns-2",
            ];
            $menuMainItems["Finance"][1] = $paymentMenu;
        }
    }
    /*
    if ($roleid == '038') {
        //for fee collection
        $menuMainItems["Finance"][0] = $paymentMenu;
        $menuMainItems["Finance"][1] = array();
        $menuMainItems["Finance"][2] = array();
    }*/

    $routeList[0] = [
        "name" => "Manage Route",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Transport/routes.php",
    ];
    $routeList[1] = [
        "name" => "Assign to Student",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Transport/assign_route.php",
    ];
    $routeList[2] = [
        "name" => "Assign to Staff",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Transport/assign_staff_route_manage.php",
    ];
    $routeList[3] = [
        "name" => "View Member in Route",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Transport/view_members_in_route.php",
    ];
    $routeMenu = ["name" => "Routes", "list" => $routeList];

    if (isset($menuMainItems["Transport"])) {
        $menuMainItems["Transport"][0] = [
            "name" => "Bus Details",
            "url" =>
                $session->get("absoluteURL") .
                "/index.php?q=/modules/Transport/bus_manage.php",
        ];
        $menuMainItems["Transport"][1] = [
            "name" => "Transport Fee",
            "url" =>
                $session->get("absoluteURL") .
                "/index.php?q=/modules/Transport/transport_fee.php",
        ];
        $menuMainItems["Transport"][2] = $routeMenu;
    }

    $curriculumList[0] = [
        "name" => "Subject Type",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/subject_type_manage.php",
    ];
    $curriculumList[1] = [
        "name" => "Subject Master",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/department_manage.php",
    ];
    $curriculumList[2] = [
        "name" => "Skill Master",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/ac_manage_skill.php",
    ];
    $curriculumList[3] = [
        "name" => "Curriculum Configuration",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/subject_to_class_manage.php",
    ];
    $curriculumList[4] = [
        "name" => "Manage Elective Group",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/manage_elective_group.php",
    ];
    $curriculumList[5] = [
        "name" => "Remarks Master",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/ac_manage_remarks.php",
    ];
    $curriculumList[6] = [
        "name" => "DI Mode",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/descriptive_indicator_config.php",
    ];

    $curriculumMenu = [
        "name" => "Curriculum",
        "list" => $curriculumList,
        "col" => "dropdown-menu-columns  dropdown-menu-columns-2",
        "url" => $session->get("absoluteURL") . "/cms.php",
    ];

    $testList[0] = [
        "name" => "Grading System",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/grade_system_manage.php",
    ];
    $testList[1] = [
        "name" => "Manage Test Room",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/examination_room_manage.php",
    ];
    $testList[2] = [
        "name" => "Reports Template",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/examination_report_template_manage.php",
    ];
    $testList[3] = [
        "name" => "Test Home",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/test_home.php",
    ];
    $testList[4] = [
        "name" => "Edit Test",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/manage_edit_test.php",
    ];
    $testList[5] = [
        "name" => "Manage Test",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/manage_test.php",
    ];
    $testList[6] = [
        "name" => "Marks Entry by Subject",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/manage_marks_entry_by_subject.php",
    ];
    $testList[7] = [
        "name" => "Marks Entry by Student",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/marks_by_student.php",
    ];
    $testList[8] = [
        "name" => "Enter A.A.T",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/manage_enter_aat.php",
    ];
    $testList[9] = [
        "name" => "Marks Upload",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/test_marks_upload.php",
    ];
    $testList[10] = [
        "name" => "Marks not Entered",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/marks_not_entered.php",
    ];
    $testList[11] = [
        "name" => "Test Results",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/manage_test_results.php",
    ];
    $testList[12] = [
        "name" => "Sketch",
        "url" =>
            $session->get("absoluteURL") .
            "/index.php?q=/modules/Academics/sketch_manage.php",
    ];

    $testMenu = [
        "name" => "Test",
        "list" => $testList,
        "col" => "dropdown-menu-columns  dropdown-menu-columns-2",
        "url" => $session->get("absoluteURL") . "/cms.php",
    ];

    if (isset($menuMainItems["Academics"])) {
        if ($roleid == "035") {
            $testList = [];
            $testList[0] = [
                "name" => "Enter A.A.T",
                "url" =>
                    $session->get("absoluteURL") .
                    "/index.php?q=/modules/Academics/manage_enter_aat.php",
            ];
            $testList[1] = [
                "name" => "Marks Entry by Subject",
                "url" =>
                    $session->get("absoluteURL") .
                    "/index.php?q=/modules/Academics/manage_marks_entry_by_subject.php",
            ];
            $testList[2] = [
                "name" => "Marks Entry by Student",
                "url" =>
                    $session->get("absoluteURL") .
                    "/index.php?q=/modules/Academics/marks_by_student.php",
            ];
            $testList[3] = [
                "name" => "Test Results",
                "url" =>
                    $session->get("absoluteURL") .
                    "/index.php?q=/modules/Academics/manage_test_results.php",
            ];
            $testList[4] = [
                "name" => "Marks not Entered",
                "url" =>
                    $session->get("absoluteURL") .
                    "/index.php?q=/modules/Academics/marks_not_entered.php",
            ];

            //$menuMainItems["Academics"][0] = $testList[0];
            $menuMainItems["Academics"][0] = $testList[1];
            $menuMainItems["Academics"][1] = $testList[2];
            $menuMainItems["Academics"][2] = $testList[4];
            $menuMainItems["Academics"][3] = $testList[3];
        } else if ($roleid == "003" || $roleid == "004") {
            $testList = [];
            $testList[0] = [
                "name" => "Elective Group",
                "url" =>
                    $session->get("absoluteURL") .
                    "/index.php?q=/modules/Academics/manage_elective_group.php",
            ];

            $testList[1] = [
                "name" => "Test Results",
                "url" =>
                    $session->get("absoluteURL") .
                    "/index.php?q=/modules/Academics/manage_test_results.php",
            ];

            //$menuMainItems["Academics"][0] = $testList[0];
            $menuMainItems["Academics"][0] = $testList[0];
            $menuMainItems["Academics"][1] = $testList[1];
            // $menuMainItems["Academics"][2] = $testList[4];
            // $menuMainItems["Academics"][3] = $testList[3];
        } else {
            $menuMainItems["Academics"][0] = $curriculumMenu;
            $menuMainItems["Academics"][1] = $testMenu;
        }
    }

    if ($isAllMenu) {
        $cmsMenu = [
            "name" => "CMS",
            "url" =>
                $session->get("absoluteURL") .
                "/index.php?q=/modules/custom/cms.php",
        ];
        $menuMainItems["Other"][2] = $cmsMenu;
        $menuMainItems["Reports"][0] = [
            "name" => "Reports",
            "url" =>
                $session->get("absoluteURL") .
                "/index.php?q=/modules/custom/reports.php",
        ];
        $menuMainItems["LMS"][0] = [
            "name" => "LMS",
            "url" =>
                $session->get("absoluteURL") .
                "/index.php?q=/modules/custom/lms.php",
        ];
    }

    if ($_SESSION[$guid]["username"] == "sinuthomas") {
        $tm2 = $menuMainItems["Admission"];
        $menuMainItems = [];
        $menuMainItems["Admission"] = $tm2;
        $menuMainItems["Reports"][0] = [
            "name" => "Reports",
            "url" =>
                $session->get("absoluteURL") .
                "/index.php?q=/modules/custom/reports.php",
        ];
    } elseif ($_SESSION[$guid]["username"] == "munirajk") {
        $tm2 = $menuMainItems["Admission"];
        $menuMainItems = [];
        $menuMainItems["Admission"] = $tm2;
    } elseif ($_SESSION[$guid]["username"] == "Geesha") {
        $tm2 = $menuMainItems["Admission"];
        $menuMainItems = [];
        $menuMainItems["Admission"] = $tm2;
    }

    //for student and parents
    if ($roleid != "001") {
        unset(
            $menuMainItems["Assess"],
            $menuMainItems["Learn"],
            $menuMainItems["Other"]
        );
    }

    if ($roleid == "003" || $roleid == "004") {
        $changeyear = "";
    } else {
        $changeyear = "allow";
    }

    $session->set("menuMainItems", $menuMainItems);
    $session->set("allmenu", $menuMainItems);
    $submenu = true;

    if ($page->getModule()) {
        $menuModule = $session->get("menuModuleName");
        $currentModule = $page->getModule()->getName();
        //print_r($currentModule);
        // die();

        if (
            $cacheLoad ||
            !$session->has("menuModuleItems") ||
            $currentModule != $menuModule
        ) {
            $menuModuleItems = $moduleGateway
                ->selectModuleActionsByRole(
                    $page->getModule()->getID(),
                    $session->get("pupilsightRoleIDCurrent")
                )
                ->fetchGrouped();
        } else {
            $menuModuleItems = $session->get("menuModuleItems");
        }

        // Update the menu items to indicate the current active action
        $chk = 0;
        $vc_array_name = [];
        foreach ($menuModuleItems as $category => &$items) {
            if ($submenu && empty($category)) {
                $submenu = false;
            }
            foreach ($items as $nk => &$item) {
                if (!empty($item["order_wise"])) {
                    $vc_array_name[$nk] = $item["order_wise"];
                    $chk = "1";
                }

                $urlList = array_map("trim", explode(",", $item["URLList"]));
                $item["active"] = in_array($session->get("action"), $urlList);
                $item["url"] =
                    $session->get("absoluteURL") .
                    "/index.php?q=/modules/" .
                    $item["moduleName"] .
                    "/" .
                    $item["entryURL"];
            }
            //closed array_multisort because throw warning while doing later will resolve this
            /*
            if ($chk == '1' && !empty($vc_array_name)) {
                array_multisort($vc_array_name, SORT_ASC, $items);
            }*/
        }

        $allmenu = $session->get("allmenu");
        if (isset($allmenu)) {
            $headFlag = false;
            foreach ($allmenu as $category => &$items) {
                foreach ($items as &$item) {
                    if ($item["name"] == $currentModule) {
                        $session->set("parentMenuSelect", $category);
                        $session->set("childMenuSelect", $currentModule);
                        $headFlag = true;
                        break;
                    }
                }
                if ($headFlag) {
                    break;
                }
            }
        }

        $session->set("menuModuleItems", $menuModuleItems);
        $session->set("menuModuleName", $currentModule);
    } else {
        $session->forget(["menuModuleItems", "menuModuleName"]);
    }
}
//print_r($menuModuleItems);
/**
 * TEMPLATE DATA
 *
 * These values are merged with the Page class settings & content, then passed
 * into the template engine for rendering. They're a work in progress, but once
 * they're more finalized we can document them for theme developers.
 */

$header = $container->get(Pupilsight\UI\Components\Header::class);

try {
    if (isset($roleCategory) == false) {
        $roleCategory = null;
    }
    //$uname = $session->get('preferredName') . ' ' . $session->get('surname')
    $uname = $session->get("preferredName");
    if (empty($session->get("preferredName"))) {
        $uname = $session->get("officialName");
    }

    $shortname = "";
    if ($uname) {
        $shortname = strtoupper($moduleGateway->get2Char($uname));
    }

    $page->addData([
        "isLoggedIn" => $isLoggedIn,
        "pupilsightThemeName" => $session->get("pupilsightThemeName"),
        "pupilsightHouseIDLogo" => $session->get("pupilsightHouseIDLogo"),
        "organisationLogo" => $session->get("organisationLogo"),
        "minorLinks" => $header->getMinorLinks($cacheLoad),
        "uname" => $uname,
        "shortname" => $shortname,
        "notificationTray" => $header->getNotificationTray($cacheLoad),
        "sidebar" => $showSidebar,
        "roleCategory" => $roleCategory,
        "version" => $pupilsight->getVersion(),
        "versionName" =>
            "v" .
            $pupilsight->getVersion() .
            ($session->get("cuttingEdgeCode") == "Y" ? "dev" : ""),
        "rightToLeft" => $session->get("i18n")["rtl"] == "Y",
    ]);
} catch (Exception $ex) {
    print_r($ex);
}

//here to catch
$peopleMenu = [
    "NA",
    "Students",
    "Alumni",
    "Behaviour",
    "Data Updater",
    "Roll Groups",
    "Staff",
    "Students",
];
$otherMenu = ["NA", "Help Desk", "Higher Education", "CMS"];
$learnMenu = [
    "NA",
    "Activities",
    "Departments",
    "Individual Needs",
    "Library",
    "Planner",
    "Timetable",
];
$assessMenu = [
    "NA",
    "Badges",
    "Crowd Assessment",
    "Formal Assessment",
    "Markbook",
    "Rubrics",
    "Tracking",
];

$framesrc = "";

$customSelect = "";
if ($_SESSION["loginuser"] == "admin") {
    $lmsuser = $_SESSION["loginuser"];
    $lmspass = $_SESSION["loginpass"];
} else {
    $lmsuser = $_SESSION["lmsuser"];
    $lmspass = $_SESSION["lmspass"];
}

if (isset($_GET["q"])) {
    $gq = explode("/", $_GET["q"]);
    //print_r($gq);
    if ($gq[2] == "custom") {
        $isframe = "y";
        if ($gq[3] == "reports.php") {
            if (isset($_SESSION["reportaccess"])) {
                $framesrc = $session->get("absoluteURL") . "/wp/wp-admin/";
            } else {
                $_SESSION["reportaccess"] = "1";
                $framesrc =
                    $session->get("absoluteURL") .
                    "/wp/wp-login.php?user=admin&pass=Admin@123456";
            }
            $customSelect = "Reports";
        } elseif ($gq[3] == "cms.php") {
            $framesrc = $session->get("absoluteURL") . "/cms/admin/index.php";
            $customSelect = "Other";
        } elseif ($gq[3] == "lms.php") {
            if (isset($_SESSION["lmsaccess"])) {
                $framesrc = $session->get("absoluteURL") . "/lms/index.php";
            } else {
                $_SESSION["lmsaccess"] = "1";
                $lmsparms = "user=" . $lmsuser . "&pass=" . $lmspass;
                $framesrc =
                    $session->get("absoluteURL") .
                    "/lms/login/index_auto.php?" .
                    $lmsparms;
            }
            $customSelect = "LMS";
        }
    }
}
if (isset($currentModule) == false) {
    $currentModule = "";
}

if ($currentModule) {
    if (array_search($currentModule, $peopleMenu)) {
        $currentModule = "People";
    } elseif (array_search($currentModule, $otherMenu)) {
        $currentModule = "Other";
    } elseif (array_search($currentModule, $learnMenu)) {
        $currentModule = "Learn";
    } elseif (array_search($currentModule, $assessMenu)) {
        $currentModule = "Assess";
    } elseif ($currentModule == "Campaign") {
        $currentModule = "Admission";
    }
} elseif ($currentModule == "") {
    if ($customSelect) {
        $currentModule = $customSelect;
    } else {
        $currentModule = "Dashboard";
    }
}

if ($isLoggedIn) {
    $smsCredits_data = getSettingByScope(
        $connection2,
        "System",
        "smsCredits",
        true
    );
    $menu_icon = [
        "Admin" => "mdi mdi-account-cog",
        "Assess" => "mdi mdi-clipboard-list-outline",
        "Learn" => "mdi mdi-head-lightbulb-outline",
        "People" => "mdi mdi-account-group",
        "Admission" => "mdi mdi-clipboard-check-multiple-outline",
        "Finance" => "mdi mdi-finance",
        "Transport" => "mdi mdi-bus-multiple",
        "Academics" => "mdi mdi-school",
        "Attendance" => "mdi mdi-calendar-check-outline",
        "Communication" => "mdi mdi-handshake",
        "Other" => "mdi mdi-dots-horizontal-circle-outline",
        "TimeTable" => "mdi mdi-calendar-month",
        "Reports" => "mdi mdi-file-chart-outline",
        "LMS" => "mdi mdi-book-open-page-variant",
    ];

    if (
        $currentModule == "Academics" ||
        $currentModule == "Finance" ||
        $currentModule == "Transport" ||
        $currentModule == "Dashboard" ||
        $currentModule == ""
    ) {
        $submenu = false;
    }

    if (
        $currentModule == "School Admin" ||
        $currentModule == "System Admin" ||
        $currentModule == "User Admin"
    ) {
        $currentModule = "Admin";
    }

    //echo $iframe ." - ".$iframesrc;
    //die();

    $sqlterm =
        "SELECT * FROM pupilsightSchoolYear ORDER BY pupilsightSchoolYearID ASC";
    $resultterm = $connection2->query($sqlterm);
    $yeardata = $resultterm->fetchAll();

    $currentYear = $session->get("pupilsightSchoolYearID");

    $totalsmsbalance = 0;
    $extrasmsused = 0;
    $totalsmsused = 0;

    $karixsmscountvalue = getsmsBalance($connection2, "Messenger", "Karix");
    $gupshupsmscountvalue = getsmsBalance($connection2, "Messenger", "Gupshup");
    //echo "karixsmscountvalue: " . $karixsmscountvalue;

    //echo "gupshupsmscountvalue: " . $gupshupsmscountvalue;
    $totalsms = gettotalsmsBalance($connection2);
    $totalsmsbalance = $totalsms;

    $totalsmsused = $gupshupsmscountvalue + $karixsmscountvalue;
    if ($totalsmsused > $totalsms) {
        $extrasmsused = $totalsmsused - $totalsms;
    } else {
        $totalsmsbalance = $totalsms - $totalsmsused;
    }

    $page->addData([
        "menuMain" => $session->get("menuMainItems", []),
        "menuMainIcon" => $menu_icon,
        "menuModule" => $session->get("menuModuleItems", []),
        "fastFinder" => $session->get("fastFinder"),
        "parentMenuSelect" => $session->get("parentMenuSelect"),
        "childMenuSelect" => $session->get("childMenuSelect"),
        "currentModule" => $currentModule,
        "submenu" => $submenu,
        "framesrc" => $framesrc,
        "parentrole" => $session->get("pupilsightRoleIDPrimary"),
        "counterid" => $session->get("counterid"),
        "smsCredits" => $smsCredits_data["value"],
        "academicYear" => $yeardata,
        "pupilsightSchoolYearID" => $currentYear,
        "changeyear" => $changeyear,
        "totalsmsused" => $totalsmsused,
        "extrasmsused" => $extrasmsused,
        "totalsmsbalance" => $totalsmsbalance,
    ]);
}

/**
 * GET PAGE CONTENT
 *
 * TODO: move queries into Gateway classes.
 * TODO: rewrite dashboards as template files.
 */

if (!$session->has("address")) {
    // Welcome message
    if (!$isLoggedIn) {
        // Create auto timeout message
        if (isset($_GET["timeout"]) && $_GET["timeout"] == "true") {
            $page->addWarning(
                __(
                    "Your session expired, so you were automatically logged out of the system."
                )
            );
        }

        $templateData = [
            "indexText" => $session->get("indexText"),
            "organisationName" => $session->get("organisationName"),
            "publicStudentApplications" =>
                getSettingByScope(
                    $connection2,
                    "Application Form",
                    "publicApplications"
                ) == "Y",
            "publicStaffApplications" =>
                getSettingByScope(
                    $connection2,
                    "Staff Application Form",
                    "staffApplicationFormPublicApplications"
                ) == "Y",
            "makeDepartmentsPublic" =>
                getSettingByScope(
                    $connection2,
                    "Departments",
                    "makeDepartmentsPublic"
                ) == "Y",
            "makeUnitsPublic" =>
                getSettingByScope($connection2, "Planner", "makeUnitsPublic") ==
                "Y",
        ];

        // Get any elements hooked into public home page, checking if they are turned on
        $sql =
            "SELECT * FROM pupilsightHook WHERE type='Public Home Page' ORDER BY name";
        $hooks = $pdo->select($sql)->fetchAll();

        foreach ($hooks as $hook) {
            $options = unserialize(str_replace("'", "\'", $hook["options"]));
            $check = getSettingByScope(
                $connection2,
                $options["toggleSettingScope"],
                $options["toggleSettingName"]
            );
            if ($check == $options["toggleSettingValue"]) {
                // If its turned on, display it
                $options["text"] = stripslashes($options["text"]);
                $templateData["indexHooks"][] = $options;
            }
        }

        $page->writeFromTemplate("welcome.twig.html", $templateData);
        $loginReturn = $_GET["loginReturn"];
        if ($loginReturn == "fail2") {
            $uhome = "home.php?loginReturn=fail";
        } else {
            $uhome = "home.php";
        }

        //header("Location: cms/index.php");
        header("Location: " . $uhome . " ");
        die();
    } else {
        // Custom content loader
        if (!$session->exists("index_custom.php")) {
            $globals = [
                "guid" => $guid,
                "connection2" => $connection2,
            ];

            $session->set(
                "index_custom.php",
                $page->fetchFromFile("./index_custom.php", $globals)
            );
        }

        if ($session->has("index_custom.php")) {
            $page->write($session->get("index_custom.php"));
        }

        // DASHBOARDS!
        $category = getRoleCategory(
            $session->get("pupilsightRoleIDCurrent"),
            $connection2
        );
        //$category = "";
        switch ($category) {
            case "Parent":
                $page->write(
                    $container
                        ->get(Pupilsight\UI\Dashboard\ParentDashboard::class)
                        ->getOutput()
                );
                break;
            case "Student":
                $page->write(
                    $container
                        ->get(Pupilsight\UI\Dashboard\StudentDashboard::class)
                        ->getOutput()
                );
                break;
            case "Staff":
                $page->write(
                    $container
                        ->get(Pupilsight\UI\Dashboard\StaffDashboard::class)
                        ->getOutput()
                );
                break;
            default:
                $page->write(
                    '<div class="alert alert-danger">' .
                        __("Your current role type cannot be determined.") .
                        "</div>"
                );
        }
    }
} else {
    $address = trim($page->getAddress(), " /");
    if ($framesrc == "") {
        if ($page->isAddressValid($address) == false) {
            $page->addError(__("Illegal address detected: access denied."));
        } else {
            // Pass these globals into the script of the included file, for backwards compatibility.
            // These will be removed when we begin the process of ooifying action pages.
            $globals = [
                "guid" => $guid,
                "pupilsight" => $pupilsight,
                "version" => $version,
                "pdo" => $pdo,
                "connection2" => $connection2,
                "autoloader" => $autoloader,
                "container" => $container,
                "page" => $page,
            ];

            if (is_file("./" . $address)) {
                $page->writeFromFile("./" . $address, $globals);
            } else {
                $page->writeFromFile("./error.php", $globals);
            }
        }
    }
}

/**
 * GET SIDEBAR CONTENT
 *
 * TODO: rewrite the Sidebar class as a template file.
 */

$sidebarContents = "";
if ($showSidebar) {
    $page->addSidebarExtra($session->get("sidebarExtra"));
    $session->set("sidebarExtra", "");

    $page->addData([
        "sidebarContents" => $container
            ->get(Pupilsight\UI\Components\Sidebar::class)
            ->getOutput(),
        "sidebarPosition" => $session->get("sidebarExtraPosition"),
    ]);
}

/**
 * DONE!!
 */

$roleid = $_SESSION[$guid]["pupilsightRoleIDPrimary"];
if ($roleid == "001") {
    if ($framesrc == "") {
        $page->addData([
            "reportAutoLogin" => "",
        ]);
        if (isset($_SESSION["reportaccess"]) == false) {
            $reportAutoLogin =
                $session->get("absoluteURL") .
                "/wp/wp-login.php?user=admin&pass=Admin@123456";
            $page->addData([
                "reportAutoLogin" => $reportAutoLogin,
            ]);
            $_SESSION["reportaccess"] = "1";
        }
        echo $page->render("index_admin.twig.html");
    } else {
        echo $page->render("index_admin_frame.twig.html");
    }
} else {
    echo $page->render("index.twig.html");
}