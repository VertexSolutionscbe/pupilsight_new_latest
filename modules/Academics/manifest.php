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
$name = 'Academics';
$description = 'Academics';
$entryURL = 'dashboard.php';
$type = 'Additional';
$category = 'Academics';
$version = '18.0.00';
$author = 'Ross Parker';
$url = 'http://rossparker.org';

//Module tables

//Action rows
$actionRows[0]['name'] = 'Manage Academics';
$actionRows[0]['precedence'] = '0';
$actionRows[0]['category'] = 'Manage Academics';
$actionRows[0]['description'] = 'Academics';
$actionRows[0]['URLList'] = 'dashboard.php';
$actionRows[0]['entryURL'] = 'dashboard.php';
$actionRows[0]['defaultPermissionAdmin'] = 'Y';
$actionRows[0]['defaultPermissionTeacher'] = 'N';
$actionRows[0]['defaultPermissionStudent'] = 'N';
$actionRows[0]['defaultPermissionParent'] = 'N';
$actionRows[0]['defaultPermissionSupport'] = 'N';
$actionRows[0]['categoryPermissionStaff'] = 'Y';
$actionRows[0]['categoryPermissionStudent'] = 'Y';
$actionRows[0]['categoryPermissionParent'] = 'Y';
$actionRows[0]['categoryPermissionOther'] = 'Y';

