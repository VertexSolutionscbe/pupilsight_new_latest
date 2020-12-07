<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $importReturn = $_GET['importReturn'] ?? '';

    $page->breadcrumbs
        ->add(__('Manage Timetables'), 'tt.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Import Timetable Data'));

    $importReturnMessage = '';
    $class = 'error';
    if (!($importReturn == '')) {
        if ($importReturn == 'fail0') {
            $importReturnMessage = __('Your request failed because you do not have access to this action.');
        } elseif ($importReturn == 'fail1') {
            $importReturnMessage = __('Your request failed because your inputs were invalid.');
        } elseif ($importReturn == 'fail2') {
            $importReturnMessage = __('Your request failed due to a database error.');
        } elseif ($importReturn == 'fail3') {
            $importReturnMessage = __('Your request failed because your inputs were invalid.');
        }
        echo "<div class='$class'>";
        echo $importReturnMessage;
        echo '</div>';
    }

    //Check if school year specified
    $pupilsightTTID = $_GET['pupilsightTTID'];
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    if ($pupilsightTTID == '' or $pupilsightSchoolYearID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightTTID' => $pupilsightTTID);
            $sql = 'SELECT * FROM pupilsightTT WHERE pupilsightTTID=:pupilsightTTID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $row = $result->fetch();

            if (isset($_GET['step'])) {
                $step = $_GET['step'];
            } else {
                $step = 1;
            }
            if (($step != 1) and ($step != 2) and ($step != 3)) {
                $step = 1;
            }

            //STEP 1, SELECT TERM
            if ($step == 1) {
                echo '<h2>';
					echo __('Step 1 - Select CSV Files');
				echo '</h2>';
				echo '<p>';
					echo __('This page allows you to import timetable data from a CSV file. The import includes all classes and their teachers. There is no support for importing students: these need to be entered manually into the relevant classes. The system will do its best to keep existing data intact, whilst updating what is necessary (note: you will lose student exceptions from timetabled classes). Select the CSV files you wish to use for the synchronise operation.')."<br/>";
				echo '</p>';

                $form = Form::create('importTimetable', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/tt_import.php&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=$pupilsightSchoolYearID&step=2");

                $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                $row = $form->addRow();
                    $row->addLabel('file', __('CSV File'))->description(__('See Notes below for specification.'));
                    $row->addFileUpload('file')->required();

                $row = $form->addRow();
                    $row->addLabel('fieldDelimiter', __('Field Delimiter'));
                    $row->addTextField('fieldDelimiter')->required()->maxLength(1)->setValue(',');

                $row = $form->addRow();
                    $row->addLabel('stringEnclosure', __('String Enclosure'));
                    $row->addTextField('stringEnclosure')->required()->maxLength(1)->setValue('"');

                $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit();

                echo $form->getOutput();

                echo '<h4>';
				echo __('Notes');
				echo '</h4>';
				echo '<ol>';
					echo '<li>'.__('You may only submit CSV files.').'</li>';
					echo '<li>'.__('Imports cannot be run concurrently (e.g. make sure you are the only person importing at any one time).').'</li>';
					echo '<li>'.__('The import includes course, class, period, teacher and room information: the structure of the target timetable must already be in place.').'</li>';
					echo '<li>'.__('The import does not include student lists.').'</li>';
					echo '<li>'.__('The submitted file must have the following fields in the following order:').'</li>';
						echo '<ol>';
							echo '<li>'.__('Course Short Name - e.g. DR10 for Year 10 Drama').'</li>';
							echo '<li>'.__('Class Short Name - e.g 1 for DR10.1').'</li>';
							echo '<li>'.__('Day Name - as used in the target timetable').'</li>';
							echo '<li>'.__('Row Long Name - as used in the target timetable').'</li>';
							echo '<li>'.__('Teacher Username - comma-separated list of Pupilsight usernames for teacher(s) of the lesson. Alternatively, give each teacher their own row.').'</li>';
							echo '<li>'.__('Space Name - the Pupilsight name for the room the lesson takes place in.').'</li>';
						echo '</ol>';
					echo '</li>';
					echo '<li>'.__('Do not include a header row in the CSV files.').'</li>';
				echo '</ol>';
            } elseif ($step == 2) {
                echo '<h2>';
					echo __('Step 2 - Data Check & Confirm');
				echo '</h2>';

                //Check file type
                if (($_FILES['file']['type'] != 'text/csv') and ($_FILES['file']['type'] != 'text/comma-separated-values') and ($_FILES['file']['type'] != 'text/x-comma-separated-values') and ($_FILES['file']['type'] != 'application/vnd.ms-excel') and ($_FILES['file']['type'] != 'application/csv')) {
                    ?>
					<div class='alert alert-danger'>
						<?php echo sprintf(__('Import cannot proceed, as the submitted file has a MIME-TYPE of %1$s, and as such does not appear to be a CSV file.'), $_FILES['file']['type']) ?><br/>
					</div>
					<?php

                } elseif (($_POST['fieldDelimiter'] == '') or ($_POST['stringEnclosure'] == '')) {
                    ?>
					<div class='alert alert-danger'>
						<?php echo __('Import cannot proceed, as the "Field Delimiter" and/or "String Enclosure" fields have been left blank.') ?><br/>
					</div>
					<?php

                } else {
                    $proceed = true;

                    //PREPARE TABLES
                    echo '<h4>';
                    echo __('Prepare Database Tables');
                    echo '</h4>';
                    //Lock tables
                    $lockFail = false;
                    try {
                        $sql = 'LOCK TABLES pupilsightTTImport WRITE,
						pupilsightPerson WRITE,
						pupilsightSpace WRITE,
						pupilsightTTDay WRITE,
						pupilsightTT WRITE,
						pupilsightTTColumn WRITE,
						pupilsightTTColumnRow WRITE,
						pupilsightTTDayRowClass WRITE,
						pupilsightTTDayRowClassException WRITE,
						pupilsightCourse WRITE,
						pupilsightCourseClass WRITE,
						pupilsightCourseClassPerson WRITE';
                        $result = $connection2->query($sql);
                    } catch (PDOException $e) {
                        $lockFail = true;
                        $proceed = false;
                    }
                    if ($lockFail == true) {
                        echo "<div class='alert alert-danger'>";
                        echo __('The database could not be locked for use.');
                        echo '</div>';
                    } elseif ($lockFail == false) {
                        echo "<div class='alert alert-sucess'>";
                        echo __('The database was successfully locked.');
                        echo '</div>';
                    }
                    //Empty table pupilsightTTImport
                    $emptyFail = false;
                    try {
                        $sql = 'DELETE FROM pupilsightTTImport ';
                        $result = $connection2->query($sql);
                    } catch (PDOException $e) {
                        $emptyFail = true;
                        $proceed = false;
                    }
                    if ($emptyFail == true) {
                        echo "<div class='alert alert-danger'>";
                        echo __('The database tables could not be emptied.');
                        echo '</div>';
                    } elseif ($emptyFail == false) {
                        echo "<div class='alert alert-sucess'>";
                        echo __('The database tables were successfully emptied.');
                        echo '</div>';
                    }

                    //TURN IMPORT FILE INTO pupilsightTTImport
                    if ($proceed == true) {
                        echo '<h4>';
                        echo __('File Import');
                        echo '</h4>';
                        $importFail = false;
                        $csvFile = $_FILES['file']['tmp_name'];
                        $handle = fopen($csvFile, 'r');
                        while (($data = fgetcsv($handle, 100000, stripslashes($_POST['fieldDelimiter']), stripslashes($_POST['stringEnclosure']))) !== false) {
                            try {
                                $data = array('courseNameShort' => $data[0], 'classNameShort' => $data[1], 'dayName' => $data[2], 'rowName' => $data[3], 'teacherUsernameList' => $data[4], 'spaceName' => $data[5]);
                                $sql = 'INSERT INTO pupilsightTTImport SET courseNameShort=:courseNameShort, classNameShort=:classNameShort, dayName=:dayName, rowName=:rowName, teacherUsernameList=:teacherUsernameList, spaceName=:spaceName';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                $importFail = true;
                                $proceed = false;
                            }
                        }
                        fclose($handle);
                        if ($importFail == true) {
                            echo "<div class='alert alert-danger'>";
                            echo __('The import file could not be temporarily stored in the database for analysis.');
                            echo '</div>';
                        } elseif ($importFail == false) {
                            echo "<div class='alert alert-sucess'>";
                            echo __('The import file was successfully stored in the database for analysis.');
                            echo '</div>';
                        }
                    }

                    //STAFF CHECK
                    if ($proceed == true) {
                        echo '<h4>';
                        echo 'Staff Check';
                        echo '</h4>';
                        $staffCheckFail = false;
                        //Get list of staff from import
                        try {
                            $data = array();
                            $sql = 'SELECT DISTINCT teacherUsernameList FROM pupilsightTTImport ORDER BY teacherUsernameList';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $staffCheckFail = true;
                            $proceed = false;
                        }
                        //Check each member of staff from import file against Pupilsight
                        if ($staffCheckFail == false) {
                            $staffs = array();
                            $count = 0;
                            while ($row = $result->fetch()) {
                                $staffTemps = explode(',', $row['teacherUsernameList']);
                                foreach ($staffTemps as $staffTemp) {
                                    $staffs[$count] = trim($staffTemp);
                                    ++$count;
                                }
                            }

                            sort($staffs);
                            $staffs = array_unique($staffs);
                            $errorList = '';
                            foreach ($staffs as $staff) {
                                try {
                                    $data = array('username' => $staff);
                                    $sql = 'SELECT * FROM pupilsightPerson WHERE username=:username';
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                }

                                if ($result->rowCount() != 1) {
                                    $staffCheckFail = true;
                                    $proceed = false;
                                    $errorList .= "$staff, ";
                                }
                            }
                        }
                        if ($staffCheckFail == true) {
                            echo "<div class='alert alert-danger'>";
                            echo sprintf(__('Staff check failed. The following staff were in the import file but could not be found in Pupilsight: %1$s. Add the staff into Pupilsight and then try the import again.'), substr($errorList, 0, -2));
                            echo '</div>';
                        } elseif ($staffCheckFail == false) {
                            echo "<div class='alert alert-sucess'>";
                            echo __('The staff check was successfully completed: all staff in the import file were found in Pupilsight.');
                            echo '</div>';
                        }
                    }

                    //SPACE CHECK
                    if ($proceed == true) {
                        echo '<h4>';
                        echo 'Space Check';
                        echo '</h4>';
                        $spaceCheckFail = false;
                        //Get list of spaces from import
                        try {
                            $data = array();
                            $sql = 'SELECT DISTINCT spaceName FROM pupilsightTTImport ORDER BY spaceName';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $spaceCheckFail = true;
                            $proceed = false;
                        }
                        //Check each member of staff from import file against Pupilsight
                        if ($spaceCheckFail == false) {
                            $errorList = '';
                            while ($row = $result->fetch()) {
                                try {
                                    $dataSpace = array('name' => $row['spaceName']);
                                    $sqlSpace = 'SELECT * FROM pupilsightSpace WHERE name=:name';
                                    $resultSpace = $connection2->prepare($sqlSpace);
                                    $resultSpace->execute($dataSpace);
                                } catch (PDOException $e) {
                                }

                                if ($resultSpace->rowCount() != 1) {
                                    $spaceCheckFail = true;
                                    $proceed = false;
                                    $errorList .= $row['spaceName'].', ';
                                }
                            }
                        }
                        if ($spaceCheckFail == true) {
                            echo "<div class='alert alert-danger'>";
                            echo sprintf(__('Space check failed. The following spaces were in the import file but could not be found in Pupilsight: %1$s. Add the spaces into Pupilsight and then try the import again.'), substr($errorList, 0, -2));
                            echo '</div>';
                        } elseif ($spaceCheckFail == false) {
                            echo "<div class='alert alert-sucess'>";
                            echo __('The space check was successfully completed: all spaces in the import file were found in Pupilsight.');
                            echo '</div>';
                        }
                    }

                    //DAY CHECK
                    if ($proceed == true) {
                        echo '<h4>';
                        echo 'Day Check';
                        echo '</h4>';
                        $dayCheckFail = false;
                        //Get list of spaces from import
                        try {
                            $data = array();
                            $sql = 'SELECT DISTINCT dayName FROM pupilsightTTImport ORDER BY dayName';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $dayCheckFail = true;
                            $proceed = false;
                        }
                        //Check each member of staff from import file against Pupilsight
                        if ($dayCheckFail == false) {
                            $errorList = '';
                            while ($row = $result->fetch()) {
                                try {
                                    $dataSpace = array('name' => $row['dayName'], 'pupilsightTTID' => $pupilsightTTID);
                                    $sqlSpace = 'SELECT * FROM pupilsightTTDay WHERE name=:name AND pupilsightTTID=:pupilsightTTID';
                                    //print_r($dataSpace);print_r($sqlSpace);die();
                                    $resultSpace = $connection2->prepare($sqlSpace);
                                    $resultSpace->execute($dataSpace);
                                } catch (PDOException $e) {
                                }

                                if ($resultSpace->rowCount() != 1) {
                                    $dayCheckFail = true;
                                    $proceed = false;
                                    $errorList .= $row['dayName'].', ';
                                }
                            }
                        }
                        if ($dayCheckFail == true) {
                            echo "<div class='alert alert-danger'>";
                            echo sprintf(__('Day check failed. The following days were in the import file but could not be found in Pupilsight: %1$s. Add the days into Pupilsight and then try the import again.'), substr($errorList, 0, -2));
                            echo '</div>';
                        } elseif ($dayCheckFail == false) {
                            echo "<div class='alert alert-sucess'>";
                            echo __('The day check was successfully completed: all days in the import file were found in Pupilsight in the specified timetable.');
                            echo '</div>';
                        }
                    }

                    //ROW CHECK
                    if ($proceed == true) {
                        echo '<h4>';
                        echo 'Row Check';
                        echo '</h4>';
                        $rowCheckFail = false;
                        //Get list of spaces from import
                        try {
                            $data = array();
                            $sql = 'SELECT DISTINCT dayName, rowName FROM pupilsightTTImport ORDER BY dayName, rowName';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $rowCheckFail = true;
                            $proceed = false;
                        }
                        //Check each member of staff from import file against Pupilsight
                        if ($rowCheckFail == false) {
                            $errorList = '';
                            while ($row = $result->fetch()) {
                                try {
                                    $dataSpace = array('rowName' => $row['rowName'], 'dayName' => $row['dayName'], 'pupilsightTTID' => $pupilsightTTID);
                                    //$sqlSpace = 'SELECT pupilsightTTColumnRow.name, pupilsightTTDay.name FROM pupilsightTTDay JOIN pupilsightTT ON (pupilsightTTDay.pupilsightTTID=pupilsightTT.pupilsightTTID) JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTColumnRow ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) WHERE pupilsightTT.pupilsightTTID=:pupilsightTTID AND pupilsightTTColumnRow.name=:rowName AND pupilsightTTDay.name=:dayName';
                                    $sqlSpace = 'select pupilsightTTColumnRow.name, pupilsightTTDay.name from pupilsightTT, pupilsightTTDay, pupilsightTTColumnRow, pupilsightTTColumn
where pupilsightTT.pupilsightTTID=pupilsightTTDay.pupilsightTTID and 
pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID and
pupilsightTT.pupilsightTTID=:pupilsightTTID and pupilsightTTColumnRow.name=:rowName and  pupilsightTTDay.name=:dayName';
                                    //print_r($sqlSpace); print_r($dataSpace);die();
                                    $resultSpace = $connection2->prepare($sqlSpace);
                                    $resultSpace->execute($dataSpace);
                                    //print_r($resultSpace->rowCount());die();
                                } catch (PDOException $e) {
                                }

                                if ($resultSpace->rowCount() < 1) {
                                    $rowCheckFail = true;
                                    $proceed = false;
                                    $errorList .= $row['dayName'].' '.$row['rowName'].', ';
                                }
                            }
                        }
                        if ($rowCheckFail == true) {
                            echo "<div class='alert alert-danger'>";
                            echo sprintf(__('Row check failed. The following rows were in the import file but could not be found in Pupilsight: %1$s. Add the rows into Pupilsight and then try the import again.'), substr($errorList, 0, -2));
                            echo '</div>';
                        } elseif ($rowCheckFail == false) {
                            echo "<div class='alert alert-sucess'>";
                            echo __('The row check was successfully completed: all rows in the import file were found in Pupilsight in the specified timetable on the specified days.');
                            echo '</div>';
                        }
                    }

                    //COURSE CHECK
                    if ($proceed == true) {
                        echo '<h4>';
                        echo 'Course Check';
                        echo '</h4>';
                        $courseCheckFail = false;
                        //Get list of courses from import
                        try {
                            $data = array();
                            $sql = 'SELECT DISTINCT courseNameShort FROM pupilsightTTImport ORDER BY courseNameShort';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $courseCheckFail = true;
                            $proceed = false;
                        }
                        //Check each course from import file against Pupilsight
                        if ($courseCheckFail == false) {
                            $errorList = '';
                            $makeList = '';
                            while ($row = $result->fetch()) {
                                $makeFail = false;
                                try {
                                    $dataSpace = array('nameShort' => $row['courseNameShort'], 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                                    $sqlSpace = 'SELECT nameShort FROM pupilsightCourse WHERE nameShort=:nameShort AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
                                    $resultSpace = $connection2->prepare($sqlSpace);
                                    $resultSpace->execute($dataSpace);
                                } catch (PDOException $e) {
                                }

                                if ($resultSpace->rowCount() != 1) {
                                    //Make the course
                                    try {
                                        $dataMake = array('name' => $row['courseNameShort'], 'nameShort' => $row['courseNameShort'], 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                                        $sqlMake = 'INSERT INTO pupilsightCourse SET name=:name, nameShort=:nameShort, pupilsightSchoolYearID=:pupilsightSchoolYearID';
                                        $resultMake = $connection2->prepare($sqlMake);
                                        $resultMake->execute($dataMake);
                                    } catch (PDOException $e) {
                                        $makeFail = true;
                                        $courseCheckFail = true;
                                        $proceed = false;
                                        $errorList .= $row['courseNameShort'].', ';
                                    }
                                    if ($makeFail == false) {
                                        $makeList .= $row['courseNameShort'].', ';
                                    }
                                }
                            }
                        }
                        if ($courseCheckFail == true) {
                            echo "<div class='alert alert-danger'>";
                            echo sprintf(__('Course check failed. The following courses were in the import file but could not be found or made in Pupilsight: %1$s. Add the courses into Pupilsight and then try the import again.'), substr($errorList, 0, -2));
                            echo '</div>';
                        } elseif ($courseCheckFail == false) {
                            echo "<div class='alert alert-sucess'>";
                            echo __('The course check was successfully completed: all courses in the import file were found in or added to Pupilsight.');
                            if ($makeList != '') {
                                echo ' '.sprintf(__('The following courses were added to Pupilsight: %1$s.'), substr($makeList, 0, -2));
                            }
                            echo '</div>';
                        }
                    }

                    //CLASS CHECK
                    if ($proceed == true) {
                        echo '<h4>';
                        echo 'Class Check';
                        echo '</h4>';
                        $classCheckFail = false;
                        //Get list of class from import
                        try {
                            $data = array();
                            $sql = 'SELECT DISTINCT courseNameShort, classNameShort FROM pupilsightTTImport ORDER BY courseNameShort, classNameShort';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $classCheckFail = true;
                            $proceed = false;
                        }
                        //Check each class from import file against Pupilsight
                        if ($classCheckFail == false) {
                            $errorList = '';
                            $makeList = '';
                            while ($row = $result->fetch()) {
                                $makeFail = false;
                                try {
                                    $dataSpace = array('classNameShort' => $row['classNameShort'], 'courseNameShort' => $row['courseNameShort'], 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                                    $sqlSpace = 'SELECT pupilsightCourseClass.nameShort FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.nameShort=:courseNameShort AND pupilsightCourseClass.nameShort=:classNameShort AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
                                    $resultSpace = $connection2->prepare($sqlSpace);
                                    $resultSpace->execute($dataSpace);
                                } catch (PDOException $e) {
                                }

                                if ($resultSpace->rowCount() != 1) {
                                    //Make the class
                                    try {
                                        $dataMake = array('name' => $row['classNameShort'], 'nameShort' => $row['classNameShort'], 'courseNameShort' => $row['courseNameShort'], 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                                        $sqlMake = 'INSERT INTO pupilsightCourseClass SET name=:name, nameShort=:nameShort, pupilsightCourseID=(SELECT pupilsightCourseID FROM pupilsightCourse WHERE nameShort=:courseNameShort AND pupilsightSchoolYearID=:pupilsightSchoolYearID)';
                                        $resultMake = $connection2->prepare($sqlMake);
                                        $resultMake->execute($dataMake);
                                    } catch (PDOException $e) {
                                        $makeFail = true;
                                        $classCheckFail = true;
                                        $proceed = false;
                                        $errorList .= $row['courseNameShort'].'.'.$row['classNameShort'].', ';
                                    }
                                    if ($makeFail == false) {
                                        $makeList .= $row['courseNameShort'].'.'.$row['classNameShort'].', ';
                                    }
                                }
                            }
                        }
                        if ($classCheckFail == true) {
                            echo "<div class='alert alert-danger'>";
                            echo sprintf(__('Class check failed. The following classes were in the import file but could not be found or made in Pupilsight: %1$s. Add the classes into Pupilsight and then try the import again.'), substr($errorList, 0, -2));
                            echo '</div>';
                        } elseif ($classCheckFail == false) {
                            echo "<div class='alert alert-sucess'>";
                            echo __('The class check was successfully completed: all classes in the import file were found in or added to Pupilsight.');
                            if ($makeList != '') {
                                echo ' '.sprintf(__('The following classes were added to Pupilsight: %1$s.'), substr($makeList, 0, -2));
                            }
                            echo '</div>';
                        }
                    }

                    //TEACHER SYNC
                    if ($proceed == true) {
                        echo '<h4>';
                        echo __('Teacher Sync');
                        echo '</h4>';
                        $teacherSyncFail = false;
                        //Get list of classes from import
                        try {
                            $data = array();
                            $sql = 'SELECT DISTINCT courseNameShort, classNameShort FROM pupilsightTTImport ORDER BY courseNameShort, classNameShort';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $teacherSyncFail = true;
                            $proceed = false;
                        }
                        //Check each class from import file against Pupilsight
                        if ($teacherSyncFail == false) {
                            $errorList = '';
                            while ($row = $result->fetch()) {
                                //Get pupilsightCourseClassID
                                $checkFail = false;
                                try {
                                    $dataCheck = array('classNameShort' => $row['classNameShort'], 'courseNameShort' => $row['courseNameShort'], 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                                    $sqlCheck = 'SELECT pupilsightCourseClassID FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.nameShort=:courseNameShort AND pupilsightCourseClass.nameShort=:classNameShort AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
                                    $resultCheck = $connection2->prepare($sqlCheck);
                                    $resultCheck->execute($dataCheck);
                                } catch (PDOException $e) {
                                    $checkFail = true;
                                }

                                if ($resultCheck->rowCount() != 1 or $checkFail == true) {
                                    $teacherSyncFail = true;
                                    $checkFail = true;
                                    $proceed = false;
                                    $errorList .= $row['courseNameShort'].'.'.$row['classNameShort'].', ';
                                } elseif ($resultCheck->rowCount() == 1 and $checkFail == false) {
                                    $rowCheck = $resultCheck->fetch();
                                    //Remove teachers
                                    $removeFail = false;
                                    try {
                                        $dataCheck = array('pupilsightCourseClassID' => $rowCheck['pupilsightCourseClassID']);
                                        $sqlCheck = "DELETE FROM pupilsightCourseClassPerson WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND role='Teacher'";
                                        $resultCheck = $connection2->prepare($sqlCheck);
                                        $resultCheck->execute($dataCheck);
                                    } catch (PDOException $e) {
                                        $teacherSyncFail = true;
                                        $removeFail = true;
                                        $proceed = false;
                                    }

                                    if ($removeFail == false) {
                                        //Get teachers from import
                                        $getFail = false;
                                        try {
                                            $dataGet = array('classNameShort' => $row['classNameShort'], 'courseNameShort' => $row['courseNameShort']);
                                            $sqlGet = 'SELECT DISTINCT teacherUsernameList FROM pupilsightTTImport WHERE classNameShort=:classNameShort AND courseNameShort=:courseNameShort';
                                            $resultGet = $connection2->prepare($sqlGet);
                                            $resultGet->execute($dataGet);
                                        } catch (PDOException $e) {
                                            $teacherSyncFail = true;
                                            $getFail = true;
                                            $proceed = false;
                                            $errorList .= $row['courseNameShort'].'.'.$row['classNameShort'].', ';
                                        }

                                        if ($getFail == false) {
                                            //Sort teachers into array
                                            $staffs = array();
                                            $count = 0;
                                            while ($rowGet = $resultGet->fetch()) {
                                                $staffTemps = explode(',', $rowGet['teacherUsernameList']);
                                                foreach ($staffTemps as $staffTemp) {
                                                    $staffs[$count] = trim($staffTemp);
                                                    ++$count;
                                                }
                                            }
                                            sort($staffs);
                                            $staffs = array_unique($staffs);

                                            //Add teachers
                                            foreach ($staffs as $staff) {
                                                //Convert username into ID
                                                try {
                                                    $dataConvert = array('username' => $staff);
                                                    $sqlConvert = "SELECT pupilsightPersonID FROM pupilsightPerson WHERE username=:username AND status='Full'";
                                                    $resultConvert = $connection2->prepare($sqlConvert);
                                                    $resultConvert->execute($dataConvert);
                                                } catch (PDOException $e) {
                                                    $teacherSyncFail = true;
                                                    $proceed = false;
                                                }

                                                if ($resultConvert->rowCount() != 1) {
                                                    $errorList .= $staff.', ';
                                                    $teacherSyncFail = true;
                                                    $proceed = false;
                                                } else {
                                                    $rowConvert = $resultConvert->fetch();

                                                    //Write ID to pupilsightCourseClassPerson
                                                    try {
                                                        $dataMake = array('pupilsightPersonID' => $rowConvert['pupilsightPersonID'], 'pupilsightCourseClassID' => $rowCheck['pupilsightCourseClassID']);
                                                        $sqlMake = "INSERT INTO pupilsightCourseClassPerson SET pupilsightPersonID=:pupilsightPersonID, pupilsightCourseClassID=:pupilsightCourseClassID, role='Teacher'";
                                                        $resultMake = $connection2->prepare($sqlMake);
                                                        $resultMake->execute($dataMake);
                                                    } catch (PDOException $e) {
                                                        $classCheckFail = true;
                                                        $proceed = false;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if ($teacherSyncFail == true) {
                            echo "<div class='alert alert-danger'>";
                            echo sprintf(__('Teacher sync failed. The following classes/teachers (and possibly some others) had problems: %1$s.'), substr($errorList, 0, -2));
                            echo '</div>';
                        } elseif ($teacherSyncFail == false) {
                            echo "<div class='alert alert-sucess'>";
                            echo __('The teacher sync was successfully completed: all teachers in the import file were added to the relevant classes in Pupilsight.');
                            echo '</div>';
                        }
                    }

                    //UNLOCK TABLES
                    try {
                        $sql = 'UNLOCK TABLES';
                        $result = $connection2->query($sql);
                    } catch (PDOException $e) {
                    }

                    //SPIT OUT RESULT
                    echo '<h4>';
                    echo __('Final Decision');
                    echo '</h4>';
                    if ($proceed == false) {
                        echo "<div class='alert alert-danger'>";
                        echo '<b><u>'.__('You cannot proceed. Fix the issues listed above and try again.').'</u></b>';
                        echo '</div>';
                    } elseif ($proceed == true) {
                        echo "<div class='alert alert-sucess'>";
                        echo '<b><u>'.sprintf(__('You are ready to go. %1$sClick here to import the timetable. Your old timetable will be obliterated%2$s.'), "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/tt_import.php&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=$pupilsightSchoolYearID&step=3'>", '</a>').'</u></b>';
                        echo '</div>';
                    }
                }
            } elseif ($step == 3) {
                ?>
				<h2>
					<?php echo __('Step 3 - Import') ?>
				</h2>
				<?php

                $proceed = true;

                //REMOVE OLD PERIODS
                $ttSyncRemoveFail = false;
                if ($proceed == true) {
                    echo '<h4>';
                    echo __('Remove Old Periods');
                    echo '</h4>';
                    try {
                        $dataDays = array('pupilsightTTID' => $pupilsightTTID);
                        $sqlDays = 'SELECT * FROM pupilsightTTDay WHERE pupilsightTTID=:pupilsightTTID';
                        $resultDays = $connection2->prepare($sqlDays);
                        $resultDays->execute($dataDays);
                    } catch (PDOException $e) {
                        $ttSyncRemoveFail = true;
                        $proceed = false;
                    }

                    if ($resultDays->rowCount() < 1) {
                        $ttSyncRemoveFail = true;
                        $proceed = false;
                    } else {
                        while ($rowDays = $resultDays->fetch()) {
                            try {
                                $dataRemove = array();
                                $sqlRemove = 'SELECT * FROM pupilsightTTDayRowClass WHERE pupilsightTTDayID='.$rowDays['pupilsightTTDayID'];
                                $resultRemove = $connection2->prepare($sqlRemove);
                                $resultRemove->execute($dataRemove);
                            } catch (PDOException $e) {
                                $ttSyncRemoveFail = true;
                                $proceed = false;
                            }

                            while ($rowRemove = $resultRemove->fetch()) {
                                try {
                                    $dataRemove2 = array();
                                    $sqlRemove2 = 'DELETE FROM pupilsightTTDayRowClassException WHERE pupilsightTTDayRowClassID='.$rowRemove['pupilsightTTDayRowClassID'];
                                    $resultRemove2 = $connection2->prepare($sqlRemove2);
                                    $resultRemove2->execute($dataRemove2);
                                } catch (PDOException $e) {
                                    $ttSyncRemoveFail = true;
                                    $proceed = false;
                                }
                            }

                            try {
                                $dataRemove3 = array();
                                $sqlRemove3 = 'DELETE FROM pupilsightTTDayRowClass WHERE pupilsightTTDayID='.$rowDays['pupilsightTTDayID'];
                                $resultRemove3 = $connection2->prepare($sqlRemove3);
                                $resultRemove3->execute($dataRemove3);
                            } catch (PDOException $e) {
                                $ttSyncRemoveFail = true;
                                $proceed = false;
                            }
                        }
                    }

                    if ($ttSyncRemoveFail == true) {
                        echo "<div class='alert alert-danger'>";
                        echo __('Removal of old periods failed.');
                        echo '</div>';
                    } elseif ($ttSyncRemoveFail == false) {
                        echo "<div class='alert alert-sucess'>";
                        echo __('Removal of old periods was successful.');
                        echo '</div>';
                    }
                }

                //ADD PERIODS
                if ($proceed == true) {
                    echo '<h4>';
                    echo __('Add Periods');
                    echo '</h4>';
                    if ($ttSyncRemoveFail == false) {
                        $ttSyncFail = false;
                        //Get all periods from pupilsightTTImport
                        try {
                            $data = array();
                            $sql = 'SELECT DISTINCT courseNameShort, classNameShort, dayName, rowName, spaceName FROM pupilsightTTImport ORDER BY courseNameShort, classNameShort, dayName, rowName';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $ttSyncFail = true;
                            $proceed = false;
                        }

                        if ($ttSyncFail == false) {
                            while ($row = $result->fetch()) {
                                //For each period, make a list of teachers
                                $getFail = false;
                                try {
                                    $dataGet = array('classNameShort' => $row['classNameShort'], 'courseNameShort' => $row['courseNameShort'], 'dayName' => $row['dayName'], 'rowName' => $row['rowName']);
                                    $sqlGet = 'SELECT DISTINCT teacherUsernameList FROM pupilsightTTImport WHERE classNameShort=:classNameShort AND courseNameShort=:courseNameShort AND dayName=:dayName AND rowName=:rowName';
                                    $resultGet = $connection2->prepare($sqlGet);
                                    $resultGet->execute($dataGet);
                                } catch (PDOException $e) {
                                    $ttSyncFail = true;
                                    $getFail = true;
                                    $proceed = false;
                                }
                                if ($getFail == false) {
                                    $staffs = array();
                                    $count = 0;
                                    while ($rowGet = $resultGet->fetch()) {
                                        $staffTemps = explode(',', $rowGet['teacherUsernameList']);
                                        foreach ($staffTemps as $staffTemp) {
                                            $staffs[$count] = trim($staffTemp);
                                            ++$count;
                                        }
                                    }
                                    sort($staffs);
                                    $staffs = array_unique($staffs);
                                }

                                $addFail = false;
                                try {
                                    $dataRow = array('name1' => $row['dayName'], 'name2' => $row['rowName'], 'pupilsightTTID' => $pupilsightTTID);
                                    $sqlRow = '(SELECT pupilsightTTColumnRowID FROM pupilsightTTDay /*JOIN pupilsightTTColumn ON (pupilsightTTColumn.pupilsightTTColumnID=pupilsightTTDay.pupilsightTTColumnID)*/ JOIN pupilsightTTColumnRow ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTDay.pupilsightTTColumnID) WHERE pupilsightTTDay.name=:name1 AND pupilsightTTColumnRow.name=:name2 AND pupilsightTTDay.pupilsightTTID=:pupilsightTTID)';
                                    //print_r($dataRow);print_r($sqlRow);die();
                                    $resultRow = $connection2->prepare($sqlRow);
                                    $resultRow->execute($dataRow);

                                    $dataDay = array('name' => $row['dayName'], 'pupilsightTTID' => $pupilsightTTID);
                                    $sqlDay = '(SELECT pupilsightTTDayID FROM pupilsightTTDay WHERE name=:name AND pupilsightTTID=:pupilsightTTID)';
                                    //print_r($dataDay); print_r($sqlDay); die();
                                    $resultDay = $connection2->prepare($sqlDay);
                                    $resultDay->execute($dataDay);

                                    $dataClass = array('nameShort1' => $row['courseNameShort'], 'nameShort2' => $row['classNameShort'], 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                                    $sqlClass = '(SELECT pupilsightCourseClassID FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.nameShort=:nameShort1 AND pupilsightCourseClass.nameShort=:nameShort2 AND pupilsightSchoolYearID=:pupilsightSchoolYearID)';
                                    //print_r($dataClass);print_r($sqlClass);die();
                                    $resultClass = $connection2->prepare($sqlClass);
                                    $resultClass->execute($dataClass);

                                    $dataSpace = array('name' => $row['spaceName']);
                                    $sqlSpace = '(SELECT pupilsightSpaceID FROM pupilsightSpace WHERE name=:name)';
                                    //print_r($dataSpace);print_r($sqlSpace);die();
                                    $resultSpace = $connection2->prepare($sqlSpace);
                                    $resultSpace->execute($dataSpace);
                                } catch (PDOException $e) {
                                    echo $e->getMessage();
                                    $ttSyncFail = true;
                                    $proceed = false;
                                    $addFail = true;
                                }

                                if ($resultRow->rowCount() != 1 and $resultDay->rowCount() != 1 and $resultClass->rowCount() != 1 and $resultSpace->rowCount() != 1) {
                                    $ttSyncFail = true;
                                    $proceed = false;
                                    $addFail = true;
                                } else {
                                    $rowRow = $resultRow->fetch();
                                    //print_r($rowRow);
                                    $rowDay = $resultDay->fetch();
                                    $rowClass = $resultClass->fetch();
                                    $rowSpace = $resultSpace->fetch();

                                    try {
                                        $sqlInsert = 'INSERT INTO pupilsightTTDayRowClass SET pupilsightTTColumnRowID='.$rowRow['pupilsightTTColumnRowID'].', pupilsightTTDayID='.$rowDay['pupilsightTTDayID'].', pupilsightCourseClassID='.$rowClass['pupilsightCourseClassID'].', pupilsightSpaceID='.$rowSpace['pupilsightSpaceID'].',pupilsightStaffID='.$staffs[0];
                                        //print_r($sqlInsert);die();
                                        $resultInsert = $connection2->query($sqlInsert);
                                        $pupilsightTTDayRowClassID = $connection2->lastInsertId();
                                        print_r($pupilsightTTDayRowClassID);
                                    } catch (PDOException $e) {
                                        $ttSyncFail = true;
                                        $proceed = false;
                                        $addFail = true;
                                    }

                                    //Add teacher exceptions
                                    $teachersFail = false;
                                    if ($addFail == false) {
                                        try {
                                            $dataTeachers = array();
                                            $sqlTeachers = "SELECT pupilsightPerson.username, pupilsightPerson.pupilsightPersonID FROM pupilsightCourseClassPerson JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full' AND role='Teacher' AND pupilsightCourseClassID=".$rowClass['pupilsightCourseClassID'];
                                            $resultTeachers = $connection2->prepare($sqlTeachers);
                                            $resultTeachers->execute($dataTeachers);
                                        } catch (PDOException $e) {
                                            $ttSyncFail = true;
                                            $proceed = false;
                                            $teachersFail = true;
                                        }

                                        if ($teachersFail == false) {
                                            while ($rowTeachers = $resultTeachers->fetch()) {
                                                $match = false;
                                                foreach ($staffs as $staff) {
                                                    if ($staff == $rowTeachers['username']) {
                                                        $match = true;
                                                    }
                                                }
                                                if ($match == false) {
                                                    try {
                                                        $dataException = array('pupilsightTTDayRowClassID' => $pupilsightTTDayRowClassID, 'pupilsightPersonID' => $rowTeachers['pupilsightPersonID']);
                                                        $sqlException = 'INSERT INTO pupilsightTTDayRowClassException SET pupilsightTTDayRowClassID=:pupilsightTTDayRowClassID, pupilsightPersonID=:pupilsightPersonID';
                                                        $resultException = $connection2->prepare($sqlException);
                                                        $resultException->execute($dataException);
                                                    } catch (PDOException $e) {
                                                        $ttSyncFail = true;
                                                        $proceed = false;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if ($ttSyncFail == true) {
                            echo "<div class='alert alert-danger'>";
                            echo __('Add/update of periods from import failed. Parts of your timetable may display correctly, but others may be missing, incomplete or incorrect.');
                            echo '</div>';
                        } elseif ($ttSyncFail == false) {
                            echo "<div class='alert alert-sucess'>";
                            echo __('Add/update of periods from import was successful. You may now wish to set long name, learning area and year groups for any new courses created in Step 2.');
                            echo '</div>';
                        }
                    }
                }

                //SPIT OUT RESULT
                echo '<h4>';
                echo __('Final Result');
                echo '</h4>';
                if ($proceed == false) {
                    echo "<div class='alert alert-danger'>";
                    echo '<b><u>'.__('Your input was partially or entirely unsuccessful.').'</u></b>';
                    echo '</div>';
                } elseif ($proceed == true) {
                    echo "<div class='alert alert-sucess'>";
                    echo '<b><u>'.__('Success! Your new timetable is in place.').'</u></b>';
                    echo '</div>';
                }
            }
        }
    }
}
