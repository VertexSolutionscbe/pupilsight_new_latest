<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/yearGroup_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Class'), 'yearGroup_manage.php')
        ->add(__('Edit Class'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightYearGroupID = $_GET['pupilsightYearGroupID'];
    if ($pupilsightYearGroupID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightYearGroupID' => $pupilsightYearGroupID);
            $sql = 'SELECT * FROM pupilsightYearGroup WHERE pupilsightYearGroupID=:pupilsightYearGroupID';
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
            
            $values = $result->fetch();

            $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
            $resulta = $connection2->query($sqla);
            $academic = $resulta->fetchAll();

            $academicData = array();
            foreach ($academic as $dt) {
                $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
            }

            $form = Form::create('yearGroup', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/yearGroup_manage_editProcess.php?pupilsightYearGroupID='.$pupilsightYearGroupID);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addLabel('pupilsightSchoolYearID', __('Academic Year'));
                $row->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($values['pupilsightSchoolYearID'])->required()->placeholder()->readonly();

            $row = $form->addRow();
                $row->addLabel('name', __('Name'))->description(__('Must be unique.'));
                $row->addTextField('name')->required()->setValue($values['name']);

            $row = $form->addRow();
                $row->addLabel('nameShort', __('Short Name'))->description(__('Must be unique.'));
                $row->addTextField('nameShort')->required()->setValue($values['nameShort']);

            $row = $form->addRow();
                $row->addLabel('sequenceNumber', __('Sequence Number'))->description(__('Must be unique. Controls chronological ordering.'));
                // $row->addSequenceNumber('sequenceNumber', 'pupilsightYearGroup', $values['sequenceNumber'])
                //     ->required()
                //     ->maxLength(3)
                //     ->setValue($values['sequenceNumber']);

                $row->addTextField('sequenceNumber')->required()->maxLength(3)->setValue($values['sequenceNumber']);
                
            
            $row = $form->addRow()->setClass('hiddencol');
                $row->addLabel('pupilsightPersonIDHOY', __('Head of Year'));
                $row->addSelectStaff('pupilsightPersonIDHOY')->placeholder()->selected($values['pupilsightPersonIDHOY']);

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
?>

