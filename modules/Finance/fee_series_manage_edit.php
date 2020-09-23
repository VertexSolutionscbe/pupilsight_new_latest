<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_series_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Series'), 'fee_series_manage.php')
        ->add(__('Edit Fee Series'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $id = $_GET['id'];
    if ($id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM fn_fee_series WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();

            echo '<h2>';
            echo __('Edit Fee Series');
            echo '</h2>';

            $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
            $resulta = $connection2->query($sqla);
            $academic = $resulta->fetchAll();

            $academicData = array();
            foreach ($academic as $dt) {
                $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
            }

            $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_series_manage_editProcess.php?id='.$id);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addLabel('pupilsightSchoolYearID', __('Academic Year'));
                $row->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($values['pupilsightSchoolYearID'])->required();

            $row = $form->addRow();
                $row->addLabel('series_name', __('Fee Series Name'))->description(__('Must be unique.'));
                $row->addTextField('series_name')->required()->setValue($values['series_name']);

                $row = $form->addRow();
                    $row->addLabel('description', __('Description'));
                    $row->addTextField('description')->setValue($values['description']);

                $row = $form->addRow();
                    $row->addLabel('format', __('Format'));
                    $row->addTextField('format')->required()->setValue($values['format']);

               
                $row = $form->addRow();
                    $row->addLabel('start_number', __('Start No'));
                    $row->addTextField('start_number')->setValue($values['start_number']);

                $row = $form->addRow();
                    $row->addLabel('no_of_digit', __('No of Digit'));
                    $row->addTextField('no_of_digit')->setValue($values['no_of_digit']);

                $row = $form->addRow();
                    $row->addLabel('start_char', __('Enter Character'));
                    $row->addTextField('start_char')->setValue($values['start_char']);  
            
           
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
