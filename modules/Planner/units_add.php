<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Module\Planner\Forms\PlannerFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

// common variables
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
$pupilsightCourseID = $_GET['pupilsightCourseID'] ?? '';

$page->breadcrumbs
    ->add(__('Unit Planner'), 'units.php', [
        'pupilsightSchoolYearID' => $pupilsightSchoolYearID,
        'pupilsightCourseID' => $pupilsightCourseID,
    ])
    ->add(__('Add Unit'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/units_add.php') == false) {
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
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        if ($pupilsightSchoolYearID == '') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                $sql = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The specified record does not exist.');
                echo '</div>';
            } else {
                $values = $result->fetch();

                if ($pupilsightCourseID == '') {
                    echo "<div class='alert alert-danger'>";
                    echo __('You have not specified one or more required parameters.');
                    echo '</div>';
                } else {
                    try {
                        if ($highestAction == 'Unit Planner_all') {
                            $dataCourse = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID);
                            $sqlCourse = 'SELECT * FROM pupilsightCourse WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseID=:pupilsightCourseID';
                        } elseif ($highestAction == 'Unit Planner_learningAreas') {
                            $dataCourse = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                            $sqlCourse = "SELECT pupilsightCourseID, pupilsightCourse.name, pupilsightCourse.nameShort, pupilsightCourse.pupilsightYearGroupIDList, pupilsightCourse.pupilsightDepartmentID FROM pupilsightCourse JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID AND (role='Coordinator' OR role='Assistant Coordinator' OR role='Teacher (Curriculum)') AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseID=:pupilsightCourseID ORDER BY pupilsightCourse.nameShort";
                        }
                        $resultCourse = $connection2->prepare($sqlCourse);
                        $resultCourse->execute($dataCourse);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    if ($resultCourse->rowCount() != 1) {
                        echo "<div class='alert alert-danger'>";
                        echo 'The selected record does not exist, or you do not have access to it.';
                        echo '</div>';
                    } else {
                        $rowCourse = $resultCourse->fetch();
                        $pupilsightYearGroupIDList = $rowCourse['pupilsightYearGroupIDList'];
                        $pupilsightDepartmentID = $rowCourse['pupilsightDepartmentID'];

                        $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/units_addProcess.php?pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseID&address=".$_GET['q']);
                        $form->setFactory(PlannerFormFactory::create($pdo));

                        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                        //OVERVIEW
                        $form->addRow()->addHeading(__('Overview'));

                        $row = $form->addRow();
                            $row->addLabel('yearName', __('School Year'));
                            $row->addTextField('yearName')->readonly()->setValue($values['name'])->required();

                        $row = $form->addRow();
                            $row->addLabel('courseName', __('Course'));
                            $row->addTextField('courseName')->readonly()->setValue($rowCourse['nameShort'])->required();

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

                        try {
                            $dataClass = array();
                            $sqlClass = "SELECT * FROM pupilsightCourseClass WHERE pupilsightCourseID=$pupilsightCourseID ORDER BY name";
                            $resultClass = $connection2->prepare($sqlClass);
                            $resultClass->execute($dataClass);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        if ($resultClass->rowCount() < 1) {
                            $form->addRow()->addAlert(__('There are no records to display.'), 'error');
                        } else {
                            $table = $form->addRow()->addTable()->addClass('colorOddEven');

                            $header = $table->addHeaderRow();
                            $header->addContent(__('Class'));
                            $header->addContent(__('Running'))->append("<br/><small>".__('Is class studying this unit?')."</small>");

                            $classCount = 0;
                            while ($rowClass = $resultClass->fetch()) {
                                $row = $table->addRow();
                                    $row->addContent($rowCourse['nameShort'].'.'.$rowClass['nameShort']);
                                    $row->addYesNo("running$classCount")->selected("N");
                                $form->addHiddenValue("pupilsightCourseClassID$classCount", $rowClass['pupilsightCourseClassID']);
                                ++$classCount;
                            }

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
                                ->accepts(substr($ext, 0, -2));

                        //ADVANCED
                        $form->addRow()->addHeading(__('Advanced Options'));

                        $form->toggleVisibilityByClass('advanced')->onCheckbox('advanced')->when('Y');
                        $row = $form->addRow();
                            $row->addCheckbox('advanced')->setValue('Y')->description('Show Advanced Options');

                        //OUTCOMES
                        $form->addRow()->addHeading(__('Outcomes'))->append(__('Link this unit to outcomes (defined in the Manage Outcomes section of the Planner), and track which outcomes are being met in which units, classes and courses.'))->addClass('advanced');
                        $allowOutcomeEditing = getSettingByScope($connection2, 'Planner', 'allowOutcomeEditing');
                        $row = $form->addRow()->addClass('advanced');
                            $row->addPlannerOutcomeBlocks('outcome', $pupilsight->session, $pupilsightYearGroupIDList, $pupilsightDepartmentID, $allowOutcomeEditing);

                        //SMART BLOCKS
                        $form->addRow()->addHeading(__('Smart Blocks'))->append(__('Smart Blocks aid unit planning by giving teachers help in creating and maintaining new units, splitting material into smaller units which can be deployed to lesson plans. As well as predefined fields to fill, Smart Units provide a visual view of the content blocks that make up a unit. Blocks may be any kind of content, such as discussion, assessments, group work, outcome etc.'))->addClass('advanced');
                        $blockCreator = $form->getFactory()
                            ->createButton('addNewFee')
                            ->setValue(__('Click to create a new block'))
                            ->addClass('advanced addBlock');

                        $row = $form->addRow()->addClass('advanced');
                            $customBlocks = $row->addPlannerSmartBlocks('smart', $pupilsight->session, $guid)
                                ->addToolInput($blockCreator);

                        for ($i=0 ; $i<5 ; $i++) {
                            $customBlocks->addBlock("block$i");
                        }

                        $form->addHiddenValue('blockCount', "5");

                        //MISCELLANEOUS SETTINGS
                        $form->addRow()->addHeading(__('Miscellaneous Settings'))->addClass('advanced');

                        $licences = array(
                            "Copyright" => __("Copyright"),
                            "Creative Commons BY" => __("Creative Commons BY"),
                            "Creative Commons BY-SA" => __("Creative Commons BY-SA"),
                            "Creative Commons BY-SA-NC" => __("Creative Commons BY-SA-NC"),
                            "Public Domain" => __("Public Domain")
                        );
                        $row = $form->addRow()->addClass('advanced');
                            $row->addLabel('license', 'License')->description(__('Under what conditions can this work be reused?'));
                            $row->addSelect('license')->fromArray($licences)->placeholder();

                        $makeUnitsPublic = getSettingByScope($connection2, 'Planner', 'makeUnitsPublic');
                        if ($makeUnitsPublic == 'Y') {
                            $row = $form->addRow()->addClass('advanced');
                                $row->addLabel('sharedPublic', __('Shared Publically'))->description(__('Share this unit via the public listing of units? Useful for building MOOCS.'));
                                $row->addYesNo('sharedPublic')->required();
                        }


                        $row = $form->addRow();
                            $row->addSubmit();

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
