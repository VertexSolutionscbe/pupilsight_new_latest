<?php
/*
Pupilsight, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//This file describes the module, including database tables

//Basic variables
$name = 'Feed';
$description = 'Offers a unified feed of latest posts from student and class websites to staff, parent and student dashboards.';
$entryURL = 'feed_view.php';
$type = 'Additional';
$category = 'Other';
$version = '1.0.05';
$author = 'Ross Parker';
$url = 'http://rossparker.org';

//Action rows
$actionRows[0]['name'] = 'View Feed';
$actionRows[0]['precedence'] = '0';
$actionRows[0]['category'] = 'Feed';
$actionRows[0]['description'] = 'Allows a user to view their feed.';
$actionRows[0]['URLList'] = 'feed_view.php';
$actionRows[0]['entryURL'] = 'feed_view.php';
$actionRows[0]['menuShow'] = 'N';
$actionRows[0]['defaultPermissionAdmin'] = 'Y';
$actionRows[0]['defaultPermissionTeacher'] = 'Y';
$actionRows[0]['defaultPermissionStudent'] = 'Y';
$actionRows[0]['defaultPermissionParent'] = 'Y';
$actionRows[0]['defaultPermissionSupport'] = 'Y';
$actionRows[0]['categoryPermissionStaff'] = 'Y';
$actionRows[0]['categoryPermissionStudent'] = 'Y';
$actionRows[0]['categoryPermissionParent'] = 'Y';
$actionRows[0]['categoryPermissionOther'] = 'Y';

//HOOKS
$array = array();
$array['sourceModuleName'] = 'Feed';
$array['sourceModuleAction'] = 'View Feed';
$array['sourceModuleInclude'] = 'hook_dashboard_feedView.php';
$hooks[0] = "INSERT INTO `pupilsightHook` (`pupilsightHookID`, `name`, `type`, `options`, pupilsightModuleID) VALUES (NULL, 'Website Feed', 'Staff Dashboard', '".serialize($array)."', (SELECT pupilsightModuleID FROM pupilsightModule WHERE name='$name'));";

$array['sourceModuleName'] = 'Feed';
$array['sourceModuleAction'] = 'View Feed';
$array['sourceModuleInclude'] = 'hook_dashboard_feedView.php';
$hooks[1] = "INSERT INTO `pupilsightHook` (`pupilsightHookID`, `name`, `type`, `options`, pupilsightModuleID) VALUES (NULL, 'Website Feed', 'Student Dashboard', '".serialize($array)."', (SELECT pupilsightModuleID FROM pupilsightModule WHERE name='$name'));";

$array['sourceModuleName'] = 'Feed';
$array['sourceModuleAction'] = 'View Feed';
$array['sourceModuleInclude'] = 'hook_dashboard_feedView.php';
$hooks[2] = "INSERT INTO `pupilsightHook` (`pupilsightHookID`, `name`, `type`, `options`, pupilsightModuleID) VALUES (NULL, 'Website Feed', 'Parental Dashboard', '".serialize($array)."', (SELECT pupilsightModuleID FROM pupilsightModule WHERE name='$name'));";
