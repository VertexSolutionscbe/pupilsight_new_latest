<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/course_rollover.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('Course Enrolment Rollover'));

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

                $form = Form::create('courseRollover', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/course_rollover.php&step=2');

                $form->addHiddenValue('nextYear', $nextYear);

                $row = $form->addRow();
                    $row->addContent(sprintf(__('By clicking the "Proceed" button below you will initiate the course enrolment rollover from %1$s to %2$s. In a big school this operation may take some time to complete. %3$sYou are really, very strongly advised to backup all data before you proceed%4$s.'), '<b>'.$_SESSION[$guid]['pupilsightSchoolYearName'].'</b>', '<b>'.$nameNext.'</b>', '<span style="color: #cc0000"><i>', '</span>'));

                $row = $form->addRow();
                    $row->addSubmit(__('Proceed'));

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

                // Get the current courses/classes
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sql = "SELECT pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY course, class";
                $result = $pdo->executeQuery($data, $sql);
                $currentCourses = ($result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE) : array();

                // Get the next year's courses/classes
                $data = array('pupilsightSchoolYearID' => $nextYear);
                $sql = "SELECT pupilsightCourseClassID as value, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) as name FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name";
                $result = $pdo->executeQuery($data, $sql);
                $nextCourses = ($result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_KEY_PAIR) : array();

                // Increment numbers in each course name and try to find a matching next-year course
                $currentCourses = array_map(function($currentCourse) use ($nextCourses) {
                    $findNextCourse = preg_replace_callback("/(\d+)/", function ($matches) {
                        return str_pad((1 + $matches[1]), strlen($matches[1]), '0', STR_PAD_LEFT);
                    }, $currentCourse['course']);

                    if ($currentCourse['course'] != $findNextCourse) {
                        $courseClassName = $findNextCourse.'.'.$currentCourse['class'];
                        $currentCourse['pupilsightCourseClassIDNext'] = array_search($courseClassName, $nextCourses);
                    }
                    return $currentCourse;
                }, $currentCourses);

                $form = Form::create('courseRollover', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/course_rollover.php&step=3');
                $form->setClass('w-full blank');

                $form->addHiddenValue('nextYear', $nextYear);

                $table = $form->addRow()->addTable()->setClass('smallIntBorder fullWidth');
                $row = $table->addRow();
                    $row->addLabel('rollStudents', __('Include Students'));
                    $row->addCheckbox('rollStudents')->checked('on');

                $row = $table->addRow();
                    $row->addLabel('rollTeachers', __('Include Teachers'));
                    $row->addCheckbox('rollTeachers')->checked('on');

                $form->addRow()->addSubheading(__('Map Classes'));
                $form->addRow()->addContent(__('Determine which classes from this year roll to which classes in next year, and which not to rollover at all.'))->wrap('<p>', '<p>');

                $table = $form->addRow()->addTable()->setClass('colorOddEven fullWidth rowHighlight');

                $header = $table->addHeaderRow();
                    $header->addContent(__('Class'));
                    $header->addContent(__('New Class'));

                foreach ($currentCourses as $pupilsightCourseClassID => $course) {
                    $pupilsightCourseClassIDNext = isset($course['pupilsightCourseClassIDNext'])? $course['pupilsightCourseClassIDNext'] : '';

                    $row = $table->addRow();
                        $row->addContent($course['course'].'.'.$course['class']);
                        $row->addSelect('pupilsightCourseClassIDNext['.$pupilsightCourseClassID.']')
                            ->fromArray($nextCourses)
                            ->selected($pupilsightCourseClassIDNext)
                            ->placeholder()
                            ->setClass('mediumWidth');
                }

                $table = $form->addRow()->addTable()->setClass('smallIntBorder fullWidth');
                $row = $table->addRow();
                    $row->addFooter();
                    $row->addSubmit(__('Proceed'));

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

                $partialFail = false;

                $count = isset($_POST['count'])? $_POST['count'] : '';
                $rollStudents = isset($_POST['rollStudents'])? $_POST['rollStudents'] : '';
                $rollTeachers = isset($_POST['rollTeachers'])? $_POST['rollTeachers'] : '';

                if ($rollStudents != 'on' and $rollTeachers != 'on') {
                    echo "<div class='alert alert-danger'>";
                    echo __('Your request failed because your inputs were invalid.');
                    echo '</div>';
                } else {
                    $classes = isset($_POST['pupilsightCourseClassIDNext'])? $_POST['pupilsightCourseClassIDNext'] : array();
                    $classes = array_filter($classes);

                    foreach ($classes as $pupilsightCourseClassID => $pupilsightCourseClassIDNext) {
                        //Get staff and students and copy them over
                        if ($rollStudents == 'on' and $rollTeachers == 'on') {
                            $sqlWhere = " AND (pupilsightCourseClassPerson.role='Student' OR pupilsightCourseClassPerson.role='Teacher')";
                        } elseif ($rollStudents == 'on' and $rollTeachers == '') {
                            $sqlWhere = " AND pupilsightCourseClassPerson.role='Student'";
                        } else {
                            $sqlWhere = " AND pupilsightCourseClassPerson.role='Teacher'";
                        }
                        //Get current enrolment, exclude people already enrolled or their status is not Full
                        try {
                            $dataCurrent = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightCourseClassIDNext' => $pupilsightCourseClassIDNext);
                            $sqlCurrent = "SELECT pupilsightCourseClassPerson.pupilsightPersonID, pupilsightCourseClassPerson.role, pupilsightCourseClassPerson.reportable
                            FROM pupilsightCourseClassPerson
                            JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                            LEFT JOIN pupilsightCourseClassPerson as pupilsightCourseClassPersonNext ON (pupilsightCourseClassPersonNext.pupilsightCourseClassID=:pupilsightCourseClassIDNext AND pupilsightCourseClassPersonNext.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID)
                            WHERE pupilsightCourseClassPerson.pupilsightCourseClassID=:pupilsightCourseClassID
                            AND pupilsightCourseClassPersonNext.pupilsightCourseClassPersonID IS NULL
                            AND pupilsightPerson.status='Full'
                            $sqlWhere";
                            $resultCurrent = $connection2->prepare($sqlCurrent);
                            $resultCurrent->execute($dataCurrent);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                        if ($resultCurrent->rowCount() > 0) {
                            while ($rowCurrent = $resultCurrent->fetch()) {
                                try {
                                    $dataInsert = array('pupilsightCourseClassID' => $pupilsightCourseClassIDNext, 'pupilsightPersonID' => $rowCurrent['pupilsightPersonID'], 'role' => $rowCurrent['role'], 'reportable' => $rowCurrent['reportable']);
                                    $sqlInsert = 'INSERT INTO pupilsightCourseClassPerson SET pupilsightCourseClassID=:pupilsightCourseClassID, pupilsightPersonID=:pupilsightPersonID, role=:role, reportable=:reportable';
                                    $resultInsert = $connection2->prepare($sqlInsert);
                                    $resultInsert->execute($dataInsert);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }
                            }
                        }
                    }

                    //Feedback result!
                    if ($partialFail == true) {
                        echo "<div class='alert alert-danger'>";
                        echo __('Your request was successful, but some data was not properly saved.');
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
