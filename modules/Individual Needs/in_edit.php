<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Individual Needs/in_edit.php') == false) {
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
        $pupilsightPersonID = $_GET['pupilsightPersonID'];

        if ($highestAction == 'Individual Needs Records_view') {
            $page->breadcrumbs
                ->add(__('View Student Records'), 'in_view.php')
                ->add(__('View Individual Needs Record'));
        } elseif ($highestAction == 'Individual Needs Records_viewContribute') {
            $page->breadcrumbs
                ->add(__('View Student Records'), 'in_view.php')
                ->add(__('View & Contribute To Individual Needs Record'));
        } elseif ($highestAction == 'Individual Needs Records_viewEdit') {
            $page->breadcrumbs
                ->add(__('View Student Records'), 'in_view.php')
                ->add(__('Edit Individual Needs Record'));
        }

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, array('success0' => 'Your request was completed successfully.'));
        }

        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID);
            $sql = "SELECT pupilsightPerson.pupilsightPersonID, pupilsightStudentEnrolmentID, surname, preferredName, pupilsightYearGroup.name AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup, dateStart, dateEnd, image_240 FROM pupilsightPerson, pupilsightStudentEnrolment, pupilsightYearGroup, pupilsightRollGroup WHERE (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) AND (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) AND (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID AND pupilsightPerson.status='Full' ORDER BY surname, preferredName";
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
            $student = $result->fetch();

            $search = isset($_GET['search'])? $_GET['search'] : null;
            $source = isset($_GET['source'])? $_GET['source'] : null;
            $pupilsightINDescriptorID = isset($_GET['pupilsightINDescriptorID'])? $_GET['pupilsightINDescriptorID'] : null;
            $pupilsightAlertLevelID = isset($_GET['pupilsightAlertLevelID'])? $_GET['pupilsightAlertLevelID'] : null;
            $pupilsightRollGroupID = isset($_GET['pupilsightRollGroupID'])? $_GET['pupilsightRollGroupID'] : null;
            $pupilsightYearGroupID = isset($_GET['pupilsightYearGroupID'])? $_GET['pupilsightYearGroupID'] : null;

            if ($search != '' and $source == '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Individual Needs/in_view.php&search='.$search."'>".__('Back to Search Results').'</a>';
                echo '</div>';
            } elseif (($pupilsightINDescriptorID != '' or $pupilsightAlertLevelID != '' or $pupilsightRollGroupID != '' or $pupilsightYearGroupID != '') and $source == 'summary') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Individual Needs/in_summary.php&pupilsightINDescriptorID='.$pupilsightINDescriptorID.'&pupilsightAlertLevelID='.$pupilsightAlertLevelID.'&=pupilsightRollGroupID'.$pupilsightRollGroupID.'&pupilsightYearGroupID='.$pupilsightYearGroupID."'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }

            // Grab educational assistant data
            $data = array('pupilsightPersonIDStudent' => $pupilsightPersonID);
            $sql = "SELECT pupilsightPersonIDAssistant, preferredName, surname, comment FROM pupilsightINAssistant JOIN pupilsightPerson ON (pupilsightINAssistant.pupilsightPersonIDAssistant=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPersonIDStudent=:pupilsightPersonIDStudent AND pupilsightPerson.status='Full' ORDER BY surname, preferredName";
            $result = $pdo->executeQuery($data, $sql);
            $educationalAssistants = ($result->rowCount() > 0)? $result->fetchAll() : array();

            // Grab IEP data
            $data = array('pupilsightPersonID' => $pupilsightPersonID);
            $sql = "SELECT * FROM pupilsightIN WHERE pupilsightPersonID=:pupilsightPersonID";
            $result = $pdo->executeQuery($data, $sql);
            $IEP = ($result->rowCount() > 0)? $result->fetch() : array();

            // Grab archived data
            $data = array('pupilsightPersonID' => $pupilsightPersonID);
            $sql = "SELECT pupilsightINArchiveID as groupBy, pupilsightINArchive.* FROM pupilsightINArchive WHERE pupilsightPersonID=:pupilsightPersonID ORDER BY archiveTimestamp DESC";
            $result = $pdo->executeQuery($data, $sql);
            $archivedIEPs = ($result->rowCount() > 0)? $result->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE) : array();

            $pupilsightINArchiveID = !empty($_POST['pupilsightINArchiveID'])? $_POST['pupilsightINArchiveID'] : '';
            $archivedIEP = array('strategies' => '', 'targets' => '', 'notes' => '', 'descriptors' => '');

            if (!empty($archivedIEPs)) {
                // Load current selected archive if exists
                if (isset($archivedIEPs[$pupilsightINArchiveID])) {
                    $archivedIEP = $archivedIEPs[$pupilsightINArchiveID];
                }

                $archiveOptions = array_map(function($item) use ($guid) {
                    return $item['archiveTitle'].' ('.dateConvertBack($guid, substr($item['archiveTimestamp'], 0, 10)).')';
                }, $archivedIEPs);

                $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/in_edit.php&pupilsightPersonID=$pupilsightPersonID&search=$search&source=$source&pupilsightINDescriptorID=$pupilsightINDescriptorID&pupilsightAlertLevelID=$pupilsightAlertLevelID&pupilsightRollGroupID=$pupilsightRollGroupID&pupilsightYearGroupID=$pupilsightYearGroupID");
                $form->setClass('blank fullWidth');
                $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                $col = $form->addRow()->addColumn()->addClass('flex justify-end items-center');
                    $col->addLabel('pupilsightINArchiveID', __('Archived Plans'))->addClass('mr-1');
                    $col->addSelect('pupilsightINArchiveID')
                        ->fromArray(array('' => __('Current Plan')))
                        ->fromArray($archiveOptions)
                        ->setClass('mediumWidth')
                        ->selected($pupilsightINArchiveID);
                    $col->addSubmit(__('Go'));

                echo "<div class='linkTop'>";
                echo $form->getOutput();
                echo '</div>';
            }
            
            // DISPLAY STUDENT DATA
            echo "<table class='table'>";
            echo '<tr>';
            echo "<td style='width: 34%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Name').'</span><br/>';
            echo Format::name('', $student['preferredName'], $student['surname'], 'Student');
            echo '</td>';
            echo "<td style='width: 33%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Year Group').'</span><br/>';
            echo '<i>'.__($student['yearGroup']).'</i>';
            echo '</td>';
            echo "<td style='width: 34%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Roll Group').'</span><br/>';
            echo '<i>'.$student['rollGroup'].'</i>';
            echo '</td>';
            echo '</tr>';
            echo '</table>';

            $form = Form::create('individualNeeds', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/in_editProcess.php?pupilsightPersonID=$pupilsightPersonID&search=$search&source=$source&pupilsightINDescriptorID=$pupilsightINDescriptorID&pupilsightAlertLevelID=$pupilsightAlertLevelID&pupilsightRollGroupID=$pupilsightRollGroupID&pupilsightYearGroupID=$pupilsightYearGroupID");

            $form->setFactory(DatabaseFormFactory::create($pdo));
            $form->setClass('w-full blank');
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);

            // IN STATUS TABLE - TODO: replace this with OO
            $form->addRow()->addHeading(__('Individual Needs Status'));

            $statusTableDisabled = (!empty($pupilsightINArchiveID) || $highestAction == 'Individual Needs Records_view' || $highestAction == 'Individual Needs Records_viewContribute')? 'disabled' : '';
            $statusTableDescriptors = !empty($pupilsightINArchiveID)? $archivedIEP['descriptors'] : '';
            $statusTable = printINStatusTable($connection2, $guid, $pupilsightPersonID, $statusTableDisabled, $statusTableDescriptors);

            if (!empty($statusTable)) {
                $form->addRow()->addContent($statusTable);
            } else {
                $form->addRow()->addAlert(__('Your request failed due to a database error.'), 'error');
            }
            
            // LIST EDUCATIONAL ASSISTANTS
            if (empty($pupilsightINArchiveID)) {
                $form->addRow()->addHeading(__('Educational Assistants'));
                
                if (!empty($educationalAssistants)) {
                    $table = $form->addRow()->addTable()->addClass('smallIntBorder fullWidth colorOddEven');
                    $header = $table->addHeaderRow();
                        $header->addContent(__('Name'));
                        $header->addContent(__('Comment'));
                        if ($highestAction == 'Individual Needs Records_viewEdit') {
                            $header->addContent(__('Action'));
                        }

                    foreach ($educationalAssistants as $ea) {
                        $row = $table->addRow();
                            $row->addContent(Format::name('', $ea['preferredName'], $ea['surname'], 'Staff', true, true));
                            $row->addContent($ea['comment']);

                        if ($highestAction == 'Individual Needs Records_viewEdit') {
                            $row->addWebLink('<i title="'.__('Delete').'" class="mdi mdi-trash-can-outline mdi-24px"></i></a>')
                                ->setURL($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/in_edit_assistant_deleteProcess.php')
                                ->addParam('address', $_GET['q'])
                                ->addParam('pupilsightPersonIDAssistant', $ea['pupilsightPersonIDAssistant'])
                                ->addParam('pupilsightPersonIDStudent', $pupilsightPersonID)
                                ->addConfirmation(__('Are you sure you wish to delete this record?'));
                        }
                    }
                } else {
                    $form->addRow()->addAlert(__('There are no records to display.'), 'warning');
                }
            }

            // ADD EDUCATIONAL ASSISTANTS
            if (empty($pupilsightINArchiveID) && $highestAction == 'Individual Needs Records_viewEdit') {
                $form->addRow()->addHeading(__('Add New Assistants'));

                $table = $form->addRow()->addTable()->setClass('smallIntBorder fullWidth');

                $row = $table->addRow();
                    $row->addLabel('staff', __('Staff'))->addClass('w-48');
                    $row->addSelectStaff('staff')->selectMultiple()->addClass('w-full sm:max-w-xs');

                $row = $table->addRow();
                    $row->addLabel('comment', __('Comment'));
                    $row->addTextArea('comment')->setRows(4)->addClass('w-full sm:max-w-xs');
            }

            // DISPLAY AND EDIT IEP
            $form->addRow()->addHeading(__('Individual Education Plan'));

            $table = $form->addRow()->addTable()->setClass('smallIntBorder fullWidth');

            if (!empty($pupilsightINArchiveID)) {
                // ARCHIVED IEP
                $col = $table->addRow()->addColumn();
                    $col->addContent(__('Targets'))->wrap('<strong style="font-size: 135%;">', '</strong>');
                    $col->addContent($archivedIEP['targets'])->wrap('<p>', '</p>');

                $col = $table->addRow()->addColumn();
                    $col->addContent(__('Teaching Strategies'))->wrap('<strong style="font-size: 135%;">', '</strong>');
                    $col->addContent($archivedIEP['strategies'])->wrap('<p>', '</p>');

                $col = $table->addRow()->addColumn();
                    $col->addContent(__('Notes & Review'))->wrap('<strong style="font-size: 135%;">', '</strong>');
                    $col->addContent($archivedIEP['notes'])->wrap('<p>', '</p>');
            } else {
                if (empty($IEP)) { // New record, get templates if they exist
                    $IEP['targets'] = getSettingByScope($connection2, 'Individual Needs', 'targetsTemplate');
                    $IEP['strategies'] = getSettingByScope($connection2, 'Individual Needs', 'teachingStrategiesTemplate');
                    $IEP['notes'] = getSettingByScope($connection2, 'Individual Needs', 'notesReviewTemplate');
                }

                // CURRENT IEP
                $col = $table->addRow()->addColumn();
                    $col->addContent(__('Targets'))->wrap('<strong style="font-size: 135%;">', '</strong>');
                    if ($highestAction == 'Individual Needs Records_viewEdit') {
                        $col->addEditor('targets', $guid)->showMedia(true)->setRows(20)->setValue($IEP['targets']);
                    } else {
                        $col->addContent($IEP['targets'])->wrap('<p>', '</p>');
                    }

                $col = $table->addRow()->addColumn();
                    $col->addContent(__('Teaching Strategies'))->wrap('<strong style="font-size: 135%;">', '</strong>');
                    if ($highestAction == 'Individual Needs Records_viewEdit' or $highestAction == 'Individual Needs Records_viewContribute') {
                        $col->addEditor('strategies', $guid)->showMedia(true)->setRows(20)->setValue($IEP['strategies']);
                    } else {
                        $col->addContent($IEP['strategies'])->wrap('<p>', '</p>');
                    }

                $col = $table->addRow()->addColumn();
                    $col->addContent(__('Notes & Review'))->wrap('<strong style="font-size: 135%;">', '</strong>');
                    if ($highestAction == 'Individual Needs Records_viewEdit') {
                        $col->addEditor('notes', $guid)->showMedia(true)->setRows(20)->setValue($IEP['notes']);
                    } else {
                        $col->addContent($IEP['notes'])->wrap('<p>', '</p>');
                    }
            }

            if (empty($pupilsightINArchiveID) && ($highestAction == 'Individual Needs Records_viewEdit' || $highestAction == 'Individual Needs Records_viewContribute')) {
                $table->addRow()->addSubmit();
            }

            echo $form->getOutput();
        }
    }
    //Set sidebar
    $_SESSION[$guid]['sidebarExtra'] = getUserPhoto($guid, $student['image_240'] ?? '', 240);
}
