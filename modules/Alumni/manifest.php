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

//Basica variables
$name = 'Alumni';
$description = 'The Alumni module allows schools to accept alumni registrations, and then link these to existing user accounts.';
$entryURL = 'alumni_manage.php';
$type = 'Additional';
$category = 'People';
$version = '0.6.00';
$author = 'Ross Parker';
$url = 'http://rossparker.org/free-learning';

//Module tables
$moduleTables[0] = "CREATE TABLE `alumniAlumnus` (  `alumniAlumnusID` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,  `title` varchar(5) NOT NULL,  `surname` varchar(30) NOT NULL DEFAULT '',  `firstName` varchar(30) NOT NULL DEFAULT '',  `officialName` varchar(150) NOT NULL,  `maidenName` varchar(30) NOT NULL,  `gender` enum('M','F','Other','Unspecified') NOT NULL DEFAULT 'Unspecified',  `username` varchar(20) NOT NULL,  `dob` date DEFAULT NULL,  `email` varchar(50) DEFAULT NULL,  `address1Country` varchar(255) NOT NULL,  `profession` varchar(30) NOT NULL,  `employer` varchar(30) NOT NULL,  `jobTitle` varchar(30) NOT NULL,  `graduatingYear` int(4) DEFAULT NULL,`formerRole` enum('Staff','Student','Parent','Other') DEFAULT NULL, `pupilsightPersonID` int(10) DEFAULT NULL, `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`alumniAlumnusID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

//Settings
$pupilsightSetting[0] = "INSERT INTO `pupilsightSetting` (`pupilsightSettingID` ,`scope` ,`name` ,`nameDisplay` ,`description` ,`value`) VALUES (NULL , 'Alumni', 'showPublicRegistration', 'Show Public Registration', 'Should the alumni registration form be displayed on the school\'s Pupilsight homepage, or available via a link only?.', 'Y');";
$pupilsightSetting[1] = "INSERT INTO `pupilsightSetting` (`pupilsightSettingID` ,`scope` ,`name` ,`nameDisplay` ,`description` ,`value`) VALUES (NULL , 'Alumni', 'facebookLink', 'Facebook Link', 'A URL pointing to a Facebook page for the school\'s alumni group.', '');";

//Action rows
$actionRows[0]['name'] = 'Manage Alumni';
$actionRows[0]['precedence'] = '0';
$actionRows[0]['category'] = 'Admin';
$actionRows[0]['description'] = 'Allows privileged users to manage all alumni records.';
$actionRows[0]['URLList'] = 'alumni_manage.php, alumni_manage_add.php, alumni_manage_edit.php, alumni_manage_delete.php';
$actionRows[0]['entryURL'] = 'alumni_manage.php';
$actionRows[0]['defaultPermissionAdmin'] = 'Y';
$actionRows[0]['defaultPermissionTeacher'] = 'N';
$actionRows[0]['defaultPermissionStudent'] = 'N';
$actionRows[0]['defaultPermissionParent'] = 'N';
$actionRows[0]['defaultPermissionSupport'] = 'N';
$actionRows[0]['categoryPermissionStaff'] = 'Y';
$actionRows[0]['categoryPermissionStudent'] = 'Y';
$actionRows[0]['categoryPermissionParent'] = 'Y';
$actionRows[0]['categoryPermissionOther'] = 'Y';

//Hooks
$array = array();
$array['toggleSettingName'] = 'showPublicRegistration';
$array['toggleSettingScope'] = 'Alumni';
$array['toggleSettingValue'] = 'Y';
$array['title'] = 'Alumni Registration';
$array['text'] = "Are you a former member of our school community? If so, please do <a href=\'./index.php?q=/modules/Alumni/publicRegistration.php\'>register as an alumnus of the school</a>.";
$hooks[0] = "INSERT INTO `pupilsightHook` (`pupilsightHookID`, `name`, `type`, `options`, pupilsightModuleID) VALUES (NULL, 'Alumni', 'Public Home Page', '".serialize($array)."', (SELECT pupilsightModuleID FROM pupilsightModule WHERE name='$name'));";
