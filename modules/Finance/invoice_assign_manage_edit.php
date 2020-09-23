<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_assign_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Invoice Assign'), 'invoice_assign_manage.php')
        ->add(__('Edit Invoice Assign'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $invoice_id = $_GET['invoice_id'];
    $pupilsightProgramID = $_GET['pupilsightProgramID'];
    if ($invoice_id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('fn_fee_invoice_id' => $invoice_id, 'pupilsightProgramID'=>$pupilsightProgramID);
            $sql = 'SELECT id, fn_fee_invoice_id, pupilsightProgramID, GROUP_CONCAT(DISTINCT pupilsightYearGroupID SEPARATOR ", ") as class FROM fn_fee_invoice_class_assign WHERE fn_fee_invoice_id=:fn_fee_invoice_id AND pupilsightProgramID=:pupilsightProgramID GROUP BY pupilsightProgramID';
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
            // echo '<pre>';
            // print_r($values);
            // echo '</pre>';

            echo '<h2>';
            echo __('Edit Invoice Assign');
            echo '</h2>';

            $pupilsightSchoolYearID = '';
            if (isset($_GET['pupilsightSchoolYearID'])) {
                $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
            }
            if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
                $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
                $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
            }
        
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT name FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result = $pdo->executeQuery($data, $sql);
        
            $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
            $resultp = $connection2->query($sqlp);
            $rowdataprog = $resultp->fetchAll();
        
            $program=array();  
            $program2=array();  
            $program1=array(''=>'Select Program');
            foreach ($rowdataprog as $dt) {
                $program2[$dt['pupilsightProgramID']] = $dt['name'];
            }
            $program= $program1 + $program2; 

            $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/invoice_assign_manage_editProcess.php?invoice_id='.$invoice_id.'&pupilsightProgramID='.$pupilsightProgramID);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $form->addHiddenValue('fn_fee_invoice_id', $values['fn_fee_invoice_id']);

            $row = $form->addRow();
                    $row->addLabel('pupilsightProgramID', __('Organisation'));
                    $row->addSelect('pupilsightProgramID')->fromArray($program)->required()->selected($values['pupilsightProgramID']);
        
            $row = $form->addRow();
                $row->addLabel('pupilsightYearGroupID', __('Class'));
                $classchk = array();
                $classchk['pupilsightYearGroupID'] = explode(', ',$values['class']);
            //      echo '<pre>';
            // print_r($classchk);
            // echo '</pre>';

                $row->addCheckboxYearGroup('pupilsightYearGroupID')->required()->addCheckAllNone()->loadFrom($classchk);
        
            // $row = $form->addRow();
            //     $row->addLabel('pupilsightRollGroupID', __('Section'));
            //     $row->addSelectRollGroup('pupilsightRollGroupID', $pupilsightSchoolYearID)->required()->selected($values['pupilsightRollGroupID']);
            
           
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
