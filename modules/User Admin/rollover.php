<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/rollover.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Rollover'));
    
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $step = null;
    if (isset($_GET['step'])) {
        $step = $_GET['step'];
    }
    if ($step != 1 and $step != 2 and $step != 3) {
        $step = 1;
    }

    //Step 1
    if ($step == 1) {
        echo '<h3>';
        echo __('Step 1');
        echo '</h3>';

        $nextYear = getNextSchoolYearID($_SESSION[$guid]['pupilsightSchoolYearID'], $connection2);
        if ($nextYear == false) {
            echo "<div class='alert alert-danger'>";
            echo __('The next school year cannot be determined, so this action cannot be performed.');
            echo '</div>';
        } else {
            try {
                $dataNext = array('pupilsightSchoolYearID' => $nextYear);
                $sqlNext = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
                $resultNext = $connection2->prepare($sqlNext);
                $resultNext->execute($dataNext);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            if ($resultNext->rowCount() == 1) {
                $rowNext = $resultNext->fetch();
            }
            $nameNext = $rowNext['name'];
            if ($nameNext == '') {
                echo "<div class='alert alert-danger'>";
                echo __('The next school year cannot be determined, so this action cannot be performed.');
                echo '</div>';
            } else {
                $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/rollover.php&step=2');

                $form->setClass('smallIntBorder fullWidth');

                $form->addHiddenValue('nextYear', $nextYear);

                $row = $form->addRow();
                    $row->addContent(sprintf(__('By clicking the "Proceed" button below you will initiate the rollover from %1$s to %2$s. In a big school this operation may take some time to complete. This will change data in numerous tables across the system! %3$sYou are really, very strongly advised to backup all data before you proceed%4$s.'), '<b>'.$_SESSION[$guid]['pupilsightSchoolYearName'].'</b>', '<b>'.$nameNext.'</b>', '<span style="color: #cc0000"><i>', '</span>'));

                $row = $form->addRow();
                    $row->addSubmit('Proceed');

                echo $form->getOutput();
            }
        }
    } elseif ($step == 2) {
        echo '<h3>';
        echo __('Step 2');
        echo '</h3>';

        $nextYear = $_POST['nextYear'];
        if ($nextYear == '' or $nextYear != getNextSchoolYearID($_SESSION[$guid]['pupilsightSchoolYearID'], $connection2)) {
            echo "<div class='alert alert-danger'>";
            echo __('The next school year cannot be determined, so this action cannot be performed.');
            echo '</div>';
        } else {
            try {
                $dataNext = array('pupilsightSchoolYearID' => $nextYear);
                $sqlNext = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
                $resultNext = $connection2->prepare($sqlNext);
                $resultNext->execute($dataNext);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            if ($resultNext->rowCount() == 1) {
                $rowNext = $resultNext->fetch();
            }
            $nameNext = $rowNext['name'];
            $sequenceNext = $rowNext['sequenceNumber'];
            if ($nameNext == '' or $sequenceNext == '') {
                echo "<div class='alert alert-danger'>";
                echo __('The next school year cannot be determined, so this action cannot be performed.');
                echo '</div>';
            } else {
                echo '<p>';
                echo sprintf(__('In rolling over to %1$s, the following actions will take place. You may need to adjust some fields below to get the result you desire.'), $nameNext);
                echo '</p>';

                //Set up years, roll groups and statuses arrays for use later on
                $yearGroups = array();
                try {
                    $dataSelect = array();
                    $sqlSelect = 'SELECT pupilsightYearGroupID, name FROM pupilsightYearGroup ORDER BY sequenceNumber';
                    $resultSelect = $connection2->prepare($sqlSelect);
                    $resultSelect->execute($dataSelect);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                while ($rowSelect = $resultSelect->fetch()) {
                    $yearGroups[$rowSelect['pupilsightYearGroupID']] =  htmlPrep($rowSelect['name']);
                }

                $rollGroups = array();
                try {
                    $dataSelect = array('pupilsightSchoolYearID' => $nextYear);
                    $sqlSelect = 'SELECT pupilsightRollGroupID, name FROM pupilsightRollGroup WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name';
                    $resultSelect = $connection2->prepare($sqlSelect);
                    $resultSelect->execute($dataSelect);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                while ($rowSelect = $resultSelect->fetch()) {
                    $rollGroups[$rowSelect['pupilsightRollGroupID']] =  htmlPrep($rowSelect['name']);
                }

                $statuses = array(
                    'Expected'     => __('Expected'),
                    'Full'  => __('Full'),
                    'Left' => __('Left'),
                );

                //START FORM
                $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/rollover.php&step=3");

                $form->setFactory(DatabaseFormFactory::create($pdo));
                $form->setClass('smallIntBorder fullWidth');

                $form->addHiddenValue('nextYear', $nextYear);

                //ADD YEAR FOLLOWING NEXT
                if (getNextSchoolYearID($nextYear, $connection2) == false) {
                    $form->addRow()->addHeading(sprintf(__('Add Year Following %1$s'), $nameNext));

                    $row = $form->addRow();
                        $row->addLabel('nextname', __('School Year Name'))->description(__('Must be unique.'));
                        $row->addTextField('nextname')->required()->maxLength(9);

                    $row = $form->addRow();
                        $row->addLabel('nextstatus', __('Status'));
                        $row->addTextField('nextstatus')->setValue(__('Upcoming'))->required()->readonly();

                    $row = $form->addRow();
                        $row->addLabel('nextsequenceNumber', __('Sequence Number'))->description(__('Must be unique. Controls chronological ordering.'));
                        $row->addSequenceNumber('nextsequenceNumber', 'pupilsightSchoolYear', '', 'sequenceNumber')->required()->maxLength(3)->readonly();

                    $row = $form->addRow();
                        $row->addLabel('nextfirstDay', __('First Day'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
                        $row->addDate('nextfirstDay')->required();

                    $row = $form->addRow();
                        $row->addLabel('nextlastDay', __('Last Day'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
                        $row->addDate('nextlastDay')->required();
                }

                //SET EXPECTED USERS TO FULL
                $form->addRow()->addHeading(__('Set Expected Users To Full'));
                $form->addRow()->addContent(__('This step primes newcomers who have status set to "Expected" to be enroled as students or added as staff (below).'));

                try {
                    $dataExpect = array();
                    $sqlExpect = "SELECT pupilsightPersonID, surname, preferredName, name FROM pupilsightPerson JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE status='Expected' ORDER BY name, surname, preferredName";
                    $resultExpect = $connection2->prepare($sqlExpect);
                    $resultExpect->execute($dataExpect);
                } catch (PDOException $e) {
                    $form->addRow()->addAlert($e->getMessage(), 'error');
                }
                if ($resultExpect->rowCount() < 1) {
                    $form->addRow()->addAlert(__('There are no records to display.'), 'warning');
                } else {
                    $row = $form->addRow()->addClass('head break');
                        $row->addColumn()->addContent(__('Name'));
                        $row->addColumn()->addContent(__('Primary Role'));
                        $row->addColumn()->addContent(__('Current Status'));
                        $row->addColumn()->addContent(__('New Status'));

                    $count = 0;
                    while ($rowExpect = $resultExpect->fetch()) {
                        $count++;
                        $form->addHiddenValue($count."-expect-pupilsightPersonID", $rowExpect['pupilsightPersonID']);
                        $row = $form->addRow();
                            $row->addColumn()->addContent(formatName('', $rowExpect['preferredName'], $rowExpect['surname'], 'Student', true));
                            $row->addColumn()->addContent(__($rowExpect['name']));
                            $row->addColumn()->addContent(__('Expected'));
                            $column = $row->addColumn();
                                $column->addSelect($count."-expect-status")->fromArray($statuses)->required()->setClass('shortWidth floatNone');
                    }
                    $form->addHiddenValue("expect-count", $count);
                }

                //ENROL NEW STUDENTS - EXPECTED
                $form->addRow()->addHeading(__('Enrol New Students (Status Expected)'));
                $form->addRow()->addContent(__('Take students who are marked expected and enrol them. All parents of new students who are enroled below will have their status set to "Full". If a student is not enroled, they will be set to "Left".'));

                if (count($yearGroups) < 1 or count($rollGroups) < 1) {
                    $form->addRow()->addAlert(__('Year groups or roll groups are not properly set up, so you cannot proceed with this section.'), 'error');
                } else {
                    try {
                        $dataEnrol = array();
                        $sqlEnrol = "SELECT pupilsightPersonID, surname, preferredName, name, category FROM pupilsightPerson JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE status='Expected' AND category='Student' ORDER BY surname, preferredName";
                        $resultEnrol = $connection2->prepare($sqlEnrol);
                        $resultEnrol->execute($dataEnrol);
                    } catch (PDOException $e) {
                        $form->addRow()->addAlert($e->getMessage(), 'error');
                    }

                    if ($resultEnrol->rowCount() < 1) {
                        $form->addRow()->addAlert(__('There are no records to display.'), 'warning');
                    } else {
                        $row = $form->addRow()->addClass('head break');
                            $row->addColumn()->addContent(__('Name'));
                            $row->addColumn()->addContent(__('Primary Role'));
                            $row->addColumn()->addContent(__('Enrol'));
                            $row->addColumn()->addContent(__('Year Group'));
                            $row->addColumn()->addContent(__('Form Group'));

                        $count = 0;
                        while ($rowEnrol = $resultEnrol->fetch()) {
                            $count++;
                            $form->addHiddenValue($count."-enrol-pupilsightPersonID", $rowEnrol['pupilsightPersonID']);
                            $row = $form->addRow();
                                $row->addColumn()->addContent(formatName('', $rowEnrol['preferredName'], $rowEnrol['surname'], 'Student', true));
                                $row->addColumn()->addContent(__($rowEnrol['name']));
                                $column = $row->addColumn();
                                    $column->addCheckbox($count."-enrol-enrol")->setValue('Y')->checked('Y');
                                $column = $row->addColumn();
                                    $column->addSelect($count."-enrol-pupilsightYearGroupID")->fromArray($yearGroups)->required()->setClass('shortWidth floatNone');
                                $column = $row->addColumn();
                                    $column->addSelect($count."-enrol-pupilsightRollGroupID")->fromArray($rollGroups)->required()->setClass('shortWidth floatNone');
                        }
                        $form->addHiddenValue("enrol-count", $count);
                    }
                }

                //ENROL NEW STUDENTS - FULL
                $form->addRow()->addHeading(__('Enrol New Students (Status Full)'));
                $form->addRow()->addContent(__('Take new students who are already set as full, but who were not enroled last year, and enrol them. These students probably came through the Online Application form, and may already be enroled in next year: if this is the case, their enrolment will be updated as per the information below. All parents of new students who are enroled below will have their status set to "Full". If a student is not enroled, they will be set to "Left"'));

                if (count($yearGroups) < 1 or count($rollGroups) < 1) {
                    $form->addRow()->addAlert(__('Year groups or roll groups are not properly set up, so you cannot proceed with this section.'), 'error');
                } else {
                    $students = array();
                    $count = 0;
                    try {
                        $dataEnrol = array();
                        $sqlEnrol = "SELECT pupilsightPerson.pupilsightPersonID, surname, preferredName, pupilsightRole.name, category
                            FROM pupilsightPerson
                                JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
                                LEFT JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                                LEFT JOIN pupilsightSchoolYear ON (pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID)
                            WHERE pupilsightPerson.status='Full'
                                AND category='Student'
                                AND (pupilsightStudentEnrolment.pupilsightPersonID IS NULL OR pupilsightSchoolYear.status='Upcoming')
                            ORDER BY surname, preferredName";
                        $resultEnrol = $connection2->prepare($sqlEnrol);
                        $resultEnrol->execute($dataEnrol);
                    } catch (PDOException $e) {
                        $form->addRow()->addAlert($e->getMessage());
                    }

                    if ($resultEnrol->rowCount() < 1) {
                        $form->addRow()->addAlert(__('There are no records to display.'), 'warning');
                    } else {
                        while ($rowEnrol = $resultEnrol->fetch()) {
                            try {
                                $dataEnrolled = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $rowEnrol['pupilsightPersonID']);
                                $sqlEnrolled = "SELECT pupilsightStudentEnrolment.* FROM pupilsightPerson JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND status='Full' AND category='Student' AND pupilsightStudentEnrolment.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName";
                                $resultEnrolled = $connection2->prepare($sqlEnrolled);
                                $resultEnrolled->execute($dataEnrolled);
                            } catch (PDOException $e) {
                                $form->addRow()->addAlert($e->getMessage(), 'error');
                            }
                            if ($resultEnrolled->rowCount() < 1) {
                                $students[$count][0] = $rowEnrol['pupilsightPersonID'];
                                $students[$count][1] = $rowEnrol['surname'];
                                $students[$count][2] = $rowEnrol['preferredName'];
                                $students[$count][3] = $rowEnrol['name'];
                                ++$count;
                            }
                        }

                        if ($count < 1) {
                            $form->addRow()->addAlert(__('There are no records to display.'), 'warning');
                        } else {
                            $row = $form->addRow()->addClass('head break');
                                $row->addColumn()->addContent(__('Name'));
                                $row->addColumn()->addContent(__('Primary Role'));
                                $row->addColumn()->addContent(__('Enrol'));
                                $row->addColumn()->addContent(__('Year Group'));
                                $row->addColumn()->addContent(__('Form Group'));

                            $count = 0;
                            foreach ($students AS $student) {
                                $count++;
                                //Check for enrolment in next year (caused by automated enrolment on application form accept)
                                $yearGroupSelect = '';
                                $rollGroupSelect = '';
                                try {
                                    $dataEnrolled = array('pupilsightSchoolYearID' => $nextYear, 'pupilsightPersonID' => $student[0]);
                                    $sqlEnrolled = 'SELECT * FROM pupilsightStudentEnrolment WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID';
                                    $resultEnrolled = $connection2->prepare($sqlEnrolled);
                                    $resultEnrolled->execute($dataEnrolled);
                                } catch (PDOException $e) {
                                    $form->addRow()->addAlert($e->getMessage(), 'error');
                                }
                                if ($resultEnrolled->rowCount() == 1) {
                                    $rowEnrolled = $resultEnrolled->fetch();
                                    $yearGroupSelect = $rowEnrolled['pupilsightYearGroupID'];
                                    $rollGroupSelect = $rowEnrolled['pupilsightRollGroupID'];
                                }

                                $form->addHiddenValue($count."-enrolFull-pupilsightPersonID", $student[0]);
                                $row = $form->addRow();
                                    $row->addColumn()->addContent(formatName('', $student[2], $student[1], 'Student', true));
                                    $row->addColumn()->addContent(__($student[3]));
                                    $column = $row->addColumn();
                                        $column->addCheckbox($count."-enrolFull-enrol")->setValue('Y')->checked('Y');
                                    $column = $row->addColumn();
                                        $column->addSelect($count."-enrolFull-pupilsightYearGroupID")->fromArray($yearGroups)->required()->setClass('shortWidth floatNone')->selected($yearGroupSelect);
                                    $column = $row->addColumn();
                                        $column->addSelect($count."-enrolFull-pupilsightRollGroupID")->fromArray($rollGroups)->required()->setClass('shortWidth floatNone')->selected($rollGroupSelect);
                            }
                            $form->addHiddenValue("enrolFull-count", $count);
                        }
                    }
                }

                //RE-ENROL OTHER STUDENTS
                $form->addRow()->addHeading(__('Re-Enrol Other Students'));
                $form->addRow()->addContent(__('Any students who are not re-enroled will have their status set to "Left".').' '.__('Students who are already enroled will have their enrolment updated.'));

                $lastYearGroup = getLastYearGroupID($connection2);

                if (count($yearGroups) < 1 or count($rollGroups) < 1) {
                    $form->addRow()->addAlert(__('Year groups or roll groups are not properly set up, so you cannot proceed with this section.'), 'error');
                } else {
                    try {
                        $dataReenrol = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightYearGroupID' => $lastYearGroup);
                        $sqlReenrol = "SELECT pupilsightPerson.pupilsightPersonID, surname, preferredName, pupilsightRole.name, category, pupilsightStudentEnrolment.pupilsightYearGroupID, pupilsightRollGroupIDNext
                            FROM pupilsightPerson
                                JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
                                JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                                JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                            WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND status='Full' AND category='Student' AND NOT pupilsightYearGroupID=:pupilsightYearGroupID ORDER BY surname, preferredName";
                        $resultReenrol = $connection2->prepare($sqlReenrol);
                        $resultReenrol->execute($dataReenrol);
                    } catch (PDOException $e) {
                        $form->addRow()->addAlert($e->getMessage(), 'error');
                    }

                    if ($resultReenrol->rowCount() < 1) {
                        $form->addRow()->addAlert(__('There are no records to display.'), 'warning');
                    } else {
                        $row = $form->addRow()->addClass('head break');
                            $row->addColumn()->addContent(__('Name'));
                            $row->addColumn()->addContent(__('Primary Role'));
                            $row->addColumn()->addContent(__('Re-Enrol'));
                            $row->addColumn()->addContent(__('Year Group'));
                            $row->addColumn()->addContent(__('Form Group'));

                        $count = 0;
                        while ($rowReenrol = $resultReenrol->fetch()) {
                            $count++;
                            //Check for enrolment in next year
                            try {
                                $dataEnrolmentCheck = array('pupilsightPersonID' => $rowReenrol['pupilsightPersonID'], 'pupilsightSchoolYearID' => $nextYear);
                                $sqlEnrolmentCheck = 'SELECT * FROM pupilsightStudentEnrolment WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
                                $resultEnrolmentCheck = $connection2->prepare($sqlEnrolmentCheck);
                                $resultEnrolmentCheck->execute($dataEnrolmentCheck);
                            } catch (PDOException $e) {
                                $form->addRow()->addAlert($e->getMessage(), 'error');
                            }
                            $enrolmentCheckYearGroup = null;
                            $enrolmentCheckRollGroup = null;
                            if ($resultEnrolmentCheck->rowCount() == 1) {
                                $rowEnrolmentCheck = $resultEnrolmentCheck->fetch();
                                $enrolmentCheckYearGroup = $rowEnrolmentCheck['pupilsightYearGroupID'];
                                $enrolmentCheckRollGroup = $rowEnrolmentCheck['pupilsightRollGroupID'];
                            }

                            $form->addHiddenValue($count."-reenrol-pupilsightPersonID", $rowReenrol['pupilsightPersonID']);
                            $row = $form->addRow();
                                $row->addColumn()->addContent(formatName('', $rowReenrol['preferredName'], $rowReenrol['surname'], 'Student', true));
                                $row->addColumn()->addContent(__($rowReenrol['name']));
                                $column = $row->addColumn();
                                    $column->addCheckbox($count."-reenrol-enrol")->setValue('Y')->checked('Y');
                                //If no enrolment, try and work out next year and roll group
                                if (is_null($enrolmentCheckYearGroup)) {
                                    $enrolmentCheckYearGroup=getNextYearGroupID($rowReenrol['pupilsightYearGroupID'], $connection2);
                                    $enrolmentCheckRollGroup=$rowReenrol['pupilsightRollGroupIDNext'];
                                }
                                $column = $row->addColumn();
                                    $column->addSelect($count."-reenrol-pupilsightYearGroupID")->fromArray($yearGroups)->required()->setClass('shortWidth floatNone')->selected($enrolmentCheckYearGroup);
                                $column = $row->addColumn();
                                        $column->addSelect($count."-reenrol-pupilsightRollGroupID")->fromArray($rollGroups)->required()->setClass('shortWidth floatNone')->selected($enrolmentCheckRollGroup);
                        }
                        $form->addHiddenValue("reenrol-count", $count);
                    }
                }

                //SET FINAL YEAR USERS TO LEFT
                $form->addRow()->addHeading(__('Set Final Year Students To Left'));
                $form->addRow()->addContent(__('This step finds students in the last year of school and sets their status.'));

                try {
                    $dataFinal = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightYearGroupID' => $lastYearGroup);
                    $sqlFinal = "SELECT pupilsightPerson.pupilsightPersonID, surname, preferredName, name, category FROM pupilsightPerson JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND status='Full' AND category='Student' AND pupilsightYearGroupID=:pupilsightYearGroupID ORDER BY surname, preferredName";
                    $resultFinal = $connection2->prepare($sqlFinal);
                    $resultFinal->execute($dataFinal);
                } catch (PDOException $e) {
                    $form->addRow()->addAlert($e->getMessage(), 'error');
                }
                if ($resultFinal->rowCount() < 1) {
                    $form->addRow()->addAlert(__('There are no records to display.'), 'warning');
                } else {
                    $row = $form->addRow()->addClass('head break');
                        $row->addColumn()->addContent(__('Name'));
                        $row->addColumn()->addContent(__('Primary Role'));
                        $row->addColumn()->addContent(__('Current Status'));
                        $row->addColumn()->addContent(__('New Status'));

                    $count = 0;
                    while ($rowFinal = $resultFinal->fetch()) {
                        $count++;
                        $form->addHiddenValue($count."-final-pupilsightPersonID", $rowFinal['pupilsightPersonID']);
                        $row = $form->addRow();
                            $row->addColumn()->addContent(formatName('', $rowFinal['preferredName'], $rowFinal['surname'], 'Student', true));
                            $row->addColumn()->addContent(__($rowFinal['name']));
                            $row->addColumn()->addContent(__('Full'));
                            $column = $row->addColumn();
                                $column->addSelect($count."-final-status")->fromArray($statuses)->required()->setClass('shortWidth floatNone')->selected('Left');
                    }
                    $form->addHiddenValue("final-count", $count);
                }

                //REGISTER NEW STAFF
                $form->addRow()->addHeading(__('Register New Staff'));
                $form->addRow()->addContent(__('Any staff who are not registered will have their status set to "Left".'));

                try {
                    $dataRegister = array();
                    $sqlRegister = "SELECT pupilsightPersonID, surname, preferredName, name, category FROM pupilsightPerson JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE status='Expected' AND category='Staff' ORDER BY surname, preferredName";
                    $resultRegister = $connection2->prepare($sqlRegister);
                    $resultRegister->execute($dataRegister);
                } catch (PDOException $e) {
                    $form->addRow()->addAlert($e->getMessage(), 'error');
                }
                if ($resultRegister->rowCount() < 1) {
                    $form->addRow()->addAlert(__('There are no records to display.'), 'warning');
                } else {
                    $row = $form->addRow()->addClass('head break');
                        $row->addColumn()->addContent(__('Name'));
                        $row->addColumn()->addContent(__('Primary Role'));
                        $row->addColumn()->addContent(__('Register'));
                        $row->addColumn()->addContent(__('Type'));
                        $row->addColumn()->addContent(__('Job Title'));

                    $count = 0;
                    while ($rowRegister = $resultRegister->fetch()) {
                        $count++;
                        $form->addHiddenValue($count."-register-pupilsightPersonID", $rowRegister['pupilsightPersonID']);
                        $row = $form->addRow();
                            $row->addColumn()->addContent(formatName('', $rowRegister['preferredName'], $rowRegister['surname'], 'Student', true));
                            $row->addColumn()->addContent(__($rowRegister['name']));
                            $column = $row->addColumn();
                                $column->addCheckbox($count."-register-enrol")->setValue('Y')->checked('Y');
                            $column = $row->addColumn();
                                $column->addSelect($count."-register-type")->fromArray(array('Teaching' => __('Teaching'), 'Support' => __('Support')))->required()->setClass('shortWidth floatNone');
                            $column = $row->addColumn();
                                $column->addtextField($count."-register-jobTitle")->setClass('shortWidth floatNone')->maxLength(100);
                    }
                    $form->addHiddenValue("register-count", $count);
                }


                $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit('Proceed');

                echo $form->getOutput();
            }
        }
    } elseif ($step == 3) {
        $nextYear = $_POST['nextYear'];
        if ($nextYear == '' or $nextYear != getNextSchoolYearID($_SESSION[$guid]['pupilsightSchoolYearID'], $connection2)) {
            echo "<div class='alert alert-danger'>";
            echo __('The next school year cannot be determined, so this action cannot be performed.');
            echo '</div>';
        } else {
            try {
                $dataNext = array('pupilsightSchoolYearID' => $nextYear);
                $sqlNext = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
                $resultNext = $connection2->prepare($sqlNext);
                $resultNext->execute($dataNext);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            if ($resultNext->rowCount() == 1) {
                $rowNext = $resultNext->fetch();
            }
            $nameNext = $rowNext['name'];
            $sequenceNext = $rowNext['sequenceNumber'];
            if ($nameNext == '' or $sequenceNext == '') {
                echo "<div class='alert alert-danger'>";
                echo __('The next school year cannot be determined, so this action cannot be performed.');
                echo '</div>';
            } else {
                echo '<h3>';
                echo __('Step 3');
                echo '</h3>';

                //ADD YEAR FOLLOWING NEXT
                if (getNextSchoolYearID($nextYear, $connection2) == false) {
                    //ADD YEAR FOLLOWING NEXT
                    echo '<h4>';
                    echo sprintf(__('Add Year Following %1$s'), $nameNext);
                    echo '</h4>';

                    $name = $_POST['nextname'];
                    $status = $_POST['nextstatus'];
                    $sequenceNumber = $_POST['nextsequenceNumber'];
                    $firstDay = dateConvert($guid, $_POST['nextfirstDay']);
                    $lastDay = dateConvert($guid, $_POST['nextlastDay']);

                    if ($name == '' or $status == '' or $sequenceNumber == '' or is_numeric($sequenceNumber) == false or $firstDay == '' or $lastDay == '') {
                        echo "<div class='alert alert-danger'>";
                        echo __('Your request failed because your inputs were invalid.');
                        echo '</div>';
                    } else {
                        //Check unique inputs for uniqueness
                        try {
                            $data = array('name' => $name, 'sequenceNumber' => $sequenceNumber);
                            $sql = 'SELECT * FROM pupilsightSchoolYear WHERE name=:name OR sequenceNumber=:sequenceNumber';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        if ($result->rowCount() > 0) {
                            echo "<div class='alert alert-danger'>";
                            echo __('Your request failed because your inputs were invalid.');
                            echo '</div>';
                        } else {
                            //Write to database
                            $fail = false;
                            try {
                                $data = array('name' => $name, 'status' => $status, 'sequenceNumber' => $sequenceNumber, 'firstDay' => $firstDay, 'lastDay' => $lastDay);
                                $sql = 'INSERT INTO pupilsightSchoolYear SET name=:name, status=:status, sequenceNumber=:sequenceNumber, firstDay=:firstDay, lastDay=:lastDay';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                $fail = true;
                            }
                            if ($fail == false) {
                                echo "<div class='alert alert-sucess'>";
                                echo __('Your request was completed successfully.');
                                echo '</div>';
                            }
                        }
                    }
                }

                //Remember year end date of current year before advance
                $dateEnd = $_SESSION[$guid]['pupilsightSchoolYearLastDay'];

                //ADVANCE SCHOOL YEAR
                echo '<h4>';
                echo __('Advance School Year');
                echo '</h4>';

                //Write to database
                $advance = true;
                try {
                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sql = "UPDATE pupilsightSchoolYear SET status='Past' WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>";
                    echo __('Your request failed due to a database error.');
                    echo '</div>';
                    $advance = false;
                }
                if ($advance) {
                    $advance2 = true;
                    try {
                        $data = array('pupilsightSchoolYearID' => $nextYear);
                        $sql = "UPDATE pupilsightSchoolYear SET status='Current' WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>";
                        echo __('Your request failed due to a database error.');
                        echo '</div>';
                        $advance2 = false;
                    }
                    if ($advance2) {
                        setCurrentSchoolYear($guid, $connection2);
                        $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
                        $_SESSION[$guid]['pupilsightSchoolYearNameCurrent'] = $_SESSION[$guid]['pupilsightSchoolYearName'];
                        $_SESSION[$guid]['pupilsightSchoolYearSequenceNumberCurrent'] = $_SESSION[$guid]['pupilsightSchoolYearSequenceNumber'];

                        echo "<div class='alert alert-sucess'>";
                        echo __('Advance was successful, you are now in a new academic year!');
                        echo '</div>';

                        //SET EXPECTED USERS TO FULL
                        echo '<h4>';
                        echo __('Set Expected Users To Full');
                        echo '</h4>';

                        $count = null;
                        if (isset($_POST['expect-count'])) {
                            $count = $_POST['expect-count'];
                        }
                        if ($count == '') {
                            echo "<div class='alert alert-warning'>";
                            echo __('No actions were selected in Step 2, and so no changes have been made.');
                            echo '</div>';
                        } else {
                            $success = 0;
                            for ($i = 1; $i <= $count; ++$i) {
                                $pupilsightPersonID = $_POST["$i-expect-pupilsightPersonID"];
                                $status = $_POST["$i-expect-status"];

                                //Write to database
                                $expected = true;
                                try {
                                    if ($status == 'Full') {
                                        $data = array('status' => $status, 'pupilsightPersonID' => $pupilsightPersonID, 'dateStart' => $_SESSION[$guid]['pupilsightSchoolYearFirstDay']);
                                        $sql = 'UPDATE pupilsightPerson SET status=:status, dateStart=:dateStart WHERE pupilsightPersonID=:pupilsightPersonID';
                                    } elseif ($status == 'Left' or $status == 'Expected') {
                                        $data = array('status' => $status, 'pupilsightPersonID' => $pupilsightPersonID);
                                        $sql = 'UPDATE pupilsightPerson SET status=:status WHERE pupilsightPersonID=:pupilsightPersonID';
                                    }
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    $expected = false;
                                }
                                if ($expected) {
                                    ++$success;
                                }
                            }

                            //Feedback result!
                            if ($success == 0) {
                                echo "<div class='alert alert-danger'>";
                                echo __('Your request failed.');
                                echo '</div>';
                            } elseif ($success < $count) {
                                echo "<div class='alert alert-warning'>";
                                echo sprintf(__('%1$s updates failed.'), ($count - $success));
                                echo '</div>';
                            } else {
                                echo "<div class='alert alert-sucess'>";
                                echo __('Your request was completed successfully.');
                                echo '</div>';
                            }
                        }

                        //ENROL NEW STUDENTS
                        echo '<h4>';
                        echo __('Enrol New Students (Status Expected)');
                        echo '</h4>';

                        $count = null;
                        if (isset($_POST['enrol-count'])) {
                            $count = $_POST['enrol-count'];
                        }
                        if ($count == '') {
                            echo "<div class='alert alert-warning'>";
                            echo __('No actions were selected in Step 2, and so no changes have been made.');
                            echo '</div>';
                        } else {
                            $success = 0;
                            for ($i = 1; $i <= $count; ++$i) {
                                $pupilsightPersonID = $_POST["$i-enrol-pupilsightPersonID"];
                                $enrol = $_POST["$i-enrol-enrol"];
                                $pupilsightYearGroupID = $_POST["$i-enrol-pupilsightYearGroupID"];
                                $pupilsightRollGroupID = $_POST["$i-enrol-pupilsightRollGroupID"];

                                //Write to database
                                if ($enrol == 'Y') {
                                    $enroled = true;
                                    try {
                                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
                                        $sql = 'INSERT INTO pupilsightStudentEnrolment SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightPersonID=:pupilsightPersonID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID';
                                        $result = $connection2->prepare($sql);
                                        $result->execute($data);
                                    } catch (PDOException $e) {
                                        $enroled = false;
                                    }
                                    if ($enroled) {
                                        ++$success;

                                        try {
                                            $dataFamily = array('pupilsightPersonID' => $pupilsightPersonID);
                                            $sqlFamily = 'SELECT pupilsightFamilyID FROM pupilsightFamilyChild WHERE pupilsightPersonID=:pupilsightPersonID';
                                            $resultFamily = $connection2->prepare($sqlFamily);
                                            $resultFamily->execute($dataFamily);
                                        } catch (PDOException $e) {
                                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                        }
                                        while ($rowFamily = $resultFamily->fetch()) {
                                            try {
                                                $dataFamily2 = array('pupilsightFamilyID' => $rowFamily['pupilsightFamilyID']);
                                                $sqlFamily2 = 'SELECT pupilsightPersonID FROM pupilsightFamilyAdult WHERE pupilsightFamilyID=:pupilsightFamilyID';
                                                $resultFamily2 = $connection2->prepare($sqlFamily2);
                                                $resultFamily2->execute($dataFamily2);
                                            } catch (PDOException $e) {
                                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                            }
                                            while ($rowFamily2 = $resultFamily2->fetch()) {
                                                try {
                                                    $dataFamily3 = array('pupilsightPersonID' => $rowFamily2['pupilsightPersonID']);
                                                    $sqlFamily3 = "UPDATE pupilsightPerson SET status='Full' WHERE pupilsightPersonID=:pupilsightPersonID";
                                                    $resultFamily3 = $connection2->prepare($sqlFamily3);
                                                    $resultFamily3->execute($dataFamily3);
                                                } catch (PDOException $e) {
                                                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $ok = true;
                                    try {
                                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'dateEnd' => $dateEnd);
                                        $sql = "UPDATE pupilsightPerson SET status='Left', dateEnd=:dateEnd WHERE pupilsightPersonID=:pupilsightPersonID";
                                        $result = $connection2->prepare($sql);
                                        $result->execute($data);
                                    } catch (PDOException $e) {
                                        $ok == false;
                                    }
                                    if ($ok = true) {
                                        ++$success;
                                    }
                                }
                            }

                            //Feedback result!
                            if ($success == 0) {
                                echo "<div class='alert alert-danger'>";
                                echo __('Your request failed.');
                                echo '</div>';
                            } elseif ($success < $count) {
                                echo "<div class='alert alert-warning'>";
                                echo sprintf(__('%1$s adds failed.'), ($count - $success));
                                echo '</div>';
                            } else {
                                echo "<div class='alert alert-sucess'>";
                                echo __('Your request was completed successfully.');
                                echo '</div>';
                            }
                        }

                        //ENROL NEW STUDENTS
                        echo '<h4>';
                        echo __('Enrol New Students (Status Full)');
                        echo '</h4>';

                        $count = null;
                        if (isset($_POST['enrolFull-count'])) {
                            $count = $_POST['enrolFull-count'];
                        }
                        if ($count == '') {
                            echo "<div class='alert alert-warning'>";
                            echo __('No actions were selected in Step 2, and so no changes have been made.');
                            echo '</div>';
                        } else {
                            $success = 0;
                            for ($i = 1; $i <= $count; ++$i) {
                                $pupilsightPersonID = $_POST["$i-enrolFull-pupilsightPersonID"].'<br/>';
                                $enrol = $_POST["$i-enrolFull-enrol"];
                                $pupilsightYearGroupID = $_POST["$i-enrolFull-pupilsightYearGroupID"];
                                $pupilsightRollGroupID = $_POST["$i-enrolFull-pupilsightRollGroupID"];

                                //Write to database
                                if ($enrol == 'Y') {
                                    $enroled = true;

                                    try {
                                        //Check for enrolment
                                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID);
                                        $sql = 'SELECT * FROM pupilsightStudentEnrolment WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID';
                                        $result = $connection2->prepare($sql);
                                        $result->execute($data);
                                    } catch (PDOException $e) {
                                        $enroled = false;
                                    }
                                    if ($enroled) {
                                        if ($result->rowCount() == 0) {
                                            try {
                                                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
                                                $sql = 'INSERT INTO pupilsightStudentEnrolment SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightPersonID=:pupilsightPersonID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID';
                                                $result = $connection2->prepare($sql);
                                                $result->execute($data);
                                            } catch (PDOException $e) {
                                                $enroled = false;
                                            }
                                        } elseif ($result->rowCount() == 1) {
                                            try {
                                                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
                                                $sql = 'UPDATE pupilsightStudentEnrolment SET pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID';
                                                $result = $connection2->prepare($sql);
                                                $result->execute($data);
                                            } catch (PDOException $e) {
                                                $enroled = false;
                                            }
                                        } else {
                                            $enroled = false;
                                        }
                                    }

                                    if ($enroled) {
                                        ++$success;
                                        try {
                                            $dataFamily = array('pupilsightPersonID' => $pupilsightPersonID);
                                            $sqlFamily = 'SELECT pupilsightFamilyID FROM pupilsightFamilyChild WHERE pupilsightPersonID=:pupilsightPersonID';
                                            $resultFamily = $connection2->prepare($sqlFamily);
                                            $resultFamily->execute($dataFamily);
                                        } catch (PDOException $e) {
                                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                        }
                                        while ($rowFamily = $resultFamily->fetch()) {
                                            try {
                                                $dataFamily2 = array('pupilsightFamilyID' => $rowFamily['pupilsightFamilyID']);
                                                $sqlFamily2 = 'SELECT pupilsightPersonID FROM pupilsightFamilyAdult WHERE pupilsightFamilyID=:pupilsightFamilyID';
                                                $resultFamily2 = $connection2->prepare($sqlFamily2);
                                                $resultFamily2->execute($dataFamily2);
                                            } catch (PDOException $e) {
                                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                            }
                                            while ($rowFamily2 = $resultFamily2->fetch()) {
                                                try {
                                                    $dataFamily3 = array('pupilsightPersonID' => $rowFamily2['pupilsightPersonID']);
                                                    $sqlFamily3 = "UPDATE pupilsightPerson SET status='Full' WHERE pupilsightPersonID=:pupilsightPersonID";
                                                    $resultFamily3 = $connection2->prepare($sqlFamily3);
                                                    $resultFamily3->execute($dataFamily3);
                                                } catch (PDOException $e) {
                                                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $ok = true;
                                    try {
                                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'dateEnd' => $dateEnd);
                                        $sql = "UPDATE pupilsightPerson SET status='Left', dateEnd=:dateEnd WHERE pupilsightPersonID=:pupilsightPersonID";
                                        $result = $connection2->prepare($sql);
                                        $result->execute($data);
                                    } catch (PDOException $e) {
                                        $ok == false;
                                    }
                                    if ($ok = true) {
                                        ++$success;
                                    }
                                }
                            }

                            //Feedback result!
                            if ($success == 0) {
                                echo "<div class='alert alert-danger'>";
                                echo __('Your request failed.');
                                echo '</div>';
                            } elseif ($success < $count) {
                                echo "<div class='alert alert-warning'>";
                                echo  sprintf(__('%1$s adds failed.'), ($count - $success));
                                echo '</div>';
                            } else {
                                echo "<div class='alert alert-sucess'>";
                                echo __('Your request was completed successfully.');
                                echo '</div>';
                            }
                        }

                        //RE-ENROL OTHER STUDENTS
                        echo '<h4>';
                        echo __('Re-Enrol Other Students');
                        echo '</h4>';

                        $count = null;
                        if (isset($_POST['reenrol-count'])) {
                            $count = $_POST['reenrol-count'];
                        }
                        if ($count == '') {
                            echo "<div class='alert alert-warning'>";
                            echo __('No actions were selected in Step 2, and so no changes have been made.');
                            echo '</div>';
                        } else {
                            $success = 0;
                            for ($i = 1; $i <= $count; ++$i) {
                                $pupilsightPersonID = $_POST["$i-reenrol-pupilsightPersonID"];
                                $enrol = $_POST["$i-reenrol-enrol"];
                                $pupilsightYearGroupID = $_POST["$i-reenrol-pupilsightYearGroupID"];
                                $pupilsightRollGroupID = $_POST["$i-reenrol-pupilsightRollGroupID"];

                                //Write to database
                                if ($enrol == 'Y') {
                                    $reenroled = true;
                                    //Check for existing record...if exists, update
                                    try {
                                        $data = array('pupilsightSchoolYearID' => $nextYear, 'pupilsightPersonID' => $pupilsightPersonID);
                                        $sql = 'SELECT * FROM pupilsightStudentEnrolment WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID';
                                        $result = $connection2->prepare($sql);
                                        $result->execute($data);
                                    } catch (PDOException $e) {
                                        $reenroled = false;
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }

                                    if ($result->rowCount() != 1 and $result->rowCount() != 0) {
                                        $reenroled = false;
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    } elseif ($result->rowCount() == 1) {
                                        try {
                                            $data2 = array('pupilsightSchoolYearID' => $nextYear, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
                                            $sql2 = 'UPDATE pupilsightStudentEnrolment SET pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID';
                                            $result2 = $connection2->prepare($sql2);
                                            $result2->execute($data2);
                                        } catch (PDOException $e) {
                                            $reenroled = false;
                                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                        }
                                        if ($reenroled) {
                                            ++$success;
                                        }
                                    } elseif ($result->rowCount() == 0) {
                                        //Else, write
                                        try {
                                            $data2 = array('pupilsightSchoolYearID' => $nextYear, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
                                            $sql2 = 'INSERT INTO pupilsightStudentEnrolment SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightPersonID=:pupilsightPersonID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID';
                                            $result2 = $connection2->prepare($sql2);
                                            $result2->execute($data2);
                                        } catch (PDOException $e) {
                                            $reenroled = false;
                                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                        }
                                        if ($reenroled) {
                                            ++$success;
                                        }
                                    }
                                } else {
                                    $reenroled = true;
                                    try {
                                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'dateEnd' => $dateEnd);
                                        $sql = "UPDATE pupilsightPerson SET status='Left', dateEnd=:dateEnd WHERE pupilsightPersonID=:pupilsightPersonID";
                                        $result = $connection2->prepare($sql);
                                        $result->execute($data);
                                    } catch (PDOException $e) {
                                        $reenroled = false;
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }
                                    if ($reenroled) {
                                        ++$success;
                                    }
                                }
                            }

                            //Feedback result!
                            if ($success == 0) {
                                echo "<div class='alert alert-danger'>";
                                echo __('Your request failed.');
                                echo '</div>';
                            } elseif ($success < $count) {
                                echo "<div class='alert alert-warning'>";
                                echo sprintf(__('%1$s adds failed.'), ($count - $success));
                                echo '</div>';
                            } else {
                                echo "<div class='alert alert-sucess'>";
                                echo __('Your request was completed successfully.');
                                echo '</div>';
                            }
                        }

                        //SET FINAL YEAR STUDENTS TO LEFT
                        echo '<h4>';
                        echo __('Set Final Year Students To Left');
                        echo '</h4>';

                        $count = null;
                        if (isset($_POST['final-count'])) {
                            $count = $_POST['final-count'];
                        }
                        if ($count == '') {
                            echo "<div class='alert alert-warning'>";
                            echo __('No actions were selected in Step 2, and so no changes have been made.');
                            echo '</div>';
                        } else {
                            $success = 0;
                            for ($i = 1; $i <= $count; ++$i) {
                                $pupilsightPersonID = $_POST["$i-final-pupilsightPersonID"];
                                $status = $_POST["$i-final-status"];

                                //Write to database
                                $left = true;
                                try {
                                    $data = array('pupilsightPersonID' => $pupilsightPersonID, 'dateEnd' => $dateEnd, 'status' => $status);
                                    $sql = 'UPDATE pupilsightPerson SET status=:status, dateEnd=:dateEnd WHERE pupilsightPersonID=:pupilsightPersonID';
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    $left = false;
                                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                }
                                if ($left) {
                                    ++$success;
                                }
                            }

                            //Feedback result!
                            if ($success == 0) {
                                echo "<div class='alert alert-danger'>";
                                echo __('Your request failed.');
                                echo '</div>';
                            } elseif ($success < $count) {
                                echo "<div class='alert alert-warning'>";
                                echo sprintf(__('%1$s updates failed.'), ($count - $success));
                                echo '</div>';
                            } else {
                                echo "<div class='alert alert-sucess'>";
                                echo __('Your request was completed successfully.');
                                echo '</div>';
                            }
                        }

                        //REGISTER NEW STAFF
                        echo '<h4>';
                        echo __('Register New Staff');
                        echo '</h4>';

                        $count = null;
                        if (isset($_POST['register-count'])) {
                            $count = $_POST['register-count'];
                        }
                        if ($count == '') {
                            echo "<div class='alert alert-warning'>";
                            echo __('No actions were selected in Step 2, and so no changes have been made.');
                            echo '</div>';
                        } else {
                            $success = 0;
                            for ($i = 1; $i <= $count; ++$i) {
                                $pupilsightPersonID = $_POST["$i-register-pupilsightPersonID"];
                                $enrol = $_POST["$i-register-enrol"];
                                $type = $_POST["$i-register-type"];
                                $jobTitle = $_POST["$i-register-jobTitle"];

                                //Write to database
                                if ($enrol == 'Y') {
                                    $enroled = true;
                                    //Check for existing record
                                    try {
                                        $dataCheck = array('pupilsightPersonID' => $pupilsightPersonID);
                                        $sqlCheck = 'SELECT * FROM pupilsightStaff WHERE pupilsightPersonID=:pupilsightPersonID';
                                        $resultCheck = $connection2->prepare($sqlCheck);
                                        $resultCheck->execute($dataCheck);
                                    } catch (PDOException $e) {
                                        $enroled = false;
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }
                                    if ($resultCheck->rowCount() == 0) {
                                        try {
                                            $data = array('pupilsightPersonID' => $pupilsightPersonID, 'type' => $type, 'jobTitle' => $jobTitle);
                                            $sql = 'INSERT INTO pupilsightStaff SET pupilsightPersonID=:pupilsightPersonID, type=:type, jobTitle=:jobTitle';
                                            $result = $connection2->prepare($sql);
                                            $result->execute($data);
                                        } catch (PDOException $e) {
                                            $enroled = false;
                                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                        }
                                        if ($enroled) {
                                            ++$success;
                                        }
                                    } elseif ($resultCheck->rowCount() == 1) {
                                        try {
                                            $data = array('pupilsightPersonID' => $pupilsightPersonID, 'type' => $type, 'jobTitle' => $jobTitle);
                                            $sql = 'UPDATE pupilsightStaff SET type=:type, jobTitle=:jobTitle WHERE pupilsightPersonID=:pupilsightPersonID';
                                            $result = $connection2->prepare($sql);
                                            $result->execute($data);
                                        } catch (PDOException $e) {
                                            $enroled = false;
                                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                        }
                                        if ($enroled) {
                                            ++$success;
                                        }
                                    }
                                } else {
                                    $left = true;
                                    try {
                                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'type' => $type, 'jobTitle' => $jobTitle, 'dateEnd' => $dateEnd);
                                        $sql = "UPDATE pupilsightPerson SET status='Left', dateEnd=:dateEnd WHERE pupilsightPersonID=$pupilsightPersonID";
                                        $result = $connection2->prepare($sql);
                                        $result->execute($data);
                                    } catch (PDOException $e) {
                                        $left = false;
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }
                                    if ($left) {
                                        ++$success;
                                    }
                                }
                            }

                            //Feedback result!
                            if ($success == 0) {
                                echo "<div class='alert alert-danger'>";
                                echo __('Your request failed.');
                                echo '</div>';
                            } elseif ($success < $count) {
                                echo "<div class='alert alert-warning'>";
                                echo sprintf(__('%1$s adds failed.'), ($count - $success));
                                echo '</div>';
                            } else {
                                echo "<div class='alert alert-sucess'>";
                                echo __('Your request was completed successfully.');
                                echo '</div>';
                            }
                        }
                    }
                }
            }
        }
    }
}
?>
