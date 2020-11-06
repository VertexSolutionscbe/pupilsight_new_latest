<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/rollGroup_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $pupilsightRollGroupID = $_GET['pupilsightRollGroupID'] ?? '';

    $page->breadcrumbs
        ->add(__('Manage Roll Groups'), 'rollGroup_manage.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Add Roll Group'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/School Admin/rollGroup_manage_edit.php&pupilsightRollGroupID='.$_GET['editID'].'&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    if ($pupilsightSchoolYearID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT name as schoolYearName FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
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

            $form = Form::create('rollGroupAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/rollGroup_manage_addProcess.php');
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

            $row = $form->addRow();
                $row->addLabel('schoolYearName', __('School Year'));
                $row->addTextField('schoolYearName')->readonly()->setValue($values['schoolYearName']);

            $row = $form->addRow();
                $row->addLabel('name', __('Name'))->description(__('Needs to be unique in school year.'));
                $row->addTextField('name')->required();

            $row = $form->addRow();
                $row->addLabel('nameShort', __('Short Name'))->description(__('Needs to be unique in school year.'));
                $row->addTextField('nameShort')->required();

            $row = $form->addRow()->addClass('hiddencol');
                $row->addLabel('tutors', __('Tutors'))->description(__('Up to 3 per roll group. The first-listed will be marked as "Main Tutor".'));
                $column = $row->addColumn()->addClass('stacked');
                $column->addSelectStaff('pupilsightPersonIDTutor')->placeholder()->photo(false);
                $column->addSelectStaff('pupilsightPersonIDTutor2')->placeholder()->photo(false);
                $column->addSelectStaff('pupilsightPersonIDTutor3')->placeholder()->photo(false);

            $row = $form->addRow()->addClass('hiddencol');
                $row->addLabel('EAs', __('Educational Assistant'))->description(__('Up to 3 per roll group.'));
                $column = $row->addColumn()->addClass('stacked');
                $column->addSelectStaff('pupilsightPersonIDEA')->placeholder()->photo(false);
                $column->addSelectStaff('pupilsightPersonIDEA2')->placeholder()->photo(false);
                $column->addSelectStaff('pupilsightPersonIDEA3')->placeholder()->photo(false);

            $row = $form->addRow()->addClass('hiddencol');
                $row->addLabel('pupilsightSpaceID', __('Location'));
                $row->addSelectSpace('pupilsightSpaceID');

            $nextYear = getNextSchoolYearID($pupilsightSchoolYearID, $connection2);
            $row = $form->addRow()->addClass('hiddencol');
                $row->addLabel('pupilsightRollGroupIDNext', __('Next Roll Group'))->description(__('Sets student progression on rollover.'));
                if (empty($nextYear)) {
                    $row->addAlert(__('The next school year cannot be determined, so this value cannot be set.'));
                } else {
                    $row->addSelectRollGroup('pupilsightRollGroupIDNext', $nextYear);
                }

            $row = $form->addRow()->addClass('hiddencol');
                $row->addLabel('attendance', __('Track Attendance?'))->description(__('Should this class allow attendance to be taken?'));
                $row->addYesNo('attendance');

            $row = $form->addRow()->addClass('hiddencol');
                $row->addLabel('website', __('Website'))->description(__('Include http://'));
                $row->addURL('website')->maxLength(255);

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}

