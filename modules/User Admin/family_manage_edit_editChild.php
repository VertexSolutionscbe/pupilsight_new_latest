<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/family_manage_edit_editChild.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $urlParams = ['pupilsightFamilyID' => $_GET['pupilsightFamilyID']];
    
    $page->breadcrumbs
        ->add(__('Manage Families'), 'family_manage.php')
        ->add(__('Edit Family'), 'family_manage_edit.php', $urlParams)
        ->add(__('Edit Child'));  

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightFamilyID = $_GET['pupilsightFamilyID'];
    $pupilsightPersonID = $_GET['pupilsightPersonID'];
    $search = $_GET['search'];
    if ($pupilsightPersonID == '' or $pupilsightFamilyID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightFamilyID' => $pupilsightFamilyID, 'pupilsightPersonID' => $pupilsightPersonID);
            $sql = "SELECT * FROM pupilsightPerson, pupilsightFamily, pupilsightFamilyChild WHERE pupilsightFamily.pupilsightFamilyID=pupilsightFamilyChild.pupilsightFamilyID AND pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightFamily.pupilsightFamilyID=:pupilsightFamilyID AND pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID AND (pupilsightPerson.status='Full' OR pupilsightPerson.status='Expected')";
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

            if ($search != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/User Admin/family_manage_edit.php&pupilsightFamilyID=$pupilsightFamilyID&search=$search'>".__('Back').'</a>';
                echo '</div>';
            }

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/family_manage_edit_editChildProcess.php?pupilsightPersonID=$pupilsightPersonID&pupilsightFamilyID=$pupilsightFamilyID&search=$search");

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $form->addRow()->addHeading(__('Edit Child'));

            $row = $form->addRow();
                $row->addLabel('child', __('Childs\'s Name'));
                $row->addTextField('child')->setValue(formatName(htmlPrep($values['title']), htmlPrep($values['officialName']), htmlPrep($values['surname']), 'Parent'))->required()->readonly();

            $row = $form->addRow();
                $row->addLabel('comment', __('Comment'))->description(__('Data displayed in full Student Profile'));
                $row->addTextArea('comment')->setRows(8);

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}
?>
