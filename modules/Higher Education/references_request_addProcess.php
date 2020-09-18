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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

include __DIR__.'/../../pupilsight.php';

//Module includes
include __DIR__.'/moduleFunctions.php';


$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/references_request_add.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_request_add.php') == false) {
    //Fail 0
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
} else {
    //Check for student enrolment
    if (studentEnrolment($_SESSION[$guid]['pupilsightPersonID'], $connection2) == false) {
        //Fail 0
        $URL = $URL.'&return=error0';
        header("Location: {$URL}");
    } else {
        //Validate Inputs
        $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $type = $_POST['type'];
        $pupilsightPersonIDReferee = null;
        if (isset($_POST['pupilsightPersonIDReferee'])) {
            $pupilsightPersonIDReferee = $_POST['pupilsightPersonIDReferee'];
        }
        $status = 'Pending';
        $statusNotes = '';
        $notes = $_POST['notes'];
        $timestamp = date('Y-m-d H:i:s');

        if ($type == '' or ($type == 'US References' and $pupilsightPersonIDReferee == '')) {
            //Fail 3
            $URL = $URL.'&return=error3';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'type' => $type, 'status' => $status, 'statusNotes' => $statusNotes, 'notes' => $notes, 'timestamp' => $timestamp);
                $sql = 'INSERT INTO higherEducationReference SET pupilsightPersonID=:pupilsightPersonID, pupilsightSchoolYearID=:pupilsightSchoolYearID, type=:type, status=:status, statusNotes=:statusNotes, notes=:notes, timestamp=:timestamp';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                //Fail 2
                $URL = $URL.'&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $higherEducationReferenceID = $connection2->lastInsertID();

            //Set referees based on type of reference
            $partialFail = false;
            //Get new unit ID
            $higherEducationReferenceIDNew = $connection2->lastInsertID();
            if ($type == 'Composite Reference') {
                //Get subject teachers
                try {
                    $dataClass = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightPersonID' => $pupilsightPersonID);
                    $sqlClass = "SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourseClass.nameShort AS class, pupilsightCourse.nameShort AS course
                        FROM pupilsightCourse
                            JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                            JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                        WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID
                            AND pupilsightPersonID=:pupilsightPersonID
                            AND NOT role LIKE '%left'
                            AND pupilsightCourseClass.reportable='Y'
                            AND pupilsightCourseClassPerson.reportable='Y'
                        ORDER BY course, class";
                    $resultClass = $connection2->prepare($sqlClass);
                    $resultClass->execute($dataClass);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
                while ($rowClass = $resultClass->fetch()) {
                    try {
                        $dataTeacher = array('pupilsightCourseClassID' => $rowClass['pupilsightCourseClassID']);
                        $sqlTeacher = "SELECT pupilsightCourseClassPerson.pupilsightPersonID
                            FROM pupilsightCourseClassPerson
                                JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                            WHERE pupilsightCourseClassID=:pupilsightCourseClassID
                                AND role='Teacher'
                                AND pupilsightPerson.status='Full'";
                        $resultTeacher = $connection2->prepare($sqlTeacher);
                        $resultTeacher->execute($dataTeacher);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                    while ($rowTeacher = $resultTeacher->fetch()) {
                        try {
                            $dataInsert = array('higherEducationReferenceID' => $higherEducationReferenceIDNew, 'pupilsightPersonID' => $rowTeacher['pupilsightPersonID'], 'title' => $rowClass['course'].'.'.$rowClass['class']);
                            $sqlInsert = "INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, pupilsightPersonID=:pupilsightPersonID, status='Pending', type='Academic', title=:title";
                            $resultInsert = $connection2->prepare($sqlInsert);
                            $resultInsert->execute($dataInsert);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }
                }

                //Get tutors
                try {
                    $dataForm = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightPersonID' => $pupilsightPersonID);
                    $sqlForm = 'SELECT pupilsightRollGroup.* FROM pupilsightRollGroup JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID';
                    $resultForm = $connection2->prepare($sqlForm);
                    $resultForm->execute($dataForm);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
                if ($resultForm->rowCount() == 1) {
                    $rowForm = $resultForm->fetch();
                    if ($rowForm['pupilsightPersonIDTutor'] != '') {
                        try {
                            $dataInsert = array('higherEducationReferenceID' => $higherEducationReferenceIDNew, 'pupilsightPersonID' => $rowForm['pupilsightPersonIDTutor'], 'title' => $rowForm['nameShort']);
                            $sqlInsert = "INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, pupilsightPersonID=:pupilsightPersonID, status='Pending', type='Pastoral', title=:title";
                            $resultInsert = $connection2->prepare($sqlInsert);
                            $resultInsert->execute($dataInsert);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }
                    if ($rowForm['pupilsightPersonIDTutor2'] != '') {
                        try {
                            $dataInsert = array('higherEducationReferenceID' => $higherEducationReferenceIDNew, 'pupilsightPersonID' => $rowForm['pupilsightPersonIDTutor2'], 'title' => $rowForm['nameShort']);
                            $sqlInsert = "INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, pupilsightPersonID=:pupilsightPersonID, status='Pending', type='Pastoral', title=:title";
                            $resultInsert = $connection2->prepare($sqlInsert);
                            $resultInsert->execute($dataInsert);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }
                    if ($rowForm['pupilsightPersonIDTutor3'] != '') {
                        try {
                            $dataInsert = array('higherEducationReferenceID' => $higherEducationReferenceIDNew, 'pupilsightPersonID' => $rowForm['pupilsightPersonIDTutor3'], 'title' => $rowForm['nameShort']);
                            $sqlInsert = "INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, pupilsightPersonID=:pupilsightPersonID, status='Pending', type='Pastoral', title=:title";
                            $resultInsert = $connection2->prepare($sqlInsert);
                            $resultInsert->execute($dataInsert);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }
                }
            }
            if ($type == 'US Reference') {
                if ($pupilsightPersonIDReferee != '') {
                    try {
                        $dataInsert = array('higherEducationReferenceID' => $higherEducationReferenceIDNew, 'pupilsightPersonID' => $pupilsightPersonIDReferee);
                        $sqlInsert = "INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, pupilsightPersonID=:pupilsightPersonID, status='Pending', type='General', title=''";
                        $resultInsert = $connection2->prepare($sqlInsert);
                        $resultInsert->execute($dataInsert);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                }
            }

            //Attempt to notify coordinators
            try {
                $dataNotify = array();
                $sqlNotify = "SELECT pupilsightPerson.pupilsightPersonID FROM higherEducationStaff JOIN pupilsightPerson ON (higherEducationStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full' AND role='Coordinator'";
                $resultNotify = $connection2->prepare($sqlNotify);
                $resultNotify->execute($dataNotify);
            } catch (PDOException $e) {
            }
            while ($rowNotify = $resultNotify->fetch()) {
                $notificationText = sprintf(__('Someone has created a new Higher Education reference request.'));
                setNotification($connection2, $guid, $rowNotify['pupilsightPersonID'], $notificationText, 'Higher Education', '/index.php?q=/modules/Higher Education/references_manage.php');
            }

            if ($partialFail == true) {
                //Fail 5
                $URL = $URL.'&return=error5';
                header("Location: {$URL}");
            } else {
                //Success 0
                $URL = $URL.'&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
