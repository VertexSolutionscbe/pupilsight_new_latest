<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/family_manage_edit_editAdult.php') == false) {
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
        ->add(__('Edit Adult'));  

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
            $sql = "SELECT * FROM pupilsightPerson, pupilsightFamily, pupilsightFamilyAdult WHERE pupilsightFamily.pupilsightFamilyID=pupilsightFamilyAdult.pupilsightFamilyID AND pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightFamily.pupilsightFamilyID=:pupilsightFamilyID AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID AND (pupilsightPerson.status='Full' OR pupilsightPerson.status='Expected')";
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

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/family_manage_edit_editAdultProcess.php?pupilsightPersonID=$pupilsightPersonID&pupilsightFamilyID=$pupilsightFamilyID&search=$search");

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $form->addRow()->addHeading(__('Edit Adult'));

            $row = $form->addRow();
                $row->addLabel('adult', __('Adult\'s Name'));
                $row->addTextField('adult')->setValue(formatName(htmlPrep($values['title']), htmlPrep($values['preferredName']), htmlPrep($values['surname']), 'Parent'))->required()->readonly();

            $row = $form->addRow();
                $row->addLabel('comment', __('Comment'))->description(__('Data displayed in full Student Profile'));
                $row->addTextArea('comment')->setRows(8);

            $row = $form->addRow();
                $row->addLabel('childDataAccess', __('Data Access?'))->description(__('Access data on family\'s children?'));
                $row->addYesNo('childDataAccess')->required();

            $priorities = array(
                '1' => __('1'),
                '2' => __('2'),
                '3' => __('3')
            );
            $row = $form->addRow();
                $row->addLabel('contactPriority', __('Contact Priority'))->description(__('The order in which school should contact family members.'));
                $row->addSelect('contactPriority')->fromArray($priorities)->required();

            $row = $form->addRow()->addClass('contact');
                $row->addLabel('contactCall', __('Call?'))->description(__('Receive non-emergency phone calls from school?'));
                $row->addYesNo('contactCall')->required();

            $row = $form->addRow()->addClass('contact');
                $row->addLabel('contactSMS', __('SMS?'))->description(__('Receive non-emergency SMS messages from school?'));
                $row->addYesNo('contactSMS')->required();

            $row = $form->addRow()->addClass('contact');
                $row->addLabel('contactEmail', __('Email?'))->description(__('Receive non-emergency emails from school?'));
                $row->addYesNo('contactEmail')->required();

            $row = $form->addRow()->addClass('contact');
                $row->addLabel('contactMail', __('Mail?'))->description(__('Receive postage mail from school?'));
                $row->addYesNo('contactMail')->required();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();

            echo "<script type=\"text/javascript\">
                $(document).ready(function(){
                    $(\"#contactCall\").attr(\"disabled\", \"disabled\");
                    $(\"#contactSMS\").attr(\"disabled\", \"disabled\");
                    $(\"#contactEmail\").attr(\"disabled\", \"disabled\");
                    $(\"#contactMail\").attr(\"disabled\", \"disabled\");
                    $(\"#contactPriority\").change(function(){
                        if ($('#contactPriority').val()==\"1\" ) {
                            $(\"#contactCall\").attr(\"disabled\", \"disabled\");
                            $(\"#contactCall\").val(\"Y\");
                            $(\"#contactSMS\").attr(\"disabled\", \"disabled\");
                            $(\"#contactSMS\").val(\"Y\");
                            $(\"#contactEmail\").attr(\"disabled\", \"disabled\");
                            $(\"#contactEmail\").val(\"Y\");
                            $(\"#contactMail\").attr(\"disabled\", \"disabled\");
                            $(\"#contactMail\").val(\"Y\");
                        }
                        else {
                            $(\"#contactCall\").removeAttr(\"disabled\");
                            $(\"#contactSMS\").removeAttr(\"disabled\");
                            $(\"#contactEmail\").removeAttr(\"disabled\");
                            $(\"#contactMail\").removeAttr(\"disabled\");
                        }
                    });
                    $(\"#contactPriority\").change();
                });
            </script>";

        }
    }
}
?>
