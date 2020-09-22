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
$name = 'Moodle';
$description = 'A module to support support Moodle Integration. This module has no actions and is not seen by users, it just alters the database.';
$entryURL = '';
$type = 'Additional';
$category = '';
$version = '1.0.04';
$author = 'Ross Parker';
$url = 'http://rossparker.org';

//Module tables
$moduleTables[0] = "CREATE VIEW moodleUser AS SELECT username, preferredName, surname, email, website FROM pupilsightPerson JOIN pupilsightRole ON (pupilsightRole.pupilsightRoleID=pupilsightPerson.pupilsightRoleIDPrimary) WHERE (category='Student' OR category='Staff') AND status='Full';";
$moduleTables[1] = "CREATE VIEW moodleCourse AS SELECT * FROM `pupilsightCourse` WHERE pupilsightSchoolYearID=(SELECT pupilsightSchoolYearID FROM pupilsightSchoolYear Where status='Current');";
$moduleTables[2] = "CREATE VIEW moodleEnrolment AS SELECT DISTINCT pupilsightCourse.pupilsightCourseID, pupilsightCourse.name, username, role FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full' AND  pupilsightSchoolYearID=(SELECT pupilsightSchoolYearID FROM pupilsightSchoolYear Where status='Current');";

//Action rows (none)
;
