<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Module\Planner\Forms\PlannerFormFactory;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

// common variables
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
$pupilsightCourseID = $_GET['pupilsightCourseID'] ?? '';
$pupilsightUnitID = $_GET['pupilsightUnitID'] ?? '';

$page->breadcrumbs
    ->add(__('Unit Planner'), 'units.php', [
        'pupilsightSchoolYearID' => $pupilsightSchoolYearID,
        'pupilsightCourseID' => $pupilsightCourseID,
    ])
    ->add(__('Edit Unit'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/units_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Proceed!
        $returns = array();
        $returns['success1'] = __('Smart Blockify was successful.');
        $returns['success2'] = __('Copy was successful. The blocks from the selected working unit have replaced those in the master unit (see below for the new block listing).');
        $returns['success3'] = __('Your unit was successfully created: you can now edit and deploy it using the form below.');
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, $returns);
        }

        //Check if courseschool year specified
        if ($pupilsightCourseID == '' or $pupilsightSchoolYearID == '') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                if ($highestAction == 'Unit Planner_all') {
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID);
                    $sql = 'SELECT pupilsightCourse.*, pupilsightSchoolYear.name as schoolYearName
                            FROM pupilsightCourse
                            JOIN pupilsightSchoolYear ON (pupilsightSchoolYear.pupilsightSchoolYearID=pupilsightCourse.pupilsightSchoolYearID)
                            WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID';
                } elseif ($highestAction == 'Unit Planner_learningAreas') {
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = "SELECT pupilsightCourseID, pupilsightCourse.name, pupilsightCourse.nameShort, pupilsightYearGroupIDList, pupilsightSchoolYear.name as schoolYearName
                    FROM pupilsightCourse
                    JOIN pupilsightSchoolYear ON (pupilsightSchoolYear.pupilsightSchoolYearID=pupilsightCourse.pupilsightSchoolYearID)
                    JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID)
                    JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID)
                    WHERE pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID AND (role='Coordinator' OR role='Assistant Coordinator' OR role='Teacher (Curriculum)') AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID ORDER BY pupilsightCourse.nameShort";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                $values = $result->fetch();
                $yearName = $values['schoolYearName'];
                $courseName = $values['name'];
                $courseNameShort = $values['nameShort'];
                $pupilsightYearGroupIDList = $values['pupilsightYearGroupIDList'];

                //Check if unit specified
                if ($pupilsightUnitID == '') {
                    echo "<div class='alert alert-danger'>";
                    echo __('You have not specified one or more required parameters.');
                    echo '</div>';
                } else {
                    try {
                        $data = array('pupilsightUnitID' => $pupilsightUnitID, 'pupilsightCourseID' => $pupilsightCourseID);
                        $sql = 'SELECT pupilsightCourse.nameShort AS courseName, pupilsightCourse.pupilsightDepartmentID, pupilsightUnit.* FROM pupilsightUnit JOIN pupilsightCourse ON (pupilsightUnit.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightUnitID=:pupilsightUnitID AND pupilsightUnit.pupilsightCourseID=:pupilsightCourseID';
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
                        $values = $result->fetch();
                        $pupilsightDepartmentID = $values['pupilsightDepartmentID'];

                        $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/units_editProcess.php?pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseID&pupilsightUnitID=$pupilsightUnitID&address=".$_GET['q']);
                        $form->setFactory(PlannerFormFactory::create($pdo));

                        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                        //OVERVIEW
                        $form->addRow()->addHeading(__('Overview'));

                        $row = $form->addRow();
                            $row->addLabel('yearName', __('School Year'));
                            $row->addTextField('yearName')->readonly()->setValue($yearName)->required();

                        $row = $form->addRow();
                            $row->addLabel('courseName', __('Course'));
                            $row->addTextField('courseName')->readonly()->setValue($courseName)->required();

                        $row = $form->addRow();
                            $row->addLabel('name', __('Name'));
                            $row->addTextField('name')->required()->maxLength(40);

                        $row = $form->addRow();
                            $row->addLabel('description', __('Description'));
                            $row->addTextArea('description')->setRows(5)->required();

                        $row = $form->addRow();
                            $row->addLabel('active', __('Active'));
                            $row->addYesNo('active')->required();

                        $row = $form->addRow();
                            $row->addLabel('map', __('Include In Curriculum Map'));
                            $row->addYesNo('map')->required();

                        $row = $form->addRow();
                            $row->addLabel('ordering', __('Ordering'))->description(__('Units are arranged form lowest to highest ordering value, then alphabetically.'));
                            $row->addNumber('ordering')->maxLength(4)->decimalPlaces(0)->setValue("0")->required();

                        $tags = getTagList($connection2);
                        $tagsOutput = array();
                        foreach ($tags as $tag) {
                            if ($tag[0] > 0) {
                                $tagsOutput[$tag[1]] = $tag[1] . " (".$tag[0].")";
                            }
                        }
                        $row = $form->addRow()->addClass('tags');
                            $column = $row->addColumn();
                            $column->addLabel('tags', __('Concepts & Keywords'))->description(__('Use tags to describe unit and its contents.'));
                            $column->addFinder('tags')
                                ->fromArray($tagsOutput)
                                ->setParameter('hintText', __('Type a tag...'))
                                ->setParameter('allowFreeTagging', true);

                        //CLASSES
                        $form->addRow()->addHeading(__('Classes'))->append(__('Select classes which will have access to this unit.'));

                        if ($_SESSION[$guid]['pupilsightSchoolYearIDCurrent'] == $pupilsightSchoolYearID && $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'] == $_SESSION[$guid]['pupilsightSchoolYearID']) {

                            $dataClass = array('pupilsightUnitID' => $pupilsightUnitID);
                            $sqlClass = "SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourseClass.nameShort, running, pupilsightUnitClassID, pupilsightUnitID
                                        FROM pupilsightCourseClass
                                        LEFT JOIN pupilsightUnitClass ON (pupilsightUnitClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID AND pupilsightUnitID=:pupilsightUnitID)
                                        WHERE pupilsightCourseID=$pupilsightCourseID
                                        ORDER BY name";
                            $resultClass = $pdo->select($sqlClass, $dataClass)->toDataSet();

                            if (count($resultClass) == 0) {
                                $form->addRow()->addAlert(__('There are no records to display.'), 'error');
                            } else {
                                $classCount = 0;

                                // Add the firstLesson date to each class, and
                                $resultClass->transform(function (&$class) use ($pdo, &$classCount, &$form) {
                                    if ($class['running'] == 'Y') {
                                        $dataDate = array('pupilsightCourseClassID' => $class['pupilsightCourseClassID'], 'pupilsightUnitID' => $class['pupilsightUnitID']);
                                        $sqlDate = "SELECT date FROM pupilsightPlannerEntry WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightUnitID=:pupilsightUnitID ORDER BY date, timeStart";
                                        $class['firstLesson'] = $pdo->selectOne($sqlDate, $dataDate);
                                    }

                                    $form->addHiddenValue("pupilsightCourseClassID{$classCount}", $class['pupilsightCourseClassID']);
                                    $class['count'] = $classCount;
                                    $classCount++;
                                });

                                // Nested DataTable for course classes
                                $table = $form->addRow()->addDataTable('classes')->withData($resultClass);

                                $table->addColumn('class', __('Class'))
                                    ->width('20%')
                                    ->format(Format::using('courseClassName', [$courseNameShort, 'nameShort']));

                                $table->addColumn('running', __('Running'))
                                    ->description(__('Is class studying this unit?'))
                                    ->width('25%')
                                    ->format(function ($class) use (&$form) {
                                        return $form->getFactory()
                                            ->createYesNo('running'.$class['count'])
                                            ->setClass('w-32 float-none')
                                            ->selected($class['running'] ?? 'N')
                                            ->getOutput();
                                    });

                                $table->addColumn('firstLesson', __('First Lesson'))
                                    ->description($_SESSION[$guid]['i18n']['dateFormat'] ?? 'dd/mm/yyyy')
                                    ->width('15%')
                                    ->format(function ($class) {
                                        return !empty($class['firstLesson']) ? Format::date($class['firstLesson']) : '';
                                    });

                                $table->addActionColumn()
                                    ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
                                    ->addParam('pupilsightUnitID', $pupilsightUnitID)
                                    ->addParam('pupilsightCourseID', $pupilsightCourseID)
                                    ->addParam('pupilsightCourseClassID')
                                    ->addParam('pupilsightUnitClassID')
                                    ->format(function ($class, $actions) {
                                        if ($class['running'] == 'N' || empty($class['running'])) return;

                                        $actions->addAction('edit', __('Edit Unit'))
                                                ->setURL(empty($class['firstLesson'])
                                                    ? '/modules/Planner/units_edit_deploy.php'
                                                    : '/modules/Planner/units_edit_working.php');

                                        $actions->addAction('view', __('View Planner'))
                                                ->addParam('viewBy', 'class')
                                                ->setIcon('planner')
                                                ->setURL('/modules/Planner/planner.php');

                                        $actions->addAction('copyBack', __('Copy Back'))
                                                ->setIcon('copyback')
                                                ->setURL('/modules/Planner/units_edit_copyBack.php');

                                        $actions->addAction('copyForward', __('Copy Forward'))
                                                ->setIcon('copyforward')
                                                ->setURL('/modules/Planner/units_edit_copyForward.php');

                                        $actions->addAction('smartBlockify', __('Smart Blockify'))
                                                ->setIcon('run')
                                                ->setURL('/modules/Planner/units_edit_smartBlockify.php');
                                    });
                            }
                        }
                        else {
                            $row = $form->addRow();
                                $row->addAlert(__('You are currently not logged into the current year and/or are looking at units in another year, and so you cannot access your classes. Please log back into the current school year, and look at units in the current year.'), 'warning');
                        }

                        $form->addHiddenValue('classCount', $classCount);

                        //UNIT OUTLINE
                        $form->addRow()->addHeading(__('Unit Outline'));

                        $unitOutline = getSettingByScope($connection2, 'Planner', 'unitOutlineTemplate');
                        $shareUnitOutline = getSettingByScope($connection2, 'Planner', 'shareUnitOutline');
                        if ($shareUnitOutline == 'Y') {
                            $content = __('The contents of both the Unit Outline field and the Downloadable Unit Outline are available to all users who can access this unit via the Lesson Planner (possibly include parents and students).');
                        }
                        else {
                            $content = __('The contents of the Unit Outline field are viewable only to those with full access to the Planner (usually teachers and administrators, but not students and parents), whereas the downloadable version (below) is available to more users (usually parents).');
                        }
                        $row = $form->addRow();
                            $column = $row->addColumn();
                            $column->addAlert($content, 'message');
                            $column->addEditor('details', $guid)->setRows(30)->showMedia()->setValue($unitOutline);

                        try {
                            $dataExt = array();
                            $sqlExt = 'SELECT * FROM pupilsightFileExtension';
                            $resultExt = $connection2->prepare($sqlExt);
                            $resultExt->execute($dataExt);
                        } catch (PDOException $e) {}
                        $ext = '';
                        while ($rowExt = $resultExt->fetch()) {
                            $ext .= "'.".$rowExt['extension']."',";
                        }
                        $row = $form->addRow();
                            $row->addLabel('file', __('Downloadable Unit Outline'))->description("Available to most users.");
                            $row->addFileUpload('file')
                                ->accepts(substr($ext, 0, -2))
                                ->setAttachment('attachment', $_SESSION[$guid]['absoluteURL'], $values['attachment']);

                        //OUTCOMES
                        $form->addRow()->addHeading(__('Outcomes'))->append(__('Link this unit to outcomes (defined in the Manage Outcomes section of the Planner), and track which outcomes are being met in which units, classes and courses.'));
                        $allowOutcomeEditing = getSettingByScope($connection2, 'Planner', 'allowOutcomeEditing');
                        $row = $form->addRow();
                            $customBlocks = $row->addPlannerOutcomeBlocks('outcome', $pupilsight->session, $pupilsightYearGroupIDList, $pupilsightDepartmentID, $allowOutcomeEditing);

                        $dataBlocks = array('pupilsightUnitID' => $pupilsightUnitID);
                        $sqlBlocks = "SELECT pupilsightUnitOutcome.*, scope, name, category FROM pupilsightUnitOutcome JOIN pupilsightOutcome ON (pupilsightUnitOutcome.pupilsightOutcomeID=pupilsightOutcome.pupilsightOutcomeID) WHERE pupilsightUnitID=:pupilsightUnitID AND active='Y' ORDER BY sequenceNumber";
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

                        //SMART BLOCKS
                        $form->addRow()->addHeading(__('Smart Blocks'))->append(__('Smart Blocks aid unit planning by giving teachers help in creating and maintaining new units, splitting material into smaller units which can be deployed to lesson plans. As well as predefined fields to fill, Smart Units provide a visual view of the content blocks that make up a unit. Blocks may be any kind of content, such as discussion, assessments, group work, outcome etc.'));
                        $blockCreator = $form->getFactory()
                            ->createButton('addNewFee')
                            ->setValue(__('Click to create a new block'))
                            ->addClass('addBlock');

                        $row = $form->addRow();
                            $customBlocks = $row->addPlannerSmartBlocks('smart', $pupilsight->session, $guid)
                                ->addToolInput($blockCreator);

                        $dataBlocks = array('pupilsightUnitID' => $pupilsightUnitID);
                        $sqlBlocks = 'SELECT * FROM pupilsightUnitBlock WHERE pupilsightUnitID=:pupilsightUnitID ORDER BY sequenceNumber';
                        $resultBlocks = $pdo->select($sqlBlocks, $dataBlocks);

                        while ($rowBlocks = $resultBlocks->fetch()) {
                            $smart = array(
                                'title' => $rowBlocks['title'],
                                'type' => $rowBlocks['type'],
                                'length' => $rowBlocks['length'],
                                'contents' => $rowBlocks['contents'],
                                'teachersNotes' => $rowBlocks['teachersNotes'],
                                'pupilsightUnitBlockID' => $rowBlocks['pupilsightUnitBlockID']
                            );
                            $customBlocks->addBlock($rowBlocks['pupilsightUnitBlockID'], $smart);
                        }

                        //MISCELLANEOUS SETTINGS
                        $form->addRow()->addHeading(__('Miscellaneous Settings'));

                        $licences = array(
                            "Copyright" => __("Copyright"),
                            "Creative Commons BY" => __("Creative Commons BY"),
                            "Creative Commons BY-SA" => __("Creative Commons BY-SA"),
                            "Creative Commons BY-SA-NC" => __("Creative Commons BY-SA-NC"),
                            "Public Domain" => __("Public Domain")
                        );
                        $row = $form->addRow();
                            $row->addLabel('license', 'License')->description(__('Under what conditions can this work be reused?'));
                            $row->addSelect('license')->fromArray($licences)->placeholder();

                        $makeUnitsPublic = getSettingByScope($connection2, 'Planner', 'makeUnitsPublic');
                        if ($makeUnitsPublic == 'Y') {
                            $row = $form->addRow();
                                $row->addLabel('sharedPublic', __('Shared Publically'))->description(__('Share this unit via the public listing of units? Useful for building MOOCS.'));
                                $row->addYesNo('sharedPublic')->required();
                        }

                        $row = $form->addRow();
                            $row->addSubmit();

                        $form->loadAllValuesFrom($values);

                        echo $form->getOutput();
                    }
                }
            }
        }
    }
    //Print sidebar
    $_SESSION[$guid]['sidebarExtra'] = sidebarExtraUnits($guid, $connection2, $pupilsightCourseID, $pupilsightSchoolYearID);
}
?>
