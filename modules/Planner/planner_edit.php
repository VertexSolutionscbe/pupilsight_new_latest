<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Module\Planner\Forms\PlannerFormFactory;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Helper\HelperGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $HelperGateway = $container->get(HelperGateway::class);
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Set variables
        $today = date('Y-m-d');

        //Proceed!
        //Get viewBy, date and class variables
        $params = [];
        $viewBy = null;
        if (isset($_GET['viewBy'])) {
            $viewBy = $_GET['viewBy'];
        }
        $subView = null;
        if (isset($_GET['subView'])) {
            $subView = $_GET['subView'];
        }
        if ($viewBy != 'date' and $viewBy != 'class') {
            $viewBy = 'date';
        }
        $date = null;
        $dateStamp = null;
        if ($viewBy == 'date') {
            $date = $_GET['date'];
            if (isset($_GET['dateHuman'])) {
                $date = dateConvert($guid, $_GET['dateHuman']);
            }
            if ($date == '') {
                $date = date('Y-m-d');
            }
            list($dateYear, $dateMonth, $dateDay) = explode('-', $date);
            $dateStamp = mktime(0, 0, 0, $dateMonth, $dateDay, $dateYear);
            $params += [
                'viewBy' => 'date',
                'date' => $date,
            ];
        } elseif ($viewBy == 'class') {
            $class = null;
            if (isset($_GET['class'])) {
                $class = $_GET['class'];
            }
            $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
            $params += [
                'viewBy' => 'class',
                'date' => $class,
                'pupilsightCourseClassID' => $pupilsightCourseClassID,
                'subView' => $subView,
            ];
        }
        $paramsVar = '&' . http_build_query($params); // for backward compatibile uses below (should be get rid of)

        list($todayYear, $todayMonth, $todayDay) = explode('-', $today);
        $todayStamp = mktime(0, 0, 0, $todayMonth, $todayDay, $todayYear);

        //Check if school year specified
        $pupilsightCourseClassID = null;
        if (isset($_GET['pupilsightCourseClassID'])) {
            $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
            $pupilsightProgramID = $_GET['pupilsightProgramID'];
        }
        $pupilsightPlannerEntryID = $_GET['pupilsightPlannerEntryID'];
        if ($pupilsightPlannerEntryID == '' or ($viewBy == 'class' and $pupilsightCourseClassID == 'Y')) {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                if ($viewBy == 'date') {
                    if ($highestAction == 'Lesson Planner_viewEditAllClasses') {
                        $data = array('date' => $date, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);

                        /* Closed By Bikash */
                        // $sql = 'SELECT pupilsightCourse.pupilsightCourseID, pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.*, pupilsightCourse.pupilsightYearGroupIDList FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE date=:date AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID';

                        
                        $sql = "SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightPlannerEntry.pupilsightProgramID, pupilsightPlannerEntry.pupilsightYearGroupID, pupilsightPlannerEntry.pupilsightRollGroupID, pupilsightPlannerEntry.pupilsightDepartmentID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightUnitID, pupilsightProgram.name AS progName, pupilsightYearGroup.name AS className , pupilsightRollGroup.name AS sectionName, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, 'Teacher' AS role, homeworkSubmission, homeworkCrowdAssess, date, pupilsightPlannerEntry.pupilsightCourseClassID, NULL AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightProgram ON (pupilsightPlannerEntry.pupilsightProgramID=pupilsightProgram.pupilsightProgramID) JOIN pupilsightYearGroup ON (pupilsightPlannerEntry.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) JOIN pupilsightRollGroup ON (pupilsightPlannerEntry.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE date=:date AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID";
                    } else {
                        $data = array('date' => $date, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = "SELECT pupilsightCourse.pupilsightCourseID, pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.*, pupilsightCourse.pupilsightYearGroupIDList FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND date=:date AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID";
                    }
                } else {
                    if ($highestAction == 'Lesson Planner_viewEditAllClasses') {
                        // $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                        // $sql = 'SELECT pupilsightCourse.pupilsightCourseID, pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightDepartmentID, pupilsightPlannerEntry.*, pupilsightCourse.pupilsightYearGroupIDList FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID';

                        $data = array('pupilsightProgramID' => $pupilsightProgramID, 'pupilsightCourseClassID' => $pupilsightCourseClassID,'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                        
                        $sql = "SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightPlannerEntry.pupilsightProgramID, pupilsightPlannerEntry.pupilsightYearGroupID, pupilsightPlannerEntry.pupilsightRollGroupID, pupilsightPlannerEntry.pupilsightDepartmentID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightUnitID, pupilsightProgram.name AS progName, pupilsightYearGroup.name AS className , pupilsightRollGroup.name AS sectionName, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, 'Teacher' AS role, homeworkSubmission, homeworkCrowdAssess, date, pupilsightPlannerEntry.pupilsightCourseClassID, NULL AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightProgram ON (pupilsightPlannerEntry.pupilsightProgramID=pupilsightProgram.pupilsightProgramID) JOIN pupilsightYearGroup ON (pupilsightPlannerEntry.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) JOIN pupilsightRollGroup ON (pupilsightPlannerEntry.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) JOIN pupilsightDepartment ON (pupilsightPlannerEntry.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightPlannerEntry.pupilsightProgramID=:pupilsightProgramID AND pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID ORDER BY date, timeStart";
                    } else {
                        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = "SELECT pupilsightCourse.pupilsightCourseID, pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightDepartmentID, pupilsightPlannerEntry.*, pupilsightCourse.pupilsightYearGroupIDList FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID";
                    }
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            //print_r($result->rowCount());

            if ($result->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                //Let's go!
                $values = $result->fetch();

                if(!empty($values['pupilsightProgramID'])){
                    $pupilsightSchoolYearID= $_SESSION[$guid]['pupilsightSchoolYearID'];
                    $pupilsightPersonID= $_SESSION[$guid]['pupilsightPersonID'];

                    $classes =  $HelperGateway->getClassByProgram($connection2, $values['pupilsightProgramID']);

                    $sections =  $HelperGateway->getSectionByProgram($connection2, $values['pupilsightYearGroupID'],  $values['pupilsightProgramID']);

                    $subjects =  $HelperGateway->getSubjectByProgramClass($connection2, $values['pupilsightYearGroupID'],  $values['pupilsightProgramID'], $pupilsightSchoolYearID, $pupilsightPersonID);
                    //print_r($subjects);

                } else {
                    $classes = array('' => 'Select Class');
                    $sections = array('' => 'Select Section');
                    $subjects = array('' => 'Select Subject');
                }
                
                if ($viewBy == 'date') {
                    $extra = dateConvertBack($guid, $date);
                } else {
                    $extra = $values['course'].'.'.$values['class'];
                    $pupilsightDepartmentID = $values['pupilsightDepartmentID'];
                }
                $pupilsightYearGroupIDList = $values['pupilsightYearGroupIDList'];

                $page->breadcrumbs
                    ->add(__('Planner for {classDesc}', [
                        'classDesc' => $extra,
                    ]), 'planner.php', $params)
                    ->add(__('Edit Lesson Plan'));

                //Get pupilsightUnitClassID
                $pupilsightUnitID = $values['pupilsightUnitID'];
                $pupilsightUnitClassID = null;
                try {
                    $dataUnitClass = array('pupilsightCourseClassID' => $values['pupilsightCourseClassID'], 'pupilsightUnitID' => $pupilsightUnitID);
                    $sqlUnitClass = 'SELECT pupilsightUnitClassID FROM pupilsightUnitClass WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightUnitID=:pupilsightUnitID';
                    $resultUnitClass = $connection2->prepare($sqlUnitClass);
                    $resultUnitClass->execute($dataUnitClass);
                } catch (PDOException $e) {
                }
                if ($resultUnitClass->rowCount() == 1) {
                    $rowUnitClass = $resultUnitClass->fetch();
                    $pupilsightUnitClassID = $rowUnitClass['pupilsightUnitClassID'];
                }

                $returns = array();
                $returns['success1'] = __('Your request was completed successfully.').__('You can now edit more details of your newly duplicated entry.');
                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, $returns);
                }

                echo "<div class='linkTop' style='margin-bottom: 7px'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_view_full.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID$paramsVar'>".__('View')."<img style='margin: 0 0 -4px 3px' title='".__('View')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a>";
                echo '</div>';
                // echo '<pre>';
                // print_r($values);
                // echo '</pre>';

                $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/planner_editProcess.php?pupilsightPlannerEntryID=$pupilsightPlannerEntryID&viewBy=$viewBy&subView=$subView&address=".$_SESSION[$guid]['address']);
                $form->setFactory(PlannerFormFactory::create($pdo));

                $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                //BASIC INFORMATION
                $form->addRow()->addHeading(__('Basic Information'));

                if ($highestAction == 'Lesson Planner_viewEditAllClasses') {
                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sql = 'SELECT pupilsightCourseClass.pupilsightCourseClassID AS value, CONCAT(pupilsightCourse.nameShort,".", pupilsightCourseClass.nameShort) AS name FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name';
                } else {
                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = 'SELECT pupilsightCourseClass.pupilsightCourseClassID AS value, CONCAT(pupilsightCourse.nameShort,".", pupilsightCourseClass.nameShort) AS name FROM pupilsightCourseClassPerson JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID ORDER BY name';
                }
                
                /* Closed By Bikash */
                // $row = $form->addRow();
                //     $row->addLabel('pupilsightCourseClassID', __('Class'));
                //     $row->addSelect('pupilsightCourseClassID')->fromQuery($pdo, $sql, $data)->required()->placeholder();

                    $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
                    $resultp = $connection2->query($sqlp);
                    $rowdataprog = $resultp->fetchAll();

                    $program = array();
                    $program2 = array();
                    $program1 = array('' => 'Select Program');
                    foreach ($rowdataprog as $dt) {
                        $program2[$dt['pupilsightProgramID']] = $dt['name'];
                    }
                    $program = $program1 + $program2;

                    $row = $form->addRow();
                        $row->addLabel('pupilsightProgramID', __('Program'));
                        $row->addSelect('pupilsightProgramID')->setId('pupilsightProgramIDbyPP')->fromArray($program)->placeholder('Select Program')->required();
                
                
                    $row = $form->addRow();
                        $row->addLabel('pupilsightYearGroupID', __('Class'));
                        $row->addSelect('pupilsightYearGroupID')->setId('pupilsightYearGroupIDbyPP')->fromArray($classes)->placeholder('Select Class')->required();
                
                        
                    $row = $form->addRow();
                        $row->addLabel('pupilsightRollGroupID', __('Section'));
                        $row->addSelect('pupilsightRollGroupID')->setId('pupilsightRollGroupIDbyPP')->fromArray($sections)->placeholder('Select Section')->required(); 

                    $row = $form->addRow();
                        $row->addLabel('pupilsightDepartmentID', __('Subject'));
                        $row->addSelect('pupilsightDepartmentID')->setId('pupilsightDepartmentIDbyPP')->fromArray($subjects)->placeholder('Select Subject')->required();     


                $sql = "SELECT GROUP_CONCAT(pupilsightCourseClassID SEPARATOR ' ') AS chainedTo, pupilsightUnit.pupilsightUnitID as value, name FROM pupilsightUnit JOIN pupilsightUnitClass ON (pupilsightUnit.pupilsightUnitID=pupilsightUnitClass.pupilsightUnitID) WHERE active='Y' AND running='Y'  GROUP BY pupilsightUnit.pupilsightUnitID ORDER BY name";
                $row = $form->addRow();
                    $row->addLabel('pupilsightUnitID', __('Unit'));
                    $row->addSelect('pupilsightUnitID')->fromQueryChained($pdo, $sql, [], 'pupilsightCourseClassID')->placeholder();

                $row = $form->addRow();
                    $row->addLabel('name', __('Lesson Name'));
                    $row->addTextField('name')->setValue()->maxLength(50)->required();

                $row = $form->addRow();
                    $row->addLabel('summary', __('Summary'));
                    $row->addTextField('summary')->setValue()->maxLength(255);

                $row = $form->addRow();
                    $row->addLabel('date', __('Date'));
                    $row->addDate('date')->required();

                $nextTimeStart = (isset($nextTimeStart)) ? substr($nextTimeStart, 0, 5) : null;
                $row = $form->addRow();
                    $row->addLabel('timeStart', __('Start Time'))->description("Format: hh:mm (24hr)");
                    $row->addTime('timeStart')->required();

                $nextTimeEnd = (isset($nextTimeEnd)) ? substr($nextTimeEnd, 0, 5) : null;
                $row = $form->addRow();
                    $row->addLabel('timeEnd', __('End Time'))->description("Format: hh:mm (24hr)");
                    $row->addTime('timeEnd')->required();


                //LESSON
                $form->addRow()->addHeading(__('Lesson Content'));

                $description = getSettingByScope($connection2, 'Planner', 'lessonDetailsTemplate') ;
                $row = $form->addRow();
                    $column = $row->addColumn();
                    $column->addLabel('description', __('Lesson Details'));
                    $column->addEditor('description', $guid)->setRows(25)->showMedia()->setValue($description);

                $teachersNotes = getSettingByScope($connection2, 'Planner', 'teachersNotesTemplate');
                $row = $form->addRow();
                    $column = $row->addColumn();
                    $column->addLabel('teachersNotes', __('Teacher\'s Notes'));
                    $column->addEditor('teachersNotes', $guid)->setRows(25)->showMedia()->setValue($teachersNotes);

                //SMART BLOCKS
                if (!empty($values['pupilsightUnitID'])) {
                    $form->addRow()->addHeading(__('Smart Blocks'));

                    $form->addRow()->addContent("<div class='float-right'><a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/units_edit_working.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightCourseID=".$values['pupilsightCourseID'].'&pupilsightUnitID='.$values['pupilsightUnitID'].'&pupilsightSchoolYearID='.$_SESSION[$guid]['pupilsightSchoolYearID']."&pupilsightUnitClassID=$pupilsightUnitClassID'>".__('Edit Unit').'</a></span>');

                    $row = $form->addRow();
                        $customBlocks = $row->addPlannerSmartBlocks('smart', $pupilsight->session, $guid);

                    $dataBlocks = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                    $sqlBlocks = 'SELECT * FROM pupilsightUnitClassBlock WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID ORDER BY sequenceNumber';
                    $resultBlocks = $pdo->select($sqlBlocks, $dataBlocks);

                    while ($rowBlocks = $resultBlocks->fetch()) {
                        $smart = array(
                            'title' => $rowBlocks['title'],
                            'type' => $rowBlocks['type'],
                            'length' => $rowBlocks['length'],
                            'contents' => $rowBlocks['contents'],
                            'teachersNotes' => $rowBlocks['teachersNotes'],
                            'pupilsightUnitClassBlockID' => $rowBlocks['pupilsightUnitClassBlockID']
                        );
                        $customBlocks->addBlock($rowBlocks['pupilsightUnitClassBlockID'], $smart);
                    }
                }

                //HOMEWORK
                $form->addRow()->addHeading(__('Homework'));

                $form->toggleVisibilityByClass('homework')->onRadio('homework')->when('Y');
                $row = $form->addRow();
                    $row->addLabel('homework', __('Homework?'));
                    $row->addRadio('homework')->fromArray(array('Y' => __('Yes'), 'N' => __('No')))->required()->checked('N')->inline(true);

                $row = $form->addRow()->addClass('homework');
                    $row->addLabel('homeworkDueDate', __('Homework Due Date'));
                    $row->addDate('homeworkDueDate')->required()->setValue(Format::date(substr($values['homeworkDueDateTime'], 0, 10)));

                $values['homeworkDueDateTime'] = substr($values['homeworkDueDateTime'], 11, 5);
                $row = $form->addRow()->addClass('homework');
                    $row->addLabel('homeworkDueDateTime', __('Homework Due Date Time'))->description("Format: hh:mm (24hr)");
                    $row->addTime('homeworkDueDateTime');

                $row = $form->addRow()->addClass('homework');
                    $column = $row->addColumn();
                    $column->addLabel('homeworkDetails', __('Homework Details'));
                    $column->addEditor('homeworkDetails', $guid)->setRows(15)->showMedia()->setValue($description)->required();

                $form->toggleVisibilityByClass('homeworkSubmission')->onRadio('homeworkSubmission')->when('Y');
                $row = $form->addRow()->addClass('homework');
                    $row->addLabel('homeworkSubmission', __('Online Submission?'));
                    $row->addRadio('homeworkSubmission')->fromArray(array('Y' => __('Yes'), 'N' => __('No')))->required()->checked('N')->inline(true);

                $values['homeworkSubmissionDateOpen'] = (!empty($values['homeworkSubmissionDateOpen'])) ? $values['homeworkSubmissionDateOpen'] : date('Y-m-d') ;
                $row = $form->addRow()->setClass('homeworkSubmission');
                    $row->addLabel('homeworkSubmissionDateOpen', __('Submission Open Date'));
                    $row->addDate('homeworkSubmissionDateOpen')->required();

                $row = $form->addRow()->setClass('homeworkSubmission');
                    $row->addLabel('homeworkSubmissionDrafts', __('Drafts'));
                    $row->addSelect('homeworkSubmissionDrafts')->fromArray(array('0' => __('None'), '1' => __('1'), '2' => __('2'), '3' => __('3')))->required();

                $row = $form->addRow()->setClass('homeworkSubmission');
                    $row->addLabel('homeworkSubmissionType', __('Submission Type'));
                    $row->addSelect('homeworkSubmissionType')->fromArray(array('Link' => __('Link'), 'File' => __('File'), 'Link/File' => __('Link/File')))->required();

                $row = $form->addRow()->setClass('homeworkSubmission');
                    $row->addLabel('homeworkSubmissionRequired', __('Submission Required'));
                    $row->addSelect('homeworkSubmissionRequired')->fromArray(array('Optional' => __('Optional'), 'Compulsory' => __('Compulsory')))->required();

                if (isActionAccessible($guid, $connection2, '/modules/Crowd Assessment/crowdAssess.php')) {
                    $form->toggleVisibilityByClass('homeworkCrowdAssess')->onRadio('homeworkCrowdAssess')->when('Y');
                    $row = $form->addRow()->addClass('homeworkSubmission');
                        $row->addLabel('homeworkCrowdAssess', __('Crowd Assessment?'));
                        $row->addRadio('homeworkCrowdAssess')->fromArray(array('Y' => __('Yes'), 'N' => __('No')))->required()->inline(true);

                    $row = $form->addRow()->addClass('homeworkCrowdAssess');
                        $row->addLabel('homeworkCrowdAssessControl', __('Access Controls?'))->description(__('Decide who can see this homework.'));
                        $column = $row->addColumn()->setClass('flex-col items-end');
                            $column->addCheckbox('homeworkCrowdAssessClassTeacher')->checked(true)->description(__('Class Teacher'))->disabled();
                            $column->addCheckbox('homeworkCrowdAssessClassSubmitter')->checked(true)->description(__('Submitter'))->disabled();
                            $column->addCheckbox('homeworkCrowdAssessClassmatesRead')->setValue('Y')->description(__('Classmates'));
                            $column->addCheckbox('homeworkCrowdAssessOtherStudentsRead')->setValue('Y')->description(__('Other Students'));
                            $column->addCheckbox('homeworkCrowdAssessOtherTeachersRead')->setValue('Y')->description(__('Other Teachers'));
                            $column->addCheckbox('homeworkCrowdAssessSubmitterParentsRead')->setValue('Y')->description(__("Submitter's Parents"));
                            $column->addCheckbox('homeworkCrowdAssessClassmatesParentsRead')->setValue('Y')->description(__("Classmates's Parents"));
                            $column->addCheckbox('homeworkCrowdAssessOtherParentsRead')->setValue('Y')->description(__('Other Parents'));
                }

                // OUTCOMES
                if ($viewBy == 'date') {
                    $form->addRow()->addHeading(__('Outcomes'));
                    $form->addRow()->addAlert(__('Outcomes cannot be set when viewing the Planner by date. Use the "Choose A Class" dropdown in the sidebar to switch to a class. Make sure to save your changes first.'), 'warning');
                } else {
                    $form->addRow()->addHeading(__('Outcomes'));
                    $form->addRow()->addContent(__('Link this lesson to outcomes (defined in the Manage Outcomes section of the Planner), and track which outcomes are being met in which lessons.'));

                    $allowOutcomeEditing = getSettingByScope($connection2, 'Planner', 'allowOutcomeEditing');

                    $row = $form->addRow();
                        $customBlocks = $row->addPlannerOutcomeBlocks('outcome', $pupilsight->session, $pupilsightYearGroupIDList, $pupilsightDepartmentID, $allowOutcomeEditing);

                    $dataBlocks = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                    $sqlBlocks = 'SELECT pupilsightPlannerEntryOutcome.*, scope, name, category FROM pupilsightPlannerEntryOutcome JOIN pupilsightOutcome ON (pupilsightPlannerEntryOutcome.pupilsightOutcomeID=pupilsightOutcome.pupilsightOutcomeID) WHERE pupilsightPlannerEntryOutcome.pupilsightPlannerEntryID=:pupilsightPlannerEntryID ORDER BY sequenceNumber';
                    $resultBlocks = $pdo->select($sqlBlocks, $dataBlocks);

                    while ($rowBlocks = $resultBlocks->fetch()) {
                        $outcome = array(
                            'outcometitle' => $rowBlocks['name'],
                            'outcomepupilsightOutcomeID' => $rowBlocks['pupilsightOutcomeID'],
                            'outcomecategory' => $rowBlocks['category'],
                            'outcomecontents' => $rowBlocks['content']
                        );
                        $customBlocks->addBlock($rowBlocks['pupilsightOutcomeID'], $outcome);
                    }
                }

                //Access
                $form->addRow()->addHeading(__('Access'));

                $row = $form->addRow();
                    $row->addLabel('viewableStudents', __('Viewable to Students'));
                    $row->addYesNo('viewableStudents')->required();

                $row = $form->addRow();
                    $row->addLabel('viewableParents', __('Viewable to Parents'));
                    $row->addYesNo('viewableParents')->required();

                //Guests
                $form->addRow()->addHeading(__('Current Guests'));

                $data = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                $sql = "SELECT title, preferredName, surname, category, pupilsightPlannerEntryGuest.* FROM pupilsightPlannerEntryGuest JOIN pupilsightPerson ON (pupilsightPlannerEntryGuest.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID ORDER BY surname, preferredName";

                $results = $pdo->executeQuery($data, $sql);

                if ($results->rowCount() == 0) {
                    $form->addRow()->addAlert(__('There are no records to display.'), 'error');
                } else {
                    $form->addRow()->addContent('<b>'.__('Warning').'</b>: '.__('If you delete a guest, any unsaved changes to this planner entry will be lost!'))->wrap('<i>', '</i>');

                    $table = $form->addRow()->addTable()->addClass('colorOddEven');

                    $header = $table->addHeaderRow();
                    $header->addContent(__('Name'));
                    $header->addContent(__('Role'));
                    $header->addContent(__('Action'));

                    while ($staff = $results->fetch()) {
                        $row = $table->addRow();
                        $row->addContent(formatName('', $staff['preferredName'], $staff['surname'], 'Staff', true, true));
                        $row->addContent($staff['role']);
                        $row->addContent("<a onclick='return confirm(\"".__('Are you sure you wish to delete this record?')."\")' href='".$_SESSION[$guid]['absoluteURL']."/modules/".$_SESSION[$guid]['module']."/planner_edit_guest_deleteProcess.php?pupilsightPlannerEntryGuestID=".$staff['pupilsightPlannerEntryGuestID']."&pupilsightPlannerEntryID=".$pupilsightPlannerEntryID."&viewBy=$viewBy&subView=$subView&pupilsightCourseClassID=$pupilsightCourseClassID&date=$date&address=".$_GET['q']."'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a>");
                    }
                }

                $form->addRow()->addHeading(__('New Guests'));

                $row = $form->addRow();
                    $row->addLabel('guests', __('Guest List'));
                    $row->addSelectUsers('guests')->selectMultiple();

                $roles = array(
                    'Guest Student' => __('Guest Student'),
                    'Guest Teacher' => __('Guest Teacher'),
                    'Guest Assistant' => __('Guest Assistant'),
                    'Guest Technician' => __('Guest Technician'),
                    'Guest Parent' => __('Guest Parent'),
                    'Other Guest' => __('Other Guest'),
                );
                $row = $form->addRow();
                    $row->addLabel('role', __('Role'));
                    $row->addSelect('role')->fromArray($roles);

                $row = $form->addRow();
                    $row->addFooter();
                    $row->addCheckbox('notify')->description('Notify all class participants');
                    $row->addSubmit();

                $form->loadAllValuesFrom($values);

                echo $form->getOutput();

            }
        }
        //Print sidebar
        $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $connection2, $todayStamp, $_SESSION[$guid]['pupilsightPersonID'], $dateStamp, $pupilsightCourseClassID);
    }
}
?>
<style>
    #guestsPhoto {
        margin: 0 0 0 -60px;
    }
</style>
<script>

$(document).ready(function(){
    $("#guests").select2();
});

$(document).on('change', '#pupilsightYearGroupIDbyPP', function () {
    var id = $(this).val();
    var pid = $('#pupilsightProgramIDbyPP').val();
    var type = 'getSubjectbasedonclassNew';
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: type, pupilsightProgramID: pid },
        async: true,
        success: function (response) {
            $("#pupilsightDepartmentIDbyPP").html();
            $("#pupilsightDepartmentIDbyPP").html(response);
        }
    });
});

</script>